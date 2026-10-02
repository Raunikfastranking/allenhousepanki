<?php

declare(strict_types=1);

/**
 * Auto-resolve CMS page payloads for SEO (DPS Amrapali seo.php pattern).
 */

if (!function_exists('cmsSeoCleanText')) {
    function cmsSeoCleanText($value): string
    {
        return trim(strip_tags((string) $value));
    }
}

if (!function_exists('cmsSeoGetCurrentSlug')) {
    function cmsSeoGetCurrentSlug(): string
    {
        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        return basename($_SERVER['PHP_SELF'] ?? 'index', '.php');
    }
}

if (!function_exists('cmsSeoIsPageLikeData')) {
    function cmsSeoIsPageLikeData(array $data): bool
    {
        if ($data === [] || array_is_list($data)) {
            return false;
        }

        return isset($data['title'])
            || isset($data['meta_title'])
            || isset($data['meta_description'])
            || isset($data['meta_keywords'])
            || isset($data['page_heading'])
            || isset($data['sections']);
    }
}

if (!function_exists('cmsSeoPageDataFromValue')) {
    function cmsSeoPageDataFromValue($value): ?array
    {
        if (!is_array($value) || empty($value['data']) || !is_array($value['data'])) {
            return null;
        }

        return cmsSeoIsPageLikeData($value['data']) ? $value : null;
    }
}

if (!function_exists('cmsSeoGlobalVarToSlug')) {
    function cmsSeoGlobalVarToSlug(string $varName): ?string
    {
        if (preg_match('/(?:_data|Data)$/i', $varName)) {
            $name = preg_replace('/(?:_data|Data)$/i', '', $varName);
            $name = preg_replace('/_+/', '_', $name);
            $name = trim((string) $name, '_');
            if ($name === '') {
                return null;
            }

            return str_replace('_', '-', strtolower($name));
        }

        if (strcasecmp($varName, 'homePage') === 0 || strcasecmp($varName, 'homePageApi') === 0) {
            return 'index';
        }

        if (strcasecmp($varName, 'cms_current_page') === 0) {
            return trim((string) ($GLOBALS['cms_page_slug'] ?? 'index'));
        }

        return null;
    }
}

if (!function_exists('cmsSeoNormalizeSlugKey')) {
    function cmsSeoNormalizeSlugKey(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        if ($slug !== '' && str_ends_with($slug, 's')) {
            return substr($slug, 0, -1);
        }

        return $slug;
    }
}

if (!function_exists('cmsSeoSlugMatchScore')) {
    function cmsSeoSlugMatchScore(string $pageSlug, string $varSlug): int
    {
        $page = cmsSeoNormalizeSlugKey($pageSlug);
        $var = cmsSeoNormalizeSlugKey($varSlug);

        if ($page === $var) {
            return 100;
        }

        if ($page !== '' && $var !== '' && (str_starts_with($var, $page) || str_starts_with($page, $var))) {
            return 85;
        }

        similar_text($page, $var, $percent);

        return (int) round($percent);
    }
}

if (!function_exists('cmsSeoFindPageBySlug')) {
    function cmsSeoFindPageBySlug(string $slug): ?array
    {
        $bestPage = null;
        $bestScore = 0;

        foreach ($GLOBALS as $key => $value) {
            if (!is_string($key) || !is_array($value)) {
                continue;
            }

            $page = cmsSeoPageDataFromValue($value);
            if ($page === null) {
                continue;
            }

            $varSlug = cmsSeoGlobalVarToSlug($key);
            if ($varSlug === null) {
                continue;
            }

            $score = cmsSeoSlugMatchScore($slug, $varSlug);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestPage = $page;
            }
        }

        return $bestScore >= 80 ? $bestPage : null;
    }
}

if (!function_exists('cmsSeoResolveBlogPage')) {
    function cmsSeoResolveBlogPage(): ?array
    {
        $blog = null;
        if (isset($GLOBALS['selectedBlog']) && is_array($GLOBALS['selectedBlog']) && $GLOBALS['selectedBlog'] !== []) {
            $blog = $GLOBALS['selectedBlog'];
        }

        if ($blog === null) {
            return null;
        }

        require_once __DIR__ . '/cms-page-seo.php';

        return [
            'data' => array_merge($blog, [
                'meta_title' => $blog['meta_title'] ?? $blog['title'] ?? $blog['heading'] ?? '',
                'meta_description' => $blog['meta_description'] ?? $blog['description'] ?? $blog['main_description'] ?? '',
                'meta_keywords' => $blog['meta_keywords'] ?? $blog['keywords'] ?? '',
                'meta_image' => $blog['image'] ?? $blog['featured_image'] ?? $blog['media_url'] ?? '',
            ]),
        ];
    }
}

