<?php

declare(strict_types=1);

if (!function_exists('cmsHeaderLayoutSlug')) {
    function cmsHeaderLayoutSlug(): string
    {
        if (function_exists('cmsCoreGet')) {
            $slug = cmsCoreGet('header_layout_slug');
            if (is_string($slug) && $slug !== '') {
                return $slug;
            }
        }

        return 'header-aps-agra';
    }
}

if (!function_exists('cmsLayoutPartFromBulk')) {
    function cmsLayoutPartFromBulk(?array $layoutParts, string $slug): ?array
    {
        if (!is_array($layoutParts)) {
            return null;
        }

        $items = $layoutParts['data'] ?? $layoutParts;
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $part) {
            if (!is_array($part)) {
                continue;
            }
            if (($part['slug'] ?? '') === $slug || ($part['layout_slug'] ?? '') === $slug) {
                return $part;
            }
            if (($part['type'] ?? '') === 'header' || ($part['layout_type'] ?? '') === 'header') {
                return $part;
            }
        }

        return null;
    }
}

if (!function_exists('cmsLayoutMetaFromBulk')) {
    function cmsLayoutMetaFromBulk(?array $layoutParts, ?string $slug = null): array
    {
        $slug ??= cmsHeaderLayoutSlug();
        $part = cmsLayoutPartFromBulk($layoutParts, $slug);
        $meta = is_array($part) ? ($part['meta_data'] ?? []) : [];

        return is_array($meta) ? $meta : [];
    }
}

if (!function_exists('cmsEmptyResponse')) {
    function cmsEmptyResponse(): array
    {
        return ['status' => 'success', 'data' => []];
    }
}

if (!function_exists('cmsWrapData')) {
    function cmsWrapData(array $rows): array
    {
        return ['status' => 'success', 'data' => $rows];
    }
}

if (!function_exists('cmsAddressLegacy')) {
    function cmsAddressLegacy(?array $layoutParts): array
    {
        $meta = cmsLayoutMetaFromBulk($layoutParts);
        if ($meta === []) {
            return cmsEmptyResponse();
        }

        $phones = $meta['phones'] ?? [];
        $phone1 = is_array($phones) ? ($phones[0] ?? '') : (string) $phones;
        $phone2 = is_array($phones) ? ($phones[1] ?? '') : '';

        return cmsWrapData([[
            'addressline1' => $meta['address_line1'] ?? '',
            'addressline2' => $meta['address_line2'] ?? '',
            'pincode' => $meta['postal_code'] ?? '',
            'contact1' => $phone1,
            'contact2' => $phone2,
            'email' => $meta['email'] ?? '',
        ]]);
    }
}

if (!function_exists('cmsContactHeaderLegacy')) {
    function cmsContactHeaderLegacy(?array $layoutParts): array
    {
        $meta = cmsLayoutMetaFromBulk($layoutParts);

        return cmsWrapData([[
            'phone' => is_array($meta['phones'] ?? null) ? ($meta['phones'][0] ?? '') : ($meta['phones'] ?? ''),
            'contact1' => is_array($meta['phones'] ?? null) ? ($meta['phones'][0] ?? '') : ($meta['phones'] ?? ''),
            'email' => $meta['email'] ?? '',
        ]]);
    }
}

if (!function_exists('cmsBulkApiCacheGet')) {
    /** @return array<string, mixed>|null */
    function cmsBulkApiCacheGet(string $key): ?array
    {
        $value = $GLOBALS['api_cache'][$key] ?? null;
        if (!is_array($value)) {
            return null;
        }
        if (function_exists('cmsApiResponseIsUsable') && !cmsApiResponseIsUsable($value)) {
            return null;
        }

        return $value;
    }
}
