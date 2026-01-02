<?php
// /epistora/bootstrap.php
// Bootstrap: load config first, then protect

declare(strict_types=1);

// Step 1: Define BASE_PATH immediately using __DIR__
define('BASE_PATH', __DIR__);  // This sets /full/path/to/epistora

// Step 2: Now load config (which also uses BASE_PATH)
require_once BASE_PATH . '/config.php';

// Step 3: Security check - now safe because BASE_PATH is already defined
if (realpath($_SERVER['SCRIPT_FILENAME']) !== false && 
    strpos(realpath($_SERVER['SCRIPT_FILENAME']), BASE_PATH) !== 0) {
    exit('Direct access not permitted');
}

// Optional: Prevent including bootstrap.php directly via URL
if (basename($_SERVER['SCRIPT_FILENAME']) === 'bootstrap.php') {
    http_response_code(403);
    exit('Direct access not permitted');
}

// Continue with session, includes, etc.
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');

session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => parse_url(SITE_URL, PHP_URL_HOST),
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Session regeneration logic...
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

if (isset($_SESSION['last_regen']) && $_SESSION['last_regen'] < time() - 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
} elseif (!isset($_SESSION['last_regen'])) {
    $_SESSION['last_regen'] = time();
}

// Include core modules
require_once BASE_PATH . '/db_engine.php';
require_once BASE_PATH . '/security.php';
require_once BASE_PATH . '/cache.php';