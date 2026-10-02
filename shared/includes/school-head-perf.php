<?php

declare(strict_types=1);

if (!function_exists('schoolNeedsGalleryScripts')) {
    function schoolNeedsGalleryScripts(): bool
    {
        if (!empty($GLOBALS['school_is_index_page'])) {
            return false;
        }

        $flags = $GLOBALS['cms_api_load_flags_resolved'] ?? null;
        if (!is_array($flags) && function_exists('cmsApiResolveLoadFlags')) {
            $flags = cmsApiResolveLoadFlags();
        }
        if (is_array($flags) && !empty($flags['gallery'])) {
            return true;
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
        if (in_array($script, [
            'gallery.php',
            'photo-gallery.php',
            'video-gallery.php',
            'achievements.php',
            'achievement-gallery.php',
            'event-gallery.php',
            'print-media.php',
            'print-media-detail.php',
            'events.php',
            'event.php',
            'awards.php',
            'academic-achievements.php',
            'x-toppers.php',
            'xii-toppers.php',
            'topper-class-X.php',
            'topper-class-XII.php',
            'topper-class-x.php',
            'topper-class-xii.php',
        ], true)) {
            return true;
        }

        $slug = strtolower(trim((string) ($GLOBALS['cms_page_slug'] ?? '')));
        if ($slug === '') {
            return false;
        }

        foreach (['gallery', 'print-media', 'achievements', 'media-events', 'video-gallery', 'awards', 'topper', 'board-topper'] as $needle) {
            if (str_contains($slug, $needle)) {
                return true;
            }
        }

        return false;
    }
}
