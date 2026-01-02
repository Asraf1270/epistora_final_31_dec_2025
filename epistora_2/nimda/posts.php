<?php require 'header.php'; ?>
<h2>All Posts</h2>
<table>
    <tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Reactions</th><th>Comments</th><th>Views</th><th>Actions</th></tr>
    <?php foreach ($posts as $pid => $p): ?>
    <tr>
        <td><?= $pid ?></td>
        <td><a href="/post/view/?id=<?= $pid ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a></td>
        <td><?= htmlspecialchars($p['author_name']) ?></td>
        <td><?= $p['status'] ?></td>
        <td><?= $p['reactions_count'] ?></td>
        <td><?= $p['comments_count'] ?></td>
        <td><?= $p['views_count'] ?></td>
        <td>
            <a href="/post/edit/?id=<?= $pid ?>">Edit</a> |
            <a href="/nimda/pending.php">Moderate</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>