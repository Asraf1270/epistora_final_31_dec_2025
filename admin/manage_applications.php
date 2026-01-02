<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

// Fetch all application files
$app_files = glob(DATA_PATH . "applications/*.json");
$applications = [];

foreach ($app_files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data) {
        $data['id'] = basename($file, '.json'); // Get ID from filename
        $applications[] = $data;
    }
}

// Sort by date (Newest first)
usort($applications, function($a, $b) {
    return strtotime($b['submitted_at'] ?? 0) - strtotime($a['submitted_at'] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writer Inbox | Epistora Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex min-h-screen flex-col md:flex-row">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10">
        <header class="mb-10">
            <h1 class="text-3xl font-[900] tracking-tight">Writer Applications</h1>
            <p class="text-slate-500 font-medium">Review talent and grow your team of writers.</p>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total Apps</p>
                <h3 class="text-2xl font-black"><?= count($applications) ?></h3>
            </div>
            <div class="bg-amber-500 p-6 rounded-[2rem] text-white shadow-xl shadow-amber-500/20">
                <p class="text-[10px] font-black uppercase opacity-80 tracking-widest">Pending Review</p>
                <h3 class="text-2xl font-black">
                    <?= count(array_filter($applications, fn($a) => $a['status'] === 'pending')) ?>
                </h3>
            </div>
        </div>

        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <button onclick="filterApps('all')" class="filter-btn active bg-slate-900 text-white px-6 py-2 rounded-full text-xs font-bold">All</button>
            <button onclick="filterApps('pending')" class="filter-btn bg-white border border-slate-200 px-6 py-2 rounded-full text-xs font-bold hover:bg-slate-50">Pending</button>
            <button onclick="filterApps('approved')" class="filter-btn bg-white border border-slate-200 px-6 py-2 rounded-full text-xs font-bold hover:bg-slate-50">Approved</button>
        </div>

        <div class="space-y-4" id="appContainer">
            <?php foreach ($applications as $app): 
                $statusColor = 'bg-slate-100 text-slate-500';
                if($app['status'] === 'pending') $statusColor = 'bg-amber-100 text-amber-600';
                if($app['status'] === 'approved') $statusColor = 'bg-emerald-100 text-emerald-600';
                if($app['status'] === 'rejected') $statusColor = 'bg-red-100 text-red-600';
            ?>
            <div class="app-card bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-blue-500 transition-all duration-300" 
                 data-status="<?= $app['status'] ?>">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-slate-400">
                        <?= strtoupper(substr($app['biodata']['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800"><?= htmlspecialchars($app['biodata']['full_name']) ?></h3>
                        <p class="text-xs text-slate-500 font-medium"><?= $app['biodata']['email'] ?></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-[10px] font-black uppercase tracking-widest <?= $statusColor ?> px-3 py-1 rounded-lg">
                        <?= $app['status'] ?>
                    </span>
                    <div class="text-right hidden md:block">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Applied On</p>
                        <p class="text-xs font-bold"><?= date('M d, Y', strtotime($app['submitted_at'] ?? 'now')) ?></p>
                    </div>
                    <a href="view_application.php?id=<?= $app['id'] ?>" 
                       class="w-full md:w-auto text-center px-6 py-3 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-600 transition-colors">
                        Review File
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
    function filterApps(status) {
        // Toggle active button style
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-slate-900', 'text-white');
            btn.classList.add('bg-white', 'text-slate-900');
        });
        event.target.classList.add('bg-slate-900', 'text-white');

        // Filter cards
        const cards = document.querySelectorAll('.app-card');
        cards.forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>