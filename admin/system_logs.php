<?php
require_once 'auth_check.php';
require_once '../db_engine.php';

// Fetch and reverse logs so the newest activity is at the top
$logs = DBEngine::readJSON("system_logs.json") ?? [];
$logs = array_reverse($logs); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs | Epistora Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .log-row { animation: fadeIn 0.3s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(-5px); } to { opacity: 1; transform: translateX(0); } }
        /* Thin scrollbar for mobile tables */
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 flex min-h-screen flex-col md:flex-row">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10 overflow-x-hidden">
        <header class="mb-8">
            <h1 class="text-3xl font-[900] tracking-tight text-slate-900">Activity Stream</h1>
            <p class="text-slate-500 font-medium italic">Audit trail of all administrative events.</p>
        </header>

        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <input type="text" id="logSearch" placeholder="Filter by action, user, or details..." 
                       class="w-full bg-white border border-slate-200 px-10 py-3 rounded-2xl text-sm outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition-all">
                <span class="absolute left-4 top-3.5 opacity-30">🔍</span>
            </div>
            <div class="bg-slate-200/50 px-6 py-3 rounded-2xl flex items-center justify-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">Live Audit Active</span>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[700px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                            <th class="px-8 py-4">Timestamp</th>
                            <th class="px-8 py-4">Administrator</th>
                            <th class="px-8 py-4">Action Type</th>
                            <th class="px-8 py-4">Event Details</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody" class="divide-y divide-slate-50">
                        <?php foreach ($logs as $log): 
                            // Dynamic Badge Logic
                            $badgeStyle = 'bg-slate-100 text-slate-600';
                            if(strpos($log['action'], 'DELETE') !== false) $badgeStyle = 'bg-red-100 text-red-600';
                            if(strpos($log['action'], 'AUTH') !== false) $badgeStyle = 'bg-purple-100 text-purple-600';
                            if(strpos($log['action'], 'ROLE') !== false || strpos($log['action'], 'PROMOTION') !== false) $badgeStyle = 'bg-emerald-100 text-emerald-600';
                            if(strpos($log['action'], 'UPDATE') !== false) $badgeStyle = 'bg-blue-100 text-blue-600';
                        ?>
                        <tr class="log-row hover:bg-slate-50/80 transition">
                            <td class="px-8 py-4 font-mono text-[11px] text-slate-400">
                                <?= date('M d, H:i:s', strtotime($log['date'])) ?>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[10px] text-slate-600">
                                        <?= strtoupper(substr($log['admin_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 leading-none"><?= htmlspecialchars($log['admin_name']) ?></p>
                                        <p class="text-[9px] font-mono text-slate-400">ID: <?= $log['admin_id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4 text-sm font-medium">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-tighter <?= $badgeStyle ?>">
                                    <?= $log['action'] ?>
                                </span>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-xs text-slate-600 font-medium max-w-xs md:max-w-md truncate hover:whitespace-normal transition-all">
                                    <?= htmlspecialchars($log['details']) ?>
                                </p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(empty($logs)): ?>
            <div class="py-20 text-center">
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs italic">No system events recorded yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Real-time log filtering
        document.getElementById('logSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#logTableBody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    </script>
</body>
</html>