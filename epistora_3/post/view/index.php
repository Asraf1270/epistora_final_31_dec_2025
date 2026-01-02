<?php
include '../../header.php';
$post_id = $_GET['id'] ?? null;
if (!$post_id) { die("Post ID missing."); }
$postIndexFile = "../../data/posts.json";
$indexData = json_decode(file_get_contents($postIndexFile), true);
$post_title = $indexData[$post_id]['title'] ?? "Untitled Post";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;        /* Indigo – modern, soft accent */
            --primary-hover: #4f46e5;
            --text: #1e293b;
            --text-light: #64748b;
            --bg: #ffffff;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --light-bg: #f8fafc;
            --reaction-bg: #f1f5f9;
            --shadow: 0 4px 20px rgba(0,0,0,0.06);
            --radius: 16px;
            --transition: all 0.25s ease;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--light-bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Ensure content starts below any fixed/sticky header from header.php */
        body {
            padding-top: 80px; /* Adjust this value to match your header height */
        }

        .view-card {
            max-width: 800px;
            margin: 40px auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        /* Top Meta */
        #top-meta {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            flex-shrink: 0;
        }

        .meta-info h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.15rem;
            color: var(--text);
        }

        .meta-info p {
            margin: 4px 0 0;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Title */
        #display-title {
            margin: 0 0 32px;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--text);
        }

        /* Post Body */
        #post-body-content {
            font-size: 1.15rem;
            line-height: 1.8;
            margin-bottom: 40px;
            color: var(--text);
        }

        /* Reaction Bar */
        .reaction-bar {
            display: flex;
            gap: 12px;
            background: var(--reaction-bg);
            padding: 12px 20px;
            border-radius: 30px;
            margin-bottom: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .react-btn {
            background: transparent;
            border: none;
            padding: 10px 18px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            font-weight: 500;
            color: var(--text-light);
        }

        .react-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .react-btn:active {
            transform: translateY(0);
        }

        /* Comments Section */
        .comments-section h3 {
            margin: 0 0 24px;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text);
        }

        .comment-input-wrapper {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            align-items: center;
        }

        #comment-input {
            flex: 1;
            padding: 14px 20px;
            border: 1px solid var(--border);
            border-radius: 30px;
            font-size: 1rem;
            background: white;
            transition: var(--transition);
        }

        #comment-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .send-btn {
            padding: 0 28px;
            height: 50px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .send-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        /* Individual Comments */
        .comment {
            background: var(--light-bg);
            padding: 18px 20px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .comment strong {
            font-weight: 600;
            color: var(--text);
        }

        .comment p {
            margin: 8px 0 0;
            color: var(--text);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Slightly less on mobile if header is smaller */
            }

            .view-card {
                margin: 20px auto;
                padding: 24px;
                border-radius: 16px;
            }

            #top-meta {
                gap: 14px;
                padding-bottom: 20px;
            }

            .avatar {
                width: 56px;
                height: 56px;
                font-size: 32px;
            }

            #display-title {
                font-size: 1.8rem;
                margin-bottom: 28px;
            }

            #post-body-content {
                font-size: 1.05rem;
            }

            .reaction-bar {
                padding: 12px;
                gap: 10px;
            }

            .react-btn {
                padding: 10px 16px;
                font-size: 0.95rem;
            }

            .comment-input-wrapper {
                flex-direction: column;
            }

            #comment-input,
            .send-btn {
                width: 100%;
                height: 52px;
            }

            .send-btn {
                padding: 0;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 60px;
            }

            .view-card {
                margin: 12px auto;
                padding: 20px;
            }

            #display-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<div class="view-card">
    <div id="top-meta">
        <div class="avatar">👤</div>
        <div class="meta-info">
            <h4 id="author-display">Loading Author...</h4>
            <p>Published: <span id="date-display">--</span> | 👁️ <span id="view-display">0</span> Views</p>
        </div>
    </div>

    <h1 id="display-title"><?php echo htmlspecialchars($post_title); ?></h1>

    <div id="post-body-content">
        Loading content...
    </div>

    <div class="reaction-bar">
        <button onclick="sendAction('react', 'like')" class="react-btn">👍 <span id="count-like">0</span></button>
        <button onclick="sendAction('react', 'love')" class="react-btn">❤️ <span id="count-love">0</span></button>
        <button onclick="sendAction('react', 'insight')" class="react-btn">💡 <span id="count-insight">0</span></button>
        <button onclick="sendAction('react', 'wow')" class="react-btn">😮 <span id="count-wow">0</span></button>
    </div>

    <div class="comments-section">
        <h3>Comments (<span id="comm-total">0</span>)</h3>

        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="comment-input-wrapper">
                <input type="text" id="comment-input" placeholder="Add a comment...">
                <button onclick="sendAction('comment')" class="send-btn">Send</button>
            </div>
        <?php endif; ?>

        <div id="realtime-comments"></div>
    </div>
</div>

<script>
function loadRealTimeData() {
    fetch(`api_handler.php?action=stream&post_id=<?php echo $post_id; ?>`)
    .then(res => res.json())
    .then(data => {
        if(!data.success) return;

        document.getElementById('author-display').innerHTML = data.author + (data.is_verified ? ' <span style="color:#1da1f2;">✔</span>' : '');
        document.getElementById('date-display').innerText = data.date;
        document.getElementById('view-display').innerText = data.views;

        document.getElementById('post-body-content').innerHTML = data.body;

        const r = data.reactions;
        document.getElementById('count-like').innerText = r.like || 0;
        document.getElementById('count-love').innerText = r.love || 0;
        document.getElementById('count-insight').innerText = r.insight || 0;
        document.getElementById('count-wow').innerText = r.wow || 0;

        const container = document.getElementById('realtime-comments');
        let html = '';
        data.comments.forEach(c => {
            html += `
                <div class="comment">
                    <strong>${c.username}</strong>
                    <p>${c.text}</p>
                </div>`;
        });
        container.innerHTML = html;
        document.getElementById('comm-total').innerText = data.comments.length;
    });
}

function sendAction(action, type = '') {
    const data = new FormData();
    data.append('post_id', '<?php echo $post_id; ?>');
    data.append('action', action);
    if(type) data.append('type', type);

    if(action === 'comment') {
        const input = document.getElementById('comment-input');
        if(!input.value.trim()) return;
        data.append('text', input.value);
        input.value = '';
    }

    fetch('api_handler.php', { method: 'POST', body: data })
    .then(() => loadRealTimeData());
}

setInterval(loadRealTimeData, 3000);
loadRealTimeData();
</script>

</body>
</html>