<?php

declare(strict_types=1);

/**
 * CMS header/footer bootstrap: layout-parts, branch menus, footer link groups.
 */

if (!function_exists('cmsFooterLinkGroup')) {
    function cmsFooterLinkGroup($linkData, string $groupSlug): array
    {
        if ($groupSlug === '') {
            return [];
        }

        $groups = [];
        if (is_array($linkData)) {
            $groups = isset($linkData['data']) && is_array($linkData['data']) ? $linkData['data'] : $linkData;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            if (($group['slug'] ?? null) === $groupSlug || ($group['group_slug'] ?? null) === $groupSlug) {
                return $group;
            }
        }

        return [];
    }
}

if (!function_exists('cmsBulkApiCacheGet')) {
    function cmsBulkApiCacheGet(string $key): ?array
    {
        if (!isset($GLOBALS['api_cache']) || !is_array($GLOBALS['api_cache'])) {
            return null;
        }

        $value = $GLOBALS['api_cache'][$key] ?? null;
        if (!is_array($value) || !cmsApiResponseIsUsable($value)) {
            return null;
        }

        return $value;
    }
}

if (!function_exists('cmsFetchFooterHierarchical')) {
    /** @return array<string, mixed>|null */
    function cmsFetchFooterHierarchical(): ?array
    {
        static $hier = null;
        if ($hier !== null) {
            return $hier;
        }

        if (!defined('BRANCH_ID')) {
            return null;
        }

        if (function_exists('cmsBulkApiCacheGet')) {
            $bulk = cmsBulkApiCacheGet('footer_hierarchical');
            if ($bulk !== null) {
                $hier = $bulk;

                return $hier;
            }
        }

        $hier = cmsLayoutApiGet('public/link-groups/position/footer/hierarchical?branch_id=' . (int) BRANCH_ID);

        return is_array($hier) ? $hier : null;
    }
}

