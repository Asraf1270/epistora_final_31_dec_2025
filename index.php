<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db_engine.php';

$all_posts = DBEngine::readJSON("posts.json") ?? [];
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'guest'; // Roles: guest, user, writer, v_writer, admin
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Shuffle posts for discovery
if (!empty($all_posts)) {
    shuffle($all_posts);
}

$json_posts = json_encode($all_posts, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Epistora | Discover</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { 
                extend: { 
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#4f46e5' }
                } 
            }
        }
    </script>
    <style>
        @keyframes reveal { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .post-card { animation: reveal 0.6s cubic-bezier(0.19, 1, 0.22, 1) forwards; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(14px); }
        .dark .glass { background: rgba(15, 23, 42, 0.9); }
        #mobile-menu { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

<header class="sticky top-0 z-[60] glass border-b border-slate-200 dark:border-slate-800">
  <div class="max-w-7xl mx-auto px-4 md:px-8 py-4 flex items-center justify-between">
    <div class="flex items-center gap-8">
        <a href="index.php" class="font-black text-2xl tracking-tighter text-primary italic">EPISTORA</a>
        <div class="hidden lg:flex relative w-64">
            <input type="text" id="searchInput" placeholder="Search..." 
                   class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-2xl py-2 px-10 text-sm focus:ring-2 focus:ring-primary outline-none">
            <span class="absolute left-3.5 top-2.5 opacity-40">🔍</span>
        </div>
    </div>

    <div class="hidden md:flex items-center gap-5">
        <a href="index.php" class="text-sm font-bold hover:text-primary transition-all">Home</a>
        <button id="themeToggle" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">🌙</button>
        
        <?php if($user_id): ?>
            <?php if($user_role === 'admin'): ?>
                <a href="admin/" class="text-sm font-bold bg-amber-50 text-amber-700 px-4 py-2 rounded-xl border border-amber-200 hover:bg-amber-100">Admin</a>
            <?php endif; ?>

            <?php if(in_array($user_role, ['writer', 'v_writer', 'admin'])): ?>
                <a href="post/create/" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-primary">Write</a>
                <a href="user/dashboard/" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-primary">Dashboard</a>
            <?php endif; ?>

            <?php if($user_role === 'user'): ?>
                <a href="user/apply_writer/index.php" class="text-sm font-bold text-primary">Become a Writer</a>
            <?php endif; ?>

            <a href="user/logout.php" class="text-sm font-bold bg-rose-50 text-rose-600 px-4 py-2 rounded-xl border border-rose-100 hover:bg-rose-100 transition-all">Logout</a>
        
        <?php else: ?>
            <a href="user/login/" class="text-sm font-bold text-slate-600 dark:text-slate-300">Login</a>
            <a href="user/register/" class="text-sm font-bold bg-primary text-white px-5 py-2.5 rounded-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all">Join Now</a>
        <?php endif; ?>
    </div>

    <button id="menuBtn" class="md:hidden p-2 text-2xl">☰</button>
  </div>
</header>

<div id="mobile-menu" class="fixed inset-0 z-[70] translate-x-full md:hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleMenu()"></div>
    <nav class="absolute right-0 top-0 h-full w-72 bg-white dark:bg-slate-900 p-8 shadow-2xl flex flex-col">
        <div class="flex justify-between items-center mb-10">
            <span class="font-black text-primary italic">EPISTORA</span>
            <button onclick="toggleMenu()" class="text-2xl text-slate-400">✕</button>
        </div>
        
        <div class="flex flex-col gap-6 flex-1 text-sm font-bold">
            <a href="index.php">🏠 Home</a>
            
            <?php if($user_id): ?>
                <?php if($user_role === 'admin'): ?>
                    <a href="admin/" class="text-amber-600">🛡️ Admin Panel</a>
                <?php endif; ?>

                <?php if(in_array($user_role, ['writer', 'v_writer', 'admin'])): ?>
                    <a href="post/create/">✍️ Write Story</a>
                    <a href="user/dashboard/">📊 Dashboard</a>
                <?php endif; ?>

                <?php if($user_role === 'user'): ?>
                    <a href="user/apply_writer/" class="text-primary">🚀 Become a Writer</a>
                <?php endif; ?>

                <a href="user/logout.php" class="text-rose-500 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">🚪 Logout</a>
            <?php else: ?>
                <a href="user/login/">🔑 Login</a>
                <a href="user/register/" class="text-primary">✨ Join Now</a>
            <?php endif; ?>
        </div>
        <button id="themeToggleMob" class="mt-auto w-full text-left font-bold text-slate-400 text-xs py-4 border-t border-slate-100 dark:border-slate-800 uppercase tracking-widest italic">🌓 Change Theme</button>
    </nav>
</div>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div id="post-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"></div>
    <div id="sentinel" class="py-20 flex flex-col items-center">
        <div id="loader" class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p id="end-msg" class="hidden text-slate-400 font-bold text-[10px] uppercase">End of feed</p>
    </div>
</main>

<script>
const allStories = <?= $json_posts ?>;
let filteredStories = [...allStories];
const grid = document.getElementById('post-grid');
const loader = document.getElementById('loader');
const endMsg = document.getElementById('end-msg');
const searchInput = document.getElementById('searchInput');

let index = 0;
const BATCH_SIZE = 9;

function renderCard(post) {
    const views = post.views || 0;
    const reacts = post.reaction_count || 0;
    const initial = post.author ? post.author.charAt(0).toUpperCase() : '?';

    return `
    <article class="post-card group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 rounded-[2.5rem] hover:border-primary/30 transition-all duration-500 flex flex-col">
        <div class="flex items-center gap-2 mb-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">
            <span class="text-primary">Discovery</span>
            <span>•</span>
            <span>${post.date}</span>
        </div>
        <h2 class="text-xl font-bold mb-4 leading-tight group-hover:text-primary transition-colors">
            <a href="post/view/?id=${post.post_id}">${post.title}</a>
        </h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm line-clamp-2 leading-relaxed mb-8">
            ${post.preview}
        </p>
        <div class="mt-auto flex items-center justify-between pt-6 border-t border-slate-50 dark:border-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-primary flex items-center justify-center font-bold text-[10px]">
                    ${initial}
                </div>
                <span class="text-xs font-bold">${post.author}</span>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-black text-slate-400">
                <span>👁️ ${views}</span>
                <span class="text-rose-500">❤️ ${reacts}</span>
            </div>
        </div>
    </article>`;
}

function loadBatch() {
    if (index >= filteredStories.length) {
        loader.classList.add('hidden');
        if(filteredStories.length > 0) endMsg.classList.remove('hidden');
        return;
    }
    const chunk = filteredStories.slice(index, index + BATCH_SIZE);
    grid.insertAdjacentHTML('beforeend', chunk.map(p => renderCard(p)).join(''));
    index += BATCH_SIZE;
}

const menu = document.getElementById('mobile-menu');
function toggleMenu() { menu.classList.toggle('translate-x-full'); }
document.getElementById('menuBtn').onclick = toggleMenu;

searchInput.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    grid.innerHTML = ''; index = 0;
    loader.classList.remove('hidden'); endMsg.classList.add('hidden');
    filteredStories = allStories.filter(p => p.title.toLowerCase().includes(term) || p.author.toLowerCase().includes(term));
    loadBatch();
});

const observer = new IntersectionObserver(ent => { if(ent[0].isIntersecting) loadBatch(); });
observer.observe(document.getElementById('sentinel'));

const themeFn = () => {
    document.documentElement.classList.toggle('dark');
    localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
};
document.getElementById('themeToggle').onclick = themeFn;
document.getElementById('themeToggleMob').onclick = themeFn;
if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
</script>
</body>
</html>