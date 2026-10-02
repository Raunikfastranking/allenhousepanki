<?php

declare(strict_types=1);

/**
 * Per-page BreadcrumbList JSON-LD for Google (DPS-style).
 * Frontend breadcrumbs (cmsRenderBreadcrumbsNav) are unchanged.
 */

if (!function_exists('cmsIsHomePageRequest')) {
    function cmsIsHomePageRequest(): bool
    {
        $requestPath = trim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
        $homePaths = ['', '/'];

        if (function_exists('site_base_url')) {
            $basePath = rtrim(site_base_url(), '/');
            $homePaths = array_merge($homePaths, [
                $basePath,
                $basePath . '/',
                $basePath . '/index.php',
            ]);
        }

        return in_array($requestPath, array_unique($homePaths), true);
    }
}

if (!function_exists('cmsBreadcrumbSchemaItemsFromCrumbs')) {
    /**
     * @param list<array{label: string, url: string}> $crumbs
     * @return list<array{name: string, item: string}>
     */
    function cmsBreadcrumbSchemaItemsFromCrumbs(array $crumbs): array
    {
        if (!function_exists('site_origin_url')) {
            return [];
        }

        $origin = rtrim(site_origin_url(), '/');
        $items = [[
            'name' => 'Home',
            'item' => $origin . '/',
        ]];

        foreach ($crumbs as $crumb) {
            $label = trim((string) ($crumb['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $url = trim((string) ($crumb['url'] ?? ''));
            if ($url === '') {
                $url = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
            }
            if (!preg_match('#^https?://#i', $url)) {
                $url = $origin . '/' . ltrim($url, '/');
            }

            $items[] = ['name' => $label, 'item' => $url];
        }

        return count($items) > 1 ? $items : [];
    }
}

if (!function_exists('cmsDynamicBreadcrumbSchemaItems')) {
    /**
     * @return list<array{name: string, item: string}>
     */
    function cmsDynamicBreadcrumbSchemaItems(): array
    {
        if (!function_exists('cmsResolvePageBreadcrumbs')) {
            $contentFile = __DIR__ . '/cms-page-content.php';
            if (is_file($contentFile)) {
                require_once $contentFile;
            }
        }

        $override = $GLOBALS['cms_breadcrumb_schema_items'] ?? null;
        if (is_array($override) && $override !== []) {
            return $override;
        }

        if (!function_exists('site_origin_url') || !function_exists('site_page_url')) {
            return [];
        }

        $origin = rtrim(site_origin_url(), '/');
        $items = [[
            'name' => 'Home',
            'item' => $origin . '/',
        ]];

        if (cmsIsHomePageRequest()) {
            return $items;
        }

        if (
            isset($GLOBALS['cms_current_blog'])
            && is_array($GLOBALS['cms_current_blog'])
            && function_exists('cmsResolveDetailPageBreadcrumbs')
            && function_exists('cmsBlogPostTitle')
        ) {
            $blog = $GLOBALS['cms_current_blog'];
            $detail = null;
            foreach ($blog['blogdetails'] ?? [] as $row) {
                if (is_array($row)) {
                    $detail = $row;
                    break;
                }
            }
            $title = cmsBlogPostTitle($blog, is_array($detail) ? $detail : null);
            $trail = cmsResolveDetailPageBreadcrumbs(null, 'Blog', site_page_url('blog'), $title);

            return cmsBreadcrumbSchemaItemsFromCrumbs($trail);
        }

        if (
            isset($GLOBALS['cms_current_page'])
            && is_array($GLOBALS['cms_current_page'])
            && function_exists('cmsResolvePageBreadcrumbs')
        ) {
            $trail = cmsResolvePageBreadcrumbs($GLOBALS['cms_current_page']);

            return cmsBreadcrumbSchemaItemsFromCrumbs($trail);
        }

        return [];
    }
}

if (!function_exists('cmsRenderBreadcrumbSchemaJsonLd')) {
    function cmsRenderBreadcrumbSchemaJsonLd(): void
    {
        if (!function_exists('cmsDynamicBreadcrumbSchemaItems')) {
            return;
        }

        $items = cmsDynamicBreadcrumbSchemaItems();
        if ($items === []) {
            return;
        }

        $payload = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                static function (array $item, int $index): array {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item['name'],
                        'item' => $item['item'],
                    ];
                },
                $items,
                array_keys($items)
            ),
        ];

        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
}
