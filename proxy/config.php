<?php
// AWS private IP routing (Investigation Report §4.3) — avoids billable public loopback.
$internalEnv = getenv('CMS_INTERNAL_BASE_URL');
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
if (is_string($internalEnv) && trim($internalEnv) !== '') {
    define('API_BASE_URL', rtrim(trim($internalEnv), '/') . '/');
} elseif (str_starts_with($serverAddr, '172.31.')) {
    define('API_BASE_URL', 'http://172.31.1.199/');
} else {
    define('API_BASE_URL', 'https://cms.allenhouseschools.com/');
}
define('CMS_API_URL', rtrim(API_BASE_URL, '/') . '/api');
define('CMS_API_HOST_HEADER', str_starts_with(API_BASE_URL, 'http://172.31.') ? 'cms.allenhouseschools.com' : '');
// Legacy alias kept for read fallbacks; same host after FastRanking → AWS migration.
define('LEGACY_API_BASE_URL', 'https://cms.allenhouseschools.com/');
define('LEGACY_CMS_API_URL', rtrim(LEGACY_API_BASE_URL, '/') . '/api');
define('SCHOOL_ID', '1');
define('BRANCH_ID', 6);

define('API_JWT_TOKEN', '582c9cad8a8786b7374e23d16fcc08de2f5a1e7098dcf00a193c331e62d718cd');

if (!function_exists('api_auth_headers')) {
    /**
     * @param string[] $extra e.g. ['Content-Type: application/json']
     * @return string[]
     */
    function api_auth_headers(array $extra = []): array
    {
        $headers = ['Authorization: Bearer ' . API_JWT_TOKEN];
        if (defined('CMS_API_HOST_HEADER') && CMS_API_HOST_HEADER !== '') {
            $headers[] = 'Host: ' . CMS_API_HOST_HEADER;
        }
        foreach ($extra as $header) {
            $headers[] = $header;
        }
        return $headers;
    }
}
@ini_set('default_socket_timeout', '8');

require_once dirname(__DIR__) . '/shared/includes/encoding.php';

if (!function_exists('apply_curl_ssl_options')) {
    /**
     * @param resource|\CurlHandle $ch
     */
    function apply_curl_ssl_options($ch): void
    {
        $isLocal = false;
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        if ($host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, 'localhost:')) {
            $isLocal = true;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$isLocal);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $isLocal ? 0 : 2);
    }
}

require_once dirname(__DIR__) . '/shared/includes/grade-api-helpers.php';

if (!function_exists('cms_curl_json_cache_ref')) {
    /**
     * @return array<string, array|null>
     */
    function &cms_curl_json_cache_ref(): array
    {
        static $cache = [];

        return $cache;
    }
}

if (!function_exists('cms_curl_seed_json_cache')) {
    function cms_curl_seed_json_cache(string $url, ?array $data): void
    {
        $cache = &cms_curl_json_cache_ref();
        $cache[$url] = $data;
    }
}

if (!function_exists('cms_curl_get_json')) {
    function cms_curl_get_json(string $url, int $timeoutSeconds = 6, bool $noCache = false): ?array
    {
        $cache = &cms_curl_json_cache_ref();

        if (!$noCache && array_key_exists($url, $cache)) {
            if (function_exists('cmsProfileRecordNetwork')) {
                cmsProfileRecordNetwork($url, true);
            }

            return $cache[$url];
        }

        $timeoutSeconds = max(2, $timeoutSeconds);
        $headers = api_auth_headers($noCache ? ['Cache-Control: no-cache'] : []);
        if (!empty($GLOBALS['cms_track_network']) && is_array($GLOBALS['cms_track_network'])) {
            $GLOBALS['cms_track_network']['count'] = (int) ($GLOBALS['cms_track_network']['count'] ?? 0) + 1;
            $GLOBALS['cms_track_network']['urls'][] = $url;
        }
        $response = cms_curl_exec_json($url, $timeoutSeconds, $headers, true);
        if ($response === null) {
            $response = cms_curl_exec_json($url, $timeoutSeconds, $headers, false);
        }

        if (!is_array($response)) {
            return null;
        }

        if (!$noCache) {
            $cache[$url] = $response;
        }

        return $response;
    }
}

