<?php
require_once '../db_engine.php';

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$posts = DBEngine::readJSON("../posts.json") ?? [];
$chunk = array_slice($posts, $offset, $limit);

foreach ($chunk as $post) {
    include '../components/post-card.php';
}
