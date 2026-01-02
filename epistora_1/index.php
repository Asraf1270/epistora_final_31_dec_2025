<?php
// /epistora/index.php
// Smart Discovery Feed - personalized scoring algorithm
// Loads only metadata for performance

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Smart Scoring Algorithm
function score_post(array $post, int $current_user_id, array $vault): float
{
    $score = 0.0;

    $age_hours = (time() - $post['created_at']) / 3600;
    $score += log(max($post['reactions_count'] + 1, 1)) * 10;           // Reactions boost
    $score += $post['comments_count'] * 3;                              // Engagement
    $score += $post['views_count'] * 0.5;                               // Popularity

    // Freshness boost (decay over time)
    $score += max(50 - $age_hours, 0);

    // Follow boost: if current user follows author
    $follows = $vault['follows'] ?? [];
    if (in_array($post['author_id'], $follows)) {
        $score += 30;
    }

    // Anti-spam: pending posts only visible to author/nimda
    if ($post['status'] !== 'published') {
        return -999; // Exclude from feed
    }

    return $score;
}

// Load all published posts metadata
$posts_index = json_read(POSTS_INDEX, true);
$feed_posts = [];

// Filter and score
$user_vault = $_SESSION['vault'] ?? ['follows' => []];
$current_user_id = $_SESSION['user_id'] ?? 0;

foreach ($posts_index as $post_id => $post) {
    if ($post['status'] !== 'published') {
        // Allow author and nimda to see their pending posts
        if (!is_logged_in() || 
            ($post['author_id'] != $current_user_id && get_user_role() < ROLES['nimda'])) {
            continue;
        }
    }

    $post['post_id'] = $post_id;
    $post['score'] = score_post($post, $current_user_id, $user_vault);
    $feed_posts[] = $post;
}

// Sort by score descending
uasort($feed_posts, fn($a, $b) => $b['score'] <=> $a['score']);

// Limit to top 30 for performance
$feed_posts = array_slice($feed_posts, 0, 30, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= SITE_NAME ?> - Discovery Feed</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/interactions.js" defer></script>
</head>
<body>
    <header>
        <h1><a href="/"><?= SITE_NAME ?></a></h1>
        <nav>
            <?php if (is_logged_in()): ?>
                <a href="/user/profile/?u=<?= urlencode($_SESSION['username']) ?>">Profile</a>
                <a href="/user/setting/">Settings</a>
                <a href="/user/notifications/">Notifications</a>
                <a href="/post/create/">Write</a>
                <a href="/user/login/?logout=1">Logout</a>
            <?php else: ?>
                <a href="/user/login/">Login</a>
                <a href="/user/register/">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="feed">
        <h2>Discovery Feed <?= is_logged_in() ? '(Personalized)' : '' ?></h2>

        <?php if (empty($feed_posts)): ?>
            <p>No posts yet. Be the first to <a href="/post/create/">write one</a>!</p>
        <?php else: ?>
            <?php foreach ($feed_posts as $post): ?>
                <article class="post-card" data-post-id="<?= $post['post_id'] ?>">
                    <h3><a href="/post/view/?id=<?= $post['post_id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a></h3>
                    <p class="excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>

                    <div class="meta">
                        <span>by <a href="/user/profile/?u=<?= urlencode($post['author_name']) ?>">
                            <?= htmlspecialchars($post['author_name']) ?>
                        </a></span>
                        <span>• <?= date('M j, Y', $post['created_at']) ?></span>
                        <span>• <?= $post['reactions_count'] ?> ♥</span>
                        <span>• <?= $post['comments_count'] ?> 💬</span>
                    </div>

                    <div class="actions">
                        <button class="like-btn" data-action="like">
                            ❤️ Like (<span class="like-count"><?= $post['reactions_count'] ?></span>)
                        </button>

                        <button class="comment-btn" onclick="location.href='/post/view/?id=<?= $post['post_id'] ?>#comments'">
                            💬 Comment
                        </button>

                        <?php if (is_logged_in() && $post['author_id'] != $_SESSION['user_id']): ?>
                            <button class="follow-btn" data-user-id="<?= $post['author_id'] ?>">
                                Follow @<?= htmlspecialchars($post['author_name']) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>