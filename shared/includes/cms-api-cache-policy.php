<?php

declare(strict_types=1);

if (!function_exists('cmsApiUsesFileCache')) {
    /**
     * File cache keeps responses fast. CMS keys refresh in the background after cmsApiContentCacheTtl() (default 10s).
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
    /** Seconds before CMS keys are refreshed in the background. Visitors are not blocked on this wait. */
    function cmsApiContentCacheTtl(): int
    {
        $env = getenv('CMS_CONTENT_CACHE_TTL');
        if ($env !== false && is_numeric($env)) {
            return max(10, (int) $env);
        }

        return 10;
    }
}

if (!function_exists('cmsApiStaleGraceTtl')) {
    /**
     * After TTL expires, keep serving cached JSON while refreshing in the background.
     * Avoids 5–10s API waits on cache expiry. Set CMS_STALE_GRACE=0 to disable.
     */
    function cmsApiStaleGraceTtl(): int
    {
        $env = getenv('CMS_STALE_GRACE');
        if ($env !== false && is_numeric($env)) {
            return max(0, (int) $env);
        }

        return 3600;
    }
}

if (!function_exists('cmsApiEndpointTtl')) {
    /**
     * Per-endpoint cache TTL (seconds). Every CMS key uses the same short TTL so edits show within about 10 seconds.
     * Expired keys are still served immediately and refreshed after the response is sent.
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
