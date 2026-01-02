<?php
// /epistora/config.php
// Global configuration: constants, roles, paths, security settings
// BASE_PATH is already defined in bootstrap.php — do NOT redefine here

declare(strict_types=1);

// Prevent direct access (extra safety)
if (!defined('BASE_PATH')) {
    exit('Direct access not permitted');
}

// -----------------------------
// Site & Security Constants
// -----------------------------
define('SITE_NAME', 'Epistora');
define('SITE_URL', 'http://localhost/epistora'); // Change to your actual URL (use https in production)

define('SESSION_NAME', 'EPISESSID');
define('SESSION_LIFETIME', 604800); // 7 days

// -----------------------------
// User Roles (hierarchical - higher number = more privileges)
// -----------------------------
define('ROLES', [
    'user'     => 10,  // Basic registered user
    'writer'   => 30,  // Can create posts
    'v_writer' => 50,  // Verified writer - auto-approved posts
    'nimda'    => 99   // Full administrator
]);

// -----------------------------
// Directory Paths (based on BASE_PATH - no trailing slash)
// -----------------------------
define('DATA_PATH',          BASE_PATH . '/data');
define('POSTS_INDEX',        DATA_PATH . '/posts.json');
define('USERS_INDEX',        DATA_PATH . '/users.json');
define('POST_CONTENT_DIR',   DATA_PATH . '/post_content');
define('USER_DATA_DIR',      DATA_PATH . '/user_data');

define('CACHE_PATH',         BASE_PATH . '/cache');
define('CACHE_FRAGMENTS',     CACHE_PATH . '/fragments');

define('LOGS_PATH',          BASE_PATH . '/logs');
define('UPLOADS_PATH',       BASE_PATH . '/assets/uploads');

// -----------------------------
// Security Settings
// -----------------------------
define('PASSWORD_ALGO', PASSWORD_ARGON2ID);

define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 minutes

// -----------------------------
// Ensure required directories exist
// -----------------------------
$required_dirs = [
    DATA_PATH,
    POST_CONTENT_DIR,
    USER_DATA_DIR,
    CACHE_PATH,
    CACHE_FRAGMENTS,
    LOGS_PATH,
    UPLOADS_PATH
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}