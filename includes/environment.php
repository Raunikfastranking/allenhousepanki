<?php

if (!function_exists('is_app_local')) {
    function is_app_local(): bool
    {
        $env = getenv('APP_ENV');
        if (is_string($env) && $env !== '') {
            $e = strtolower(trim($env));
            if ($e === 'production' || $e === 'prod') {
                return false;
            }
            if ($e === 'local' || $e === 'development' || $e === 'dev') {
                return true;
            }
        }

        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        if ($host === '' || $host === 'localhost') {
            return true;
        }
        if (strpos($host, '127.0.0.1') === 0 || strpos($host, 'localhost:') === 0) {
            return true;
        }
        if (strpos($host, '.local') !== false || strpos($host, '.test') !== false) {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_production_public_host')) {
    function is_production_public_host(): bool
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');

        return $host === 'allenhousepanki.com' || $host === 'www.allenhousepanki.com';
    }
}

if (!function_exists('site_base_url_normalize')) {
    /**
     * Strip JSON-escaped backslashes (e.g. "\/") from IIS/env base paths.
     */
    function site_base_url_normalize(string $base): string
    {
        $base = str_replace('\\', '', trim($base));
        if ($base === '' || $base === '/') {
            return '/';
        }

        return '/' . trim($base, '/') . '/';
    }
}

if (!function_exists('request_is_https')) {
    function request_is_https(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwarded = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        if ($forwarded !== '') {
            foreach (explode(',', $forwarded) as $part) {
                if (trim($part) === 'https') {
                    return true;
                }
            }
        }

        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on';
    }
}

if (!function_exists('site_origin_url')) {
    function site_origin_url(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $scheme = request_is_https() ? 'https' : 'http';
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $basePath = rtrim(site_base_url_normalize(site_base_url()), '/');

        if ($basePath === '' || $basePath === '/') {
            $cached = $scheme . '://' . $host;
        } else {
            $cached = $scheme . '://' . $host . $basePath;
        }

        return rtrim($cached, '/');
    }
}

if (!function_exists('site_base_url')) {
    /**
     * Web path prefix for this site (e.g. /api_integration/allenhousepanki/ or /).
     */
    function site_base_url(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        if (defined('SITE_BASE_URL')) {
            $cached = site_base_url_normalize((string) SITE_BASE_URL);
            return $cached;
        }

        if (!empty($_SERVER['APS_BASE_PATH'])) {
            $cached = site_base_url_normalize((string) $_SERVER['APS_BASE_PATH']);
            return $cached;
        }

        $siteRoot = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__)), '/');
        $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');

        if ($docRoot !== '' && stripos($siteRoot, $docRoot) === 0) {
            $relative = ltrim(substr($siteRoot, strlen($docRoot)), '/');
            if ($relative !== '') {
                $cached = site_base_url_normalize('/' . $relative . '/');
                return $cached;
            }
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '' && $scriptName[0] === '/') {
            $dir = dirname($scriptName);
            if ($dir === '/' || $dir === '.') {
                $cached = '/';
                return $cached;
            }
            $cached = site_base_url_normalize(rtrim($dir, '/') . '/');
            return $cached;
        }

        $cached = '/';
        return $cached;
    }
}

if (!function_exists('site_asset_url')) {
    function site_asset_url(string $path): string
    {
        $path = ltrim(str_replace('\\', '', $path), '/');
        if (is_production_public_host()) {
            return rtrim(site_origin_url(), '/') . '/' . $path;
        }

        return site_base_url() . $path;
    }
}

if (!function_exists('cmsMenuUrl')) {
    function cmsMenuUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '#';
        }
        if (str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $url;
        }

        if (preg_match('#^https?://#i', $url)) {
            if (is_app_local()) {
                $path = parse_url($url, PHP_URL_PATH) ?: '';
                $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
                if (
                    $path !== '' &&
                    (
                        $host === 'allenhousepanki.com'
                        || $host === 'www.allenhousepanki.com'
                        || $host === 'localhost'
                        || $host === '127.0.0.1'
                    )
                ) {
                    return site_base_url() . ltrim($path, '/');
                }
            }

            return $url;
        }

        if ($url === '/') {
            return site_base_url();
        }

        return site_base_url() . ltrim($url, '/');
    }
}
