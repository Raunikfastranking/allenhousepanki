<?php

declare(strict_types=1);

/**
 * CMS extra-section rendering — lets footer auto-render new CMS sections
 * added after legacy/custom partial templates (home + inner pages).
 */

if (!function_exists('cmsSetRenderedSectionStartIndex')) {
    function cmsSetRenderedSectionStartIndex(int $startIndex): void
    {
        $GLOBALS['cms_rendered_section_count'] = max(0, $startIndex);
    }
}

if (!function_exists('cmsMarkAllPageSectionsRendered')) {
    function cmsMarkAllPageSectionsRendered(array $page): void
    {
        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            cmsSetRenderedSectionStartIndex(0);

            return;
        }

        cmsSetRenderedSectionStartIndex(count($sections));
    }
}

if (!function_exists('cmsRenderedStartIndexFromSectionOrders')) {
    /**
     * @param list<int> $orders
     */
    function cmsRenderedStartIndexFromSectionOrders(array $page, array $orders): int
    {
        $orderSet = [];
        foreach ($orders as $order) {
            $orderSet[(int) $order] = true;
        }

        $maxIndex = -1;
        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            return 0;
        }

        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            if (isset($orderSet[(int) ($section['order'] ?? 0)])) {
                $maxIndex = max($maxIndex, (int) $index);
            }
        }

        return $maxIndex + 1;
    }
}

if (!function_exists('cmsRenderedStartIndexFromMappedSections')) {
    /**
     * @param list<array<string, mixed>|null> $mapped
     */
    function cmsRenderedStartIndexFromMappedSections(array $page, array $mapped): int
    {
        $orders = [];
        foreach ($mapped as $section) {
            if (!is_array($section)) {
                continue;
            }
            $order = (int) ($section['order'] ?? 0);
            if ($order > 0) {
                $orders[] = $order;
            }
        }

        return cmsRenderedStartIndexFromSectionOrders($page, $orders);
    }
}

if (!function_exists('cmsRenderedStartIndexFromSectionIndices')) {
    /**
     * @param list<int> $indices
     */
    function cmsRenderedStartIndexFromSectionIndices(array $indices): int
    {
        if ($indices === []) {
            return 0;
        }

        return max($indices) + 1;
    }
}

if (!function_exists('cmsMarkPartialPageSectionsRendered')) {
    /**
     * @param list<array<string, mixed>|null> $mappedSections
     */
    function cmsMarkPartialPageSectionsRendered(array $page, array $mappedSections): void
    {
        cmsSetRenderedSectionStartIndex(
            cmsRenderedStartIndexFromMappedSections($page, $mappedSections)
        );
    }
}

if (!function_exists('cmsSchoolSyncPageCacheIfAvailable')) {
    function cmsSchoolSyncPageCacheIfAvailable(string $slug, array $page): void
    {
        foreach ([
            'cmsAgraSyncPageCache',
            'cmsGhaziabadSyncPageCache',
            'cmsJhansiSyncPageCache',
            'cmsKhalasilineSyncPageCache',
            'cmsLucknowSyncPageCache',
            'cmsPankiSyncPageCache',
            'cmsRoomaSyncPageCache',
        ] as $syncFn) {
            if (function_exists($syncFn)) {
                $syncFn($slug, $page);
                return;
            }
        }
    }
}

if (!function_exists('cmsFetchSchoolHomePage')) {
  /**
   * @param callable(string): array $fetchFn
   */
    function cmsFetchSchoolHomePage(string $slug, callable $fetchFn): array
    {
        static $memoBySlug = [];

        if (isset($memoBySlug[$slug])) {
            return $memoBySlug[$slug];
        }

        $restoreSlug = empty($GLOBALS['school_is_index_page']);
        $previousSlug = $GLOBALS['cms_page_slug'] ?? null;
        $GLOBALS['cms_page_slug'] = $slug;

        try {
            $isIndexHome = !empty($GLOBALS['school_is_index_page']);

            if (
                !$isIndexHome
                && isset($GLOBALS['api_cache']['home_data'])
                && is_array($GLOBALS['api_cache']['home_data'])
                && !empty($GLOBALS['api_cache']['home_data']['data'])
            ) {
                $slugNorm = strtolower(trim($slug));
                if ($slugNorm === 'home-page-ghaziabad' || str_contains($slugNorm, 'home-page')) {
                    $memoBySlug[$slug] = $GLOBALS['api_cache']['home_data'];
                    cmsSchoolSyncPageCacheIfAvailable($slug, $memoBySlug[$slug]);

                    return $memoBySlug[$slug];
                }
            }

            $page = $fetchFn($slug);

            cmsSchoolSyncPageCacheIfAvailable($slug, $page);
            $memoBySlug[$slug] = $page;

            return $page;
        } finally {
            if ($restoreSlug) {
                if (is_string($previousSlug) && $previousSlug !== '') {
                    $GLOBALS['cms_page_slug'] = $previousSlug;
                } else {
                    unset($GLOBALS['cms_page_slug']);
                }
            }
        }
    }
}

