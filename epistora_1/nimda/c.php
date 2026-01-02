<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/bootstrap.php';

require_role(ROLES['nimda']); // Only nimda access

// Common admin navigation
$admin_nav = '
<nav class="admin-nav">
    <a href="/nimda/">Dashboard</a> |
    <a href="/nimda/user_manage.php">Users</a> |
    <a href="/nimda/pending.php">Pending Posts</a> |
    <a href="/nimda/posts.php">All Posts</a> |
    <a href="/nimda/comments.php">Comments</a> |
    <a href="/nimda/reports.php">Reports</a> |
    <a href="/nimda/cache.php">Cache</a> |
    <a href="/nimda/security.php">Security Logs</a> |
    <a href="/nimda/settings.php">Site Settings</a> |
    <a href="/nimda/tools.php">Tools</a> |
    <a href="/">← Front Site</a>
</nav>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nimda • <?= SITE_NAME ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .admin-nav { background: #333; padding: 10px; margin-bottom: 20px; }
        .admin-nav a { color: white; margin: 0 10px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .btn { padding: 5px 10px; margin: 2px; cursor: pointer; }
        .danger { background: #ff4444; color: white; }
        .success { background: #44ff44; color: black; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <?= $admin_nav ?>