<?php
// admin/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page, $current) {
    return ($page == $current) 
        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 scale-[1.02]' 
        : 'text-slate-400 hover:bg-slate-800/50 hover:text-white hover:translate-x-1';
}

// Simulated count for the pending badge
$pending_badge = 3; 
?>

<div class="md:hidden fixed top-0 left-0 right-0 z-[100] px-6 py-4 bg-white/80 backdrop-blur-lg border-b border-slate-200 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xs font-black italic shadow-lg shadow-blue-500/20">E</div>
        <span class="font-black tracking-tighter text-slate-900 italic">EPISTORA <span class="text-blue-600">ADMIN</span></span>
    </div>
    <button id="menuToggle" class="p-2.5 bg-slate-900 text-white rounded-xl shadow-lg active:scale-90 transition-all">
        <div id="hamburgerIcon" class="space-y-1.5">
            <span class="block w-5 h-0.5 bg-white transition-all duration-300"></span>
            <span class="block w-5 h-0.5 bg-white transition-all duration-300"></span>
            <span class="block w-3 h-0.5 bg-white transition-all duration-300"></span>
        </div>
    </button>
</div>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[110] hidden transition-opacity duration-300 opacity-0"></div>

<aside id="adminSidebar" class="fixed inset-y-0 left-0 z-[120] w-72 bg-slate-950 text-white transform -translate-x-full transition-all duration-500 cubic-bezier(0.4, 0, 0.2, 1) md:translate-x-0 md:static md:flex flex-col shrink-0 border-r border-slate-800">
    <div class="p-8 mb-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center text-xl font-black italic shadow-xl shadow-blue-600/20">E</div>
            <div>
                <h2 class="text-xl font-black tracking-tighter text-white italic leading-none">EPISTORA</h2>
                <span class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em]">Command Center</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 custom-scrollbar overflow-y-auto">
        <div class="space-y-8">
            <div>
                <p class="px-4 mb-4 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Analytics & Data</p>
                <ul class="space-y-2">
                    <li><a href="../index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('index.php', $current_page) ?>"><span class="text-lg">📊</span> Dashboard</a></li>
                    <li>
                        <a href="pending/" class="flex items-center justify-between px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('pending.php', $current_page) ?>">
                            <div class="flex items-center gap-3"><span class="text-lg">⏳</span> Pending</div>
                            <?php if($pending_badge > 0): ?>
                            <span class="bg-amber-500 text-[10px] px-2 py-0.5 rounded-full animate-pulse">New</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="../manage_posts.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('manage_posts.php', $current_page) ?>"><span class="text-lg">📝</span> Manage Posts</a></li>
                    <li><a href="../manage_users.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('manage_users.php', $current_page) ?>"><span class="text-lg">👥</span> Manage Users</a></li>
                </ul>
            </div>
            <div>
                <p class="px-4 mb-4 text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">Workflow</p>
                <ul class="space-y-2">
                    <li><a href="../manage_applications.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('manage_applications.php', $current_page) ?>"><span class="text-lg">📩</span> Writer Apps</a></li>
                    <li><a href="../system_logs.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('system_logs.php', $current_page) ?>"><span class="text-lg">📜</span> System Logs</a></li>
                    <li><a href="../settings.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-bold text-sm transition-all duration-300 <?= isActive('system_logs.php', $current_page) ?>"><span class="text-lg">⚙️</span> Settings</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="p-6 mt-4">
        <div class="p-1 bg-slate-900 rounded-[2rem]">
            <a href="../index.php" class="flex items-center justify-center gap-3 py-4 bg-slate-800 hover:bg-rose-600 text-white rounded-[1.8rem] font-black text-[10px] uppercase tracking-[0.2em] transition-all duration-300">
                <span>🚀</span> Exit Terminal
            </a>
        </div>
    </div>
</aside>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const hamSpans = document.querySelectorAll('#hamburgerIcon span');

    function toggleAdminMenu() {
        const isHidden = adminSidebar.classList.contains('-translate-x-full');
        if (isHidden) {
            adminSidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            setTimeout(() => sidebarOverlay.classList.add('opacity-100'), 10);
            hamSpans[0].classList.add('rotate-45', 'translate-y-2');
            hamSpans[1].classList.add('opacity-0');
            hamSpans[2].classList.add('-rotate-45', '-translate-y-2', 'w-5');
        } else {
            adminSidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.remove('opacity-100');
            setTimeout(() => sidebarOverlay.classList.add('hidden'), 300);
            hamSpans[0].classList.remove('rotate-45', 'translate-y-2');
            hamSpans[1].classList.remove('opacity-0');
            hamSpans[2].classList.remove('-rotate-45', '-translate-y-2', 'w-5');
        }
    }
    menuToggle.addEventListener('click', toggleAdminMenu);
    sidebarOverlay.addEventListener('click', toggleAdminMenu);
</script>