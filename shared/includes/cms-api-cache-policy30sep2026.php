<?php

declare(strict_types=1);

if (!function_exists('cmsApiUsesFileCache')) {
    /**
     * File cache keeps responses fast. CMS content keys use cmsApiContentCacheTtl() (default 5 min).
     * Set CMS_FILE_CACHE=0 to disable (not recommended on production).
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

        if (function_exists('is_app_local') && is_app_local()) {
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
    /** Seconds before editable CMS page/home keys refresh from the API. */
    function cmsApiContentCacheTtl(): int
    {
        $env = getenv('CMS_CONTENT_CACHE_TTL');
        if ($env !== false && is_numeric($env)) {
            return max(60, (int) $env);
        }

        return 300;
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
     * Per-endpoint cache TTL (seconds). Structural data gets longer TTL for speed.
     */
    function cmsApiEndpointTtl(string $key): int
    {
        if (!cmsApiUsesFileCache()) {
            return 0;
        }

        $contentTtl = cmsApiContentCacheTtl();

        if (str_starts_with($key, 'page_')) {
            return $contentTtl;
        }

        return match ($key) {
            'home_data', 'landing_data' => $contentTtl,
            'scroll_text_data', 'flyer_data', 'blogData' => max($contentTtl, 600),
            'statistics_data', 'statistic_data' => 1800,
            'photo_gallery_data', 'achievement_gallery_data', 'gallery_data', 'jobs_data' => 1800,
            // Menu/nav is also force-refetched (cmsApiEndpointRequiresLiveFetch) so CMS
            // add/remove is visible on the next page load — not stuck for up to 2 hours.
            'menu_data' => $contentTtl,
            'header_footer_data' => max($contentTtl, 300),
            // Keep short so newly created CMS pages resolve without waiting hours.
            'pages_index' => max($contentTtl, 600),
            'footer_important_links', 'footer_quick_links', 'footer_hierarchical' => 24 * 3600,
            'school_sessions_data', 'image_alt_data' => 3600,
            'grades_data', 'cities_data' => 24 * 3600,
            default => $contentTtl,
        };

        if (str_starts_with($key, 'legacy_')) {
            return 3600;
        }
    }
}
