<?php
// /epistora/cache.php
// Simple filesystem cache with expiration

declare(strict_types=1);

if (!defined('BASE_PATH')) exit;

/**
 * Get cached data if not expired
 */
function cache_get(string $key, int $max_age = 3600): mixed
{
    $file = CACHE_PATH . '/' . md5($key) . '.json';

    if (!file_exists($file)) {
        return null;
    }

    $meta = json_read($file, true);
    if (!is_array($meta) || !isset($meta['expires'], $meta['data'])) {
        return null;
    }

    if (time() > $meta['expires']) {
        @unlink($file);
        return null;
    }

    return $meta['data'];
}

/**
 * Store data in cache
 */
function cache_set(string $key, mixed $data, int $ttl = 3600): bool
{
    $file = CACHE_PATH . '/' . md5($key) . '.json';

    $payload = [
        'expires' => time() + $ttl,
        'data'    => $data
    ];

    return json_write($file, $payload);
}

/**
 * Invalidate specific cache key
 */
function cache_invalidate(string $key): bool
{
    $file = CACHE_PATH . '/' . md5($key) . '.json';
    return file_exists($file) ? @unlink($file) : true;
}

/**
 * Clear all cache (used in admin panel)
 */
function cache_clear_all(): void
{
    $files = glob(CACHE_PATH . '/*.json');
    foreach ($files as $file) {
        @unlink($file);
    }

    // Also clear fragments
    $fragments = glob(CACHE_FRAGMENTS . '/*.json');
    foreach ($fragments as $file) {
        @unlink($file);
    }
}