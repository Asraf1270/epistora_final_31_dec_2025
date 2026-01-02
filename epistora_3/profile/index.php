<?php
session_start();

if (!isset($_GET['id'])) {
    die("User ID is required to view a profile.");
}

$profile_id = $_GET['id'];
$usersFile = '../data/users.json';
$postsFile = '../data/posts.json';

// Check if data files exist
if (!file_exists($usersFile) || !file_exists($postsFile)) {
    die("System data missing.");
}

$allUsers = json_decode(file_get_contents($usersFile), true);
$allPosts = json_decode(file_get_contents($postsFile), true);

// 1. Verify if the user exists
if (!isset($allUsers[$profile_id])) {
    die("User profile not found.");
}

$userData = $allUsers[$profile_id];

// 2. Filter posts by this author that are 'approved'
$authorPosts = array_filter($allPosts, function($post) use ($profile_id) {
    return $post['author_id'] === $profile_id && $post['status'] === 'approved';
});

// Sort posts by date (newest first)
uasort($authorPosts, function($a, $b) {
    return $b['created'] <=> $a['created'];
});

// Helper for Verified Badge
function getBadge($role) {
    return ($role === 'v_writer') ? '<span style="color:#1da1f2; font-size: 0.8em;" title="Verified Writer">✔</span>' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($userData['username']); ?>'s Profile</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #1c1e21; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        
        /* Profile Header */
        .profile-card { background: white; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .avatar { width: 100px; height: 100px; background: #ddd; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .username { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .role-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; background: #e7f3ff; color: #1877f2; }
        
        /* Post List */
        .post-card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .post-card:hover { transform: translateY(-2px); }
        .post-title { font-size: 20px; color: #1877f2; text-decoration: none; font-weight: bold; }
        .post-meta { font-size: 13px; color: #65676b; margin-top: 5px; }
        .post-body { margin-top: 10px; color: #4b4b4b; line-height: 1.4; }
        .stats-bar { border-top: 1px solid #eee; margin-top: 15px; padding-top: 10px; display: flex; gap: 15px; font-size: 13px; color: #65676b; }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <div class="avatar">👤</div>
        <div class="username">
            <?php echo htmlspecialchars($userData['username']) . " " . getBadge($userData['role']); ?>
        </div>
        <div class="role-badge"><?php echo str_replace('_', ' ', $userData['role']); ?></div>
        <p style="color: #65676b;">Member since <?php echo date("F Y", $userData['created']); ?></p>
    </div>

    <h3 style="margin-bottom: 15px;">Published Posts (<?php echo count($authorPosts); ?>)</h3>

    <?php if (empty($authorPosts)): ?>
        <div class="post-card" style="text-align: center; color: #65676b;">
            This user hasn't shared any posts yet.
        </div>
    <?php else: ?>
        <?php foreach ($authorPosts as $post_id => $post): ?>
            <div class="post-card">
                <a href="../post/view/index.php?id=<?php echo $post_id; ?>" class="post-title">
                    <?php echo htmlspecialchars($post['title']); ?>
                </a>
                <div class="post-meta">Published on <?php echo date("M d, Y", $post['created']); ?></div>
                <div class="post-body">
                    <?php echo htmlspecialchars($post['body']); ?>
                </div>
                <div class="stats-bar">
                    <span>👍 <?php echo $post['reactions']; ?> Reactions</span>
                    <span>💬 <?php echo $post['comments']; ?> Comments</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>