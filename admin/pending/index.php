<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

if ($_SESSION['role'] !== ROLE_ADMIN) die("Unauthorized.");

$message = "";

// 1. Approval Logic
if (isset($_GET['approve'])) {
    $pid = $_GET['approve'];
    $post_data = DBEngine::readJSON("post_content/$pid.json");

    if ($post_data) {
        $posts_index = DBEngine::readJSON("posts.json") ?? [];
        $posts_index[] = [
            "post_id" => $pid,
            "title"   => $post_data['title'],
            "preview" => substr(strip_tags($post_data['content']), 0, 150) . "...",
            "author"  => $post_data['author'],
            "date"    => date('Y-m-d')
        ];
        DBEngine::writeJSON("posts.json", $posts_index);

        $post_data['status'] = 'published';
        DBEngine::writeJSON("post_content/$pid.json", $post_data);
        
        $message = "Submission authorized and published successfully.";
    }
}

// 2. Rejection/Deletion Logic
if (isset($_GET['delete'])) {
    $pid = $_GET['delete'];
    $file_path = POST_CONTENT_PATH . "$pid.json";
    
    if (file_exists($file_path)) {
        unlink($file_path); // Physically removes the pending JSON file
        $message = "Submission has been rejected and permanently removed.";
    }
}

$files = glob(POST_CONTENT_PATH . "*.json");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Station | Epistora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid #e2e8f0; }
        .btn-reject { transition: all 0.3s ease; }
        .btn-reject:hover { background-color: #fff1f2; color: #e11d48; border-color: #fda4af; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-6 md:p-12 mt-16 md:mt-0">
        
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Pending Review</h1>
                <p class="text-slate-400 font-bold text-xs tracking-widest uppercase">Editorial Decision Terminal</p>
            </div>
            
            <?php if($message): ?>
                <div class="mt-4 md:mt-0 p-4 bg-slate-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest animate-bounce">
                    ⚡ <?= $message ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 gap-8">
            <?php 
            $count = 0;
            foreach ($files as $file): 
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['status']) && $data['status'] === 'pending'): 
                    $count++;
            ?>
                <div class="glass-card p-8 rounded-[3rem] shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-full -mr-16 -mt-16"></div>

                    <div class="relative z-10 flex flex-col lg:flex-row gap-10">
                        <div class="flex-1">
                            <h3 class="text-2xl font-black text-slate-800 mb-1"><?= htmlspecialchars($data['title']) ?></h3>
                            <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-6 italic">Submitted by <?= htmlspecialchars($data['author']) ?></p>
                            
                            <details class="group bg-slate-50 rounded-[2rem] border border-slate-100 overflow-hidden">
                                <summary class="list-none cursor-pointer p-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] flex justify-between items-center group-open:bg-slate-100 transition-all">
                                    Expand Full Manuscript
                                    <span class="transition-transform group-open:rotate-180">▼</span>
                                </summary>
                                <div class="p-8 text-slate-600 text-sm leading-relaxed border-t border-slate-100 bg-white">
                                    <?= nl2br($data['content']) ?>
                                </div>
                            </details>
                        </div>

                        <div class="lg:w-72 flex flex-col gap-3 justify-center">
                            <a href="?approve=<?= $data['post_id'] ?>" 
                               class="w-full py-5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all shadow-xl shadow-blue-100 text-center">
                                Approve & Publish
                            </a>
                            
                            <button onclick="confirmRejection('<?= $data['post_id'] ?>')" 
                               class="btn-reject w-full py-5 bg-white text-slate-400 border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] text-center">
                                Reject & Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php 
                endif; 
            endforeach; 

            if ($count === 0): ?>
                <div class="py-32 text-center glass-card rounded-[4rem] border-2 border-dashed border-slate-200">
                    <div class="text-5xl mb-4 opacity-20">📂</div>
                    <p class="text-slate-400 font-black text-xs uppercase tracking-[0.3em]">All Clear: No Pending Tasks</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function confirmRejection(pid) {
            if (confirm("PROTOCOL ALERT: This will permanently delete the submission for '" + pid + "'. Are you sure?")) {
                window.location.href = "?delete=" + pid;
            }
        }
    </script>
</body>
</html>