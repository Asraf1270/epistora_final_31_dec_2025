<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

$post_id = $_GET['id'] ?? die("Post ID missing.");
$post_file = "post_content/$post_id.json";
$post_data = DBEngine::readJSON($post_file);

// Verify Ownership
if ($post_data['author_id'] !== $_SESSION['user_id']) {
    die("Access Denied: You do not own this post.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data['title'] = strip_tags($_POST['title']);
    $post_data['content'] = $_POST['content'];
    $post_data['updated_at'] = date('Y-m-d H:i:s');

    // 1. Save Detail File
    DBEngine::writeJSON($post_file, $post_data);

    // 2. Sync to Global Index
    $index = DBEngine::readJSON("posts.json");
    foreach ($index as &$p) {
        if ($p['post_id'] === $post_id) {
            $p['title'] = $post_data['title'];
            $p['preview'] = substr(strip_tags($post_data['content']), 0, 150) . "...";
            break;
        }
    }
    DBEngine::writeJSON("posts.json", $index);

    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Story | Epistora</title>
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
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            display: flex; 
            justify-content: center; 
        }

        .container { 
            width: 100%; 
            max-width: 800px; 
            background: var(--bg-card); 
            padding: 40px; 
            border-radius: var(--radius); 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        h1 { 
            font-size: 1.75rem; 
            font-weight: 700; 
            margin-bottom: 24px; 
            letter-spacing: -0.025em;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input[type="text"], textarea { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 1rem;
            font-family: inherit;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        textarea { 
            min-height: 400px; 
            line-height: 1.6; 
            resize: vertical; 
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        button { 
            background: var(--primary); 
            color: #fff; 
            border: none; 
            padding: 12px 32px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.95rem;
            transition: var(--transition);
        }

        button:hover { 
            background: var(--primary-hover); 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .cancel-link {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .cancel-link:hover {
            color: #ef4444; /* Soft Red for cancel */
        }

        @media (max-width: 640px) {
            .container { padding: 24px; }
            .actions { flex-direction: column; align-items: stretch; }
            button { width: 100%; }
            .cancel-link { text-align: center; order: 2; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Story</h1>
    
    <form method="POST">
        <label class="form-label">Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($post_data['title']) ?>" placeholder="Post title..." required>

        <label class="form-label">Story Content</label>
        <textarea name="content" placeholder="Write your story..." required><?= htmlspecialchars($post_data['content']) ?></textarea>

        <div class="actions">
            <button type="submit">Save Changes</button>
            <a href="index.php" class="cancel-link">Discard Changes</a>
        </div>
    </form>
</div>

</body>
</html>