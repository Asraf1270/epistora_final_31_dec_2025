<?php
// /epistora/user/profile/index.php
// Public user profile page – viewable by anyone

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

$username = $_GET['u'] ?? '';
if (empty($username)) {
    http_response_code(404);
    exit('User not specified');
}

// Load users index to find user_id
$users = json_read(USERS_INDEX, true);
$target_user_id = null;
$target_user = null;

foreach ($users as $id => $user) {
    if ($user['username'] === $username) {
        $target_user_id = $id;
        $target_user = $user;
        break;
    }
}

if (!$target_user) {
    http_response_code(404);
    exit('User not found');
}

// Load public posts (published only)
$posts_index = json_read(POSTS_INDEX, true);
$user_posts = [];

foreach ($posts_index as $post_id => $meta) {
    if ($meta['author_id'] == $target_user_id && $meta['status'] === 'published') {
        $user_posts[$post_id] = $meta;
    }
}

// Sort by newest first
uasort($user_posts, fn($a, $b) => $b['created_at'] - $a['created_at']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@<?= htmlspecialchars($target_user['username']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="profile-header">
        <h1>@<?= htmlspecialchars($target_user['username']) ?></h1>
        <p>Member since <?= date('F Y', $target_user['created_at']) ?></p>
        <?php if (is_logged_in() && $_SESSION['user_id'] == $target_user_id): ?>
            <p><a href="/user/setting/">Edit Settings</a> | <a href="/user/notifications/">Notifications</a></p>
        <?php endif; ?>
    </div>

    <h2>Posts (<?= count($user_posts) ?>)</h2>

    <?php if (empty($user_posts)): ?>
        <p>No published posts yet.</p>
    <?php else: ?>
        <div class="post-list">
            <?php foreach ($user_posts as $post_id => $meta): ?>
                <div class="post-card">
                    <h3><a href="/post/view/?id=<?= $post_id ?>"><?= htmlspecialchars($meta['title']) ?></a></h3>
                    <p><?= htmlspecialchars($meta['excerpt']) ?></p>
                    <small>
                        <?= date('M j, Y', $meta['created_at']) ?>
                        • <?= $meta['reactions_count'] ?> reactions
                        • <?= $meta['comments_count'] ?> comments
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>