<?php
// /epistora/security/firewall.php
// Simple but effective IP and pattern-based firewall
// Blocks known bad actors, bots, scanners

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    exit('Direct access not permitted');
}

class Firewall
{
    private array $blocked_ips = [];
    private array $blocked_patterns = [];
    private string $block_file;

    public function __construct()
    {
        $this->block_file = LOGS_PATH . '/blocked_ips.json';
        $this->loadBlocks();

        // Common bad patterns (User-Agent, URL paths)
        $this->blocked_patterns = [
            '/etc/passwd',
            '/wp-admin',
            '/wp-login',
            '/phpmyadmin',
            '/.env',
            '/adminer',
            'sqlmap',
            'nessus',
            'nikto',
            'dirbuster',
            'wpscan',
            'union select',
            'information_schema',
            'eval(',
            'base64_decode(',
            '$_GET[',
            '$_POST[',
            'shell_exec'
        ];
    }

    private function loadBlocks(): void
    {
        if (file_exists($this->block_file)) {
            $data = json_decode(file_get_contents($this->block_file), true);
            if (is_array($data)) {
                $this->blocked_ips = $data;
            }
        }
    }

    private function saveBlocks(): void
    {
        file_put_contents($this->block_file, json_encode($this->blocked_ips, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Main check - call early in bootstrap or .htaccess wrapper
     */
    public function protect(): void
    {
        $ip = $this->getClientIP();

        // Permanent block list
        if (in_array($ip, $this->blocked_ips)) {
            $this->blockResponse('Blocked by firewall');
        }

        // Pattern scanning
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $input = $uri . $ua . http_build_query($_GET) . http_build_query($_POST);

        foreach ($this->blocked_patterns as $pattern) {
            if (stripos($input, $pattern) !== false) {
                $this->blockAndLog($ip, 'pattern_match', $pattern);
            }
        }

        // Aggressive scanners (common vuln probes)
        if (preg_match('/(select.*from|union.*select|information_schema|<\?php)/i', $input)) {
            $this->blockAndLog($ip, 'sql_injection_attempt');
        }
    }

    /**
     * Block IP permanently
     */
    public function blockIP(string $ip, string $reason = 'manual'): void
    {
        $client_ip = $this->getClientIP();
        if (!in_array($ip, $this->blocked_ips)) {
            $this->blocked_ips[] = $ip;
            $this->saveBlocks();

            security_log('firewall_block', [
                'blocked_ip' => $ip,
                'reason' => $reason,
                'triggered_by' => $client_ip
            ]);
        }
    }

    /**
     * Unblock IP (admin only)
     */
    public function unblockIP(string $ip): void
    {
        $this->blocked_ips = array_diff($this->blocked_ips, [$ip]);
        $this->saveBlocks();
    }

    private function blockAndLog(string $ip, string $type, string $detail = ''): void
    {
        $this->blockIP($ip, $type);
        security_log('firewall_trigger', [
            'ip' => $ip,
            'type' => $type,
            'detail' => $detail,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        $this->blockResponse('Access denied');
    }

    private function blockResponse(string $message): void
    {
        http_response_code(403);
        exit('<h1>403 Forbidden</h1><p>' . htmlspecialchars($message) . '</p>');
    }

    private function getClientIP(): string
    {
        // Same as RateLimiter
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    // Get list of blocked IPs (for admin panel)
    public function getBlockedIPs(): array
    {
        return $this->blocked_ips;
    }
}

// Auto-protect on include (optional - remove if you want manual call)
if (php_sapi_name() === 'apache2handler' || php_sapi_name() === 'cgi-fcgi') {
    $firewall = new Firewall();
    $firewall->protect();
}