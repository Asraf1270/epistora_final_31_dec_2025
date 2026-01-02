<article class="bg-white dark:bg-slate-800 rounded-2xl shadow hover:-translate-y-1 transition p-6">
    <span class="text-xs font-bold text-blue-600 uppercase">
        <?= htmlspecialchars($post['tags'][0] ?? 'General') ?>
    </span>

    <h2 class="mt-2 text-lg font-semibold">
        <a href="post/view/?id=<?= $post['post_id'] ?>" class="hover:text-blue-600">
            <?= htmlspecialchars($post['title']) ?>
        </a>
    </h2>

    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
        <?= htmlspecialchars($post['preview'] ?? '') ?>
    </p>

    <div class="mt-4 flex justify-between text-xs text-slate-500">
        <span><?= htmlspecialchars($post['author']) ?></span>
        <span>👁 <?= $post['views'] ?? 0 ?></span>
    </div>
</article>
