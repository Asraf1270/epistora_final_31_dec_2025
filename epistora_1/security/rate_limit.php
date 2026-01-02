<?php
// /epistora/security/rate_limit.php
// Advanced IP-based rate limiting using filesystem storage
// Supports multiple actions (login, register, comment, api, etc.)
// More persistent and accurate than session-only

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    exit('Direct access not permitted');
}

class RateLimiter
{
    private string $storage_dir;
    private int $default_window = 900; // 15 minutes

    public function __construct()
    {
        $this->storage_dir = LOGS_PATH . '/rate_limits';
        if (!is_dir($this->storage_dir)) {
            mkdir($this->storage_dir, 0755, true);
        }
    }

    /**
     * Check and record a request
     * @param string $action e.g., 'login', 'register', 'comment', 'api'
     * @param int $max_attempts Maximum allowed in window
     * @param int $window_seconds Time window in seconds
     * @return bool true if limited (blocked), false if allowed
     */
    public function isLimited(string $action, int $max_attempts = 10, int $window_seconds = null): bool
    {
        $ip = $this->getClientIP();
        $window_seconds = $window_seconds ?: $this->default_window;

        $file = $this->storage_dir . '/' . md5($ip . $action) . '.json';

        $data = ['attempts' => [], 'blocked_until' => 0];

        if (file_exists($file)) {
            $content = json_decode(file_get_contents($file), true);
            if (is_array($content)) {
                $data = $content;
            }
        }

        $now = time();

        // Check if currently blocked
        if ($data['blocked_until'] > $now) {
            return true;
        }

        // Clean old attempts
        $data['attempts'] = array_filter($data['attempts'], fn($ts) => $ts > $now - $window_seconds);

        // Add current attempt
        $data['attempts'][] = $now;

        // Check limit
        if (count($data['attempts']) > $max_attempts) {
            $data['blocked_until'] = $now + $window_seconds * 2; // Double block time
            security_log('rate_limit_exceeded', [
                'action' => $action,
                'ip' => $ip,
                'attempts' => count($data['attempts']),
                'blocked_until' => date('Y-m-d H:i:s', $data['blocked_until'])
            ]);
        }

        // Save back
        file_put_contents($file, json_encode($data), LOCK_EX);

        return count($data['attempts']) > $max_attempts;
    }

    /**
     * Get real client IP (handles proxies)
     */
    private function getClientIP(): string
    {
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

    /**
     * Reset limit for specific action + IP (used by admin)
     */
    public function reset(string $action, string $ip): void
    {
        $file = $this->storage_dir . '/' . md5($ip . $action) . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

// Global instance helper
function rate_limit_check(string $action, int $max = 10, int $window = null): bool
{
    static $limiter = null;
    if ($limiter === null) {
        $limiter = new RateLimiter();
    }
    return $limiter->isLimited($action, $max, $window);
}

// Specific common helpers
function rate_limit_login(): bool    { return rate_limit_check('login', RATE_LIMIT_LOGIN_ATTEMPTS, RATE_LIMIT_WINDOW); }
function rate_limit_register(): bool { return rate_limit_check('register', 8, 3600); }
function rate_limit_comment(): bool { return rate_limit_check('comment', 15, 300); }
function rate_limit_api(): bool     { return rate_limit_check('api', 100, 60); }