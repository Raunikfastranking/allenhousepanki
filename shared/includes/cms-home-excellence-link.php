<?php

declare(strict_types=1);

if (!function_exists('cmsHomeExcellenceRouteFromTitle')) {
  function cmsHomeExcellenceRouteFromTitle(?string $title): ?string
    {
        $title = strtolower(trim(strip_tags((string) $title)));
        if ($title === '') {
            return null;
        }

        if (str_contains($title, 'celebrating')) {
            return 'celebrating-excellence';
        }
        if (str_contains($title, 'spotlight')) {
            return 'excellence-in-spotlight';
        }
        if (str_contains($title, 'embark') || str_contains($title, 'journey')) {
            return 'embark-on-journey';
        }
        if (str_contains($title, 'action')) {
            return 'excellence-in-action';
        }

        return null;
    }
}

if (!function_exists('cmsHomeExcellenceLinkUrl')) {
    /**
     * Map legacy CMS /view/... excellence URLs to site PHP routes.
     */
    function cmsHomeExcellenceLinkUrl(?string $url, ?string $title = null): string
    {
        $routeFromTitle = cmsHomeExcellenceRouteFromTitle($title);
        if ($routeFromTitle !== null) {
            return function_exists('cmsMenuUrl') ? cmsMenuUrl($routeFromTitle) : '/' . $routeFromTitle;
        }

        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return '#';
        }

        $path = trim((string) (parse_url($url, PHP_URL_PATH) ?: $url), '/');
        if ($path === '') {
            return function_exists('cmsMenuUrl') ? cmsMenuUrl($url) : $url;
        }

        if (str_starts_with($path, 'view/')) {
            $path = substr($path, 5);
        }

        static $legacyViewSlugs = [
            'celebrating-excellence' => 'celebrating-excellence',
            'excellence-in-action' => 'excellence-in-action',
            'excellence-in-the-spotlight' => 'excellence-in-spotlight',
            'embark-on-a-journey-of-excellence' => 'embark-on-journey',
        ];

        if (isset($legacyViewSlugs[$path])) {
            $path = $legacyViewSlugs[$path];
        }

        if (function_exists('cmsMenuUrl')) {
            return cmsMenuUrl($path);
        }

        return '/' . ltrim($path, '/');
    }
}
