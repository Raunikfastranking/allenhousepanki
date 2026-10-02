<?php

declare(strict_types=1);

if (!function_exists('cmsRenderFlyerPopupFromCache')) {
    function cmsRenderFlyerPopupFromCache(): void
    {
        $raw = $GLOBALS['api_cache']['flyer_data'] ?? null;
        if ($raw === null) {
            return;
        }

        $rows = [];
        if (isset($raw['data']) && is_array($raw['data'])) {
            $rows = $raw['data'];
        } elseif (is_array($raw)) {
            $rows = $raw;
        }

        $first = is_array($rows[0] ?? null) ? $rows[0] : [];
        $image = '';
        if (function_exists('cmsAssetUrl')) {
            $image = cmsAssetUrl($first['image'] ?? $first['image_url'] ?? $first['media_url'] ?? '');
        } elseif (function_exists('fixCmsAssetUrl')) {
            $image = fixCmsAssetUrl($first['image'] ?? $first['image_url'] ?? '');
        }
        $image = trim($image);
        if ($image === '') {
            return;
        }

        $url = trim((string) ($first['url'] ?? $first['link'] ?? $first['link_url'] ?? '#'));
        $text = trim((string) ($first['text'] ?? $first['title'] ?? ''));
        $showPopup = true;
        require __DIR__ . '/home-flyer-popup.php';
    }
}
