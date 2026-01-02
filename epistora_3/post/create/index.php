<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$allowed_roles = ['writer', 'v_writer', 'admin'];
$current_role = $_SESSION['profile']['role'] ?? 'user';

if (!in_array($current_role, $allowed_roles)) {
    die("Access Denied.");
}

function get_short_body($text, $limit = 20) {
    $words = explode(' ', $text);
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $content = $_POST['content'];
    // Clean and store tags as an array
    $tags = array_filter(array_map('trim', explode(',', $_POST['tags']))); 
    
    $post_id = "POST" . time() . rand(100, 999);
    $author_id = $_SESSION['user_id'];
    $timestamp = time();

    $postsFile = '../../data/posts.json';
    $postsIndex = json_decode(file_get_contents($postsFile), true) ?: [];

    // --- UPDATED: Saving tags into the Index ---
    $postsIndex[$post_id] = [
        "title" => $title,
        "body" => get_short_body(strip_tags($content)),
        "author_id" => $author_id,
        "created" => $timestamp,
        "updated" => $timestamp,
        "reactions" => 0,
        "comments" => 0,
        "views" => 0, // Initial views
        "tags" => $tags, // IMPORTANT: Added tags here
        "status" => "approved"
    ];

    $fullPostData = [
        "meta" => [
            "post_id" => $post_id,
            "author_id" => $author_id,
            "tags" => $tags,
            "read_time" => ceil(str_word_count(strip_tags($content)) / 200)
        ],
        "body" => $content,
        "comments" => [],
        "history" => [
            ["action" => "created", "time" => $timestamp]
        ],
        "status" => "approved"
    ];

    file_put_contents($postsFile, json_encode($postsIndex, JSON_PRETTY_PRINT));
    file_put_contents("../../data/post_data/$post_id.json", json_encode($fullPostData, JSON_PRETTY_PRINT));

    $success = "Post published successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;        /* Indigo - modern accent color */
            --primary-hover: #4f46e5;
            --text: #1f2937;
            --text-light: #6b7280;
            --bg: #ffffff;
            --border: #e5e7eb;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.25s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f9fafb;
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: var(--bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            border: 1px solid var(--border);
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .user-info {
            font-size: 15px;
            color: var(--text-light);
            margin-bottom: 32px;
        }

        .user-info strong {
            color: var(--text);
        }

        .success {
            background-color: #dcfce7;
            color: #166534;
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 32px;
            font-weight: 500;
            border: 1px solid #bbf7d0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        label {
            font-weight: 600;
            font-size: 15px;
            color: var(--text);
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 16px;
            transition: var(--transition);
            background-color: #ffffff;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        textarea {
            min-height: 300px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 16px;
        }

        button {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 16px;
            padding: 14px 28px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
        }

        .back-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 0 16px;
            }

            .card {
                padding: 24px;
            }

            h2 {
                font-size: 24px;
            }

            textarea {
                min-height: 250px;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 20px;
            }

            input[type="text"],
            textarea {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Create a New Blog Post</h2>
            <p class="user-info">
                Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                (<?php echo htmlspecialchars($current_role); ?>)
            </p>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div>
                    <label for="title">Post Title</label>
                    <input type="text" name="title" id="title" placeholder="Enter a compelling title..." required>
                </div>

                <div>
                    <label for="content">Content</label>
                    <textarea name="content" id="content" placeholder="Write your post content here... Use Markdown if you like." required></textarea>
                </div>

                <div>
                    <label for="tags">Tags</label>
                    <input type="text" name="tags" id="tags" placeholder="e.g. tech, programming, php, web-development" required>
                    <small style="color: var(--text-light); margin-top: 6px; display: block;">
                        Separate tags with commas
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit">Publish Post</button>
                    <a href="../../" class="back-link">← Back to Home</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>