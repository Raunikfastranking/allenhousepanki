<?php

declare(strict_types=1);

if (!function_exists('cmsAssetUrl')) {
    function cmsAssetUrl(?string $path): string
    {
        require_once dirname(__DIR__) . '/shared/includes/cms-media-url.php';

        return cmsResolveMediaUrl($path);
    }
}

if (!function_exists('fixCmsAssetUrl')) {
    function fixCmsAssetUrl(?string $path): string
    {
        return cmsAssetUrl($path);
    }
}
