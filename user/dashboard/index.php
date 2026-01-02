<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}

$user_id = $_SESSION['user_id'];
// Path for DBEngine to reach htdocs/data/posts.json
$all_posts = DBEngine::readJSON("posts.json") ?? [];

// 1. Filter posts belonging to the logged-in writer
$my_posts = array_filter($all_posts, function($p) use ($user_id) {
    return ($p['author_id'] ?? '') === $user_id;
});

// 2. Calculate Aggregate Stats
$total_views = 0;
$total_reactions = 0;

foreach ($my_posts as $p) {
    $total_views += ($p['views'] ?? 0);
    // Updated: Uses 'reaction_count' synced by react.php and index.php
    $total_reactions += ($p['reaction_count'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writer Dashboard | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .dash-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dash-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            transition: transform 0.2s ease;
        }

        .stat-card h3 {
            margin: 0 0 12px 0;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .color-views { color: var(--success); }
        .color-reactions { color: var(--danger); }
        .color-articles { color: var(--primary); }

        /* Table/List Styling */
        .content-section {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 1.1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #fcfcfd;
            padding: 12px 24px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        .post-title {
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
        }

        .post-title:hover { color: var(--primary); }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            background: #f1f5f9;
            font-weight: 600;
        }

        .actions a {
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            margin-right: 12px;
        }

        .btn-view { color: var(--primary); }
        .btn-edit { color: var(--warning); }
        .btn-delete { color: var(--danger); }

        @media (max-width: 768px) {
            th:nth-child(3), td:nth-child(3), 
            th:nth-child(4), td:nth-child(4) { display: none; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <header class="dash-header">
        <div>
            <h1>Writer Dashboard</h1>
            <p style="color: var(--text-muted); margin-top: 5px;">Overview of your performance on <?= APP_NAME ?></p>
        </div>
        <a href="../../index.php" style="text-decoration: none; font-weight: 600; color: var(--primary);">← Back to Feed</a>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Views</h3>
            <span class="stat-value color-views"><?= number_format($total_views) ?></span>
        </div>
        <div class="stat-card">
            <h3>Total Reactions</h3>
            <span class="stat-value color-reactions"><?= number_format($total_reactions) ?></span>
        </div>
        <div class="stat-card">
            <h3>Articles</h3>
            <span class="stat-value color-articles"><?= count($my_posts) ?></span>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">Your Published Stories</div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Views</th>
                    <th>Reactions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($my_posts as $post): 
                    // Using the new 'reaction_count' key
                    $engagement = $post['reaction_count'] ?? 0;
                ?>
                <tr>
                    <td>
                        <a href="../../post/view/?id=<?= $post['post_id'] ?>" class="post-title">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </td>
                    <td><span style="color: var(--text-muted);"><?= date('M d, Y', strtotime($post['date'])) ?></span></td>
                    <td><span class="badge"><?= number_format($post['views'] ?? 0) ?></span></td>
                    <td><span class="badge" style="background: #fee2e2; color: #b91c1c;">❤️ <?= number_format($engagement) ?></span></td>
                    <td class="actions">
                        <a href="../../post/view/?id=<?= $post['post_id'] ?>" class="btn-view">View</a>
                        <a href="edit.php?id=<?= $post['post_id'] ?>" class="btn-edit">Edit</a>
                        <a href="#" class="btn-delete" 
                           onclick="confirmDelete('<?= $post['post_id'] ?>', '<?= addslashes($post['title']) ?>')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($my_posts)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 60px; color: var(--text-muted);">
                        <p>You haven't published any stories yet.</p>
                        <a href="../publish/" style="color: var(--primary); font-weight: 600;">Write your first story →</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
        window.location.href = `delete.php?id=${id}`;
    }
}
</script>

</body>
</html>