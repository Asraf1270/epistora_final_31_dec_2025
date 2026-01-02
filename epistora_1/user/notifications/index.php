<?php
// /epistora/user/notifications/index.php
// User notifications center

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

if (!is_logged_in()) {
    header('Location: /user/login/');
    exit;
}

$user_id = $_SESSION['user_id'];
$vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
$vault = $_SESSION['vault'] ?? json_read($vault_file, true);

$notifications = $vault['notifications'] ?? [];
$unread_count = count(array_filter($notifications, fn($n) => empty($n['read'])));

// Mark all as read on visit
if (!empty($notifications)) {
    $updated = false;
    foreach ($notifications as &$n) {
        if (empty($n['read'])) {
            $n['read'] = true;
            $updated = true;
        }
    }
    if ($updated) {
        json_write($vault_file, $vault);
        $_SESSION['vault'] = $vault;
    }
}

// Sort newest first
usort($notifications, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications (<?= $unread_count ?>) - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <h1>Notifications <?= $unread_count > 0 ? "<span style='color:red;'>($unread_count new)</span>" : '' ?></h1>
    <a href="/user/profile/?u=<?= urlencode($_SESSION['username']) ?>">← Back to Profile</a>

    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notif): ?>
                <div class="notification <?= $notif['read'] ? 'read' : 'unread' ?>">
                    <strong><?= htmlspecialchars($notif['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($notif['message'])) ?><br>
                    <small><?= date('M j, Y H:i', $notif['timestamp']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><small>Notifications are stored privately in your vault.</small></p>
</body>
</html>