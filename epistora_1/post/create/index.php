<?php
// /epistora/post/create/index.php
// Post creation form – restricted to writers and above

declare(strict_types=1);

require_once '../../bootstrap.php';

// Enforce minimum role: writer
require_role(ROLES['writer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit(json_encode(['error' => 'Invalid CSRF token']));
    }

    $title   = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Will be sanitized on output only
    $tags    = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    if (empty($title) || empty($content)) {
        exit(json_encode(['error' => 'Title and content are required']));
    }

    // Load posts index
    $posts_index = json_read(POSTS_INDEX, true);

    // Generate new post ID
    $post_id = get_next_id(POSTS_INDEX);

    // Determine status: nimda/v_writer auto-approve, others pending
    $is_admin = get_user_role() >= ROLES['nimda'];
    $is_verified = get_user_role() >= ROLES['v_writer'];
    $status = ($is_admin || $is_verified) ? 'published' : 'pending';

    // Create metadata entry
    $posts_index[$post_id] = [
        'title'       => $title,
        'excerpt'     => $excerpt,
        'author_id'   => $_SESSION['user_id'],
        'author_name' => $_SESSION['username'],
        'created_at'  => time(),
        'updated_at'  => time(),
        'status'      => $status,        // published | pending
        'tags'        => $tags,
        'likes'       => 0,
        'comments'    => 0,
        'views'       => 0
    ];

    // Save full content separately
    $content_file = POST_CONTENT_DIR . '/' . $post_id . '.json';
    $full_content = [
        'title'   => $title,
        'content' => $content,           // Raw HTML/markup from editor
        'tags'    => $tags
    ];

    // Atomic writes
    $success = json_write(POSTS_INDEX, $posts_index) &&
               json_write($content_file, $full_content);

    if ($success) {
        // Invalidate homepage/feed caches
        cache_invalidate('homepage');
        cache_invalidate('feed_' . $_SESSION['user_id']);

        security_log('post_created', ['post_id' => $post_id, 'status' => $status]);

        echo json_encode([
            'success' => true,
            'message' => $status === 'published' ? 'Post published!' : 'Post submitted for approval.',
            'redirect' => '/post/view/?id=' . $post_id
        ]);
    } else {
        echo json_encode(['error' => 'Failed to save post']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <h1>Create New Post</h1>
    <form id="create-post-form" method="POST">
        <?= csrf_field() ?>
        <label>Title:<br>
            <input type="text" name="title" required style="width:100%;">
        </label><br><br>

        <label>Excerpt (short summary):<br>
            <textarea name="excerpt" rows="3" style="width:100%;"></textarea>
        </label><br><br>

        <label>Content (HTML/Markdown allowed):<br>
            <textarea name="content" rows="15" required style="width:100%;"></textarea>
        </label><br><br>

        <label>Tags (comma-separated):<br>
            <input type="text" name="tags" placeholder="php, security, webdev">
        </label><br><br>

        <button type="submit">Submit Post</button>
    </form>

    <script>
        document.getElementById('create-post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('', {method: 'POST', body: fd});
            const data = await res.json();
            alert(data.message || data.error);
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }
        });
    </script>
</body>
</html>