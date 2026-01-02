<?php
// /epistora/post/view/index.php
// Full post view – accessible to all, respects status

declare(strict_types=1);

require_once '../../bootstrap.php';

$post_id = (int)($_GET['id'] ?? 0);
if ($post_id <= 0) {
    http_response_code(404);
    exit('Post not found');
}

// Load metadata
$posts_index = json_read(POSTS_INDEX, true);
if (!isset($posts_index[$post_id])) {
    http_response_code(404);
    exit('Post not found');
}

$post_meta = $posts_index[$post_id];

// Status check: pending posts only visible to author or nimda
if ($post_meta['status'] === 'pending') {
    if (!is_logged_in() || 
        ($_SESSION['user_id'] != $post_meta['author_id'] && get_user_role() < ROLES['nimda'])) {
        http_response_code(404);
        exit('Post not found');
    }
}

// Load full content
$content_file = POST_CONTENT_DIR . '/' . $post_id . '.json';
$full_post = json_read($content_file, true);

if (!$full_post) {
    http_response_code(500);
    exit('Content missing');
}

// Increment view count (simple, no dedup)
json_update(POSTS_INDEX, function($data) use ($post_id) {
    if (isset($data[$post_id])) {
        $data[$post_id]['views']++;
        $data[$post_id]['updated_at'] = time();
    }
    return $data;
});

// Invalidate caches if needed
cache_invalidate('homepage');
if (is_logged_in()) {
    cache_invalidate('feed_' . $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($full_post['title']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <article>
        <h1><?= htmlspecialchars($full_post['title']) ?></h1>
        <p><small>By <?= htmlspecialchars($post_meta['author_name']) ?> 
            | <?= date('F j, Y', $post_meta['created_at']) ?>
            | Views: <?= $post_meta['views'] ?>
            <?php if ($post_meta['status'] === 'pending'): ?>
                <span style="color:orange;"> (Pending Approval)</span>
            <?php endif; ?>
        </small></p>

        <div class="post-content">
            <?= $full_post['content'] // Already trusted HTML from editor ?>
        </div>

        <?php if (!empty($full_post['tags'])): ?>
            <p>Tags: <?= htmlspecialchars(implode(', ', $full_post['tags'])) ?></p>
        <?php endif; ?>

        <!-- Interaction buttons (AJAX) -->
        <div id="interactions">
            <button data-action="like" data-post="<?= $post_id ?>">Like (<?= $post_meta['likes'] ?>)</button>
            <button data-action="bookmark" data-post="<?= $post_id ?>">Bookmark</button>
        </div>

        <?php if (is_logged_in() && 
                  ($_SESSION['user_id'] == $post_meta['author_id'] || get_user_role() >= ROLES['nimda'])): ?>
            <p><a href="/post/edit/?id=<?= $post_id ?>">Edit Post</a></p>
        <?php endif; ?>
    </article>

    <script src="/assets/js/interactions.js"></script> <!-- Will implement later -->
</body>
</html>