<?php

declare(strict_types=1);

/** Default CMS file-cache freshness window (seconds). CMS edits should appear within ~1–2 minutes site-wide. */
if (!defined('CMS_DEFAULT_CONTENT_CACHE_TTL')) {
    define('CMS_DEFAULT_CONTENT_CACHE_TTL', 60);
}

if (!defined('CMS_DEFAULT_STALE_GRACE_TTL')) {
    define('CMS_DEFAULT_STALE_GRACE_TTL', 60);
}

if (!function_exists('cmsApiUsesFileCache')) {
    /**
     * File cache keeps responses fast. CMS keys refresh in the background after cmsApiContentCacheTtl().
     * Set CMS_FILE_CACHE=0 to disable (not recommended; every page then waits on the CMS API).
     */
    function cmsApiUsesFileCache(): bool
    {
        if (isset($_GET['clear_cache']) || isset($_GET['clear-cache']) || isset($_GET['nocache'])) {
            $cacheFile = function_exists('cmsApiScopedCachePath') ? cmsApiScopedCachePath() : '';
            if ($cacheFile !== '' && is_file($cacheFile)) {
                @unlink($cacheFile);
            }
            return false;
        }

        $env = getenv('CMS_FILE_CACHE');
        if ($env !== false) {
            $value = strtolower(trim((string) $env));

            if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }

            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }

        return true;
    }
}

if (!function_exists('cmsApiContentCacheTtl')) {
    /** Fresh window (seconds). After this, keys revalidate in the background or on the next request. */
    function cmsApiContentCacheTtl(): int
    {
        $env = getenv('CMS_CONTENT_CACHE_TTL');
        if ($env !== false && is_numeric($env)) {
            return max(30, min(120, (int) $env));
        }

        return CMS_DEFAULT_CONTENT_CACHE_TTL;
    }
}

if (!function_exists('cmsApiStaleGraceTtl')) {
    /**
     * After TTL expires, keep serving cached JSON briefly while refreshing in a separate process.
     * Past TTL + grace, the next request fetches live CMS data synchronously.
     */
    function cmsApiStaleGraceTtl(): int
    {
        $env = getenv('CMS_STALE_GRACE');
        if ($env !== false && is_numeric($env)) {
            return max(0, min(120, (int) $env));
        }

        return CMS_DEFAULT_STALE_GRACE_TTL;
    }
}

if (!function_exists('cmsApiMaxContentStaleSeconds')) {
    /** Maximum age (seconds) of CMS JSON we will still serve before forcing a live fetch. */
    function cmsApiMaxContentStaleSeconds(): int
    {
        return cmsApiContentCacheTtl() + cmsApiStaleGraceTtl();
    }
}

if (!function_exists('cmsApiEndpointTtl')) {
    /**
     * Per-endpoint cache TTL (seconds). Every CMS key uses the same TTL for predictable refresh site-wide.
     */
    function cmsApiEndpointTtl(string $key): int
    {
        unset($key);

        if (!cmsApiUsesFileCache()) {
            return 0;
        }

        return cmsApiContentCacheTtl();
    }
}