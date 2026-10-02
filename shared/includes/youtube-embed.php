<?php

declare(strict_types=1);

/**
 * YouTube embed helpers — fixes Error 153 (missing Referer) on embedded players.
 * Use youtube-nocookie.com and strict-origin-when-cross-origin referrer policy.
 */

if (!function_exists('cmsYoutubeExtractVideoId')) {
    function cmsYoutubeExtractVideoId(string $videoUrl): string
    {
        $videoUrl = trim($videoUrl);
        if ($videoUrl === '') {
            return '';
        }

        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{11})#i', $videoUrl, $m)) {
            return $m[1];
        }

        $path = trim((string) parse_url($videoUrl, PHP_URL_PATH), '/');
        if (preg_match('#^[A-Za-z0-9_-]{11}$#', $path)) {
            return $path;
        }

        return '';
    }
}

if (!function_exists('cmsYoutubeEmbedUrl')) {
    function cmsYoutubeEmbedUrl(string $videoIdOrUrl, array $query = []): string
    {
        $id = preg_match('#^[A-Za-z0-9_-]{11}$#', $videoIdOrUrl)
            ? $videoIdOrUrl
            : cmsYoutubeExtractVideoId($videoIdOrUrl);

        if ($id === '') {
            return '';
        }

        $params = $query !== [] ? '?' . http_build_query($query) : '';

        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($id) . $params;
    }
}

if (!function_exists('cmsYoutubeIframeReferrerPolicyAttr')) {
    function cmsYoutubeIframeReferrerPolicyAttr(): string
    {
        return 'referrerpolicy="strict-origin-when-cross-origin"';
    }
}
