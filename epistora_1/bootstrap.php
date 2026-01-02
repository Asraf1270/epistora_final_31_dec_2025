<?php
// /epistora/bootstrap.php
// Updated: CSRF system completely removed

declare(strict_types=1);

// ====================== FORCE WRITABLE SESSION DIRECTORY ======================
$sessions_dir = __DIR__ . '/cache/sessions';

if (!is_dir($sessions_dir)) {
    @mkdir($sessions_dir, 0777, true);
}

if (is_dir($sessions_dir) && is_writable($sessions_dir)) {
    ini_set('session.save_path', $sessions_dir);
}

ini_set('session.gc_maxlifetime', '604800');
ini_set('session.cookie_lifetime', '604800');
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// ====================== SESSION HARDENING ======================
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');

session_name('EPISESSID');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session regeneration
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

if (isset($_SESSION['last_regen']) && $_SESSION['last_regen'] < time() - 1800) {
    session_regenerate_id(true);
}
$_SESSION['last_regen'] = time();

// ====================== DEFINE BASE_PATH & LOAD CONFIG ======================
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config.php';

// ====================== CORE MODULES ======================
require_once BASE_PATH . '/db_engine.php';
require_once BASE_PATH . '/security.php';  // Now without CSRF
require_once BASE_PATH . '/cache.php';

// ====================== ERROR HANDLING (PREVENT 500) ======================
set_exception_handler(function ($e) {
    error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<h1>500 Server Error</h1><p>Please try again later.</p>';
    exit;
});