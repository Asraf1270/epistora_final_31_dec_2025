<?php require 'header.php'; ?>
<h2>User Management</h2>

<?php
if ($_POST['action'] ?? '' === 'update_role' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $uid = (int)$_POST['user_id'];
    $new_role = (int)$_POST['role'];
    json_update(USERS_INDEX, function($users) use ($uid, $new_role) {
        if (isset($users[$uid])) {
            $users[$uid]['role'] = $new_role;
        }
        return $users;
    });
    echo '<p style="color:green;">Role updated!</p>';
}
?>

<table>
    <tr>
        <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th>
    </tr>
    <?php foreach ($users as $uid => $u): ?>
    <tr>
        <td><?= $uid ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= $uid ?>">
                <input type="hidden" name="action" value="update_role">
                <select name="role" onchange="this.form.submit()">
                    <?php foreach (ROLES as $name => $val): ?>
                    <option value="<?= $val ?>" <?= $u['role'] == $val ? 'selected' : '' ?>><?= ucfirst($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </td>
        <td><?= date('Y-m-d', $u['created_at']) ?></td>
        <td><a href="/user/profile/?u=<?= urlencode($u['username']) ?>" target="_blank">View</a></td>
    </tr>
    <?php endforeach; ?>
</table>
</body></html>