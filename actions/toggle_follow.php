<?php
session_start();
require_once '../config.php';
require_once '../db_engine.php';

header('Content-Type: application/json');

$current_user_id = $_SESSION['user_id'] ?? null;
$target_writer_id = $_POST['writer_id'] ?? '';

if (!$current_user_id || $current_user_id === $target_writer_id) {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// 1. Update Current User's Vault (Following List)
$my_vault_path = "user_data/$current_user_id.json";
$my_vault = DBEngine::readJSON($my_vault_path);

if (!in_array($target_writer_id, $my_vault['following'])) {
    $my_vault['following'][] = $target_writer_id;
    $action = 'followed';
} else {
    $my_vault['following'] = array_diff($my_vault['following'], [$target_writer_id]);
    $action = 'unfollowed';
}
DBEngine::writeJSON($my_vault_path, $my_vault);

// 2. Update Target Writer's Vault (Followers List)
$target_vault_path = "user_data/$target_writer_id.json";
$target_vault = DBEngine::readJSON($target_vault_path);

if ($target_vault) {
    // Ensure the 'followers' key exists in the vault
    if (!isset($target_vault['followers'])) $target_vault['followers'] = [];
    
    if ($action === 'followed') {
        $target_vault['followers'][] = $current_user_id;
    } else {
        $target_vault['followers'] = array_diff($target_vault['followers'], [$current_user_id]);
    }
    DBEngine::writeJSON($target_vault_path, $target_vault);
}

echo json_encode(['success' => true, 'action' => $action]);