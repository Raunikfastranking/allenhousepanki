<?php

declare(strict_types=1);

/**
 * CMS image alt text — reads alt fields from API section/item/media payloads.
 */

if (!function_exists('cms_media_alt_plain')) {
    function cms_media_alt_plain($item, string $fallback = ''): string
    {
        if (!is_array($item)) {
            return trim($fallback);
        }

        $candidates = [
            $item['image_alt'] ?? null,
            $item['image_alt_text'] ?? null,
            $item['media_alt_text'] ?? null,
            $item['alt_text'] ?? null,
            $item['alt'] ?? null,
            $item['footer_logo_alt'] ?? null,
            $item['logo_alt'] ?? null,
            $item['android_badge_alt'] ?? null,
            $item['ios_badge_alt'] ?? null,
            $item['alternative_text'] ?? null,
        ];

        if (isset($item['media']) && is_array($item['media'])) {
            $candidates[] = $item['media']['image_alt'] ?? null;
            $candidates[] = $item['media']['image_alt_text'] ?? null;
            $candidates[] = $item['media']['media_alt_text'] ?? null;
            $candidates[] = $item['media']['alt_text'] ?? null;
            $candidates[] = $item['media']['alt'] ?? null;
        }

        foreach ($candidates as $candidate) {
            $alt = trim(strip_tags((string) $candidate));
            if ($alt !== '') {
                return $alt;
            }
        }

        return trim($fallback);
    }
}

if (!function_exists('cms_image_alt_map_register_entry')) {
    /**
     * @param array<string, string> $map
     */
    function cms_image_alt_map_register_entry(array &$map, string $urlRaw, string $alt): void
    {
        $urlRaw = trim($urlRaw);
        $alt = trim(strip_tags($alt));
        if ($urlRaw === '' || $alt === '') {
            return;
        }

        if (function_exists('cms_normalize_image_src_url')) {
            $norm = cms_normalize_image_src_url($urlRaw);
            if ($norm !== '') {
                $map[$norm] = $alt;
            }
        }

        $pathOnly = parse_url($urlRaw, PHP_URL_PATH);
        if (is_string($pathOnly) && $pathOnly !== '') {
            $map['path:' . rtrim(str_replace('\\', '/', $pathOnly), '/')] = $alt;
        }
    }
}

if (!function_exists('cms_collect_image_alt_map_from_page')) {
    /**
     * @param array<string, string> $map
     */
    function cms_collect_image_alt_map_from_page(array $page, array &$map): void
    {
        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            return;
        }

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $resolved = $section['resolved_content'] ?? [];
            if (!is_array($resolved)) {
                continue;
            }

            foreach (['items', 'media'] as $bucket) {
                foreach ($resolved[$bucket] ?? [] as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    $alt = cms_media_alt_plain($entry, '');
                    if ($alt === '') {
                        continue;
                    }

                    foreach (['media_url', 'media_file', 'image_url', 'mobile_image_url', 'urls', 'url', 'image', 'src'] as $urlKey) {
                        $urlRaw = trim((string) ($entry[$urlKey] ?? ''));
                        if ($urlRaw !== '') {
                            cms_image_alt_map_register_entry($map, $urlRaw, $alt);
                        }
                    }
                }
            }

            foreach ($section['columns'] ?? [] as $column) {
                if (!is_array($column)) {
                    continue;
                }

                $alt = cms_media_alt_plain($column, '');
                if ($alt === '') {
                    continue;
                }

                foreach (['image_url', 'image_path', 'media_url'] as $urlKey) {
                    $urlRaw = trim((string) ($column[$urlKey] ?? ''));
                    if ($urlRaw !== '') {
                        cms_image_alt_map_register_entry($map, $urlRaw, $alt);
                    }
                }
            }
        }
    }
}

if (!function_exists('cms_collect_image_alt_map_from_api_cache')) {
    /** @return array<string, string> */
    function cms_collect_image_alt_map_from_api_cache(): array
    {
        static $cacheMap = null;
        if (is_array($cacheMap)) {
            return $cacheMap;
        }

        $cacheMap = [];
        $apiCache = $GLOBALS['api_cache'] ?? null;
        if (!is_array($apiCache)) {
            return $cacheMap;
        }

        foreach ($apiCache as $payload) {
            if (!is_array($payload) || empty($payload['data'])) {
                continue;
            }
            cms_collect_image_alt_map_from_page($payload, $cacheMap);
        }

        return $cacheMap;
    }
}

if (!function_exists('cms_image_alt')) {
    function cms_image_alt($item, string $fallback = ''): string
    {
        return htmlspecialchars(cms_media_alt_plain($item, $fallback), ENT_QUOTES, 'UTF-8');
    }
}
