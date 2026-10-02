<?php

declare(strict_types=1);

if (!function_exists('cmsContactLayoutItems')) {
    /** @return list<array<string, mixed>> */
    function cmsContactLayoutItems($layoutData): array
    {
        if (!is_array($layoutData)) {
            return [];
        }
        if (isset($layoutData['data']) && is_array($layoutData['data'])) {
            return $layoutData['data'];
        }
        if (isset($layoutData[0]) && is_array($layoutData[0])) {
            return $layoutData;
        }

        return [];
    }
}

if (!function_exists('cmsContactLayoutPart')) {
    function cmsContactLayoutPart($layoutData, string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }
        foreach (cmsContactLayoutItems($layoutData) as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['slug'] ?? null) === $slug || ($item['layout_slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('cmsContactLayoutMeta')) {
    function cmsContactLayoutMeta(string $footerSlug): array
    {
        global $header_footer_data;

        $layoutData = is_array($header_footer_data ?? null) ? $header_footer_data : [];
        if ($layoutData === []) {
            $cache = $GLOBALS['api_cache'] ?? [];
            $layoutData = is_array($cache['header_footer_data'] ?? null) ? $cache['header_footer_data'] : [];
        }

        $footerPart = cmsContactLayoutPart($layoutData, $footerSlug);
        if (is_array($footerPart['meta_data'] ?? null)) {
            return $footerPart['meta_data'];
        }

        foreach (cmsContactLayoutItems($layoutData) as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['type'] ?? null) === 'footer' || ($item['layout_type'] ?? null) === 'footer') {
                return is_array($item['meta_data'] ?? null) ? $item['meta_data'] : [];
            }
        }

        return [];
    }
}

if (!function_exists('cmsParseFooterAboutAddress')) {
    /** @return array{addressline1: string, addressline2: string} */
    function cmsParseFooterAboutAddress(string $text): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        if ($text === '') {
            return ['addressline1' => '', 'addressline2' => ''];
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), static fn(string $line): bool => $line !== ''));
        if ($lines === []) {
            return ['addressline1' => '', 'addressline2' => ''];
        }

        return [
            'addressline1' => $lines[0],
            'addressline2' => count($lines) > 1 ? implode(', ', array_slice($lines, 1)) : '',
        ];
    }
}

if (!function_exists('cmsResolveSchoolContactDetails')) {
    /**
     * @param array<string, string> $defaults
     * @return array<string, string>
     */
    function cmsResolveSchoolContactDetails(array $defaults, string $footerLayoutSlug = ''): array
    {
        if ($footerLayoutSlug === '' && function_exists('cmsCoreGet')) {
            $footerLayoutSlug = (string) cmsCoreGet('footer_layout_slug', '');
        }

        $footerMeta = $footerLayoutSlug !== '' ? cmsContactLayoutMeta($footerLayoutSlug) : [];
        $footerAbout = cmsParseFooterAboutAddress((string) ($footerMeta['footer_about'] ?? ''));

        $addr = [];
        $contact = [];
        if (function_exists('fetchApiData') && defined('BRANCH_ID')) {
            $address = fetchApiData('all-address-details/' . BRANCH_ID);
            $contacts = fetchApiData('header-contact/' . BRANCH_ID);
            $addr = is_array($address['data'][0] ?? null) ? $address['data'][0] : [];
            $contact = is_array($contacts['data'][0] ?? null) ? $contacts['data'][0] : [];
        }

        $phonesRaw = trim((string) ($footerMeta['footer_phones'] ?? $footerMeta['phones'] ?? ''));
        $phones = $phonesRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $phonesRaw)))) : [];

        $addressline1 = trim((string) ($footerMeta['address_line1'] ?? ''));
        if ($addressline1 === '' && $footerAbout['addressline1'] !== '') {
            $addressline1 = $footerAbout['addressline1'];
        }
        if ($addressline1 === '') {
            $addressline1 = trim((string) ($addr['addressline1'] ?? ''));
        }
        if ($addressline1 === '') {
            $addressline1 = trim((string) ($defaults['addressline1'] ?? ''));
        }

        $addressline2 = trim((string) ($footerMeta['address_line2'] ?? ''));
        if ($addressline2 === '' && $footerAbout['addressline2'] !== '') {
            $addressline2 = $footerAbout['addressline2'];
        }
        if ($addressline2 === '') {
            $addressline2 = trim((string) ($addr['addressline2'] ?? ''));
        }
        if ($addressline2 === '') {
            $addressline2 = trim((string) ($defaults['addressline2'] ?? ''));
        }

        $pincode = trim((string) ($footerMeta['postal_code'] ?? ''));
        if ($pincode === '') {
            $pincode = trim((string) ($addr['pincode'] ?? ''));
        }
        if ($pincode === '') {
            $pincode = trim((string) ($defaults['pincode'] ?? ''));
        }

        $contact1 = $phones[0] ?? '';
        if ($contact1 === '') {
            $contact1 = trim((string) ($contact['contact1'] ?? ''));
        }
        if ($contact1 === '') {
            $contact1 = trim((string) ($defaults['contact1'] ?? ''));
        }

        $contact2 = $phones[1] ?? trim((string) ($contact['contact2'] ?? ($defaults['contact2'] ?? '')));

        $email = trim((string) ($footerMeta['footer_email'] ?? $footerMeta['email'] ?? ''));
        if ($email === '') {
            $email = trim((string) ($addr['email'] ?? $contact['email'] ?? ''));
        }
        if ($email === '') {
            $email = trim((string) ($defaults['email'] ?? ''));
        }

        return [
            'addressline1' => $addressline1,
            'addressline2' => $addressline2,
            'pincode' => $pincode,
            'contact1' => $contact1,
            'contact2' => $contact2,
            'email' => $email,
        ];
    }
}
