<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

$root = dirname(__DIR__);
require_once $root . '/proxy/config.php';
require_once $root . '/includes/api.php';
require_once $root . '/includes/api-scoped-loader.php';

$queueFile = $root . '/cache/api-refresh-queue.json';
$lockFile = $root . '/cache/api-refresh.lock';
$lock = @fopen($lockFile, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit;
}

try {
    while (is_file($queueFile)) {
        $pending = json_decode((string) file_get_contents($queueFile), true);
        @unlink($queueFile);
        if (!is_array($pending) || $pending === []) {
            break;
        }

        $fresh = cmsApiCurlMultiFetch($pending);
        $cacheFile = cmsApiScopedCachePath();
        $stored = ['keys' => [], 'times' => []];
        if (is_file($cacheFile)) {
            $decoded = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($decoded) && cmsApiScopedCacheHasLiveData($decoded)) {
                $stored['keys'] = is_array($decoded['keys'] ?? null) ? $decoded['keys'] : [];
                $stored['times'] = is_array($decoded['times'] ?? null) ? $decoded['times'] : [];
            }
        }

        $now = time();
        foreach ($fresh as $key => $value) {
            if (!cmsApiResponseIsUsable($value)) {
                if (!isset($stored['keys'][$key])) {
                    $stored['keys'][$key] = ['status' => 'success', 'data' => []];
                    $stored['times'][$key] = $now;
                }
                continue;
            }
            $stored['keys'][$key] = $value;
            $stored['times'][$key] = $now;
        }

        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $encoded = json_encode($stored);
        if ($encoded !== false) {
            @file_put_contents($cacheFile, $encoded, LOCK_EX);
        }
    }
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
