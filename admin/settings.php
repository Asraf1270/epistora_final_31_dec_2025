<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

// Load or Initialize System State
$state = DBEngine::readJSON("system_state.json") ?? [
    'site_name' => 'Epistora',
    'site_description' => 'A place for stories.',
    'maintenance_mode' => false,
    'allow_registration' => true,
    'last_updated_by' => 'System'
];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect Data
    $state['site_name']        = $_POST['site_name'] ?? $state['site_name'];
    $state['site_description'] = $_POST['site_description'] ?? $state['site_description'];
    $state['maintenance_mode'] = isset($_POST['m_mode']);
    $state['allow_registration'] = isset($_POST['reg_mode']);
    $state['last_updated_by']  = $_SESSION['user_name'];
    
    // Save to JSON
    DBEngine::writeJSON("system_state.json", $state);
    
    // Log for Strength
    DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'SYSTEM_CONFIG', "Updated global settings. Maintenance: " . ($state['maintenance_mode'] ? 'ON' : 'OFF'));
    
    $message = "Settings applied successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #2563eb; }
        input:checked + .slider:before { transform: translateX(20px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col md:flex-row">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10">
        <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-[900] tracking-tight text-slate-900">System Configuration</h1>
                <p class="text-slate-500 font-medium">Core engine and environment settings.</p>
            </div>
            <?php if($message): ?>
                <div class="bg-emerald-500 text-white px-6 py-2 rounded-2xl text-xs font-black uppercase animate-bounce"><?= $message ?></div>
            <?php endif; ?>
        </header>

        <form method="POST" class="max-w-4xl space-y-8">
            
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-sm">
                <h3 class="text-lg font-black mb-6 text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Site Identity
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Platform Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($state['site_name']) ?>" 
                               class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 font-bold focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Tagline</label>
                        <input type="text" name="site_description" value="<?= htmlspecialchars($state['site_description']) ?>" 
                               class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 font-bold focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="font-black text-slate-800">Maintenance Mode</h3>
                            <label class="switch">
                                <input type="checkbox" name="m_mode" <?= $state['maintenance_mode'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Locks the front-end for everyone except Administrators. Useful for major updates.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-50 text-[10px] font-bold text-amber-500 uppercase tracking-widest">
                        ⚠️ Impacts all public routes
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="font-black text-slate-800">Open Registration</h3>
                            <label class="switch">
                                <input type="checkbox" name="reg_mode" <?= ($state['allow_registration'] ?? true) ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            When off, new users cannot create accounts. Use this to prevent spam or bot attacks.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-50 text-[10px] font-bold text-blue-500 uppercase tracking-widest">
                        🛡️ Security recommendation: ON
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between bg-slate-900 rounded-[2rem] p-6 shadow-xl">
                <div class="hidden md:block">
                    <p class="text-white/40 text-[10px] font-black uppercase tracking-widest">Last Update By</p>
                    <p class="text-white font-bold text-sm"><?= $state['last_updated_by'] ?></p>
                </div>
                <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-500/30">
                    Apply Global Changes
                </button>
            </div>

        </form>
    </main>

</body>
</html>