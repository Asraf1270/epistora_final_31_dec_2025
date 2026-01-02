<?php
session_start();
require_once '../config.php';
require_once '../db_engine.php';

header('Content-Type: application/json');

// 1. Validation
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$post_id = $_POST['post_id'] ?? '';
$type    = $_POST['type'] ?? 'love'; // Defaults to love

if (empty($post_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing Post ID']);
    exit;
}

// 2. Update Post Content File (Source of Truth)
$post_file = "post_content/$post_id.json";
$post_data = DBEngine::readJSON($post_file);

if (!$post_data) {
    echo json_encode(['success' => false, 'error' => 'Post data not found']);
    exit;
}

// Ensure nested reactions array exists
if (!isset($post_data['reactions']) || !is_array($post_data['reactions'])) {
    $post_data['reactions'] = [];
}

// Increment specific emoji type
$new_count = ($post_data['reactions'][$type] ?? 0) + 1;
$post_data['reactions'][$type] = $new_count;

if (DBEngine::writeJSON($post_file, $post_data)) {
    
    // 3. Sync to Global Index (posts.json) for Feed display
    $posts_index = DBEngine::readJSON("posts.json") ?? [];
    foreach ($posts_index as &$p) {
        if ($p['post_id'] === $post_id) {
            $p['reactions'][$type] = $new_count;
            break;
        }
    }
    DBEngine::writeJSON("posts.json", $posts_index);

    // 4. Trigger Notification to Author
    if ($_SESSION['user_id'] !== $post_data['author_id']) {
        DBEngine::pushNotification(
            $post_data['author_id'], 
            'reaction', 
            $_SESSION['user_name'], 
            $post_id
        );
    }

    echo json_encode([
        'success' => true, 
        'new_count' => $new_count, 
        'type' => $type
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save reaction']);
}