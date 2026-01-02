<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

// 1. Security Check: Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}

$user_id = $_SESSION['user_id'];
$vault_path = "user_data/$user_id.json";
$user_data = DBEngine::readJSON($vault_path);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email     = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $bio       = sanitize($_POST['bio']);
    $new_pass  = $_POST['new_password'] ?? '';

    // 2. Process Data Updates
    $user_data['full_name'] = $full_name;
    $user_data['email']     = $email;
    $user_data['bio']       = $bio;

    // 3. Sensitive Action: Password Change
    $pass_changed = false;
    if (!empty($new_pass)) {
        if (strlen($new_pass) < 8) {
            header("Location: index.php?error=pass_too_short");
            exit;
        }
        $user_data['password'] = password_hash($new_pass, PASSWORD_DEFAULT);
        $pass_changed = true;
    }

    // 4. Commit to JSON Vault
    if (DBEngine::writeJSON($vault_path, $user_data)) {
        // Update Session in case name/email changed
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_email'] = $email;

        // 5. Mandatory System Logging
        $log_details = "Updated profile info." . ($pass_changed ? " (Password also changed)" : "");
        DBEngine::logAction($user_id, $full_name, 'USER_UPDATE', $log_details);

        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=write_failed");
    }
    exit;
}