<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

// 1. FETCH DATA
$post_id = $_GET['id'] ?? die("Error: Post ID required.");

// Assuming DBEngine prepends 'data/', this points to htdocs/data/post_content/id.json
$post_file = "post_content/$post_id.json"; 
$post = DBEngine::readJSON($post_file);

if (!$post) die("Error: Story not found.");

// 2. VIEW TRACKING & USER HISTORY
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    // Relative to the data/ folder
    $user_path = "user_data/$uid.json"; 
    
    $u_vault = DBEngine::readJSON($user_path) ?? [];
    if (!isset($u_vault['history'])) $u_vault['history'] = [];
    
    if (!in_array($post_id, $u_vault['history'])) {
        $u_vault['history'][] = $post_id;
        DBEngine::writeJSON($user_path, $u_vault);
        $post['stats']['unique_views'] = ($post['stats']['unique_views'] ?? 0) + 1;
    }
}

// 3. UPDATE TOTAL VIEWS
$post['stats']['total_views'] = ($post['stats']['total_views'] ?? 0) + 1;
DBEngine::writeJSON($post_file, $post);

// 4. SYNC TO GLOBAL INDEX (htdocs/data/posts.json)
$index_path = "posts.json"; 
$index = DBEngine::readJSON($index_path) ?? [];

foreach ($index as &$p) {
    if ($p['post_id'] === $post_id) { 
        $p['views'] = $post['stats']['total_views']; 
        // Sync the total reaction count for the homepage feed
        if (isset($post['reactions'])) {
            $p['reaction_count'] = array_sum($post['reactions']);
        }
        break; 
    }
}
DBEngine::writeJSON($index_path, $index);

// Recursive Comment Renderer
function renderComments($comments, $post_id) {
    if (empty($comments)) return;
    foreach ($comments as $c) {
        ?>
        <div class="comment-item" id="c-<?= $c['comment_id'] ?>" style="border-left: 2px solid #e2e8f0; margin-left: 20px; padding-left: 15px; margin-bottom: 15px;">
            <div class="comment-meta" style="font-size: 0.85rem; color: #64748b;">
                <strong><?= htmlspecialchars($c['user_name']) ?></strong> • <?= $c['date'] ?>
            </div>
            <p style="margin: 5px 0;"><?= htmlspecialchars($c['text']) ?></p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <button onclick="document.getElementById('rf-<?= $c['comment_id'] ?>').style.display='block'" style="background:none; border:none; color:#4f46e5; font-size: 0.8rem; cursor:pointer; padding:0;">Reply</button>
                <form id="rf-<?= $c['comment_id'] ?>" method="POST" action="../../actions/add_comment.php" style="display:none; margin-top:10px;">
                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                    <input type="hidden" name="parent_id" value="<?= $c['comment_id'] ?>">
                    <textarea name="text" style="width:100%; border:1px solid #ddd; border-radius:8px; padding:8px;" placeholder="Write a reply..." required></textarea>
                    <button type="submit" style="background:#4f46e5; color:white; border:none; padding:5px 10px; border-radius:5px; margin-top:5px; cursor:pointer;">Post</button>
                </form>
            <?php endif; ?>

            <?php if (!empty($c['replies'])) renderComments($c['replies'], $post_id); ?>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; margin: 0; line-height: 1.6; }
        .container { max-width: 700px; margin: 40px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h1 { font-size: 2.5rem; margin-bottom: 10px; letter-spacing: -1px; }
        .meta { color: #64748b; font-size: 0.9rem; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; }
        .content { font-size: 1.15rem; white-space: pre-wrap; margin-bottom: 40px; }
        .react-bar { display: flex; gap: 10px; margin-bottom: 40px; }
        .react-btn { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 10px; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .react-btn:hover { background: #e2e8f0; }
        .react-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; }
        #progress-bar { position: fixed; top: 0; left: 0; height: 4px; background: #4f46e5; width: 0%; z-index: 100; transition: width 0.1s; }
        
        /* Comments Styling */
        .comment-section { margin-top: 60px; border-top: 2px solid var(--border); padding-top: 40px; }
        .comment-item { padding: 20px 0; border-bottom: 1px solid #f8fafc; }
        .comment-meta { font-size: 0.85rem; margin-bottom: 8px; }
        .author-name { font-weight: 700; }
        .replies-indent { margin-left: 25px; border-left: 2px solid var(--border); padding-left: 20px; }
        .reply-link { background: none; border: none; color: var(--primary); font-weight: 700; cursor: pointer; padding: 0; font-size: 0.85rem; }
        .reply-form { display: none; margin-top: 15px; }
        .reply-form.active { display: block; }

        textarea { width: 100%; padding: 15px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 1rem; margin-top: 10px; resize: none; }
        .btn-post-reply { background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    </style>
</head>
<body>
    <div id="progress-bar"></div>
    <div class="container">
        <a href="../../index.php" style="text-decoration: none; color: #4f46e5; font-weight: bold;">← Back</a>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="meta">By <strong><?= htmlspecialchars($post['author']) ?></strong> • <?= $post['stats']['total_views'] ?> Views</div>
        
        <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

        <div class="react-bar">
            <?php 
            $emojis = ['love'=>'❤️', 'insight'=>'💡', 'clap'=>'👏', 'laugh'=>'😂'];
            foreach ($emojis as $key => $icon): ?>
                <button class="react-btn" id="btn-<?= $key ?>" onclick="handleReact('<?= $post_id ?>', '<?= $key ?>')">
                    <?= $icon ?> <span id="cnt-<?= $key ?>"><?= $post['reactions'][$key] ?? 0 ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="comment-section">
        <h3>Discussion</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="../../actions/add_comment.php" method="POST" style="margin-bottom: 40px;">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <textarea name="text" rows="3" placeholder="Add a comment..." required></textarea>
                <button type="submit" class="btn-post-reply">Post Comment</button>
            </form>
        <?php endif; ?>

        <div class="comment-list">
            <?php renderComments($post['comments'] ?? [], $post_id); ?>
        </div>
    </div>
    </div>

    <script>
        window.onscroll = function() {
            let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            document.getElementById("progress-bar").style.width = (winScroll / height * 100) + "%";
        };

        function handleReact(postId, type) {
            if(sessionStorage.getItem('reacted_' + postId + '_' + type)) return;

            const span = document.getElementById('cnt-' + type);
            const btn = document.getElementById('btn-' + type);

            span.innerText = parseInt(span.innerText) + 1;
            btn.classList.add('active');
            sessionStorage.setItem('reacted_' + postId + '_' + type, 'true');

            fetch('../../actions/react.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&type=${type}`
            })
            .then(res => res.json())
            .then(data => {
                if(!data.success) alert('Failed to sync reaction.');
            });
        }
    </script>
</body>
</html>