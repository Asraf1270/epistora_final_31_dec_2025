<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

$app_id = $_GET['id'] ?? die("Application ID required.");
$app = DBEngine::readJSON("applications/$app_id.json");

if (!$app) die("Application not found.");

// --- DECISION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $uid = $app['user_id'];

    if ($action === 'approve') {
        $user_vault = DBEngine::readJSON("user_data/$uid.json");
        $user_vault['role'] = 'writer';
        $user_vault['writer_status'] = 'approved';
        DBEngine::writeJSON("user_data/$uid.json", $user_vault);

        $app['status'] = 'approved';
        DBEngine::writeJSON("applications/$app_id.json", $app);
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'APPROVE_WRITER', "Promoted user $uid to Writer.");
        
        header("Location: index.php?msg=approved");
    } else {
        $app['status'] = 'rejected';
        DBEngine::writeJSON("applications/$app_id.json", $app);
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'REJECT_WRITER', "Rejected app $app_id.");
        header("Location: index.php?msg=rejected");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?= htmlspecialchars($app['biodata']['full_name']) ?> | Epistora Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 font-['Inter'] min-h-screen flex flex-col md:flex-row">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10">
        <a href="index.php" class="text-sm font-bold text-slate-400 hover:text-blue-600 transition flex items-center gap-2 mb-6">
            <span>←</span> Back to Dashboard
        </a>

        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-200 shadow-sm mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-full">
                                <?= $app['status'] ?> Application
                            </span>
                            <span class="text-slate-300 text-xs">ID: <?= $app_id ?></span>
                        </div>
                        <h1 class="text-4xl font-[900] tracking-tight text-slate-900">
                            <?= htmlspecialchars($app['biodata']['full_name']) ?>
                        </h1>
                    </div>
                    
                    <?php if($app['status'] === 'pending'): ?>
                    <form method="POST" class="flex gap-3 w-full md:w-auto">
                        <button type="submit" name="action" value="reject" 
                                onclick="return confirm('Reject this applicant?')"
                                class="flex-1 md:flex-none px-6 py-3 border-2 border-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-100 rounded-2xl font-bold transition">
                            Reject
                        </button>
                        <button type="submit" name="action" value="approve" 
                                class="flex-1 md:flex-none px-8 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded-2xl font-bold shadow-lg shadow-blue-500/30 transition">
                            Approve & Promote
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 pt-12 border-t border-slate-50">
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Contact Email</p>
                        <p class="font-bold text-slate-700"><?= $app['biodata']['email'] ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Phone Number</p>
                        <p class="font-bold text-slate-700"><?= $app['biodata']['phone'] ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Location</p>
                        <p class="font-bold text-slate-700 line-clamp-1"><?= htmlspecialchars($app['biodata']['address']) ?></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <section class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                            Writing Sample
                        </h3>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed italic bg-slate-50 p-8 rounded-2xl border border-slate-100 font-serif text-lg">
                            <?= nl2br(htmlspecialchars($app['portfolio']['sample'])) ?>
                        </div>
                    </section>
                </div>

                <div class="space-y-8">
                    <section class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm">
                        <h3 class="font-black text-sm uppercase tracking-widest text-slate-400 mb-6">Personal Details</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400">Father's Name</p>
                                <p class="text-sm font-bold"><?= htmlspecialchars($app['biodata']['father_name']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400">Date of Birth</p>
                                <p class="text-sm font-bold"><?= $app['biodata']['dob'] ?></p>
                            </div>
                        </div>
                    </section>

                    <section class="bg-blue-600 rounded-[2rem] p-8 text-white shadow-xl shadow-blue-500/20">
                        <h3 class="font-black text-sm uppercase tracking-widest opacity-70 mb-6">Portfolio Links</h3>
                        <div class="flex flex-col gap-3">
                            <?php foreach($app['portfolio']['bookmarks'] as $link): ?>
                                <a href="<?= $link ?>" target="_blank" class="bg-white/10 hover:bg-white/20 p-3 rounded-xl text-xs font-bold truncate transition">
                                    🔗 <?= str_replace(['https://', 'http://'], '', $link) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

</body>
</html>