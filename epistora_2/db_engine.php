<?php
// /epistora/db_engine.php
// JSON-based CRUD engine with atomic file locking and safe updates

declare(strict_types=1);

if (!defined('BASE_PATH')) exit;

/**
 * Safely read a JSON file with locking
 */
function json_read(string $file, bool $assoc = true): mixed
{
    if (!file_exists($file)) {
        return $assoc ? [] : null;
    }

    $fp = fopen($file, 'r');
    if (!$fp) return $assoc ? [] : null;

    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return json_decode($content ?: '[]', $assoc) ?: ($assoc ? [] : null);
}

/**
 * Safely write JSON with exclusive lock and atomic feel
 */
function json_write(string $file, mixed $data): bool
{
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $tmp_file = $file . '.tmp.' . uniqid();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($tmp_file, $json) === false) {
        return false;
    }

    $fp = fopen($file, 'c'); // Create if not exists
    if (!$fp || !flock($fp, LOCK_EX)) {
        @unlink($tmp_file);
        return false;
    }

    // Truncate and write
    ftruncate($fp, 0);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    // Replace original atomically
    rename($tmp_file, $file);
    @chmod($file, 0644);

    return true;
}

/**
 * Atomic update: read → modify → write with lock
 */
function json_update(string $file, callable $callback): bool
{
    $data = json_read($file, true);

    $new_data = $callback($data);

    return json_write($file, $new_data);
}

/**
 * Get next ID from index file (e.g., posts.json or users.json)
 */
function get_next_id(string $index_file): int
{
    $data = json_read($index_file, true);
    return $data ? max(array_keys($data)) + 1 : 1;
}