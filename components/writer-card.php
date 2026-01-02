<?php if (!isset($writer)) return; ?>

<div class="flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-xl shadow">
    <img src="<?= $writer['avatar'] ?? '/assets/avatar.png' ?>"
         class="w-12 h-12 rounded-full object-cover">

    <div class="flex-1">
        <div class="font-semibold"><?= htmlspecialchars($writer['name']) ?></div>
        <div class="text-xs text-slate-500">
            <?= $writer['followers'] ?? 0 ?> followers
        </div>
    </div>

    <button class="px-3 py-1 text-xs rounded-full bg-blue-600 text-white hover:bg-blue-700">
        Follow
    </button>
</div>
