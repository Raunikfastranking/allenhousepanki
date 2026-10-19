<?php

declare(strict_types=1);

$_cmsApiPolicy = dirname(__DIR__) . '/shared/includes/cms-api-cache-policy.php';
if (is_file($_cmsApiPolicy)) {
    require_once $_cmsApiPolicy;
}

if (!function_exists('cmsApiResolveLoadFlags')) {
    /**
     * @return array{core: bool, home: bool, blog: bool, gallery: bool, careers: bool}
     */
    function cmsApiResolveLoadFlags(): array
    {
        $flags = [
            'core' => true,
            'home' => false,
            'blog' => false,
            'gallery' => false,
            'careers' => false,
        ];

        if (isset($GLOBALS['cms_api_load_flags']) && is_array($GLOBALS['cms_api_load_flags'])) {
            foreach ($GLOBALS['cms_api_load_flags'] as $key => $value) {
                if (array_key_exists($key, $flags)) {
                    $flags[$key] = (bool) $value;
                }
            }

            return $flags;
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? 'index.php');

        if ($script === 'index.php' || !empty($GLOBALS['school_is_index_page'])) {
            $flags['home'] = true;
        }
        if (in_array($script, ['blog.php', 'detail.php'], true)) {
            $flags['blog'] = true;
        }
        if ($script === 'print-media-detail.php') {
            $flags['gallery'] = true;
        }
        if (in_array($script, ['apply-jobs.php', 'job-apply.php'], true)) {
            $flags['careers'] = true;
        }
        if (in_array($script, [
            'x-toppers.php',
            'xii-toppers.php',
            'topper-class-X.php',
            'topper-class-XII.php',
            'topper-class-x.php',
            'topper-class-xii.php',
        ], true)) {
            $flags['gallery'] = true;
        }

        $slug = strtolower(trim((string) ($GLOBALS['cms_page_slug'] ?? '')));
        if ($slug !== '') {
            if (str_starts_with($slug, 'home-page')) {
                $flags['home'] = true;
            }
            if (
                str_contains($slug, 'blogs-page')
                || str_contains($slug, 'blog-page')
                || str_ends_with($slug, '-blog')
                || $slug === 'blog'
            ) {
                $flags['blog'] = true;
            }
            if (
                str_contains($slug, 'gallery')
                || str_contains($slug, 'print-media')
                || str_contains($slug, 'achievements')
                || str_contains($slug, 'media-events')
                || str_contains($slug, 'video-gallery')
                || str_contains($slug, 'awards')
                || str_contains($slug, 'topper')
                || str_contains($slug, 'board-topper')
            ) {
                $flags['gallery'] = true;
            }
            if (str_contains($slug, 'career') || str_contains($slug, 'job')) {
                $flags['careers'] = true;
            }
        }

        return $flags;
    }
}

if (!function_exists('cmsApiHomePageSlug')) {
    function cmsApiHomePageSlug(): string
    {
        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        if (function_exists('cmsCoreGet')) {
            $home = cmsCoreGet('home_page_slug');
            if (is_string($home) && $home !== '') {
                return $home;
            }
        }

        return 'home-page-agra';
    }
}

if (!function_exists('cmsApiBuildEndpoints')) {
  /**
   * @param array{core?: bool, home?: bool, blog?: bool, gallery?: bool, careers?: bool} $flags
   * @return array<string, string>
   */
    function cmsApiBuildEndpoints(array $flags, int $branchId): array
    {
        $endpoints = [
            'menu_data' => '/menus/branch/' . $branchId,
            'header_footer_data' => '/public/branches/' . $branchId . '/layout-parts/',
            'scroll_text_data' => '/scrolling-texts/branch/' . $branchId,
            'footer_important_links' => '/link-groups/important-links',
            'footer_quick_links' => '/link-groups/quick-links',
            'footer_hierarchical' => '/public/link-groups/position/footer/hierarchical?branch_id=' . $branchId,
            'pages_index' => '/pages?branch_id=' . $branchId,
            'school_sessions_data' => '/school-sessions',
            'image_alt_data' => '/image_alt_text/' . $branchId,
        ];

        if (!empty($flags['home'])) {
            $endpoints['home_data'] = '/pages/' . cmsApiHomePageSlug();
            $endpoints['statistic_data'] = '/statistics/branch/' . $branchId;
            $endpoints['flyer_data'] = '/flyers/branch/' . $branchId;
            $endpoints['photo_gallery_data'] = '/galleries/type/gallery/branch/' . $branchId;
            // $endpoints['grades_data'] = '/grades/' . $branchId;
            $endpoints['cities_data'] = '/cities/' . (defined('SCHOOL_ID') ? SCHOOL_ID : (string) $branchId);
        }

        if (!empty($flags['blog'])) {
            $endpoints['blogData'] = '/blogs/branch/' . $branchId;
        }

        if (!empty($flags['gallery'])) {
            $endpoints['photo_gallery_data'] = '/galleries/type/gallery/branch/' . $branchId;
            $endpoints['achievement_gallery_data'] = '/galleries/type/achievements/branch/' . $branchId;
            $endpoints['gallery_data'] = '/galleries/branch/' . $branchId;
        }

        if (!empty($flags['careers'])) {
            $endpoints['jobs_data'] = '/jobs/branch/' . $branchId;
        }

        return $endpoints;
    }
}


