<?php
// /epistora/nimda/header.php
// Common header for all nimda/* pages – include with: require 'header.php';

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Enforce nimda access
require_role(ROLES['nimda']);

// Admin navigation menu
$admin_nav = '
<nav class="admin-nav">
    <strong>Epistora Admin Panel</strong> &nbsp;|&nbsp;
    <a href="/nimda/">Dashboard</a> |
    <a href="/nimda/user_manage.php">Users</a> |
    <a href="/nimda/pending.php">Pending Posts</a> |
    <a href="/nimda/posts.php">All Posts</a> |
    <a href="/nimda/comments.php">Comments</a> |
    <a href="/nimda/reports.php">Reports</a> |
    <a href="/nimda/cache.php">Cache</a> |
    <a href="/nimda/security.php">Security Logs</a> |
    <a href="/nimda/settings.php">Site Settings</a> |
    <a href="/nimda/tools.php">Tools</a> &nbsp;|&nbsp;
    <a href="/">← View Site</a>
</nav>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nimda • <?= SITE_NAME ?></title>
    <meta name="csrf" content="<?= csrf_token() ?>">
    <style>
        body {
            font-family: system-ui, -apple-system, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f7f9fc;
            color: #333;
        }
        .admin-nav {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            margin: -20px -20px 30px -20px;
            font-size: 1.1em;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .admin-nav a {
            color: #ecf0f1;
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
        }
        .admin-nav a:hover {
            color: #3498db;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #ecf0f1;
            font-weight: 600;
        }
        tr:hover {
            background: #f1f8ff;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            text-decoration: none;
            display: inline-block;
            margin: 4px 2px;
        }
        .btn.success { background: #27ae60; color: white; }
        .btn.danger  { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        .card big {
            font-size: 2.2em;
            display: block;
            margin: 10px 0;
            color: #3498db;
        }
        pre {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow-x: auto;
            max-height: 70vh;
        }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <?= $admin_nav ?>

    <div class="content">