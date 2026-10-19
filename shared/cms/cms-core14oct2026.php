<?php

declare(strict_types=1);

if (!function_exists('cmsCoreConfig')) {
    /** @return array<string, mixed> */
    function cmsCoreConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = [];
        }
        return $config;
    }

    /** @param array<string, mixed> $config */
    function cmsCoreConfigure(array $config): void
    {
        $GLOBALS['cms_core_config'] = array_merge(cmsCoreConfig(), $config);
    }

    function cmsCoreGet(string $key, mixed $default = null): mixed
    {
        $config = $GLOBALS['cms_core_config'] ?? cmsCoreConfig();
        return $config[$key] ?? $default;
    }

    /** Load a file from site/shared/includes (standalone school deploy). */
    function cmsRequireSharedInclude(string $basename): void
    {
        static $resolved = [];
        if (isset($resolved[$basename])) {
            require_once $resolved[$basename];
            return;
        }
        if (!function_exists('cms_school_root')) {
            require_once dirname(__DIR__) . '/includes/school-root.php';
        }
        $path = cms_school_root(__DIR__) . '/shared/includes/' . $basename;
        if (!is_file($path)) {
            throw new RuntimeException('Shared include not found: ' . $basename);
        }
        $resolved[$basename] = $path;
        require_once $path;
    }

    function cmsBranchSuffix(): string
    {
        return (string) cmsCoreGet('branch_suffix', 'agra');
    }

    function cmsApiBase(): string
    {
        if (defined('CMS_API_URL')) {
            return rtrim(CMS_API_URL, '/');
        }
        if (defined('API_BASE_URL')) {
            return rtrim(API_BASE_URL, '/');
        }
        return 'https://cms.allenhouseschools.com/api';
    }

    function cmsCmsGetJson(string $endpoint, int $timeout = 20): ?array
    {
        $url = cmsApiBase() . '/' . ltrim($endpoint, '/');
        if (function_exists('cms_curl_get_json')) {
            $decoded = cms_curl_get_json($url, $timeout);
            return is_array($decoded) ? $decoded : null;
        }

        $ch = curl_init($url);
        $coreHeaders = function_exists('api_auth_headers') ? api_auth_headers() : [];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => $coreHeaders,
        ]);
        if (function_exists('apply_curl_ssl_options')) {
            apply_curl_ssl_options($ch);
        }
        $body = curl_exec($ch);
        curl_close($ch);
        if ($body === false) {
            return null;
        }
        $decoded = json_decode((string) $body, true);
        return is_array($decoded) ? $decoded : null;
    }

    function cmsNavItemIsBoardToppersPlaceholder(array $item): bool
    {
        $name = strtolower(trim((string) ($item['name'] ?? '')));
        $name = preg_replace('/\s*page$/i', '', $name) ?? $name;
        $compact = preg_replace('/[^a-z]/', '', $name) ?? $name;
        if (!str_contains($compact, 'boardt')) {
            return false;
        }
        if (!str_contains($compact, 'topper') && !str_contains($compact, 'tpooer')) {
            return false;
        }

        $url = trim((string) ($item['url'] ?? ''));
        return $url === '' || $url === '#';
    }

    /** @return list<array<string, mixed>> */
    function cmsNavDefaultBoardToppersChildren(): array
    {
        return [
            [
                'name' => 'Class X Board Toppers',
                'url' => '/x-toppers',
                'target' => '_self',
                'status' => 'published',
                'children' => [],
            ],
            [
                'name' => 'Class XII Board Toppers',
                'url' => '/xii-toppers',
                'target' => '_self',
                'status' => 'published',
                'children' => [],
            ],
        ];
    }

    function cmsNormalizeMenuTree(array $item): array
    {
        $children = $item['children'] ?? [];
        $descendants = $item['descendants'] ?? [];
        if ((!is_array($children) || $children === []) && is_array($descendants) && $descendants !== []) {
            $item['children'] = $descendants;
        }
        unset($item['descendants']);

        $rawChildren = $item['children'] ?? [];
        if ((!is_array($rawChildren) || $rawChildren === []) && cmsNavItemIsBoardToppersPlaceholder($item)) {
            $item['children'] = cmsNavDefaultBoardToppersChildren();
        }

        $normalized = [];
        foreach ($item['children'] ?? [] as $child) {
            if (is_array($child)) {
                $normalized[] = cmsNormalizeMenuTree($child);
            }
        }
        if ($normalized !== []) {
            $item['children'] = $normalized;
        }

        $item['name'] = preg_replace('/\s*Page$/i', '', (string) ($item['name'] ?? ''));
        return $item;
    }

    /** @return list<array<string, mixed>> */
    function cmsNavItems(): array
    {
        static $items = null;
        if ($items !== null) {
            return $items;
        }

        $items = [];
        if (!defined('BRANCH_ID')) {
            return $items;
        }

        $branchResponse = null;
        if (function_exists('cmsLoadBranchMenu')) {
            $branchResponse = cmsLoadBranchMenu();
        }
        if (!is_array($branchResponse) || empty($branchResponse['data'])) {
            $branchResponse = cmsCmsGetJson('menus/branch/' . BRANCH_ID, 8);
        }
        $menus = is_array($branchResponse['data'] ?? null) ? $branchResponse['data'] : [];
        $branchMenu = is_array($menus[0] ?? null) ? $menus[0] : [];
        $rawItems = is_array($branchMenu['menu_items'] ?? null) ? $branchMenu['menu_items'] : [];

        $menuId = $branchMenu['id'] ?? null;
        if ($menuId !== null && $menuId !== '' && $rawItems === []) {
            $menuResponse = cmsCmsGetJson('menus/' . rawurlencode((string) $menuId));
            $fullMenu = is_array($menuResponse['data'] ?? null) ? $menuResponse['data'] : [];
            $fullItems = is_array($fullMenu['menu_items'] ?? null) ? $fullMenu['menu_items'] : [];
            if ($fullItems !== []) {
                $rawItems = $fullItems;
            }
        }

        foreach ($rawItems as $item) {
            if (is_array($item)) {
                $items[] = cmsNormalizeMenuTree($item);
            }
        }

        return cmsFilterPublishedNavItems($items);
    }

    /** Whether a CMS page payload should be shown on the public site. */
    function cmsPageIsPublished(?array $page): bool
    {
        if (!is_array($page)) {
            return false;
        }

        $data = $page['data'] ?? null;
        if (!is_array($data) || $data === []) {
            return false;
        }

        if (array_key_exists('is_active', $data)) {
            $active = $data['is_active'];
            if ($active === false || $active === 0 || $active === '0' || $active === 'false') {
                return false;
            }
        }

        $status = strtolower(trim((string) ($data['status'] ?? '')));
        if ($status !== '') {
            return in_array($status, ['published', 'publish', 'active', '1', 'true'], true);
        }

        $sections = $data['sections'] ?? null;
        if (is_array($sections) && $sections !== []) {
            return true;
        }

        return false;
    }

    /** @return list<array<string, mixed>> */
    function cmsFilterPublishedNavItems(array $items): array
    {
        $visible = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (!cmsNavMenuItemIsPublished($item)) {
                continue;
            }
            $children = $item['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $item['children'] = cmsFilterPublishedNavItems($children);
            }
            $visible[] = $item;
        }

        return $visible;
    }

    function cmsNavMenuItemIsPublished(array $item): bool
    {
        if (!empty($GLOBALS['school_is_index_page']) || basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'index.php') {
            return true;
        }

        $linkableType = (string) ($item['linkable_type'] ?? '');
        $linkableId = $item['linkable_id'] ?? null;
        $pages = cmsAllPagesIndex();
        if ($pages === []) {
            return true;
        }

        if ($linkableId !== null && $linkableId !== '' && str_contains($linkableType, 'Page')) {
            foreach ($pages as $page) {
                if ((string) ($page['id'] ?? '') === (string) $linkableId) {
                    return cmsPageIsPublished(['data' => $page]);
                }
            }

            return false;
        }

        $url = trim(strtolower((string) ($item['url'] ?? '')), '/');
        if ($url === '' || $url === '#') {
            return true;
        }

        foreach ($pages as $page) {
            $slug = (string) ($page['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            if (cmsPathFromSlug($slug) === $url) {
                return cmsPageIsPublished(['data' => $page]);
            }
        }

        $guess = $url . '-page-' . cmsBranchSuffix();
        foreach ($pages as $page) {
            if ((string) ($page['slug'] ?? '') === $guess) {
                return cmsPageIsPublished(['data' => $page]);
            }
        }

        return true;
    }

    function cmsAbortIfUnpublishedPage(?array $page): void
    {
        if (cmsPageIsPublished($page)) {
            return;
        }

        if (!headers_sent()) {
            http_response_code(404);
        }

        $root = cmsSiteRootPath();
        if (is_file($root . '/404.php')) {
            include $root . '/404.php';
        } else {
            echo '404 Not Found';
        }
        exit;
    }

    function cmsSiteRootPath(): string
    {
        static $root = null;
        if ($root !== null) {
            return $root;
        }

        $fromShared = dirname(__DIR__, 2);
        if (is_file($fromShared . '/404.php')) {
            $root = $fromShared;
            return $root;
        }

        $script = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');
        if ($script !== '' && is_file($script)) {
            $dir = dirname($script);
            while ($dir !== '' && $dir !== dirname($dir)) {
                if (is_file($dir . '/404.php')) {
                    $root = $dir;
                    return $root;
                }
                $dir = dirname($dir);
            }
        }

        $root = $fromShared;
        return $root;
    }

    function cmsAutoGuardCurrentScriptPage(): void
    {
        if (!empty($GLOBALS['cms_skip_published_guard'])) {
            return;
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
        if ($script === '' || in_array($script, [
            'index', 'cms-page', 'header', 'footer', 'foot', '404', 'home',
        ], true)) {
            return;
        }

        if (!function_exists('cmsGuessSlugFromScript')) {
            return;
        }

        $slug = cmsGuessSlugFromScript();
        if ($slug === null || $slug === '') {
            return;
        }

        cmsAbortIfUnpublishedPage(cmsFetchPageBySlug($slug));
    }

    /** @return list<array<string, mixed>> */
    function cmsAllPagesIndex(): array
    {
        static $pages = null;
        if ($pages !== null) {
            return $pages;
        }

        $pages = [];
        if (!defined('BRANCH_ID')) {
            return $pages;
        }

        $response = null;
        if (function_exists('cmsBulkApiCacheGet')) {
            $response = cmsBulkApiCacheGet('pages_index');
        }
        if ($response === null) {
            $response = cmsCmsGetJson('pages?branch_id=' . BRANCH_ID, 45);
        }
        $list = is_array($response['data'] ?? null) ? $response['data'] : [];
        foreach ($list as $page) {
            if (is_array($page) && !empty($page['slug'])) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    /** @return list<array<string, mixed>> */
    function cmsPagesIndex(): array
    {
        static $pages = null;
        if ($pages !== null) {
            return $pages;
        }

        $pages = [];
        foreach (cmsAllPagesIndex() as $page) {
            if (!cmsPageIsPublished(['data' => $page])) {
                continue;
            }
            $pages[] = $page;
        }

        return $pages;
    }

    function cmsPagePayloadUpdatedAt(?array $page): string
    {
        if (!is_array($page)) {
            return '';
        }

        $data = $page['data'] ?? $page;
        if (!is_array($data)) {
            return '';
        }

        return trim((string) ($data['updated_at'] ?? $page['updated_at'] ?? ''));
    }

    function cmsPageIndexUpdatedAt(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        foreach (cmsAllPagesIndex() as $entry) {
            if (!is_array($entry) || (string) ($entry['slug'] ?? '') !== $slug) {
                continue;
            }

            return trim((string) ($entry['updated_at'] ?? ''));
        }

        return '';
    }

    function cmsPageCacheIsBehindIndex(string $slug, array $cachedPage): bool
    {
        $indexAt = cmsPageIndexUpdatedAt($slug);
        $cacheAt = cmsPagePayloadUpdatedAt($cachedPage);
        if ($indexAt === '' || $cacheAt === '') {
            return false;
        }

        $indexTs = strtotime($indexAt);
        $cacheTs = strtotime($cacheAt);
        if ($indexTs === false || $cacheTs === false) {
            return false;
        }

        return $indexTs > $cacheTs;
    }

    /** @return array{carousel: bool, card: bool} */
    function cmsPageSectionLayoutFlags(?array $page): array
    {
        $hasCarousel = false;
        $hasCard = false;
        if (!is_array($page) || !is_array($page['data']['sections'] ?? null)) {
            return ['carousel' => $hasCarousel, 'card' => $hasCard];
        }

        foreach ($page['data']['sections'] as $section) {
            if (!is_array($section)) {
                continue;
            }
            $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
            if ($layout === 'carousel' || str_contains($layout, 'carousel')) {
                $hasCarousel = true;
            }
            if ($layout === 'card' || str_contains($layout, 'card')) {
                $hasCard = true;
            }
        }

        return ['carousel' => $hasCarousel, 'card' => $hasCard];
    }

    function cmsPageCarouselItemCount(?array $page): int
    {
        if (!is_array($page) || !is_array($page['data']['sections'] ?? null)) {
            return 0;
        }

        $count = 0;
        foreach ($page['data']['sections'] as $section) {
            if (!is_array($section)) {
                continue;
            }
            $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
            if ($layout !== 'carousel' && !str_contains($layout, 'carousel')) {
                continue;
            }
            $items = $section['resolved_content']['items'] ?? [];
            $count += is_array($items) ? count($items) : 0;
        }

        return $count;
    }

    function cmsPageCardMediaCount(?array $page): int
    {
        if (!is_array($page) || !is_array($page['data']['sections'] ?? null)) {
            return 0;
        }

        $count = 0;
        foreach ($page['data']['sections'] as $section) {
            if (!is_array($section)) {
                continue;
            }
            $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
            if ($layout !== 'card' && !str_contains($layout, 'card')) {
                continue;
            }
            $media = $section['resolved_content']['media'] ?? [];
            $count += is_array($media) ? count($media) : 0;
        }

        return $count;
    }

    function cmsPageNeedsLiveRefresh(string $slug, array $cachedPage): bool
    {
        if (cmsPageCacheIsBehindIndex($slug, $cachedPage)) {
            return true;
        }

        if (!function_exists('cmsPageHasRenderableContent') || !cmsPageHasRenderableContent($cachedPage)) {
            return true;
        }

        $sections = $cachedPage['data']['sections'] ?? [];
        if (!is_array($sections) || $sections === []) {
            return true;
        }

        $flags = cmsPageSectionLayoutFlags($cachedPage);
        if ($flags['carousel'] && cmsPageCarouselItemCount($cachedPage) === 0) {
            return true;
        }
        if ($flags['card'] && cmsPageCardMediaCount($cachedPage) === 0) {
            return true;
        }

        return false;
    }

    function cmsFetchPageBySlug(string $slug, bool $forceLive = false): array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return ['data' => []];
        }

        static $cache = [];
        $cacheKey = $forceLive ? ($slug . ':live') : $slug;
        if (isset($cache[$cacheKey]) && is_array($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $homeSlug = (string) cmsCoreGet('home_page_slug', 'home-page-agra');
        if (
            !$forceLive
            && $slug === $homeSlug
            && isset($GLOBALS['api_cache']['home_data'])
            && is_array($GLOBALS['api_cache']['home_data'])
        ) {
            $bulkHome = $GLOBALS['api_cache']['home_data'];
            if (!empty($bulkHome['data']) && !cmsPageNeedsLiveRefresh($slug, $bulkHome)) {
                $cache[$cacheKey] = $bulkHome;

                return $bulkHome;
            }
        }

        $fileKey = 'page_' . $slug;
        if (!$forceLive && function_exists('cmsScopedFileCacheGet')) {
            $fileCached = cmsScopedFileCacheGet($fileKey);
            if (
                is_array($fileCached)
                && !empty($fileCached['data'])
                && cmsPageIsPublished($fileCached)
                && !cmsPageNeedsLiveRefresh($slug, $fileCached)
            ) {
                $cache[$cacheKey] = $fileCached;

                return $fileCached;
            }
        }

        $endpoint = 'pages/' . rawurlencode($slug);
        if ($forceLive && function_exists('cms_curl_get_json')) {
            $page = cms_curl_get_json(cmsApiBase() . '/' . $endpoint, 20, true);
        } else {
            $page = cmsCmsGetJson($endpoint);
        }
        if (!is_array($page) || empty($page['data']['sections'] ?? null)) {
            if ($forceLive && function_exists('cms_curl_get_json')) {
                $retry = cms_curl_get_json(cmsApiBase() . '/' . $endpoint, 25, true);
                if (is_array($retry) && !empty($retry['data'])) {
                    $page = $retry;
                }
            } elseif (function_exists('cms_fetch_json_endpoint')) {
                $retry = cms_fetch_json_endpoint('pages/' . $slug);
                if (is_array($retry) && !empty($retry['data'])) {
                    $page = $retry;
                }
            }
        }
        if (
            $forceLive
            && (!is_array($page) || empty($page['data']))
            && function_exists('cmsScopedFileCacheGetAny')
        ) {
            $stale = cmsScopedFileCacheGetAny($fileKey);
            if (is_array($stale) && !empty($stale['data']) && cmsPageIsPublished($stale)) {
                $page = $stale;
            }
        }
        if (!is_array($page)) {
            $page = ['data' => []];
        }

        if (!cmsPageIsPublished($page)) {
            $page = ['data' => []];
        }

        if (!empty($page['data']) && function_exists('cmsApiStoreScopedKey')) {
            cmsApiStoreScopedKey($fileKey, $page);
        }

        if ($slug === $homeSlug && !empty($page['data']) && isset($GLOBALS['api_cache']) && is_array($GLOBALS['api_cache'])) {
            $GLOBALS['api_cache']['home_data'] = $page;
        }

        $cache[$cacheKey] = $page;
        if ($forceLive) {
            $cache[$slug] = $page;
        }

        return $page;
    }

    function cmsFetchPageById(int|string $id): array
    {
        $page = cmsCmsGetJson('pages/' . rawurlencode((string) $id));
        return is_array($page) ? $page : ['data' => []];
    }

    function cmsPathFromSlug(string $slug): string
    {
        $suffix = '-page-' . cmsBranchSuffix();
        if (str_ends_with($slug, $suffix)) {
            return substr($slug, 0, -strlen($suffix));
        }
        return $slug;
    }

    function cmsScriptAliases(): array
    {
        return [
            'faq' => 'faqs',
            'blog' => 'blogs',
            'testimonial' => 'testimonials',
            'club' => 'clubs',
            'event' => 'events',
            'apply-jobs' => 'careers',
            'job-apply' => 'careers',
        ];
    }

    function cmsBreadcrumbScriptPathAliases(): array
    {
        $aliases = [
            'detail-view' => 'event',
            'event-gallery' => 'event',
            'gallery' => 'photo-gallery',
            'achievement-gallery' => 'achievements',
            'print-media-detail' => 'print-media',
            'magazines' => 'print-media',
            'detail' => 'blog',
            'our-curriculam' => 'our-curriculum',
            'topper-class-x' => 'x-toppers',
            'topper-class-xii' => 'xii-toppers',
            'school-infrastructure' => 'facilities',
            'infrastructure' => 'facilities',
            'coding' => 'robotics',
            'our-campus' => 'campus-tour',
            'teacher-training' => 'staff-teaching',
            'documents-and-information' => 'mandatory-public-disclosure',
            'apply-jobs' => 'careers',
            'job-apply' => 'careers',
        ];

        if (function_exists('cmsScriptAliases')) {
            foreach (cmsScriptAliases() as $script => $path) {
                if (!isset($aliases[$script])) {
                    $aliases[$script] = $path;
                }
            }
        }

        return $aliases;
    }

    function cmsGuessSlugFromScript(): ?string
    {
        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
        if ($script === '' || in_array($script, ['index', 'cms-page', 'header', 'footer', 'foot'], true)) {
            return null;
        }

        $base = cmsScriptAliases()[$script] ?? $script;
        $suffix = cmsBranchSuffix();
        $candidates = [
            $base . '-page-' . $suffix,
            $script . '-page-' . $suffix,
            str_replace('-', '', $base) . '-page-' . $suffix,
        ];

        $index = cmsAllPagesIndex();
        $known = [];
        foreach ($index as $page) {
            $known[(string) $page['slug']] = true;
        }

        foreach ($candidates as $candidate) {
            if (isset($known[$candidate])) {
                return $candidate;
            }
        }

        foreach ($index as $page) {
            $slug = (string) ($page['slug'] ?? '');
            if ($slug !== '' && str_contains($slug, $base . '-page-') && str_ends_with($slug, '-' . $suffix)) {
                return $slug;
            }
        }

        return null;
    }

    function cmsResolveSlugFromPath(string $path): ?string
    {
        $path = trim(strtolower($path), '/');
        if ($path === '' || $path === 'home' || $path === 'index') {
            $homeSlug = cmsCoreGet('home_page_slug');
            if (is_string($homeSlug) && $homeSlug !== '') {
                return $homeSlug;
            }

            return 'home-page-' . cmsBranchSuffix();
        }

        static $map = null;
        if ($map === null) {
            $map = [];
            foreach (cmsPagesIndex() as $page) {
                $slug = (string) ($page['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                $derived = cmsPathFromSlug($slug);
                $map[$derived] = $slug;
            }

            $walk = static function (array $items) use (&$walk, &$map): void {
                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $url = trim((string) ($item['url'] ?? ''), '/');
                    if ($url !== '' && $url !== '#') {
                        $linkableType = (string) ($item['linkable_type'] ?? '');
                        $linkableId = $item['linkable_id'] ?? null;
                        if ($linkableId && str_contains($linkableType, 'Page')) {
                            foreach (cmsPagesIndex() as $page) {
                                if ((string) ($page['id'] ?? '') === (string) $linkableId) {
                                    $map[$url] = (string) $page['slug'];
                                    break;
                                }
                            }
                        }
                    }
                    $children = $item['children'] ?? [];
                    if (is_array($children) && $children !== []) {
                        $walk($children);
                    }
                }
            };
            $walk(cmsNavItems());
        }

        $aliases = cmsScriptAliases();
        $aliased = $aliases[$path] ?? $path;

        if (isset($map[$path])) {
            return $map[$path];
        }
        if ($aliased !== $path && isset($map[$aliased])) {
            return $map[$aliased];
        }

        $suffix = cmsBranchSuffix();
        foreach ([$aliased, $path] as $candidatePath) {
            $guess = $candidatePath . '-page-' . $suffix;
            foreach (cmsPagesIndex() as $page) {
                $pageSlug = (string) ($page['slug'] ?? '');
                if ($pageSlug === $guess || $pageSlug === $candidatePath) {
                    return $pageSlug;
                }
            }
        }

        // Newly published CMS pages may not be in the cached pages_index yet
        // (TTL can be hours). Return the conventional slug so cmsFetchPageBySlug
        // can resolve it from the live API instead of hard-404ing.
        // Prefer aliased path (faq -> faqs-page-*, testimonial -> testimonials-page-*).
        return $aliased . '-page-' . $suffix;
    }

    /**
     * Gallery listing pages store items via the galleries API, not page sections.
     *
     * @return array<string, mixed>|null
     */
    function cmsPageGalleryMeta(string $slug): ?array
    {
        $path = strtolower(cmsPathFromSlug($slug));
        if ($path === '') {
            return null;
        }

        $rules = [
            ['paths' => ['achievements', 'our-students-achievements', 'achievement-gallery'], 'mode' => 'subtype', 'subType' => 'achievements', 'detail' => 'achievement-gallery'],
            ['paths' => ['awards', 'awards-achievements', 'awards-honors', 'ghaziabad-awards-honors'], 'mode' => 'subtype', 'subType' => 'awards', 'detail' => 'awards'],
            ['paths' => ['events', 'media-events', 'event-gallery', 'media-events-page', 'event'], 'mode' => 'subtype', 'subType' => 'events', 'detail' => 'event-gallery'],
            ['paths' => ['photo-gallery'], 'mode' => 'subtype', 'subType' => 'photo', 'detail' => 'photo-gallery'],
            ['paths' => ['video-gallery'], 'mode' => 'subtype', 'subType' => 'video', 'detail' => 'video-gallery'],
            ['paths' => ['print-media'], 'mode' => 'subtype', 'subType' => 'print', 'detail' => 'print-media'],
            ['paths' => ['x-toppers', 'class-x-board-toppers', 'x-toppers-page', 'x-board-toppers', '-x-board-toppers'], 'mode' => 'subtype', 'subType' => 'x_toppers', 'detail' => 'x-toppers'],
            ['paths' => ['xii-toppers', 'class-xii-board-toppers', 'xii-board-toppers'], 'mode' => 'subtype', 'subType' => 'xii_toppers', 'detail' => 'xii-toppers'],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['paths'] as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix . '-')) {
                    return [
                        'mode' => $rule['mode'],
                        'subType' => $rule['subType'] ?? null,
                        'detailBase' => $rule['detail'],
                    ];
                }
            }
        }

        if (preg_match('/(gallery|event|achievement|award|photo|video|print)/', $path)) {
            return ['mode' => 'events', 'subType' => null, 'detailBase' => 'event-gallery'];
        }

        return null;
    }

    /**
     * Gallery listing pages may have an empty hero/banner section in CMS while items live in the galleries API.
     */
    function cmsPageSectionsAreGalleryShell(?array $page): bool
    {
        if (!is_array($page['data'] ?? null)) {
            return true;
        }
        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections) || $sections === []) {
            return true;
        }

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $resolved = $section['resolved_content'] ?? null;
            if (is_array($resolved) && ($resolved['items'] ?? []) !== []) {
                return false;
            }

            foreach (['gallery_json_data', 'items', 'section_items'] as $key) {
                $raw = $section[$key] ?? null;
                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    $raw = is_array($decoded) ? $decoded : [];
                }
                if (is_array($raw) && $raw !== []) {
                    return false;
                }
            }

            $columns = $section['columns'] ?? $section['section_columns'] ?? [];
            if (is_array($columns)) {
                foreach ($columns as $column) {
                    if (!is_array($column)) {
                        continue;
                    }
                    if (trim((string) ($column['content'] ?? '')) !== '') {
                        return false;
                    }
                    if (trim((string) ($column['image_url'] ?? $column['media_url'] ?? '')) !== '') {
                        return false;
                    }
                }
            }

            foreach (['content', 'section_content', 'html', 'body'] as $field) {
                if (trim(strip_tags((string) ($section[$field] ?? ''))) !== '') {
                    return false;
                }
            }

            $media = $section['media'] ?? $section['section_media'] ?? $section['carousel_items'] ?? [];
            if (is_array($media) && $media !== []) {
                return false;
            }
        }

        return true;
    }

    function cmsPageTitle(array $page, string $fallback = ''): string
    {
        $data = is_array($page['data'] ?? null) ? $page['data'] : [];
        $title = trim((string) ($data['page_heading'] ?? $data['title'] ?? ''));
        return $title !== '' ? $title : $fallback;
    }

    function cmsNavMenuUrl(?string $url): string
    {
        if (function_exists('cmsMenuUrl')) {
            return cmsMenuUrl($url);
        }
        $url = trim((string) $url);
        return $url !== '' ? $url : '#';
    }

    function renderCmsNavMenu(array $items, bool $isSub = false, string $variant = 'agra'): void
    {
        if ($items === []) {
            return;
        }

        if ($variant === 'ghaziabad') {
            renderGhaziabadCmsMenu($items, $isSub);
            return;
        }

        if (function_exists('renderAgraMenu')) {
            renderAgraMenu($items, $isSub);
            return;
        }

        renderGhaziabadCmsMenu($items, $isSub);
    }

    function renderGhaziabadCmsMenu(array $items, bool $isSub = false): void
    {
        $dropdownSvg = '<svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg" class="transition fill-white sm:fill-black group-hover:fill-white"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z"/></svg>';

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $children = $item['children'] ?? [];
            $hasChildren = is_array($children) && $children !== [];
            $name = htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $href = cmsNavMenuUrl($item['url'] ?? '#');
            $target = htmlspecialchars((string) ($item['target'] ?? '_self'), ENT_QUOTES, 'UTF-8');
            $linkClass = 'hover:text-white hover:bg-[#002a5b] p-2 transition-all';
            $parentLinkClass = 'group ' . $linkClass . ' flex items-center gap-1';

            if ($hasChildren) {
                if ($isSub) {
                    $href = '#';
                    echo '<li class="nav-has-children">';
                    echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="' . $parentLinkClass . ' nav-parent-link" aria-haspopup="true" aria-expanded="false">' . $name . $dropdownSvg . '</a><ul>';
                } else {
                    echo '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="' . $target . '" class="' . $parentLinkClass . '">' . $name . $dropdownSvg . '</a><ul>';
                }
                renderGhaziabadCmsMenu($children, true);
                echo '</ul></li>';
            } else {
                echo '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="' . $target . '" class="' . $linkClass . '">' . $name . '</a></li>';
            }
        }
    }

    function renderGhaziabadMobileCmsMenu(array $items): void
    {
        $arrowSvg = '<svg width="13" height="8" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg" class="transition fill-white arrow group-hover:fill-white"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z"/></svg>';

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $children = $item['children'] ?? [];
            $hasChildren = is_array($children) && $children !== [];
            $name = htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $href = cmsNavMenuUrl($item['url'] ?? '#');
            $target = htmlspecialchars((string) ($item['target'] ?? '_self'), ENT_QUOTES, 'UTF-8');
            $linkClass = 'hover:text-white hover:bg-[#002a5b] p-2 transition-all';

            if ($hasChildren) {
                echo '<li class="mobile-nav-item has-submenu">';
                echo '<a href="#" class="group ' . $linkClass . ' flex items-center gap-1 nav-parent-link">' . $name . $arrowSvg . '</a>';
                echo '<ul class="mobile-submenu">';
                renderGhaziabadMobileCmsMenu($children);
                echo '</ul></li>';
            } else {
                echo '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="' . $target . '" class="' . $linkClass . '">' . $name . '</a></li>';
            }
        }
    }

    function cmsLoadFooterBootstrap(): array
    {
        static $loaded = false;
        static $layout = [];
        static $links = [];

        if ($loaded) {
            return ['layout' => $layout, 'links' => $links];
        }
        $loaded = true;

        if (!defined('BRANCH_ID')) {
            return ['layout' => [], 'links' => []];
        }

        cmsRequireSharedInclude('cms-layout-bootstrap.php');

        $boot = cmsLoadSiteLayoutBootstrap();
        $layout = $boot['layout'];
        $links = $boot['links'];

        return ['layout' => $layout, 'links' => $links];
    }

    if (!function_exists('cmsTitleFromPageSlug')) {
        function cmsTitleFromPageSlug(string $slug): string
        {
            $slug = trim(strtolower($slug));
            if ($slug === '') {
                return '';
            }

            $suffix = '-page-' . cmsBranchSuffix();
            if (str_ends_with($slug, $suffix)) {
                $slug = substr($slug, 0, -strlen($suffix));
            }

            return ucwords(str_replace(['-', '_'], ' ', $slug));
        }
    }
}
