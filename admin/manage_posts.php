<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

$posts = DBEngine::readJSON("posts.json") ?? [];
$message = "";

if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if ($_GET['token'] !== $_SESSION['csrf_token']) { die("Security Breach."); }
    $target_id = $_GET['delete_id'];
    $posts = array_filter($posts, function($p) use ($target_id) { return $p['post_id'] !== $target_id; });
    DBEngine::writeJSON("posts.json", array_values($posts));
    $content_file = DATA_PATH . "post_content/$target_id.json";
    if (file_exists($content_file)) { unlink($content_file); }
    DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'DELETE_POST', "Deleted ID: $target_id");
    $message = "Post removed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Mobile-first scroll fix */
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col md:flex-row">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-[900] tracking-tight">Post Manager</h1>
                <p class="text-slate-500 text-sm">Review and moderate all content.</p>
            </div>
            <?php if($message): ?>
                <div class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold self-start animate-fade-in"><?= $message ?></div>
            <?php endif; ?>
        </header>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <input type="text" id="postSearch" placeholder="Filter by title, author or ID..." 
                   class="w-full bg-slate-50 border-none px-4 py-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[600px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                            <th class="px-6 py-4">Content Details</th>
                            <th class="px-6 py-4">Author</th>
                            <th class="px-6 py-4">Views</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="postTableBody">
                        <?php foreach ($posts as $post): ?>
                        <tr class="hover:bg-slate-50/50 transition border-b border-slate-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($post['title']) ?></div>
                                <div class="text-[10px] font-mono text-slate-400"><?= $post['post_id'] ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= htmlspecialchars($post['author']) ?></td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-[11px] font-black italic">
                                    👁️ <?= $post['views'] ?? 0 ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="../post/view/?id=<?= $post['post_id'] ?>" target="_blank" class="p-2 bg-slate-100 rounded-lg hover:bg-blue-100 transition">👁️</a>
                                    <button onclick="confirmDelete('<?= $post['post_id'] ?>')" class="p-2 bg-slate-100 rounded-lg text-red-400 hover:bg-red-100 transition">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    document.getElementById('postSearch').addEventListener('input', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('#postTableBody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    function confirmDelete(id) {
        if(confirm("Confirm deletion?")) {
            window.location.href = "?delete_id=" + id + "&token=<?= $_SESSION['csrf_token'] ?>";
        }
    }
    </script>
</body>
</html>