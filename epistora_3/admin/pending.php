<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['profile']['role'] !== 'admin') {
    die("Access Denied.");
}

$postsIndexFile = '../data/posts.json';
$postDataDir = '../data/post_data/';

// 2. Handle Approval Action
if (isset($_POST['approve_id'])) {
    $approve_id = $_POST['approve_id'];

    // Update individual file
    $fullPath = $postDataDir . $approve_id . ".json";
    if (file_exists($fullPath)) {
        $data = json_decode(file_get_contents($fullPath), true);
        $data['status'] = 'approved';
        file_put_contents($fullPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Update central index
    $index = json_decode(file_get_contents($postsIndexFile), true);
    if (isset($index[$approve_id])) {
        $index[$approve_id]['status'] = 'approved';
        file_put_contents($postsIndexFile, json_encode($index, JSON_PRETTY_PRINT));
    }
    $msg = "Post approved successfully!";
}

// 3. Get all Pending Data
$pendingList = [];
$files = glob($postDataDir . "*.json");
foreach ($files as $file) {
    $content = json_decode(file_get_contents($file), true);
    if ($content['status'] === 'pending') {
        $id = $content['meta']['post_id'];
        // Cross-reference with index to get the title
        $index = json_decode(file_get_contents($postsIndexFile), true);
        $content['title'] = $index[$id]['title'] ?? 'Untitled';
        $pendingList[] = $content;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Review Posts</title>
    <style>
        body { font-family: sans-serif; background: #f8f9fa; padding: 30px; }
        .post-card { background: white; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .post-content-preview { 
            background: #fffbe6; border-left: 5px solid #ffe58f; 
            padding: 15px; margin: 15px 0; display: none; /* Hidden by default */
        }
        .btn { padding: 8px 16px; cursor: pointer; border: none; border-radius: 4px; margin-right: 5px; }
        .btn-read { background: #007bff; color: white; }
        .btn-approve { background: #28a745; color: white; }
        .tag { background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; }
    </style>
    <script>
        function toggleRead(id) {
            var el = document.getElementById('body-' + id);
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</head>
<body>

    <h2>Pending for Approval (<?php echo count($pendingList); ?>)</h2>
    <a href="index.php">Back to Dashboard</a>
    <hr>

    <?php if (isset($msg)) echo "<p style='color:green'>$msg</p>"; ?>

    <?php foreach ($pendingList as $post): ?>
        <div class="post-card">
            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
            <p>
                <strong>Author:</strong> <?php echo $post['meta']['author_id']; ?> | 
                <strong>Tags:</strong> 
                <?php foreach($post['meta']['tags'] as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </p>

            <div id="body-<?php echo $post['meta']['post_id']; ?>" class="post-content-preview">
                <strong>Full Content:</strong><br>
                <?php echo nl2br(htmlspecialchars($post['body'])); ?>
            </div>

            <div style="margin-top: 10px;">
                <button class="btn btn-read" onclick="toggleRead('<?php echo $post['meta']['post_id']; ?>')">Read Content</button>
                
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="approve_id" value="<?php echo $post['meta']['post_id']; ?>">
                    <button type="submit" class="btn btn-approve">Approve Post</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($pendingList)) echo "<p>Everything is caught up! No pending posts.</p>"; ?>

</body>
</html>