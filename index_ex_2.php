<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db_engine.php';

$all_posts = DBEngine::readJSON("posts.json") ?? [];
$user_id   = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Sort by latest date
if (!empty($all_posts)) {
    usort($all_posts, function($a, $b) {
        return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
    });
}

$json_posts = json_encode($all_posts, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Epistora | Discover Stories</title>
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
        body::-webkit-scrollbar { width: 5px; }
        body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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
        <div class="hidden lg:flex relative w-80">
            <input type="text" id="searchInput" placeholder="Search stories..." 
                   class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-2xl py-2 px-10 text-sm focus:ring-2 focus:ring-primary outline-none">
            <span class="absolute left-3.5 top-2.5 opacity-40">🔍</span>
        </div>
    </div>

    <div class="hidden md:flex items-center gap-6">
        <button id="themeToggle" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">🌙</button>
        
        <?php if($user_id): ?>
            <a href="post/create/" class="text-sm font-bold text-slate-600 hover:text-primary transition-all">Write</a>
            <a href="user/dashboard/" class="text-sm font-bold text-slate-600 hover:text-primary transition-all">Dashboard</a>
            <a href="user/logout.php" class="text-sm font-bold bg-rose-50 text-rose-600 px-4 py-2 rounded-xl hover:bg-rose-100 border border-rose-100 transition-all">Logout</a>
        <?php else: ?>
            <a href="user/login/" class="text-sm font-bold text-slate-600 hover:text-primary transition-all underline decoration-2 decoration-primary/20 underline-offset-4">Become a Writer</a>
            <a href="user/login/" class="text-sm font-bold text-slate-600 hover:text-primary transition-all">Login</a>
            <a href="user/register/" class="text-sm font-bold bg-primary text-white px-5 py-2.5 rounded-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all">Get Started</a>
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
        
        <div class="flex flex-col gap-6 flex-1">
            <a href="index.php" class="text-lg font-bold flex items-center gap-3">🏠 Home</a>
            <?php if($user_id): ?>
                <a href="post/create/" class="text-lg font-bold flex items-center gap-3">✍️ Write Story</a>
                <a href="user/dashboard/" class="text-lg font-bold flex items-center gap-3">👤 Dashboard</a>
                <a href="user/logout.php" class="text-lg font-bold text-rose-500 flex items-center gap-3">🚪 Logout</a>
            <?php else: ?>
                <a href="user/login/" class="text-lg font-bold flex items-center gap-3 text-primary">🚀 Become a Writer</a>
                <a href="user/login/" class="text-lg font-bold flex items-center gap-3">🔑 Login</a>
                <a href="user/register/" class="text-lg font-bold flex items-center gap-3">✨ Join Now</a>
            <?php endif; ?>
        </div>

        <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
            <button id="themeToggleMob" class="w-full text-left font-bold text-slate-400">🌓 Toggle Dark Mode</button>
        </div>
    </nav>
</div>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">Feed Your Mind.</h1>
        <p class="text-slate-500 dark:text-slate-400 max-w-xl">A collection of human stories, technical insights, and creative perspectives from our global community.</p>
    </div>

    <div id="searchInfo" class="hidden mb-10 p-4 bg-primary/5 rounded-2xl border border-primary/10">
        <p class="text-primary font-medium">Results for: <span id="searchTerm" class="font-bold underline"></span></p>
    </div>

    <div id="post-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        </div>

    <div id="sentinel" class="py-20 flex flex-col items-center">
        <div id="loader" class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p id="end-msg" class="hidden text-slate-400 font-bold tracking-widest text-[10px] uppercase">You're all caught up!</p>
    </div>
</main>

<?php if($user_id): ?>
<a href="post/create/" class="md:hidden fixed bottom-6 right-6 w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center text-2xl shadow-2xl z-40 hover:scale-110 transition-all">
    +
</a>
<?php endif; ?>

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
    <article class="post-card group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-[2.5rem] hover:border-primary/30 transition-all duration-500 flex flex-col">
        <div class="flex items-center gap-2 mb-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            <span class="text-primary">New Story</span>
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
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-[10px]">
                    ${initial}
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">${post.author}</span>
            </div>
            
            <div class="flex items-center gap-3 text-[10px] font-black">
                <span class="flex items-center gap-1 text-slate-400">👁️ ${views}</span>
                <span class="flex items-center gap-1 text-rose-500">❤️ ${reacts}</span>
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

// Mobile Menu Control
const menu = document.getElementById('mobile-menu');
function toggleMenu() { menu.classList.toggle('translate-x-full'); }
document.getElementById('menuBtn').onclick = toggleMenu;

// Search System
searchInput.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    grid.innerHTML = ''; index = 0;
    loader.classList.remove('hidden'); endMsg.classList.add('hidden');

    filteredStories = allStories.filter(p => 
        p.title.toLowerCase().includes(term) || 
        p.author.toLowerCase().includes(term)
    );

    const info = document.getElementById('searchInfo');
    if (term.trim() !== "") {
        info.classList.remove('hidden');
        document.getElementById('searchTerm').innerText = term;
    } else {
        info.classList.add('hidden');
    }
    loadBatch();
});

// Intersection Observer
const observer = new IntersectionObserver(ent => { if(ent[0].isIntersecting) loadBatch(); });
observer.observe(document.getElementById('sentinel'));

// Theme Toggle
const toggle = (el) => {
    el.onclick = () => {
        document.documentElement.classList.toggle('dark');
        localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    };
}
toggle(document.getElementById('themeToggle'));
toggle(document.getElementById('themeToggleMob'));
if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
</script>
</body>
</html>