if (!function_exists('cmsSeoShouldForceLiveFetch')) {
    function cmsSeoShouldForceLiveFetch(string $slug): bool
    {
        $slug = trim($slug);
        if ($slug === '') {
            return false;
        }

        if (!empty($GLOBALS['school_is_index_page']) && function_exists('cmsCoreGet')) {
            $homeSlug = trim((string) cmsCoreGet('home_page_slug', ''));
            if ($homeSlug !== '' && $slug === $homeSlug) {
                return true;
            }
        }

        $requested = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));

        return $requested !== '' && $requested === $slug;
    }
}

if (!function_exists('cmsSeoHomeSlug')) {
    function cmsSeoHomeSlug(): string
    {
        if (function_exists('cmsCoreGet')) {
            $home = trim((string) cmsCoreGet('home_page_slug', ''));
            if ($home !== '') {
                return $home;
            }
        }

        return '';
    }
}

if (!function_exists('cmsSeoIsHomeRequest')) {
    function cmsSeoIsHomeRequest(string $slug): bool
    {
        if (!empty($GLOBALS['school_is_index_page'])) {
            return true;
        }
        if ($slug === 'index') {
            return true;
        }

        $homeSlug = cmsSeoHomeSlug();
        if ($homeSlug === '') {
            return false;
        }

        $cmsSlug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));

        return $slug === $homeSlug || $cmsSlug === $homeSlug;
    }
}

if (!function_exists('cmsSeoResolveCurrentPage')) {
    /** @return array<string, mixed>|null Full API page wrapper ['data' => ...] */
    function cmsSeoResolveCurrentPage(): ?array
    {
        if (isset($GLOBALS['cms_current_page']) && is_array($GLOBALS['cms_current_page'])) {
            $page = $GLOBALS['cms_current_page'];
            if (!empty($page['data']) && is_array($page['data'])) {
                return $page;
            }
        }

        if ($blogPage = cmsSeoResolveBlogPage()) {
            return $blogPage;
        }

        $slug = cmsSeoGetCurrentSlug();
        $homeSlug = cmsSeoHomeSlug();
        $isHomeRequest = cmsSeoIsHomeRequest($slug);

        if ($isHomeRequest && function_exists('cmsFetchPageBySlug')) {
            $fetchSlug = $homeSlug !== '' ? $homeSlug : trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
            if ($fetchSlug !== '') {
                $fetched = cmsFetchPageBySlug($fetchSlug, true);
                if (cmsSeoPageDataFromValue($fetched) !== null) {
                    return $fetched;
                }
            }
        }

        if ($slug === 'index' || ($homeSlug !== '' && $slug === $homeSlug)) {
            foreach (['homePageApi', 'home_data', 'homePage'] as $varName) {
                if (!isset($GLOBALS[$varName])) {
                    continue;
                }
                $page = cmsSeoPageDataFromValue($GLOBALS[$varName]);
                if ($page !== null) {
                    return $page;
                }
            }

            if (isset($GLOBALS['api_cache']['home_data']) && is_array($GLOBALS['api_cache']['home_data'])) {
                $page = cmsSeoPageDataFromValue($GLOBALS['api_cache']['home_data']);
                if ($page !== null) {
                    return $page;
                }
            }
        }

        if (isset($GLOBALS['pageData']) && is_array($GLOBALS['pageData'])) {
            $page = cmsSeoPageDataFromValue($GLOBALS['pageData']);
            if ($page !== null) {
                return $page;
            }
        }

        if ($page = cmsSeoFindPageBySlug($slug)) {
            return $page;
        }

        $cmsSlug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($cmsSlug !== '' && function_exists('cmsFetchPageBySlug')) {
            $fetched = cmsFetchPageBySlug($cmsSlug, cmsSeoShouldForceLiveFetch($cmsSlug));
            if (cmsSeoPageDataFromValue($fetched) !== null) {
                return $fetched;
            }
        }

        return null;
    }
}
