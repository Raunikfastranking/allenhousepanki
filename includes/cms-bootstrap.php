<?php

if (!function_exists('cmsSharedBasePath')) {
    function cmsSharedBasePath(): string
    {
        $siteShared = dirname(__DIR__) . '/shared/cms';
        if (is_file($siteShared . '/cms-core.php')) {
            return $siteShared;
        }

        throw new RuntimeException('shared/cms not found at site/shared/cms');
    }
}

$cmsShared = cmsSharedBasePath();
require_once dirname(__DIR__) . '/shared/includes/cms-page-content.php';
require_once $cmsShared . '/cms-core.php';
require_once $cmsShared . '/cms-section-renderer.php';
require_once $cmsShared . '/cms-gallery-dynamic.php';
require_once dirname(__DIR__) . '/shared/includes/cms-extra-sections.php';

cmsCoreConfigure([
    'branch_suffix' => 'panki',
    'branch_hosts' => ['allenhousepanki.com', 'www.allenhousepanki.com'],
    'header_layout_slug' => 'header-aps-panki',
    'footer_layout_slug' => 'footer-aps-panki',
    'home_page_slug' => 'home-page-panki',
]);

if (!function_exists('cmsPankiSyncPageCache')) {
    function cmsPankiSyncPageCache(string $slug, array $page): void
    {
        if ($slug === '' || !isset($GLOBALS['api_cache']) || !is_array($GLOBALS['api_cache'])) {
            return;
        }

        if (!function_exists('cmsPankiPageSlugs')) {
            require_once __DIR__ . '/cms-panki-slugs.php';
        }

        foreach (cmsPankiPageSlugs() as $cacheKey => $cachedSlug) {
            if ($cachedSlug === $slug) {
                $GLOBALS['api_cache'][$cacheKey] = $page;
                break;
            }
        }
    }
}

if (!function_exists('cmsPankiPageRequestIsLive')) {
    function cmsPankiPageRequestIsLive(string $slug): bool
    {
        $slug = trim($slug);
        if ($slug === '') {
            return false;
        }

        $requested = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));

        return $requested !== '' && $requested === $slug;
    }
}

if (!function_exists('cmsFetchPankiPage')) {
    function cmsFetchPankiPage(string $slug): array
    {
        if (!function_exists('cmsPankiNormalizePageTitles')) {
            require_once __DIR__ . '/cms-page-helpers.php';
        }

        $slug = trim($slug);
        if ($slug === '') {
            return ['data' => []];
        }

        if (!function_exists('cmsFetchPageBySlug')) {
            return ['data' => []];
        }

        $page = cmsFetchPageBySlug($slug, false);
        if (function_exists('cmsPankiNormalizePageTitles')) {
            $page = cmsPankiNormalizePageTitles($page, $slug);
        }
        cmsPankiSyncPageCache($slug, $page);

        return $page;
    }
}

if (!function_exists('cmsPankiCmsItemIsVisible')) {
    function cmsPankiCmsItemIsVisible(array $item): bool
    {
        $status = strtolower(trim((string) ($item['status'] ?? 'active')));
        $publish = strtolower(trim((string) ($item['publish_status'] ?? 'published')));
        if ($status !== '' && !in_array($status, ['active', '1', 'enabled'], true)) {
            return false;
        }
        if ($publish !== '' && !in_array($publish, ['published', 'publish', 'active', '1'], true)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('cmsPankiFlyersResponse')) {
    /** @return array<string, mixed>|list<array<string, mixed>>|null */
    function cmsPankiFlyersResponse(): ?array
    {
        if (!defined('BRANCH_ID') || !defined('CMS_API_URL')) {
            return null;
        }

        if (!function_exists('cmsBulkFlyersResponse')) {
            require_once dirname(__DIR__) . '/shared/includes/cms-bulk-reuse.php';
        }
        $bulk = cmsBulkFlyersResponse();
        if ($bulk !== null) {
            return $bulk;
        }

        if (function_exists('cmsBulkApiCacheGet')) {
            $cached = cmsBulkApiCacheGet('flyer_data');
            if ($cached !== null) {
                return $cached;
            }
        }

        $endpoint = 'flyers/branch/' . BRANCH_ID;
        if (function_exists('cmsCmsGetJson')) {
            $viaCore = cmsCmsGetJson($endpoint, 4);
            if (is_array($viaCore)) {
                return $viaCore;
            }
        }

        return null;
    }
}

if (!function_exists('cmsPankiScrollingTextsResponse')) {
    function cmsPankiScrollingTextsResponse(): ?array
    {
        if (!defined('BRANCH_ID') || !defined('CMS_API_URL')) {
            return null;
        }

        if (function_exists('cmsBulkApiCacheGet')) {
            $cached = cmsBulkApiCacheGet('scroll_text_data');
            if ($cached !== null) {
                return $cached;
            }
        }

        $endpoint = 'scrolling-texts/branch/' . BRANCH_ID;
        if (function_exists('cmsCmsGetJson')) {
            $viaCore = cmsCmsGetJson($endpoint, 4);
            if (is_array($viaCore)) {
                return $viaCore;
            }
        }

        return null;
    }
}

if (!function_exists('cmsPankiScrollingText')) {
    function cmsPankiScrollingText(): string
    {
        static $resolved = false;
        static $text = '';

        if ($resolved) {
            return $text;
        }
        $resolved = true;

        $response = cmsPankiScrollingTextsResponse();
        $items = [];
        if (is_array($response)) {
            $items = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
        }
        if (!is_array($items)) {
            return $text;
        }

        foreach ($items as $item) {
            if (!is_array($item) || !cmsPankiCmsItemIsVisible($item)) {
                continue;
            }
            $content = trim((string) ($item['content'] ?? $item['contant'] ?? $item['title'] ?? ''));
            if (function_exists('cmsPlainText')) {
                $content = cmsPlainText($content);
            }
            if ($content !== '') {
                $text = $content;
                break;
            }
        }

        return $text;
    }
}

if (!function_exists('cmsPankiHeaderMeta')) {
    function cmsPankiHeaderMeta(): array
    {
        static $meta = null;
        if ($meta !== null) {
            return $meta;
        }

        $meta = [];
        if (!defined('BRANCH_ID')) {
            return $meta;
        }

        $slug = (string) cmsCoreGet('header_layout_slug', 'header-aps-panki');
        $layoutResponse = cmsLoadFooterBootstrap()['layout'] ?? [];
        $items = [];
        if (isset($layoutResponse['data']) && is_array($layoutResponse['data'])) {
            $items = $layoutResponse['data'];
        } elseif (is_array($layoutResponse)) {
            $items = $layoutResponse;
        }

        $headerPart = null;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['slug'] ?? null) === $slug || ($item['layout_slug'] ?? null) === $slug) {
                $headerPart = $item;
                break;
            }
        }
        if ($headerPart === null) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (($item['type'] ?? null) === 'header' || ($item['layout_type'] ?? null) === 'header') {
                    $headerPart = $item;
                    break;
                }
            }
        }

        return is_array($headerPart['meta_data'] ?? null) ? $headerPart['meta_data'] : [];
    }
}

if (function_exists('cmsAutoGuardCurrentScriptPage')) {
    cmsAutoGuardCurrentScriptPage();
}
