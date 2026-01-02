<?php require 'header.php'; ?>
<h2>Cache Management</h2>

<?php
if (isset($_POST['clear_all'])) {
    cache_clear_all();
    echo '<p style="color:green;">All cache cleared!</p>';
}
?>

<form method="POST">
    <?= csrf_field() ?>
    <button name="clear_all" value="1" class="btn danger">Clear All Cache Files</button>
</form>

<p>Current cache files: <?= count(glob(CACHE_PATH . '/*.json')) + count(glob(CACHE_FRAGMENTS . '/*.json')) ?></p>
</body></html>