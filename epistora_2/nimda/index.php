<?php require 'header.php'; // Assume you save common header as header.php in /nimda/ ?>

<h2>Dashboard</h2>

<?php
$users = json_read(USERS_INDEX, true);
$posts = json_read(POSTS_INDEX, true);

$total_users = count($users);
$total_posts = count($posts);
$pending_posts = count(array_filter($posts, fn($p) => $p['status'] === 'pending'));
$total_reactions = array_sum(array_column($posts, 'reactions_count'));
$total_comments = array_sum(array_column($posts, 'comments_count'));
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 20px;">
    <div style="background:white;padding:20px;border:1px solid #ccc;">
        <h3>Total Users</h3><big><?= $total_users ?></big>
    </div>
    <div style="background:white;padding:20px;border:1px solid #ccc;">
        <h3>Total Posts</h3><big><?= $total_posts ?></big>
    </div>
    <div style="background:white;padding:20px;border:1px solid #ccc;">
        <h3>Pending Approval</h3><big><?= $pending_posts ?></big>
    </div>
    <div style="background:white;padding:20px;border:1px solid #ccc;">
        <h3>Total Reactions</h3><big><?= $total_reactions ?></big>
    </div>
    <div style="background:white;padding:20px;border:1px solid #ccc;">
        <h3>Total Comments</h3><big><?= $total_comments ?></big>
    </div>
</div>

<hr>
<h3>Quick Actions</h3>
<a href="/nimda/pending.php" class="btn success">Review Pending Posts</a>
<a href="/nimda/cache.php" class="btn">Clear All Cache</a>
<a href="/nimda/security.php" class="btn">View Security Logs</a>

</body></html>