if (!function_exists('cmsHomeLegacySectionOrders')) {
    /**
     * @return list<int>
     */
    function cmsHomeLegacySectionOrders(): array
    {
        $orders = [1, 2, 3, 4, 5, 6, 7, 8];
        foreach ([8, 9, 10, 11, 12] as $roomaOrder) {
            $orders[] = function_exists('cmsHomeMappedOrder')
                ? cmsHomeMappedOrder($roomaOrder)
                : $roomaOrder;
        }

        $unique = [];
        foreach ($orders as $order) {
            $unique[(int) $order] = (int) $order;
        }

        return array_values($unique);
    }
}

if (!function_exists('cmsHomeLegacyRenderedStartIndexFromSections')) {
    function cmsHomeLegacyRenderedStartIndexFromSections(array $sections): int
    {
        return cmsRenderedStartIndexFromSectionOrders(
            ['data' => ['sections' => $sections]],
            cmsHomeLegacySectionOrders()
        );
    }
}

if (!function_exists('cmsHomeLegacyRenderedStartIndex')) {
    function cmsHomeLegacyRenderedStartIndex(): int
    {
        if (!function_exists('cmsHomeSections')) {
            return 0;
        }

        return cmsHomeLegacyRenderedStartIndexFromSections(cmsHomeSections());
    }
}

if (!function_exists('cmsFetchLiveCmsPageBySlug')) {
    function cmsFetchLiveCmsPageBySlug(string $slug): array
    {
        $slug = trim($slug);
        if ($slug === '' || !function_exists('cmsFetchPageBySlug')) {
            return ['data' => []];
        }

        return cmsFetchPageBySlug($slug, true);
    }
}

if (!function_exists('cmsResolveExtraSectionsPageSlug')) {
    function cmsResolveExtraSectionsPageSlug(): ?string
    {
        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        if (!empty($GLOBALS['school_is_index_page']) && function_exists('cmsCoreGet')) {
            $home = cmsCoreGet('home_page_slug', '');
            if (is_string($home) && $home !== '') {
                return $home;
            }
        }

        return function_exists('cmsGuessSlugFromScript') ? cmsGuessSlugFromScript() : null;
    }
}

if (!function_exists('cmsMarkExtractPagePartsRendered')) {
    /**
     * @param list<int> $usedIndices
     */
    function cmsMarkExtractPagePartsRendered(array $raw_page, array $usedIndices): void
    {
        if ($usedIndices === [] || !function_exists('cmsSetRenderedSectionStartIndex')) {
            return;
        }

        $pageSlug = trim((string) ($raw_page['data']['slug'] ?? ''));
        $activeSlug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($activeSlug === '' || ($pageSlug !== '' && $pageSlug !== $activeSlug)) {
            return;
        }

        cmsSetRenderedSectionStartIndex(cmsRenderedStartIndexFromSectionIndices($usedIndices));
    }
}

if (!function_exists('cmsMaybeRenderExtraSections')) {
    function cmsMaybeRenderExtraSections(): void
    {
        if (!empty($GLOBALS['cms_skip_auto_sections'])) {
            return;
        }

        $slug = cmsResolveExtraSectionsPageSlug();
        if ($slug === null || $slug === '') {
            return;
        }

        $start = (int) ($GLOBALS['cms_rendered_section_count'] ?? 0);
        if ($start < 0) {
            return;
        }

        $page = cmsFetchLiveCmsPageBySlug($slug);
        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            return;
        }

        $total = count($sections);
        if ($total <= $start) {
            return;
        }

        $isHome = !empty($GLOBALS['school_is_index_page']);
        if (!$isHome && function_exists('cmsCoreGet')) {
            $homeSlug = trim((string) cmsCoreGet('home_page_slug', ''));
            $isHome = $homeSlug !== '' && $slug === $homeSlug;
        }

        cmsRenderPageSections($page, [
            'start_index' => $start,
            'skip_hero' => true,
            'skip_page_banner' => true,
            'skip_breadcrumbs' => true,
            'is_home' => $isHome,
        ]);
    }
}
