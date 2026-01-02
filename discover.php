<?php
session_start();
require_once 'config.php';
require_once 'db_engine.php';

$writers = DBEngine::readJSON("writers.json") ?? [];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <title>Epistora — Discover</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>

<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">

<!-- ================= HEADER ================= -->
<header class="sticky top-0 z-50 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-4">
        <a href="/" class="font-bold text-xl">Epistora</a>

        <input type="text"
               placeholder="Search stories…"
               class="flex-1 px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none">

        <button id="themeToggle"
                class="px-3 py-2 rounded-full bg-slate-200 dark:bg-slate-700">
            🌙
        </button>
    </div>
</header>

<!-- ================= WRITERS ================= -->
<section class="max-w-7xl mx-auto px-4 mt-6">
    <h2 class="font-semibold mb-3">Popular Writers</h2>

    <div class="flex gap-4 overflow-x-auto pb-2">
        <?php foreach(array_slice($writers, 0, 6) as $w): ?>
            <div class="min-w-[220px] bg-white dark:bg-slate-800 rounded-xl p-4 shadow">
                <div class="flex items-center gap-3">
                    <img src="<?= $w['avatar'] ?? 'assets/img/avatar.png' ?>"
                         class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <div class="font-semibold"><?= htmlspecialchars($w['name']) ?></div>
                        <div class="text-xs text-slate-500"><?= $w['followers'] ?> followers</div>
                    </div>
                </div>
                <a href="writer.php?id=<?= $w['id'] ?>"
                   class="block mt-3 text-center bg-blue-600 text-white py-1 rounded-lg text-sm">
                    View Profile
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ================= FEED ================= -->
<main class="max-w-7xl mx-auto px-4 mt-10">
    <div id="postGrid"
         class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Skeletons injected by JS -->
    </div>

    <div id="loader" class="text-center py-10 hidden">
        <span class="text-slate-400">Loading more stories…</span>
    </div>
</main>

<script src="assets/js/app.js"></script>
</body>
</html>
