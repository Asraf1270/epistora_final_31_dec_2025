<?php
session_start();
header('Content-Type: application/json');

// 1. CONFIGURATION & PATHS
$post_id = $_REQUEST['post_id'] ?? '';
$postFile = "../../data/post_data/$post_id.json";
$indexFile = "../../data/posts.json";
$usersFile = "../../data/users.json";

// Validation
if (!$post_id || !file_exists($postFile)) {
    echo json_encode(['success' => false, 'message' => 'Post data not found.']);
    exit;
}

// 2. LOAD DATA
$postData   = json_decode(file_get_contents($postFile), true);
$indexData  = json_decode(file_get_contents($indexFile), true);
$allUsers   = json_decode(file_get_contents($usersFile), true);

// ---------------------------------------------------------
// ACTION: STREAM (GET) - For Real-Time Updates
// ---------------------------------------------------------
if (($_GET['action'] ?? '') === 'stream') {
    // Get Author Details
    $auth_id = $postData['meta']['author_id'] ?? '';
    $author  = $allUsers[$auth_id] ?? ['username' => 'Unknown', 'role' => 'user'];
    
    // Count multi-reactions
    $counts = ['like' => 0, 'love' => 0, 'insight' => 0, 'wow' => 0];
    if (isset($postData['reactions_list'])) {
        foreach ($postData['reactions_list'] as $r) {
            $type = $r['type'] ?? 'like';
            if (isset($counts[$type])) $counts[$type]++;
        }
    }

    echo json_encode([
        'success'     => true,
        'author'      => htmlspecialchars($author['username']),
        'is_verified' => ($author['role'] === 'v_writer'),
        'date'        => date("M d, Y", $postData['history'][0]['time'] ?? time()),
        'views'       => $indexData[$post_id]['views'] ?? 0,
        'body'        => nl2br($postData['body'] ?? ''),
        'reactions'   => $counts,
        'comments'    => $postData['comments'] ?? []
    ]);
    exit;
}

// ---------------------------------------------------------
// ACTION: UPDATES (POST) - Reactions, Comments, Replies
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Auth required']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $u_id   = $_SESSION['user_id'];
    $u_name = $_SESSION['username'];

    // --- CASE A: REACTION ---
    if ($action === 'react') {
        $type = $_POST['type'] ?? 'like';
        if (!isset($postData['reactions_list'])) $postData['reactions_list'] = [];
        
        $postData['reactions_list'][$u_id] = [
            'username' => $u_name,
            'type'     => $type,
            'time'     => time()
        ];
        $indexData[$post_id]['reactions'] = count($postData['reactions_list']);
    }

    // --- CASE B: COMMENT ---
    if ($action === 'comment') {
        $text = trim($_POST['text'] ?? '');
        if ($text === '') { echo json_encode(['success'=>false, 'message'=>'Empty comment']); exit; }

        $newComment = [
            "comment_id" => "COM" . uniqid(),
            "user_id"    => $u_id,
            "username"   => $u_name,
            "text"       => htmlspecialchars($text),
            "time"       => time(),
            "replies"    => []
        ];
        
        if (!isset($postData['comments'])) $postData['comments'] = [];
        array_unshift($postData['comments'], $newComment);
        $indexData[$post_id]['comments'] = count($postData['comments']);
    }

    // --- CASE C: REPLY ---
    if ($action === 'reply') {
        $parent_id  = $_POST['parent_id'] ?? '';
        $reply_text = trim($_POST['text'] ?? '');
        
        $newReply = [
            "reply_id" => "REP" . uniqid(),
            "user_id"  => $u_id,
            "username" => $u_name,
            "text"     => htmlspecialchars($reply_text),
            "time"     => time()
        ];

        foreach ($postData['comments'] as &$c) {
            if ($c['comment_id'] === $parent_id) {
                $c['replies'][] = $newReply;
                break;
            }
        }
    }

    // 3. SAVE ALL DATA
    $s1 = file_put_contents($postFile, json_encode($postData, JSON_PRETTY_PRINT));
    $s2 = file_put_contents($indexFile, json_encode($indexData, JSON_PRETTY_PRINT));

    if ($s1 && $s2) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Permission error: could not save JSON files.']);
    }
    exit;
}