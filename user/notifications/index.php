<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$uid = $_SESSION['user_id'];
$vault = DBEngine::readJSON("user_data/$uid.json");
$notifications = $vault['notifications'] ?? [];

// Mark all as read when page is opened
$has_unread = false;
foreach ($notifications as &$n) { 
    if (!$n['is_read']) {
        $n['is_read'] = true; 
        $has_unread = true;
    }
}
if ($has_unread) {
    $vault['notifications'] = $notifications;
    DBEngine::writeJSON("user_data/$uid.json", $vault);
}

// Sort notifications by newest first
usort($notifications, function($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --unread-bg: #f0f7ff;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .notif-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notif-item {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 16px;
            transition: background 0.2s ease;
            position: relative;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover {
            background-color: #fcfcfd;
        }

        /* Icon Styling */
        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .icon-reaction { background: #fee2e2; color: #ef4444; }
        .icon-comment { background: #dcfce7; color: #22c55e; }

        .notif-content {
            flex-grow: 1;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .notif-content a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .notif-content a:hover {
            text-decoration: underline;
        }

        .notif-meta {
            display: block;
            margin-top: 4px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Unread Indicator */
        .unread-dot {
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
        }

        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state span {
            display: block;
            font-size: 3rem;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Notifications</h2>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <span>🔔</span>
            <p>Your inbox is empty. No new activity yet!</p>
        </div>
    <?php else: ?>
        <ul class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <li class="notif-item">
                    <div class="notif-icon <?= $n['type'] === 'reaction' ? 'icon-reaction' : 'icon-comment' ?>">
                        <?= $n['type'] === 'reaction' ? '❤️' : '💬' ?>
                    </div>

                    <div class="notif-content">
                        <strong><?= htmlspecialchars($n['from_name']) ?></strong> 
                        <?= $n['type'] === 'reaction' ? 'reacted to your' : 'commented on your' ?> 
                        <a href="../../post/view/?id=<?= $n['post_id'] ?>">
                            <?= $n['type'] === 'reaction' ? 'post' : 'article' ?>
                        </a>.
                        <span class="notif-meta"><?= $n['date_human'] ?></span>
                    </div>

                    <?php if (isset($n['is_read']) && !$n['is_read']): ?>
                        <div class="unread-dot"></div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>