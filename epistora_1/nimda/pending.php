<?php require 'header.php'; ?>
<h2>Pending Post Approval</h2>

<?php
if ($_POST['action'] ?? '' === 'approve' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $pid = (int)$_POST['post_id'];
    json_update(POSTS_INDEX, function($posts) use ($pid) {
        if (isset($posts[$pid]) && $posts[$pid]['status'] === 'pending') {
            $posts[$pid]['status'] = 'published';
        }
        return $posts;
    });
    cache_invalidate('homepage');
    echo '<p style="color:green;">Post approved!</p>';
}

if ($_POST['action'] ?? '' === 'reject' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $pid = (int)$_POST['post_id'];
    // Optional: delete or mark rejected
    json_update(POSTS_INDEX, function($posts) use ($pid) {
        unset($posts[$pid]);
        return $posts;
    });
    @unlink(POST_CONTENT_DIR . "/$pid.json");
    echo '<p style="color:red;">Post rejected and deleted.</p>';
}
?>

<table>
    <tr><th>Title</th><th>Author</th><th>Date</th><th>Actions</th></tr>
    <?php foreach ($posts as $pid => $p): if ($p['status'] !== 'pending') continue; ?>
    <tr>
        <td><a href="/post/view/?id=<?= $pid ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a></td>
        <td><?= htmlspecialchars($p['author_name']) ?></td>
        <td><?= date('Y-m-d', $p['created_at']) ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= $pid ?>">
                <button name="action" value="approve" class="btn success">Approve</button>
                <button name="action" value="reject" class="btn danger">Reject</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>