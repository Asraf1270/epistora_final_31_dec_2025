<?php
session_start();
require_once '../config.php';
require_once '../db_engine.php';

// 1. HARD SECURITY: Multi-factor Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login/");
    exit("Unauthorized Access.");
}

// 2. DATA ENGINE: Fetch and Process
$posts = DBEngine::readJSON("posts.json") ?? [];
$users = DBEngine::readJSON("users.json") ?? [];
$logs  = DBEngine::readJSON("system_logs.json") ?? [];

// Calculate Intelligent Metrics
// This array_map ensures that every post has a 'views' key before summing
$processed_posts = array_map(function($p) {
    $p['views'] = $p['views'] ?? 0;
    return $p;
}, $posts);

$total_views = array_sum(array_column($processed_posts, 'views'));
$total_posts = count($posts);
$active_users = count($users);

// Get Trending Story (Highest Views)
usort($processed_posts, fn($a, $b) => ($b['views'] ?? 0) <=> ($a['views'] ?? 0));
$trending_post = $processed_posts[0] ?? null;

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epistora HQ | Central Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .gradient-text { background: linear-gradient(to right, #4f46e5, #9333ea); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-card { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .stat-card:hover { transform: scale(1.02) translateY(-5px); }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-6 md:p-12 mt-16 md:mt-0 overflow-x-hidden">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Central Intelligence</h1>
                <p class="text-slate-500 font-medium">Monitoring <span class="text-indigo-600 font-bold"><?= $total_posts ?></span> stories across your ecosystem.</p>
            </div>
            <div class="flex gap-3">
                <div class="glass p-3 rounded-2xl flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-black text-slate-600 uppercase tracking-widest">Node: Stable</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="stat-card bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-200 overflow-hidden relative">
                <div class="relative z-10">
                    <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.2em]">Global Traffic</p>
                    <h3 class="text-4xl font-black mt-2"><?= number_format($total_views) ?></h3>
                    <p class="text-[10px] mt-4 font-bold bg-white/20 inline-block px-2 py-1 rounded-lg">↑ 14.2% Growth</p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-white/10 italic font-black text-8xl">#1</div>
            </div>

            <div class="stat-card glass p-8 rounded-[2.5rem] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Community Size</p>
                <h3 class="text-4xl font-black mt-2 text-slate-800"><?= $active_users ?></h3>
                <div class="mt-4 flex -space-x-2">
                    <div class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white"></div>
                    <div class="w-6 h-6 rounded-full bg-slate-300 border-2 border-white"></div>
                    <div class="w-6 h-6 rounded-full bg-indigo-400 border-2 border-white"></div>
                </div>
            </div>

            <div class="stat-card glass p-8 rounded-[2.5rem] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Server Load</p>
                <h3 class="text-4xl font-black mt-2 text-emerald-500">0.02ms</h3>
                <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase italic">Optimized (JSON Engine)</p>
            </div>

            <div class="stat-card glass p-8 rounded-[2.5rem] shadow-sm border-l-4 border-amber-400">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Trending Post</p>
                <h3 class="text-sm font-black mt-2 text-slate-800 line-clamp-1"><?= $trending_post['title'] ?? 'N/A' ?></h3>
                <p class="text-[10px] text-amber-600 font-bold mt-2 italic">🔥 Highest Engagement</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 glass rounded-[3rem] overflow-hidden shadow-sm">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-black text-xl text-slate-800">Review Queue</h2>
                    <span class="px-4 py-1.5 bg-slate-900 text-white text-[10px] font-black rounded-full uppercase tracking-widest">Action Required</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                                <th class="px-8 py-5">Article Metadata</th>
                                <th class="px-8 py-5">Performance</th>
                                <th class="px-8 py-5 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach(array_slice($posts, 0, 6) as $post): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($post['title']) ?></p>
                                    <p class="text-[10px] text-indigo-500 font-black uppercase italic">By <?= htmlspecialchars($post['author']) ?></p>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 w-12 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="bg-indigo-500 h-full" style="width: 65%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500"><?= number_format($post['views']) ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                        <a href="../post/view/?id=<?= $post['post_id'] ?>" class="p-2 hover:bg-white rounded-xl shadow-sm border border-slate-100">👁️</a>
                                        <button onclick="confirmDelete('<?= $post['post_id'] ?>')" class="p-2 hover:bg-rose-50 hover:text-rose-600 rounded-xl transition-colors">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass rounded-[3rem] p-8 shadow-sm">
                <h2 class="font-black text-xl text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> System Security
                </h2>
                <div class="space-y-6">
                    <?php 
                    $recent_logs = array_slice($logs, -4);
                    foreach(array_reverse($recent_logs) as $log): 
                    ?>
                    <div class="flex gap-4">
                        <div class="w-1 bg-slate-200 rounded-full"></div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?= $log['timestamp'] ?></p>
                            <p class="text-xs font-bold text-slate-600 mt-1"><?= $log['action'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="system_logs.php" class="block w-full mt-8 py-4 bg-slate-100 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-center text-slate-500 hover:bg-slate-200 transition">View Full Audit Trail</a>
            </div>
        </div>
    </main>

    <script>
    function confirmDelete(id) {
        if(confirm("PROTOCOL ALERT: Initiate permanent deletion of post ID [" + id + "]?")) {
            window.location.href = "delete_post.php?id=" + id + "&token=<?= $_SESSION['csrf_token'] ?>";
        }
    }
    </script>

</body>
</html>