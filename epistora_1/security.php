<?php
// /epistora/security.php
// Updated: All CSRF functions removed

declare(strict_types=1);

if (!defined('BASE_PATH')) exit;

// ====================== SANITIZATION ======================
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ====================== AUTH HELPERS ======================
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function get_user_role(): int
{
    return $_SESSION['role'] ?? ROLES['user'];
}

function require_role(int $min_role): void
{
    if (!is_logged_in() || get_user_role() < $min_role) {
        http_response_code(403);
        exit('Access denied');
    }
}

// ====================== RATE LIMITING (SESSION-BASED) ======================
function rate_limit(string $action, int $max_attempts = 5, int $window = 900): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_{$action}_{$ip}";

    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => 0];

    if (time() - $attempts['time'] > $window) {
        $attempts = ['count' => 1, 'time' => time()];
    } else {
        $attempts['count']++;
    }

    $_SESSION[$key] = $attempts;

    return $attempts['count'] > $max_attempts;
}

// ====================== SECURITY LOGGING ======================
function security_log(string $event, array $context = []): void
{
    $line = sprintf(
        "[%s] IP:%s User:%s Event:%s Context:%s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SESSION['user_id'] ?? 'guest',
        $event,
        json_encode($context)
    );
    @file_put_contents(LOGS_PATH . '/security.log', $line, FILE_APPEND | LOCK_EX);
}