<?php
/**
 * Epistora Core Configuration v1.1
 * Integrated with Maintenance Mode & Session Security
 */

// 1. ABSOLUTE PATHING
define('BASE_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOT_PATH', BASE_DIR);
define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);

// Sub-Data Directories
define('USER_DATA_PATH',    DATA_PATH . 'user_data' . DIRECTORY_SEPARATOR);
define('POST_CONTENT_PATH', DATA_PATH . 'post_content' . DIRECTORY_SEPARATOR);

// 2. USER ROLE DEFINITIONS
define('ROLE_USER',     'user');     
define('ROLE_WRITER',   'writer');    
define('ROLE_V_WRITER', 'v_writer');  
define('ROLE_ADMIN',     'admin');     

// 3. PERMISSIONS HIERARCHY
$GLOBALS['ROLES_HIERARCHY'] = [
    ROLE_USER     => 0,
    ROLE_WRITER   => 1,
    ROLE_V_WRITER => 2,
    ROLE_ADMIN    => 3
];

// 4. SYSTEM DEFAULTS
define('APP_NAME', 'Epistora');
define('APP_VERSION', '1.1.0');

// 5. SECURITY & SESSION START
if (session_status() === PHP_SESSION_NONE) {
    // Protect cookies from Javascript access
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// 6. MAINTENANCE MODE INTERCEPTOR
/**
 * Automatically redirects non-admins to maintenance page if active
 */
function handleMaintenanceMode() {
    $state_file = DATA_PATH . "system_state.json";
    
    if (file_exists($state_file)) {
        $state = json_decode(file_get_contents($state_file), true);
        
        if (isset($state['maintenance_mode']) && $state['maintenance_mode'] === true) {
            // Check if current user is admin via hierarchy or role
            $user_role = $_SESSION['role'] ?? ROLE_USER;
            $is_admin = ($user_role === ROLE_ADMIN);
            
            if (!$is_admin) {
                $m_file = ROOT_PATH . "maintenance.php";
                if (file_exists($m_file)) {
                    include $m_file;
                    exit;
                } else {
                    die("<h1>Epistora is under maintenance.</h1><p>We'll be back shortly.</p>");
                }
            }
        }
    }
}
handleMaintenanceMode();

// 7. DEFAULTS & ERRORS
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load DB Engine automatically
require_once ROOT_PATH . 'db_engine.php';