<?php
// /epistora/post/edit/index.php
// Edit existing post – author or nimda only

declare(strict_types=1);

require_once '../../bootstrap.php';

$post_id = (int)($_GET['id'] ?? 0);
if ($post_id <= 0) exit('Invalid post');

$posts_index = json_read(POSTS_INDEX, true);
if (!isset($posts_index[$post_id])) exit('Post not found');

$post_meta = $posts_index[$post_id];

// Permission check
if (!is_logged_in() || 
    ($_SESSION['user_id'] != $post_meta['author_id'] && get_user_role() < ROLES['nimda'])) {
    http_response_code(403);
    exit('Access denied');
}

// Load content
$content_file = POST_CONTENT_DIR . '/' . $post_id . '.json';
$full_post = json_read($content_file, true);
if (!$full_post) exit('Content missing');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        exit(json_encode(['error' => 'Invalid CSRF']));
    }

    $title   = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $tags    = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

    if (empty($title) || empty($content)) {
        exit(json_encode(['error' => 'Title and content required']));
    }

    // Update metadata
    $posts_index[$post_id]['title']      = $title;
    $posts_index[$post_id]['excerpt']    = $excerpt;
    $posts_index[$post_id]['tags']       = $tags;
    $posts_index[$post_id]['updated_at'] = time();

    // If nimda editing a pending post, can approve
    if (get_user_role() >= ROLES['nimda'] && isset($_POST['approve'])) {
        $posts_index[$post_id]['status'] = 'published';
    }

    // Update full content
    $full_post = [
        'title'   => $title,
        'content' => $content,
        'tags'    => $tags
    ];

    $success = json_write(POSTS_INDEX, $posts_index) &&
               json_write($content_file, $full_post);

    if ($success) {
        cache_invalidate('homepage');
        if (is_logged_in()) cache_invalidate('feed_' . $_SESSION['user_id']);

        security_log('post_edited', ['post_id' => $post_id]);

        echo json_encode(['success' => true, 'redirect' => "/post/view/?id=$post_id"]);
    } else {
        echo json_encode(['error' => 'Save failed']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post - <?= SITE_NAME ?></title>
</head>
<body>
    <h1>Edit Post</h1>
    <form id="edit-post-form" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $post_id ?>">

        <label>Title:<br>
            <input type="text" name="title" value="<?= htmlspecialchars($full_post['title']) ?>" required style="width:100%;">
        </label><br><br>

        <label>Excerpt:<br>
            <textarea name="excerpt" rows="3" style="width:100%;"><?= htmlspecialchars($post_meta['excerpt'] ?? '') ?></textarea>
        </label><br><br>

        <label>Content:<br>
            <textarea name="content" rows="15" required style="width:100%;"><?= htmlspecialchars($full_post['content']) ?></textarea>
        </label><br><br>

        <label>Tags:<br>
            <input type="text" name="tags" value="<?= htmlspecialchars(implode(', ', $full_post['tags'] ?? [])) ?>">
        </label><br><br>

        <?php if (get_user_role() >= ROLES['nimda'] && $post_meta['status'] === 'pending'): ?>
            <label><input type="checkbox" name="approve" value="1"> Approve and publish now</label><br><br>
        <?php endif; ?>

        <button type="submit">Save Changes</button>
    </form>

    <script>
        document.getElementById('edit-post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('', {method: 'POST', body: fd});
            const data = await res.json();
            alert(data.success ? 'Saved!' : data.error);
            if (data.redirect) location.href = data.redirect;
        });
    </script>
</body>
</html>