if (!function_exists('cmsApiEndpointRequiresLiveFetch')) {
    function cmsApiEndpointRequiresLiveFetch(string $key, string $endpoint): bool
    {
        // Nav must match CMS immediately (add/remove), not wait on the file cache.
        if ($key === 'menu_data') {
            return true;
        }

        if (!empty($GLOBALS['school_is_index_page']) && $key === 'home_data') {
            return true;
        }

        // Legacy of Excellence statistics must match CMS immediately on homepage.
        if (!empty($GLOBALS['school_is_index_page']) && $key === 'statistic_data') {
            return true;
        }

        $liveSlug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($liveSlug === '') {
            return false;
        }

        if ($key === 'home_data' && str_ends_with($endpoint, '/' . $liveSlug)) {
            return true;
        }

        return $key === 'page_' . $liveSlug;
    }
}

if (!function_exists('cmsApiInvalidateScopedCache')) {
    /** Invalidate one scoped cache key (file cache). */
    function cmsApiInvalidateScopedCache(string $key): void
    {
        $cacheFile = cmsApiScopedCachePath();
        if (!is_file($cacheFile)) {
            return;
        }
        $decoded = json_decode((string) file_get_contents($cacheFile), true);
        if (!is_array($decoded)) {
            return;
        }
        unset($decoded['keys'][$key], $decoded['times'][$key]);
        @file_put_contents($cacheFile, json_encode($decoded));
    }
}

if (!function_exists('cmsApiScopedCachePath')) {
    function cmsApiScopedCachePath(): string
    {
        return dirname(__DIR__) . '/cache/api-scoped-v4.json';
    }
}