if (!function_exists('cms_curl_exec_json')) {
    /**
     * @param list<string> $headers
     */
    function cms_curl_exec_json(string $url, int $timeoutSeconds, array $headers, bool $verifySsl = true): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(3, $timeoutSeconds));
        curl_setopt($ch, CURLOPT_TIMEOUT, min(8, $timeoutSeconds));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($verifySsl) {
            apply_curl_ssl_options($ch);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (($response === false || $httpCode !== 200) && defined('LEGACY_CMS_API_URL') && defined('CMS_API_URL')) {
            $legacyUrl = str_replace(CMS_API_URL, LEGACY_CMS_API_URL, $url);
            if ($legacyUrl !== $url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $legacyUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(3, $timeoutSeconds));
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
        }

        if ($response === false || $httpCode !== 200) {
            return null;
        }
        if (strncmp((string) $response, "\xEF\xBB\xBF", 3) === 0) {
            $response = substr((string) $response, 3);
        }
        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return null;
        }
        if (function_exists('cmsFixUtf8Deep')) {
            $decoded = cmsFixUtf8Deep($decoded);
        }

        return $decoded;
    }
}

if (!function_exists('cms_bust_fetch_json_endpoint_cache')) {
    function cms_bust_fetch_json_endpoint_cache(string $endpoint): void
    {
        $endpoint = ltrim($endpoint, '/');
        if ($endpoint === '' || !defined('CMS_API_URL')) {
            return;
        }

        $baseUrl = rtrim(CMS_API_URL, '/') . '/';
        if (function_exists('cms_curl_json_cache_ref')) {
            $cache = &cms_curl_json_cache_ref();
            foreach (array_keys($cache) as $url) {
                if (str_starts_with($url, $baseUrl . $endpoint)) {
                    unset($cache[$url]);
                }
            }
        }
    }
}

if (!function_exists('cms_prefetch_json_urls')) {
    /**
     * @param list<string> $urls
     */
    function cms_prefetch_json_urls(array $urls): void
    {
        if (!function_exists('curl_multi_init')) {
            return;
        }

        $cache = &cms_curl_json_cache_ref();
        $pending = [];
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url === '' || array_key_exists($url, $cache)) {
                continue;
            }
            $pending[] = $url;
        }

        if ($pending === []) {
            return;
        }

        $mh = curl_multi_init();
        if ($mh === false) {
            return;
        }

        $handles = [];
        foreach ($pending as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_HTTPHEADER, api_auth_headers());
            apply_curl_ssl_options($ch);
            curl_multi_add_handle($mh, $ch);
            $handles[$url] = $ch;
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running > 0) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running > 0 && $status === CURLM_OK);

        foreach ($handles as $url => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($response !== false && $httpCode === 200) {
                $decoded = json_decode((string) $response, true);
                if (is_array($decoded)) {
                    if (function_exists('cmsFixUtf8Deep')) {
                        $decoded = cmsFixUtf8Deep($decoded);
                    }
                    $cache[$url] = $decoded;
                }
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
    }
}

if (!function_exists('cms_prefetch_endpoints')) {
    /**
     * @param list<string> $endpoints
     */
    function cms_prefetch_endpoints(array $endpoints): void
    {
        $urls = [];
        foreach ($endpoints as $endpoint) {
            $endpoint = ltrim((string) $endpoint, '/');
            if ($endpoint === '') {
                continue;
            }
            if (cms_is_new_cms_route($endpoint)) {
                $urls[] = rtrim(CMS_API_URL, '/') . '/' . $endpoint;
                $urls[] = rtrim(LEGACY_CMS_API_URL, '/') . '/' . $endpoint;
            } else {
                $urls[] = rtrim(LEGACY_CMS_API_URL, '/') . '/' . $endpoint;
            }
        }
        cms_prefetch_json_urls($urls);
    }
}

