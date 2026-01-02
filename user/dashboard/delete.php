<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

// 1. Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$post_id = $_GET['id'] ?? '';
$post_file = "post_content/$post_id.json";

// 2. Load the post to verify ownership
$post_data = DBEngine::readJSON($post_file);

if (!$post_data) {
    die("Error: Post not found.");
}

// 3. Authorization Check: Only the author or an Admin can delete
if ($post_data['author_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== ROLE_ADMIN) {
    die("Access Denied: You cannot delete content you do not own.");
}

// 4. Perform Deletion
// A. Remove the detailed content file
if (file_exists(DATA_PATH . $post_file)) {
    unlink(DATA_PATH . $post_file);
}

// B. Remove from Global Index (posts.json)
$index = DBEngine::readJSON("posts.json") ?? [];
$updated_index = array_filter($index, function($p) use ($post_id) {
    return $p['post_id'] !== $post_id;
});

// Re-index the array keys and save
DBEngine::writeJSON("posts.json", array_values($updated_index));

// 5. Success Redirect
header("Location: index.php?deleted=1");
exit;