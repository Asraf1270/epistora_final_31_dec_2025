<?php
$all_posts_index = DBEngine::readJSON("posts.json") ?? [];
$unique_tags = [];

// Extract all tags from the index
foreach ($all_posts_index as $p) {
    if (isset($p['tags']) && is_array($p['tags'])) {
        foreach ($p['tags'] as $t) {
            $unique_tags[] = trim($t);
        }
    }
}
$unique_tags = array_unique($unique_tags);
asort($unique_tags); // Alphabetical order
?>

<nav class="top-bar" style="background: #2c3e50; color: white; padding: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: auto;">
        <div class="logo">
            <a href="/index.php" style="color: white; font-weight: bold; text-decoration: none; font-size: 1.5em;">EPISTORA</a>
        </div>

        <form action="/search.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search titles, authors, tags..." style="padding: 8px; border-radius: 4px; border: none; width: 300px;">
            <button type="submit">🔍</button>
        </form>

        <div class="user-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/user/dashboard/" style="color: white; margin-right: 15px;">Dashboard</a>
                <a href="/user/logout.php" style="color: #ecf0f1;">Logout</a>
            <?php else: ?>
                <a href="/user/login/" style="color: white;">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="tag-ribbon" style="background: #34495e; padding: 8px 0; margin-top: 10px; overflow-x: auto; white-space: nowrap;">
        <div style="max-width: 1200px; margin: auto; padding: 0 15px;">
            <span style="font-size: 0.8em; color: #bdc3c7; margin-right: 10px;">TOPICS:</span>
            <?php foreach (array_slice($unique_tags, 0, 10) as $tag): ?>
                <a href="/search.php?q=<?= urlencode($tag) ?>" style="color: #3498db; background: #fff; padding: 2px 10px; border-radius: 15px; text-decoration: none; font-size: 0.85em; margin-right: 8px;">
                    #<?= htmlspecialchars($tag) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>