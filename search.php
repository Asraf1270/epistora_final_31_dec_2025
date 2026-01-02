<?php
session_start();
require_once 'config.php';
require_once 'db_engine.php';
require_once 'theme_engine.php';

$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$all_posts = DBEngine::readJSON("posts.json") ?? [];
$results = [];

if ($query !== '') {
    foreach ($all_posts as $post) {
        $in_title   = str_contains(strtolower($post['title']), $query);
        $in_author  = str_contains(strtolower($post['author']), $query);
        $in_tags    = false;

        // Check tags array
        if (isset($post['tags']) && is_array($post['tags'])) {
            foreach ($post['tags'] as $tag) {
                if (str_contains(strtolower($tag), $query)) {
                    $in_tags = true;
                    break;
                }
            }
        }

        if ($in_title || $in_author || $in_tags) {
            $results[] = $post;
        }
    }
} else {
    $results = $all_posts; // Show all if no query
}
?>

<div class="search-results">
    <h2>Search Results for: "<?= htmlspecialchars($query) ?>"</h2>
    <p><?= count($results) ?> stories found.</p>
    
    <div class="feed-container">
        <?php foreach ($results as $post): ?>
            <article class="post-card">
                <h3><a href="/post/view/?id=<?= $post['post_id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                <p>By <?= htmlspecialchars($post['author']) ?> • <?= implode(', ', (array)$post['tags']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</div>