<?php 
include 'header.php'; 

// 1. DATA PATHS
$postsFile = 'data/posts.json';
$usersFile = 'data/users.json';

// Load Data
$allPosts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];
$allUsers = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// 2. FILTER: ONLY SHOW APPROVED POSTS
$approvedPosts = array_filter($allPosts, function($post) {
    return isset($post['status']) && $post['status'] === 'approved';
});

// 3. GENERATE TAG CLOUD FROM APPROVED POSTS
$tagCloud = [];
foreach($approvedPosts as $p) {
    if(isset($p['tags']) && is_array($p['tags'])) {
        foreach($p['tags'] as $t) { 
            $tagCloud[] = strtolower(trim($t)); 
        }
    }
}
$uniqueTags = array_unique($tagCloud);
sort($uniqueTags);

// 4. SORT POSTS (NEWEST FIRST)
uasort($approvedPosts, function($a, $b) {
    return $b['created'] <=> $a['created'];
});

$verifiedBadge = '
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-left:4px; vertical-align:middle;">
    <path d="M22.5 12.5L20.84 10.61L21.1 8.12L18.67 7.57L17.4 5.4L15.04 6.41L12.92 4.66L10.8 6.41L8.44 5.4L7.17 7.57L4.74 8.12L5 10.61L3.34 12.5L5 14.39L4.74 16.88L7.17 17.43L8.44 19.6L10.8 18.59L12.92 20.34L15.04 18.59L17.4 19.6L18.67 17.43L21.1 16.88L20.84 14.39L22.5 12.5Z" fill="#1d9bf0"/>
    <path d="M10.29 16L6 11.71L7.41 10.3L10.29 13.18L16.59 6.88L18 8.29L10.29 16Z" fill="white"/>
</svg>';
?>

<style>
    :root { --primary: #007bff; --bg: #f4f7f6; --text: #333; }
    body { background-color: var(--bg); color: var(--text); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    /* Search & Navigation Bar */
    .top-nav-box { background: #fff; padding: 25px 0; border-bottom: 1px solid #e0e0e0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .search-wrapper { max-width: 600px; margin: 0 auto; position: relative; }
    .search-input { width: 100%; padding: 12px 25px; border-radius: 30px; border: 1px solid #ddd; font-size: 1rem; outline: none; transition: 0.3s; }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }

    /* Tag Filtering */
    .tag-scroll { display: flex; justify-content: center; gap: 10px; margin-top: 15px; overflow-x: auto; padding: 5px; scrollbar-width: none; }
    .tag-scroll::-webkit-scrollbar { display: none; }
    .tag-pill { background: #eee; padding: 6px 18px; border-radius: 20px; font-size: 0.85rem; cursor: pointer; white-space: nowrap; transition: 0.3s; border: 1px solid transparent; }
    .tag-pill:hover, .tag-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* Grid Layout */
    .post-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; padding: 40px 0; }
    .post-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; display: flex; flex-direction: column; }
    .post-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .card-content { padding: 20px; flex-grow: 1; }
    .post-author { font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .post-title { font-size: 1.25rem; font-weight: 700; line-height: 1.4; margin-bottom: 12px; }
    .post-title a { text-decoration: none; color: #222; transition: 0.2s; }
    .post-title a:hover { color: var(--primary); }
    .post-excerpt { color: #666; font-size: 0.95rem; line-height: 1.6; }

    /* Card Footer Stats */
    .card-stats { padding: 15px 20px; background: #fafafa; border-top: 1px solid #f0f0f0; display: flex; gap: 15px; font-size: 0.85rem; color: #888; }
    .stat-item { display: flex; align-items: center; gap: 5px; }

    .no-results { text-align: center; padding: 50px; display: none; }
</style>

<div class="top-nav-box">
    <div class="container">
        <div class="search-wrapper">
            <input type="text" id="siteSearch" class="search-input" placeholder="Search titles, content, or #tags...">
        </div>
        <div class="tag-scroll">
            <div class="tag-pill active" onclick="filterTag('all', this)">All Stories</div>
            <?php foreach($uniqueTags as $tag): ?>
                <div class="tag-pill" onclick="filterTag('<?php echo $tag; ?>', this)">#<?php echo $tag; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container">
    <div id="postsGrid" class="post-grid">
        <?php foreach($approvedPosts as $id => $post): 
            $authorData = $allUsers[$post['author_id']] ?? null;
            $authorName = $authorData['username'] ?? 'Anonymous';
            $isVerified = ($authorData['role'] ?? '') === 'v_writer';
            $tagsArray = $post['tags'] ?? [];
            $tagString = implode(',', $tagsArray);
        ?>
            <div class="post-card" 
                 data-searchable="<?php echo strtolower($post['title'] . ' ' . $post['body'] . ' ' . $authorName . ' ' . $tagString); ?>"
                 data-tags="<?php echo strtolower($tagString); ?>">
                
                <div class="card-content">
                    <span class="post-author">
                        <?php echo htmlspecialchars($authorName); ?> 
                        <?php if($isVerified) echo $verifiedBadge; ?>
                    </span>
                    <h2 class="post-title">
                        <a href="post/view/index.php?id=<?php echo $id; ?>">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </h2>
                    <p class="post-excerpt"><?php echo htmlspecialchars($post['body']); ?></p>
                </div>

                <div class="card-stats">
                    <div class="stat-item">👁️ <?php echo number_format($post['views'] ?? 0); ?></div>
                    <div class="stat-item">❤️ <?php echo number_format($post['reactions'] ?? 0); ?></div>
                    <div class="stat-item">💬 <?php echo number_format($post['comments'] ?? 0); ?></div>
                    <div class="stat-item" style="margin-left:auto; font-size: 0.75rem;">
                        <?php echo date("M d", $post['created']); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="noResults" class="no-results">
        <h3>No matches found</h3>
        <p>Try searching for different keywords or tags.</p>
    </div>
</div>

<script>
const searchBar = document.getElementById('siteSearch');
const postsGrid = document.getElementById('postsGrid');
const allCards = document.querySelectorAll('.post-card');
const noResults = document.getElementById('noResults');

// 1. Real-time Search Logic
searchBar.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    let visibleCount = 0;

    allCards.forEach(card => {
        const searchContent = card.getAttribute('data-searchable');
        if (searchContent.includes(query)) {
            card.style.display = "flex";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    noResults.style.display = (visibleCount === 0) ? "block" : "none";
});

// 2. Real-time Tag Filtering Logic
function filterTag(tag, element) {
    // Reset Search Bar
    searchBar.value = '';
    
    // Update active UI state
    document.querySelectorAll('.tag-pill').forEach(p => p.classList.remove('active'));
    element.classList.add('active');

    let visibleCount = 0;
    allCards.forEach(card => {
        const cardTags = card.getAttribute('data-tags').split(',');
        if (tag === 'all' || cardTags.includes(tag.toLowerCase())) {
            card.style.display = "flex";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    noResults.style.display = (visibleCount === 0) ? "block" : "none";
}
</script>

<?php include 'footer.php'; ?>