if (!function_exists('cmsApiCurlMultiFetch')) {
    /**
     * @param array<string, string> $endpoints
     * @return array<string, array<string, mixed>>
     */
    function cmsApiCurlMultiFetch(array $endpoints): array
    {
        if ($endpoints === []) {
            return [];
        }

        $baseUrl = CMS_API_URL;
        $mh = curl_multi_init();
        $curlHandles = [];
        $responses = [];

        foreach ($endpoints as $key => $endpoint) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, api_auth_headers());
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            if (function_exists('apply_curl_ssl_options')) {
                apply_curl_ssl_options($ch);
            }
            curl_multi_add_handle($mh, $ch);
            $curlHandles[$key] = $ch;
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($status > CURLM_OK) {
                break;
            }
            curl_multi_select($mh, 1.0);
        } while ($running > 0);

        foreach ($curlHandles as $key => $ch) {
            $content = curl_multi_getcontent($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $decoded = json_decode((string) $content, true);
            if (($httpCode !== 200 || !is_array($decoded)) && defined('LEGACY_CMS_API_URL') && defined('CMS_API_URL')) {
                $legacyUrl = str_replace(CMS_API_URL, LEGACY_CMS_API_URL, $baseUrl . $endpoints[$key]);
                $rch = curl_init($legacyUrl);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($rch, CURLOPT_HTTPHEADER, api_auth_headers());
                curl_setopt($rch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($rch, CURLOPT_TIMEOUT, 8);
                curl_setopt($rch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($rch, CURLOPT_SSL_VERIFYHOST, 0);
                $content = curl_exec($rch);
                curl_close($rch);
                $decoded = json_decode((string) $content, true);
            }
            if (is_array($decoded) && function_exists('cmsFixUtf8Deep')) {
                $decoded = cmsFixUtf8Deep($decoded);
            }
            $responses[$key] = is_array($decoded) ? $decoded : ['status' => 'error', 'data' => []];
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        return $responses;
    }
}

if (!function_exists('cmsApiScopedCacheHasLiveData')) {
    function cmsApiScopedCacheHasLiveData(?array $stored): bool
    {
        if (!is_array($stored)) {
            return false;
        }

        $keys = $stored['keys'] ?? null;
        if (!is_array($keys)) {
            return false;
        }

        foreach ($keys as $value) {
            if (cmsApiResponseIsUsable(is_array($value) ? $value : null)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cmsScopedFileCacheGet')) {
    function cmsScopedFileCacheGet(string $key): ?array
    {
        if (function_exists('cmsApiUsesFileCache') && !cmsApiUsesFileCache()) {
            return null;
        }

        $cacheFile = cmsApiScopedCachePath();
        if (!is_file($cacheFile)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($cacheFile), true);
        if (!is_array($decoded)) {
            return null;
        }
        $value = $decoded['keys'][$key] ?? null;
        $fetchedAt = (int) ($decoded['times'][$key] ?? 0);
        $ttl = cmsApiEndpointTtl($key);
        if (!cmsApiResponseIsUsable(is_array($value) ? $value : null)) {
            return null;
        }
        if ($fetchedAt <= 0 || (time() - $fetchedAt) >= $ttl) {
            return null;
        }

        return $value;
    }
}

if (!function_exists('cmsScopedFileCacheGetAny')) {
    /** Read scoped cache entry ignoring TTL (fallback when live API fails). */
    function cmsScopedFileCacheGetAny(string $key): ?array
    {
        if (function_exists('cmsApiUsesFileCache') && !cmsApiUsesFileCache()) {
            return null;
        }

        $cacheFile = cmsApiScopedCachePath();
        if (!is_file($cacheFile)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($cacheFile), true);
        if (!is_array($decoded)) {
            return null;
        }
        $value = $decoded['keys'][$key] ?? null;

        return cmsApiResponseIsUsable(is_array($value) ? $value : null) ? $value : null;
    }
}

if (!function_exists('cmsApiStoreScopedKey')) {
    function cmsApiStoreScopedKey(string $key, array $value): void
    {
        if (!cmsApiResponseIsUsable($value)) {
            return;
        }

        if (function_exists('cmsApiUsesFileCache') && !cmsApiUsesFileCache()) {
            if (isset($GLOBALS['api_cache']) && is_array($GLOBALS['api_cache'])) {
                $GLOBALS['api_cache'][$key] = $value;
            }

            return;
        }

        $cacheFile = cmsApiScopedCachePath();
        $stored = ['keys' => [], 'times' => []];
        if (is_file($cacheFile)) {
            $decoded = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($decoded) && cmsApiScopedCacheHasLiveData($decoded)) {
                $stored['keys'] = is_array($decoded['keys'] ?? null) ? $decoded['keys'] : [];
                $stored['times'] = is_array($decoded['times'] ?? null) ? $decoded['times'] : [];
            }
        }
        $stored['keys'][$key] = $value;
        $stored['times'][$key] = time();
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($cacheFile, json_encode($stored));
        if (isset($GLOBALS['api_cache']) && is_array($GLOBALS['api_cache'])) {
            $GLOBALS['api_cache'][$key] = $value;
        }
    }
}

if (!function_exists('cmsApiRefreshScopedKeysAsync')) {
    /**
     * Refresh expired cache keys after the HTTP response is sent (stale-while-revalidate).
     *
     * @param array<string, string> $endpoints
     */
    function cmsApiRefreshScopedKeysAsync(array $endpoints): void
    {
        if ($endpoints === []) {
            return;
        }

        register_shutdown_function(static function () use ($endpoints): void {
            if (!function_exists('cmsApiCurlMultiFetch') || !function_exists('cmsApiScopedCachePath')) {
                return;
            }

            $fresh = cmsApiCurlMultiFetch($endpoints);
            $cacheFile = cmsApiScopedCachePath();
            $stored = ['keys' => [], 'times' => []];
            if (is_file($cacheFile)) {
                $decoded = json_decode((string) file_get_contents($cacheFile), true);
                if (
                    is_array($decoded)
                    && function_exists('cmsApiScopedCacheHasLiveData')
                    && cmsApiScopedCacheHasLiveData($decoded)
                ) {
                    $stored['keys'] = is_array($decoded['keys'] ?? null) ? $decoded['keys'] : [];
                    $stored['times'] = is_array($decoded['times'] ?? null) ? $decoded['times'] : [];
                }
            }

            $now = time();
            foreach ($fresh as $key => $value) {
                if (!cmsApiResponseIsUsable($value)) {
                    continue;
                }
                $stored['keys'][$key] = $value;
                $stored['times'][$key] = $now;
            }

            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            @file_put_contents($cacheFile, json_encode($stored));
        });
    }
}

if (!function_exists('cmsApiFetchScopedEndpoints')) {
    /**
     * @param array<string, string> $endpoints
     * @return array<string, array<string, mixed>>
     */
    function cmsApiFetchScopedEndpoints(array $endpoints, int $cacheTtl = 300): array
    {
        unset($cacheTtl);


        $cacheFile = cmsApiScopedCachePath();
        $now = time();
        $stored = ['keys' => [], 'times' => []];

        if (is_file($cacheFile)) {
            $readStart = microtime(true);
            $rawCache = (string) file_get_contents($cacheFile);
            $readMs = (microtime(true) - $readStart) * 1000;
            if (function_exists('cmsProfileRecordFileRead')) {
                cmsProfileRecordFileRead($cacheFile, $readMs, strlen($rawCache));
            }
            $decodeStart = microtime(true);
            $decoded = json_decode($rawCache, true);
            if (function_exists('cmsProfileRecordJsonDecode')) {
                cmsProfileRecordJsonDecode('api-scoped-v4.json', (microtime(true) - $decodeStart) * 1000, strlen($rawCache));
            }
            if (is_array($decoded) && cmsApiScopedCacheHasLiveData($decoded)) {
                $stored['keys'] = is_array($decoded['keys'] ?? null) ? $decoded['keys'] : [];
                $stored['times'] = is_array($decoded['times'] ?? null) ? $decoded['times'] : [];
            }
        }

        $toFetch = [];
        $staleRefresh = [];
        $grace = function_exists('cmsApiStaleGraceTtl') ? cmsApiStaleGraceTtl() : 0;

        foreach ($endpoints as $key => $endpoint) {
            $fetchedAt = (int) ($stored['times'][$key] ?? 0);
            $cachedValue = $stored['keys'][$key] ?? null;
            $keyTtl = function_exists('cmsApiEndpointTtl') ? cmsApiEndpointTtl($key) : 300;
            $hasStale = cmsApiResponseIsUsable(is_array($cachedValue) ? $cachedValue : null);
            $isExpired = $fetchedAt <= 0 || ($now - $fetchedAt) >= $keyTtl;
            $requiresLive = function_exists('cmsApiEndpointRequiresLiveFetch')
                && cmsApiEndpointRequiresLiveFetch($key, $endpoint);

            if ($requiresLive) {
                $toFetch[$key] = $endpoint;
                if (function_exists('cmsProfileRecordCacheMiss')) {
                    cmsProfileRecordCacheMiss($key, 'file-live');
                }
                continue;
            }

            if (!$hasStale) {
                $toFetch[$key] = $endpoint;
                if (function_exists('cmsProfileRecordCacheMiss')) {
                    cmsProfileRecordCacheMiss($key, 'file');
                }
                continue;
            }

            if ($isExpired) {
                // Never serve a stale nav — deleted CMS items must disappear immediately.
                $allowStale = $key !== 'menu_data'
                    && $grace > 0
                    && ($now - $fetchedAt) < ($keyTtl + $grace);
                if ($allowStale) {
                    $staleRefresh[$key] = $endpoint;
                    if (function_exists('cmsProfileRecordCacheHit')) {
                        cmsProfileRecordCacheHit($key, 'file-stale');
                    }
                    continue;
                }
                $toFetch[$key] = $endpoint;
                if (function_exists('cmsProfileRecordCacheMiss')) {
                    cmsProfileRecordCacheMiss($key, 'file');
                }
                continue;
            }

            if (function_exists('cmsProfileRecordCacheHit')) {
                cmsProfileRecordCacheHit($key, 'file');
            }
        }

        if ($toFetch !== []) {
            if (!empty($GLOBALS['cms_track_network']) && is_array($GLOBALS['cms_track_network'])) {
                $GLOBALS['cms_track_network']['count'] = (int) ($GLOBALS['cms_track_network']['count'] ?? 0) + count($toFetch);
                foreach ($toFetch as $key => $endpoint) {
                    $GLOBALS['cms_track_network']['urls'][] = CMS_API_URL . $endpoint;
                }
            }
            $fresh = cmsApiCurlMultiFetch($toFetch);
            foreach ($fresh as $key => $value) {
                if (!cmsApiResponseIsUsable($value)) {
                    if (!isset($stored['keys'][$key])) {
                        unset($stored['keys'][$key], $stored['times'][$key]);
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
            @file_put_contents($cacheFile, json_encode($stored));
        }

        if ($staleRefresh !== [] && function_exists('cmsApiRefreshScopedKeysAsync')) {
            cmsApiRefreshScopedKeysAsync($staleRefresh);
        }

        $result = [];
        foreach ($endpoints as $key => $_endpoint) {
            $result[$key] = $stored['keys'][$key] ?? ['status' => 'error', 'data' => []];
        }

        return $result;
    }
}

if (!function_exists('cmsApiSeedCurlCacheFromBulk')) {
    /**
     * Populate in-request curl JSON cache from bulk keys so prefetch skips duplicate network calls.
     *
     * @param array<string, array<string, mixed>> $data
     */
    function cmsApiSeedCurlCacheFromBulk(array $data): void
    {
        if (!defined('CMS_API_URL') || !function_exists('cms_curl_json_cache_ref')) {
            return;
        }

        $base = rtrim(CMS_API_URL, '/');
        $map = [
            'menu_data' => '/menus/branch/' . BRANCH_ID,
            'header_footer_data' => '/public/branches/' . BRANCH_ID . '/layout-parts/',
            'scroll_text_data' => '/scrolling-texts/branch/' . BRANCH_ID,
            'statistic_data' => '/statistics/branch/' . BRANCH_ID,
            'flyer_data' => '/flyers/branch/' . BRANCH_ID,
            'home_data' => '/pages/' . cmsApiHomePageSlug(),
            'photo_gallery_data' => '/galleries/type/gallery/branch/' . BRANCH_ID,
            'achievement_gallery_data' => '/galleries/type/achievements/branch/' . BRANCH_ID,
            'gallery_data' => '/galleries/branch/' . BRANCH_ID,
            'jobs_data' => '/jobs/branch/' . BRANCH_ID,
            'blogData' => '/blogs/branch/' . BRANCH_ID,
            'footer_important_links' => '/link-groups/important-links',
            'footer_quick_links' => '/link-groups/quick-links',
            'footer_hierarchical' => '/public/link-groups/position/footer/hierarchical?branch_id=' . BRANCH_ID,
            'pages_index' => '/pages?branch_id=' . BRANCH_ID,
            'grades_data' => '/grades/' . BRANCH_ID,
            'cities_data' => '/cities/' . (defined('SCHOOL_ID') ? SCHOOL_ID : (string) BRANCH_ID),
            'school_sessions_data' => '/school-sessions',
            'image_alt_data' => '/image_alt_text/' . BRANCH_ID,
        ];

        $cache = &cms_curl_json_cache_ref();
        $skipHomeSeed = !empty($GLOBALS['school_is_index_page']);
        $liveSlug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        foreach ($map as $key => $path) {
            if ($skipHomeSeed && $key === 'home_data') {
                continue;
            }
            if ($liveSlug !== '' && $key === 'home_data' && str_ends_with($path, '/' . $liveSlug)) {
                continue;
            }
            if (!isset($data[$key]) || !is_array($data[$key])) {
                continue;
            }
            if (!cmsApiResponseIsUsable($data[$key])) {
                continue;
            }
            $cache[$base . $path] = $data[$key];
        }
    }
}
