<?php
session_start();
require_once '../db_engine.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? '';
    $type = $_POST['type'] ?? '';

    // 1. UPDATE DETAILED CONTENT FILE
    // Path relative to htdocs/data/
    $content_path = "post_content/$post_id.json";
    $post_data = DBEngine::readJSON($content_path);

    if ($post_data) {
        if (!isset($post_data['reactions'])) {
            $post_data['reactions'] = ['love'=>0, 'insight'=>0, 'clap'=>0, 'laugh'=>0];
        }
        
        // Increment the specific reaction
        if (array_key_exists($type, $post_data['reactions'])) {
            $post_data['reactions'][$type]++;
        }
        
        DBEngine::writeJSON($content_path, $post_data);

        // 2. UPDATE GLOBAL INDEX (for homepage display)
        $index_path = "posts.json";
        $index = DBEngine::readJSON($index_path) ?? [];
        
        foreach ($index as &$p) {
            if ($p['post_id'] === $post_id) {
                // Store the sum of all reaction types in the index
                $p['reaction_count'] = array_sum($post_data['reactions']);
                break;
            }
        }
        DBEngine::writeJSON($index_path, $index);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Post data not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid Request']);
}