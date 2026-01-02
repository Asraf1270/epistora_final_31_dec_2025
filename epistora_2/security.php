<?php
// /epistora/security.php
// Security helpers: CSRF, sanitization, rate limiting, auth checks

declare(strict_types=1);

if (!defined('BASE_PATH')) exit;

/**
 * Generate and store CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from POST/GET
 */
function csrf_validate(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Output CSRF hidden input
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Sanitize user input
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Get current user role (default: user if not set)
 */
function get_user_role(): int
{
    return $_SESSION['role'] ?? ROLES['user'];
}

/**
 * Require minimum role
 */
function require_role(int $min_role): void
{
    if (!is_logged_in() || get_user_role() < $min_role) {
        http_response_code(403);
        exit('Access denied');
    }
}

/**
 * Simple IP-based rate limiter (for login, registration, etc.)
 */
function rate_limit(string $action, int $max_attempts = RATE_LIMIT_LOGIN_ATTEMPTS, int $window = RATE_LIMIT_WINDOW): bool
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

/**
 * Log security event
 */
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
    file_put_contents(LOGS_PATH . '/security.log', $line, FILE_APPEND | LOCK_EX);
}