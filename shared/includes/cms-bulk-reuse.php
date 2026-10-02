<?php

declare(strict_types=1);

/**
 * Reuse scoped bulk API cache before issuing duplicate network requests.
 */

if (!function_exists('cmsBulkStatisticsResponse')) {
    /** @return array<string, mixed>|null */
    function cmsBulkStatisticsResponse(): ?array
    {
        if (function_exists('cmsBulkApiCacheGet')) {
            $bulk = cmsBulkApiCacheGet('statistic_data');
            if ($bulk !== null) {
                return $bulk;
            }
        }

        if (isset($GLOBALS['statistic_data']) && is_array($GLOBALS['statistic_data'])) {
            if (!function_exists('cmsApiResponseIsUsable') || cmsApiResponseIsUsable($GLOBALS['statistic_data'])) {
                return $GLOBALS['statistic_data'];
            }
        }

        return null;
    }
}

if (!function_exists('cmsBulkFlyersResponse')) {
    /** @return array<string, mixed>|null */
    function cmsBulkFlyersResponse(): ?array
    {
        $candidates = [];
        if (function_exists('cmsBulkApiCacheGet')) {
            $bulk = cmsBulkApiCacheGet('flyer_data');
            if ($bulk !== null) {
                $candidates[] = $bulk;
            }
        }

        if (isset($GLOBALS['flyer_data']) && is_array($GLOBALS['flyer_data'])) {
            $candidates[] = $GLOBALS['flyer_data'];
        }

        foreach ($candidates as $bulk) {
            if (!function_exists('cmsApiResponseIsUsable') || !cmsApiResponseIsUsable($bulk)) {
                continue;
            }

            $items = isset($bulk['data']) && is_array($bulk['data']) ? $bulk['data'] : $bulk;
            if (!is_array($items) || $items === []) {
                continue;
            }

            $hasImage = false;
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (trim((string) ($item['image_url'] ?? $item['image'] ?? '')) !== '') {
                    $hasImage = true;
                    break;
                }
            }

            if ($hasImage) {
                return $bulk;
            }
        }

        return null;
    }
}