if (!function_exists('cmsLayoutApiGet')) {
    function cmsLayoutApiGet(string $endpoint, int $timeout = 20): ?array
    {
        if (!defined('CMS_API_URL')) {
            return null;
        }

        $path = ltrim($endpoint, '/');
        if (function_exists('cmsCmsGetJson')) {
            $decoded = cmsCmsGetJson($path, min(4, $timeout));

            return is_array($decoded) ? $decoded : null;
        }

        if (function_exists('cms_curl_get_json')) {
            $decoded = cms_curl_get_json(rtrim(CMS_API_URL, '/') . '/' . $path, $timeout);

            return is_array($decoded) ? $decoded : null;
        }

        $url = rtrim(CMS_API_URL, '/') . '/' . $path;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => function_exists('api_auth_headers') ? api_auth_headers() : [],
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
}

if (!function_exists('cmsLayoutItemsFromResponse')) {
    /** @return list<array<string, mixed>> */
    function cmsLayoutItemsFromResponse(?array $layoutData): array
    {
        if (!is_array($layoutData)) {
            return [];
        }
        if (isset($layoutData['data']) && is_array($layoutData['data'])) {
            return array_values($layoutData['data']);
        }
        if (isset($layoutData[0]) && is_array($layoutData[0])) {
            return array_values($layoutData);
        }

        return [];
    }
}

if (!function_exists('cmsLinkGroupBranchActive')) {
    function cmsLinkGroupBranchActive(array $group, int $branchId): bool
    {
        $branches = $group['branches'] ?? [];
        if (!is_array($branches) || $branches === []) {
            return true;
        }

        foreach ($branches as $branch) {
            if (!is_array($branch)) {
                continue;
            }
            if ((int) ($branch['id'] ?? 0) === $branchId) {
                $pivotActive = $branch['pivot']['is_active'] ?? '1';
                if ($pivotActive === false || $pivotActive === 0 || $pivotActive === '0') {
                    return false;
                }

                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cmsNormalizeFooterLinkTree')) {
    /**
     * @param list<array<string, mixed>> $links
     * @return list<array<string, mixed>>
     */
    function cmsNormalizeFooterLinkTree(array $links): array
    {
        $normalized = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            if (array_key_exists('is_active', $link) && !$link['is_active']) {
                continue;
            }
            $children = $link['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $link['children'] = cmsNormalizeFooterLinkTree($children);
            }
            $normalized[] = $link;
        }

        usort($normalized, static function (array $a, array $b): int {
            return ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0));
        });

        return $normalized;
    }
}

if (!function_exists('cmsBuildFooterLinkTreeFromFlat')) {
    /**
     * Build parent/child tree from flat /link-groups/{slug} links using parent_id + depth.
     *
     * @param list<array<string, mixed>> $flatLinks
     * @return list<array<string, mixed>>
     */
function cmsBuildFooterLinkTreeFromFlat(array $flatLinks): array
{
    if ($flatLinks === []) {
        return [];
    }

    $hasHierarchy = false;
    foreach ($flatLinks as $link) {
        if (!is_array($link)) {
            continue;
        }
        if (!empty($link['parent_id']) || (int) ($link['depth'] ?? 0) > 0) {
            $hasHierarchy = true;
            break;
        }
    }

    if (!$hasHierarchy) {
        return cmsNormalizeFooterLinkTree($flatLinks);
    }

    $indexed = [];
    foreach ($flatLinks as $link) {
        if (!is_array($link)) {
            continue;
        }
        $id = (string) ($link['id'] ?? '');
        if ($id === '') {
            continue;
        }
        $link['children'] = [];
        $indexed[$id] = $link;
    }

    $roots = [];
    foreach ($indexed as $id => $link) {
        $parentId = (string) ($link['parent_id'] ?? '');
        if ($parentId !== '' && isset($indexed[$parentId])) {
            $indexed[$parentId]['children'][] = $link;
        } else {
            $roots[] = $link;
        }
    }

    return cmsNormalizeFooterLinkTree($roots);
}
}

if (!function_exists('cmsFetchLinkGroupBySlug')) {
    /** @return array<string, mixed> */
    function cmsFetchLinkGroupBySlug(string $slug): array
    {
        static $memo = [];
        if (isset($memo[$slug])) {
            return $memo[$slug];
        }

        if ($slug === '' || !defined('BRANCH_ID')) {
            return [];
        }

        $bulkKey = match ($slug) {
            'important-links' => 'footer_important_links',
            'quick-links' => 'footer_quick_links',
            default => null,
        };
        if ($bulkKey !== null) {
            $response = cmsBulkApiCacheGet($bulkKey);
            if (is_array($response) && is_array($response['data'] ?? null)) {
                $group = $response['data'];
                if ($group !== [] && cmsLinkGroupBranchActive($group, (int) BRANCH_ID)) {
                    $links = is_array($group['links'] ?? null) ? $group['links'] : [];
                    $group['links'] = cmsBuildFooterLinkTreeFromFlat($links);
                    $memo[$slug] = $group;

                    return $group;
                }
            }
        }

        $response = cmsLayoutApiGet('link-groups/' . rawurlencode($slug));
        $group = is_array($response['data'] ?? null) ? $response['data'] : [];
        if ($group === [] || !cmsLinkGroupBranchActive($group, (int) BRANCH_ID)) {
            return [];
        }

        $links = is_array($group['links'] ?? null) ? $group['links'] : [];
        $group['links'] = cmsBuildFooterLinkTreeFromFlat($links);
        $memo[$slug] = $group;

        return $group;
    }
}

if (!function_exists('cmsMergeBranchFooterLinksFromHierarchical')) {
    /** @param array<string, mixed> $slugGroup */
    function cmsMergeBranchFooterLinksFromHierarchical(array $slugGroup, string $slug): array
    {
        if ($slugGroup === [] || !defined('BRANCH_ID')) {
            return $slugGroup;
        }

        if (isset($GLOBALS['footer_link_data']) && is_array($GLOBALS['footer_link_data'])) {
            $fromGlobal = cmsFooterLinkGroup($GLOBALS['footer_link_data'], $slug);
            if ($fromGlobal !== [] && is_array($fromGlobal['links'] ?? null) && $fromGlobal['links'] !== []) {
                $slugGroup['links'] = cmsNormalizeFooterLinkTree($fromGlobal['links']);

                return $slugGroup;
            }
        }

        $hier = cmsFetchFooterHierarchical();
        foreach (is_array($hier['data'] ?? null) ? $hier['data'] : [] as $branchGroup) {
            if (!is_array($branchGroup) || (string) ($branchGroup['slug'] ?? '') !== $slug) {
                continue;
            }
            if (is_array($branchGroup['links'] ?? null) && $branchGroup['links'] !== []) {
                $slugGroup['links'] = cmsNormalizeFooterLinkTree($branchGroup['links']);
            }
            break;
        }

        return $slugGroup;
    }
}

if (!function_exists('cmsLoadFooterLinkGroups')) {
    /** @return array{data: list<array<string, mixed>>} */
    function cmsLoadFooterLinkGroups(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $important = cmsMergeBranchFooterLinksFromHierarchical(
            cmsFetchLinkGroupBySlug('important-links'),
            'important-links'
        );
        $quick = cmsMergeBranchFooterLinksFromHierarchical(
            cmsFetchLinkGroupBySlug('quick-links'),
            'quick-links'
        );

        $groups = [];
        if ($important !== []) {
            $groups[] = $important;
        }
        if ($quick !== []) {
            $groups[] = $quick;
        }

        $cache = ['data' => $groups];

        return $cache;
    }
}

if (!function_exists('cmsBuildFooterLinkDataFromBulk')) {
    /**
     * @param array<string, mixed> $bulkCache
     * @return array{data: list<array<string, mixed>>}
     */
    function cmsBuildFooterLinkDataFromBulk(array $bulkCache): array
    {
        $importantRaw = is_array($bulkCache['footer_important_links'] ?? null)
            ? $bulkCache['footer_important_links']
            : [];
        $quickRaw = is_array($bulkCache['footer_quick_links'] ?? null)
            ? $bulkCache['footer_quick_links']
            : [];

        $important = is_array($importantRaw['data'] ?? null) ? $importantRaw['data'] : [];
        $quick = is_array($quickRaw['data'] ?? null) ? $quickRaw['data'] : [];

        if ($important !== [] && cmsLinkGroupBranchActive($important, (int) (defined('BRANCH_ID') ? BRANCH_ID : 0))) {
            $links = is_array($important['links'] ?? null) ? $important['links'] : [];
            $important['links'] = cmsBuildFooterLinkTreeFromFlat($links);
            $important = cmsMergeBranchFooterLinksFromHierarchical($important, 'important-links');
        } else {
            $important = [];
        }

        if ($quick !== [] && cmsLinkGroupBranchActive($quick, (int) (defined('BRANCH_ID') ? BRANCH_ID : 0))) {
            $links = is_array($quick['links'] ?? null) ? $quick['links'] : [];
            $quick['links'] = cmsBuildFooterLinkTreeFromFlat($links);
            $quick = cmsMergeBranchFooterLinksFromHierarchical($quick, 'quick-links');
        } else {
            $quick = [];
        }

        if ($important === [] || $quick === []) {
            $live = cmsLoadFooterLinkGroups();
            if ($important === []) {
                $liveImportant = cmsFooterLinkGroup($live, 'important-links');
                if ($liveImportant !== []) {
                    $important = $liveImportant;
                }
            }
            if ($quick === []) {
                $liveQuick = cmsFooterLinkGroup($live, 'quick-links');
                if ($liveQuick !== []) {
                    $quick = $liveQuick;
                }
            }
        }

        $groups = [];
        if ($important !== []) {
            $groups[] = $important;
        }
        if ($quick !== []) {
            $groups[] = $quick;
        }

        if ($groups === []) {
            return cmsLoadFooterLinkGroups();
        }

        return ['data' => $groups];
    }
}

if (!function_exists('cmsLoadLayoutParts')) {
    function cmsLoadLayoutParts(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $fromBulk = cmsBulkApiCacheGet('header_footer_data');
        if ($fromBulk !== null && cmsLayoutItemsFromResponse($fromBulk) !== []) {
            $cache = $fromBulk;

            return $cache;
        }

        if (!defined('BRANCH_ID')) {
            $cache = [];

            return $cache;
        }

        $response = cmsLayoutApiGet('public/branches/' . (int) BRANCH_ID . '/layout-parts/');
        $cache = is_array($response) ? $response : [];

        return $cache;
    }
}

if (!function_exists('cmsLoadBranchMenu')) {
    function cmsLoadBranchMenu(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $fromBulk = cmsBulkApiCacheGet('menu_data');
        if ($fromBulk !== null) {
            $cache = $fromBulk;

            return $cache;
        }

        if (!defined('BRANCH_ID')) {
            $cache = [];

            return $cache;
        }

        $response = cmsLayoutApiGet('menus/branch/' . (int) BRANCH_ID);
        $cache = is_array($response) ? $response : [];

        return $cache;
    }
}

if (!function_exists('cmsLoadSiteLayoutBootstrap')) {
    /** @return array{layout: array, menu: array, links: array} */
    function cmsLoadSiteLayoutBootstrap(): array
    {
        return [
            'layout' => cmsLoadLayoutParts(),
            'menu' => cmsLoadBranchMenu(),
            'links' => cmsLoadFooterLinkGroups(),
        ];
    }
}
