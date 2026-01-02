<?php
require_once '../config.php';
require_once '../db_engine.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$posts = DBEngine::readJSON("posts.json") ?? [];
$results = [];

if (strlen($query) >= 2) {
    foreach ($posts as $p) {
        $matchTitle = str_contains(strtolower($p['title']), $query);
        $matchTags  = false;
        
        foreach ((array)($p['tags'] ?? []) as $tag) {
            if (str_contains(strtolower($tag), $query)) { $matchTags = true; break; }
        }

        if ($matchTitle || $matchTags) {
            $results[] = [
                'id' => $p['post_id'],
                'title' => $p['title'],
                'type' => $matchTitle ? 'Article' : 'Topic'
            ];
        }
        if (count($results) >= 6) break; // Keep it fast
    }
}

echo json_encode($results);