if (!function_exists('cms_is_new_cms_route')) {
    function cms_is_new_cms_route(string $endpoint): bool
    {
        $prefixes = [
            'public/',
            'pages/',
            'menus/',
            'galleries/',
            'blogs/',
            'statistics/',
            'flyers/',
            'scrolling-texts/',
            'jobs/',
            'grades/',
            'cities/',
            'school-sessions',
        ];

        foreach ($prefixes as $prefix) {
            if (strpos($endpoint, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cms_fetch_json_endpoint')) {
    function cms_fetch_json_endpoint(string $endpoint): ?array
    {
        $endpoint = ltrim($endpoint, '/');
        static $cache = [];

        $fileKey = 'legacy_' . md5($endpoint);
        if (function_exists('cmsScopedFileCacheGet')) {
            $fileCached = cmsScopedFileCacheGet($fileKey);
            if (is_array($fileCached) && function_exists('cmsApiResponseIsUsable') && cmsApiResponseIsUsable($fileCached)) {
                $cache[$endpoint] = $fileCached;

                return $fileCached;
            }
        }

        $primaryUrl = rtrim(CMS_API_URL, '/') . '/' . $endpoint;
        $legacyUrl = rtrim(LEGACY_CMS_API_URL, '/') . '/' . $endpoint;
        $preferPrimary = cms_is_new_cms_route($endpoint);

        if ($preferPrimary) {
            $data = cms_curl_get_json($primaryUrl);
            if (!is_array($data)) {
                $data = cms_curl_get_json($legacyUrl);
            }
        } else {
            $data = cms_curl_get_json($legacyUrl);
        }

        $cache[$endpoint] = is_array($data) ? $data : null;

        if (
            is_array($data)
            && function_exists('cmsApiStoreScopedKey')
            && function_exists('cmsApiResponseIsUsable')
            && cmsApiResponseIsUsable($data)
        ) {
            cmsApiStoreScopedKey($fileKey, $data);
        }

        return $cache[$endpoint];

        $primaryUrl = rtrim(CMS_API_URL, '/') . '/' . $endpoint;
        $legacyUrl = rtrim(LEGACY_CMS_API_URL, '/') . '/' . $endpoint;
        $preferPrimary = cms_is_new_cms_route($endpoint);

        if ($preferPrimary) {
            $data = cms_curl_get_json($primaryUrl);
            if (!is_array($data)) {
                $data = cms_curl_get_json($legacyUrl);
            }
        } else {
            $data = cms_curl_get_json($legacyUrl);
        }

        $cache[$endpoint] = is_array($data) ? $data : null;

        return $cache[$endpoint];
    }
}

if (!function_exists('cms_fetch_json_url')) {
    function cms_fetch_json_url(string $url): ?array
    {
        $normalized = str_replace('\\', '/', trim($url));
        $normalized = preg_replace('#^https?://[^/]+/api/#i', '', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        return cms_fetch_json_endpoint($normalized);
    }
}

if (!function_exists('cms_post_json_endpoint')) {
    function cms_post_json_endpoint(string $path, array $payload): array
    {
        $path = ltrim($path, '/');
        $urls = [
            rtrim(API_BASE_URL, '/') . '/' . $path,
            rtrim(LEGACY_API_BASE_URL, '/') . '/' . $path,
        ];
        $urls = array_values(array_unique($urls));

        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, api_auth_headers(['Content-Type: application/json']));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            apply_curl_ssl_options($ch);
            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $status >= 200 && $status < 500) {
                return [
                    'status' => $status,
                    'body' => (string) $response,
                ];
            }
        }

        return [
            'status' => 502,
            'body' => json_encode(['status' => 'error', 'message' => 'Upstream API unavailable']),
        ];
    }
}
