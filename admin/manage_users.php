<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

$user_files = glob(DATA_PATH . "user_data/*.json");
$message = "";

if (isset($_POST['update_role'])) {
    $uid = $_POST['user_id'];
    $new_role = $_POST['role'];
    $vault = DBEngine::readJSON("user_data/$uid.json");
    if ($vault) {
        $vault['role'] = $new_role;
        DBEngine::writeJSON("user_data/$uid.json", $vault);
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'ROLE_UPDATE', "User: $uid to $new_role");
        $message = "Role updated.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen flex flex-col md:flex-row font-['Inter']">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-10">
        <header class="mb-10">
            <h1 class="text-3xl font-[900] tracking-tight text-slate-900">User Management</h1>
            <p class="text-slate-500 font-medium">Control permissions and verified status.</p>
            <?php if($message): ?>
                <div class="mt-4 bg-green-600 text-white text-[10px] font-black uppercase px-4 py-1 rounded-full w-fit"><?= $message ?></div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($user_files as $file): 
                $user = json_decode(file_get_contents($file), true);
                if (!$user) continue;
            ?>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col justify-between group hover:border-blue-500 transition-all duration-300">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xl">
                            <?= strtoupper(substr($user['profile']['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($user['profile']['name'] ?: 'Guest User') ?></h3>
                            <p class="text-[9px] font-mono text-slate-400 truncate w-32 uppercase"><?= $user['user_id'] ?></p>
                        </div>
                    </div>
                    <div class="text-[9px] font-black px-2 py-1 bg-slate-100 rounded uppercase tracking-tighter">
                        <?= $user['role'] ?>
                    </div>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <div>
                        <label class="text-[9px] font-black uppercase text-slate-400 mb-2 block">Assign Permission</label>
                        <select name="role" onchange="this.form.submit()" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="writer" <?= $user['role'] == 'writer' ? 'selected' : '' ?>>Writer</option>
                            <option value="v_writer" <?= $user['role'] == 'v_writer' ? 'selected' : '' ?>>Verified Writer</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                        <input type="hidden" name="update_role" value="1">
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>