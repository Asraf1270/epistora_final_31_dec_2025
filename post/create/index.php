<?php
session_start();
// Force UTF-8 headers for Bangla support
header('Content-Type: text/html; charset=utf-8');

require_once '../../config.php';
require_once '../../db_engine.php';

$allowed_roles = [ROLE_WRITER, ROLE_V_WRITER, ROLE_ADMIN];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    die("Access Denied.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = strip_tags($_POST['title']);
    $content   = $_POST['content']; 
    $author_id = $_SESSION['user_id'];
    
    $raw_tags = explode(',', $_POST['tags']);
    $tags     = array_filter(array_map('trim', $raw_tags));

    $unique_prefix = bin2hex(random_bytes(3));
    $post_id = strtoupper($unique_prefix) . "-" . date('Ym') . "-" . $author_id;

    $status = ($_SESSION['role'] === ROLE_V_WRITER || $_SESSION['role'] === ROLE_ADMIN) ? 'published' : 'pending';
    
    $full_post_data = [
        "post_id"    => $post_id,
        "title"      => $title,
        "content"    => $content,
        "tags"       => $tags,
        "author"     => $_SESSION['user_name'],
        "author_id"  => $author_id,
        "status"     => $status,
        "views"      => 0,
        "created_at" => date('Y-m-d H:i:s'),
        "timestamp"  => time()
    ];

    // Path must climb out of post/create/ to reach data folder
    DBEngine::writeJSON("post_content/$post_id.json", $full_post_data);

    if ($status === 'published') {
        $index_file = "posts.json"; // Relative to DATA_PATH defined in config
        $posts_index = DBEngine::readJSON($index_file) ?? [];
        
        // FIX: mb_strimwidth is required for Bangla to prevent cutting characters in half
        $preview = mb_strimwidth(strip_tags($content), 0, 100, "...", "UTF-8");

        $posts_index[] = [
            "post_id"   => $post_id,
            "title"     => $title,
            "tags"      => $tags,
            "preview"   => $preview,
            "author"    => $_SESSION['user_name'],
            "author_id" => $author_id,
            "date"      => date('Y-m-d'),
            "views"     => 0
        ];

        DBEngine::writeJSON($index_file, $posts_index);
        $message = "<div class='alert alert-success'>Published! ID: $post_id</div>";
    } else {
        $message = "<div class='alert alert-warning'>Submitted for review.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --transition: all 0.2s ease-in-out;
        }

        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main);
            margin: 0;
            padding: 60px 20px;
            display: flex; 
            justify-content: center; 
            line-height: 1.5;
        }

        .container { 
            width: 100%; 
            max-width: 680px; 
            background: var(--bg-card); 
            padding: 48px; 
            border-radius: var(--radius); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        header { margin-bottom: 32px; }

        h2 { 
            font-size: 1.875rem; 
            font-weight: 700; 
            color: var(--text-main); 
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
        }

        p.subtitle { color: var(--text-muted); font-size: 0.95rem; }

        .form-group { margin-bottom: 24px; }

        label { 
            display: block; 
            font-weight: 500; 
            font-size: 0.875rem;
            margin-bottom: 8px; 
            color: var(--text-main);
        }

        input, textarea { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-main);
            transition: var(--transition);
            background-color: #ffffff;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        textarea { min-height: 300px; line-height: 1.6; resize: vertical; }

        button { 
            background: var(--primary); 
            color: #fff; 
            border: none; 
            padding: 14px 24px; 
            border-radius: 8px; 
            width: 100%; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 1rem;
            transition: var(--transition);
            margin-top: 10px;
        }

        button:hover { 
            background: var(--primary-hover); 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        button:active { transform: translateY(0); }

        .alert { 
            padding: 16px; 
            border-radius: 8px; 
            margin-bottom: 32px; 
            font-size: 0.95rem; 
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fef3c7; }

        /* Responsive Tweak */
        @media (max-width: 640px) {
            body { padding: 20px 15px; }
            .container { padding: 32px 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h2>Create New Post</h2>
        <p class="subtitle">Share your thoughts with the Epistora community.</p>
    </header>

    <?= $message ?>

    <form method="POST">
        <div class="form-group">
            <label for="title">Post Title</label>
            <input type="text" id="title" name="title" placeholder="Enter a descriptive title..." required>
        </div>

        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" id="tags" name="tags" placeholder="e.g. Technology, Lifestyle, Art (comma separated)">
        </div>

        <div class="form-group">
            <label for="content">Story Content</label>
            <textarea id="content" name="content" placeholder="Tell your story..." required></textarea>
        </div>

        <button type="submit">Publish Post</button>
    </form>
</div>

</body>
</html>