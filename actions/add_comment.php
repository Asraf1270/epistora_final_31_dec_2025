<?php
session_start();
require_once '../config.php';
require_once '../db_engine.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id   = $_POST['post_id'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null; // Null for top-level, ID for replies
    $text      = strip_tags(trim($_POST['text']));

    if (empty($text) || empty($post_id)) {
        die("Invalid input.");
    }

    // 2. Load Post Data
    $filename = "post_content/$post_id.json";
    $post = DBEngine::readJSON($filename);

    if (!$post) {
        die("Post not found.");
    }

    // 3. Construct Comment Object
    $new_comment = [
        "comment_id" => uniqid('c_'),
        "user_id"    => $_SESSION['user_id'],
        "user_name"  => $_SESSION['user_name'],
        "text"       => $text,
        "date"       => date('Y-m-d H:i'),
        "replies"    => []
    ];

    // 4. Append Comment (Directly or Recursively)
    if (!$parent_id) {
        // Top-level comment
        if (!isset($post['comments'])) $post['comments'] = [];
        $post['comments'][] = $new_comment;
    } else {
        // Nested Reply Logic
        function findAndReply(&$comments, $target_id, $new_node) {
            foreach ($comments as &$c) {
                if ($c['comment_id'] === $target_id) {
                    $c['replies'][] = $new_node;
                    return true;
                }
                if (!empty($c['replies'])) {
                    if (findAndReply($c['replies'], $target_id, $new_node)) return true;
                }
            }
            return false;
        }
        findAndReply($post['comments'], $parent_id, $new_comment);
    }

    // 5. Save and Notify
    if (DBEngine::writeJSON($filename, $post)) {
        // Send notification to author if they aren't the one commenting
        if ($_SESSION['user_id'] !== $post['author_id']) {
            DBEngine::pushNotification(
                $post['author_id'], 
                'comment', 
                $_SESSION['user_name'], 
                $post_id
            );
        }
        
        // Redirect back to article
        header("Location: ../post/view/?id=$post_id");
        exit;
    }
}