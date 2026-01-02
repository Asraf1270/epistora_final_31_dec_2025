<?php
session_start();
require_once 'config.php';
require_once 'db_engine.php';

/* ---------- LOAD ALL DATA ---------- */
$all_posts = DBEngine::readJSON("posts.json") ?? [];
$user_id   = $_SESSION['user_id'] ?? null;

/* ---------- REMOVE ALGO & SHUFFLE ---------- */
// This replaces the complex algo_score logic with a simple random shuffle
shuffle($all_posts); 

// Prepare the JSON for JavaScript to handle the loading
$json_posts = json_encode($all_posts);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epistora | Random Stories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>

<body class="bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 transition-colors duration-300">

<header class="sticky top-0 z-50 bg-white/95 dark:bg-slate-900/95 border-b border-slate-200 dark:border-slate-700 backdrop-blur">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="index.php" class="font-black text-blue-600 text-2xl tracking-tighter">EPISTORA</a>
    <div class="flex gap-4 items-center">
        <?php if($user_id): ?>
            <a href="user/dashboard/" class="text-sm font-semibold">Dashboard</a>
        <?php else: ?>
            <a href="user/login/" class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-bold">Sign In</a>
        <?php endif; ?>
        <button id="themeToggle" class="p-2">🌙</button>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Random Feed</h1>
        <p class="text-slate-500 dark:text-slate-400">Discover something new and unexpected.</p>
    </div>

    <div id="post-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        </div>

    <div id="sentinel" class="h-24 flex items-center justify-center mt-10">
        <div id="loader" class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p id="end-msg" class="hidden text-slate-400 font-medium">✨ You've seen all the random stories for now!</p>
    </div>
</main>

<script>
/* 1. DATA */
const allStories = <?= $json_posts ?>;
const postGrid = document.getElementById('post-grid');
const loader = document.getElementById('loader');
const endMsg = document.getElementById('end-msg');

let currentIndex = 0;
const CHUNK_SIZE = 6; 

/* 2. CARD COMPONENT */
function createCard(post) {
    const preview = post.preview || (post.content ? post.content.substring(0, 120) + "..." : "No preview available.");
    const tag = (post.tags && post.tags[0]) ? post.tags[0] : "Explore";
    
    return `
    <article class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:border-blue-500 transition-all duration-300 flex flex-col">
        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-2">${tag}</span>
        <h2 class="text-xl font-bold mb-4">
            <a href="post/view/?id=${post.post_id}" class="hover:text-blue-600 transition">${post.title}</a>
        </h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm line-clamp-3 mb-6">${preview}</p>
        <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center text-xs">
            <span class="font-bold">${post.author}</span>
            <div class="flex gap-3 text-slate-400">
                <span>👁 ${post.views || 0}</span>
            </div>
        </div>
    </article>`;
}

/* 3. INFINITE LOAD LOGIC */
function loadMore() {
    if (currentIndex >= allStories.length) {
        loader.classList.add('hidden');
        endMsg.classList.remove('hidden');
        return;
    }

    const nextBatch = allStories.slice(currentIndex, currentIndex + CHUNK_SIZE);
    nextBatch.forEach(post => {
        postGrid.insertAdjacentHTML('beforeend', createCard(post));
    });

    currentIndex += CHUNK_SIZE;
}

/* 4. OBSERVER */
const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
        loadMore();
    }
}, { threshold: 0.1 });

observer.observe(document.getElementById('sentinel'));

/* 5. THEME TOGGLE */
const root = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const applyTheme = (t) => {
    root.classList.toggle('dark', t === 'dark');
    themeToggle.textContent = t === 'dark' ? '☀️' : '🌙';
    localStorage.theme = t;
};
applyTheme(localStorage.theme || 'light');
themeToggle.onclick = () => applyTheme(root.classList.contains('dark') ? 'light' : 'dark');
</script>

</body>
</html>