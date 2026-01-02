<?php
// /epistora/actions/api.php
// Unified AJAX handler for all interactions: like, comment, follow, view

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    exit(json_encode(['error' => 'Invalid CSRF']));
}

$action = $_POST['action'] ?? '';
$post_id = (int)($_POST['post_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if (!is_logged_in() && $action !== 'view') {
    exit(json_encode(['error' => 'Login required']));
}

$post_content_file = POST_CONTENT_DIR . '/' . $post_id . '.json';
$full_post = json_read($post_content_file, true);

if (!$full_post) {
    exit(json_encode(['error' => 'Post not found']));
}

// === VIEW TRACKING ===
if ($action === 'view' && $user_id > 0) {
    if (!in_array($user_id, $full_post['viewers'] ?? [])) {
        $full_post['viewers'][] = $user_id;

        // Update views_count in index
        json_update(POSTS_INDEX, function($index) use ($post_id) {
            if (isset($index[$post_id])) {
                $index[$post_id]['views_count']++;
            }
            return $index;
        });

        json_write($post_content_file, $full_post);
    }
    exit(json_encode(['success' => true]));
}

// Require login for all other actions
if (!is_logged_in()) {
    exit(json_encode(['error' => 'Authentication required']));
}

// === LIKE / REACTION ===
if ($action === 'like') {
    $reactions = $full_post['reactions'] ?? [];

    if (isset($reactions[$user_id])) {
        unset($reactions[$user_id]); // Unlike
        $liked = false;
    } else {
        $reactions[$user_id] = 'like';
        $liked = true;
    }

    $full_post['reactions'] = $reactions;

    $new_count = count($reactions);

    // Update both files atomically
    json_write($post_content_file, $full_post);
    json_update(POSTS_INDEX, function($index) use ($post_id, $new_count) {
        if (isset($index[$post_id])) {
            $index[$post_id]['reactions_count'] = $new_count;
        }
        return $index;
    });

    cache_invalidate('homepage');
    cache_invalidate('feed_' . $user_id);

    exit(json_encode([
        'success' => true,
        'liked' => $liked,
        'count' => $new_count
    ]));
}

// === COMMENT ===
if ($action === 'comment') {
    $text = trim($_POST['text'] ?? '');
    if (empty($text)) {
        exit(json_encode(['error' => 'Comment cannot be empty']));
    }

    $comments = $full_post['comments'] ?? [];
    $comment_id = count($comments) + 1;

    $comments[] = [
        'id' => $comment_id,
        'user_id' => $user_id,
        'username' => $_SESSION['username'],
        'text' => $text,
        'timestamp' => time(),
        'replies' => []
    ];

    $full_post['comments'] = $comments;

    $new_count = count($comments);

    json_write($post_content_file, $full_post);
    json_update(POSTS_INDEX, function($index) use ($post_id, $new_count) {
        if (isset($index[$post_id])) {
            $index[$post_id]['comments_count'] = $new_count;
        }
        return $index;
    });

    cache_invalidate('homepage');

    // Add notification to post author (if not self)
    if ($full_post['author_id'] != $user_id) {
        $author_vault_file = USER_DATA_DIR . '/' . $full_post['author_id'] . '.json';
        json_update($author_vault_file, function($vault) use ($user_id, $_SESSION, $post_id) {
            $vault['notifications'][] = [
                'title' => 'New comment',
                'message' => $_SESSION['username'] . ' commented on your post',
                'timestamp' => time(),
                'read' => false,
                'link' => '/post/view/?id=' . $post_id
            ];
            return $vault;
        });
    }

    exit(json_encode([
        'success' => true,
        'count' => $new_count,
        'comment_html' => '<div class="comment"><strong>' . htmlspecialchars($_SESSION['username']) . ':</strong> ' . htmlspecialchars($text) . '</div>'
    ]));
}

// === FOLLOW ===
if ($action === 'follow') {
    $target_user_id = (int)($_POST['target_user_id'] ?? 0);
    if ($target_user_id <= 0 || $target_user_id == $user_id) {
        exit(json_encode(['error' => 'Invalid user']));
    }

    $vault_file = USER_DATA_DIR . '/' . $user_id . '.json';

    json_update($vault_file, function($vault) use ($target_user_id) {
        $follows = $vault['follows'] ?? [];
        if (($key = array_search($target_user_id, $follows)) !== false) {
            unset($follows[$key]);
            $following = false;
        } else {
            $follows[] = $target_user_id;
            $following = true;
        }
        $vault['follows'] = array_values($follows);
        return $vault;
    });

    $_SESSION['vault']['follows'] = json_read($vault_file, true)['follows'] ?? [];

    cache_invalidate('feed_' . $user_id);

    exit(json_encode([
        'success' => true,
        'following' => $following ?? true
    ]));
}

exit(json_encode(['error' => 'Invalid action']));