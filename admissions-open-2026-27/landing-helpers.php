<?php

require_once dirname(__DIR__) . '/includes/api-adapters.php';

if (!function_exists('cmsLayoutPart')) {
    function cmsLayoutPart(?array $layoutParts, string $slug): ?array
    {
        if (!is_array($layoutParts)) {
            return null;
        }

        foreach ($layoutParts as $part) {
            if (is_array($part) && ($part['slug'] ?? '') === $slug) {
                return $part;
            }
        }

        return null;
    }
}

if (!function_exists('cmsLayoutMeta')) {
    function cmsLayoutMeta(?array $layoutParts, string $slug = 'header-landing-page-panki'): array
    {
        $part = cmsLayoutPart($layoutParts, $slug);
        $meta = $part['meta_data'] ?? [];

        return is_array($meta) ? $meta : [];
    }
}

function landingSections(?array $page): array
{
    if (!is_array($page) || !is_array($page['data']['sections'] ?? null)) {
        return [];
    }

    return $page['data']['sections'];
}

function landingSection(?array $page, int $index): ?array
{
    $sections = landingSections($page);

    return isset($sections[$index]) && is_array($sections[$index]) ? $sections[$index] : null;
}

function landingCardDetails(?array $section): array
{
    if (!is_array($section)) {
        return [];
    }

    $media = $section['resolved_content']['media'] ?? [];
    if (!is_array($media)) {
        return [];
    }

    $details = [];
    foreach ($media as $item) {
        if (!is_array($item)) {
            continue;
        }
        $url = trim((string) ($item['media_url'] ?? $item['media_file'] ?? ''));
        $heading = trim((string) ($item['heading'] ?? ''));
        $content = trim((string) ($item['content'] ?? ''));
        $details[] = [
            'media' => ['urls' => $url],
            'description' => $heading !== '' ? (string) ($item['heading'] ?? '') : $content,
        ];
    }

    return $details;
}

function landingCarouselDetails(?array $section): array
{
    if (!is_array($section)) {
        return [];
    }

    $items = $section['resolved_content']['items'] ?? [];
    if (!is_array($items)) {
        return [];
    }

    $details = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $details[] = [
            'media' => ['urls' => (string) ($item['image_url'] ?? '')],
            'description' => (string) ($item['description'] ?? ''),
            'mediaid' => (string) ($item['title'] ?? ''),
        ];
    }

    return $details;
}

function landingHeroTextLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    $content = (string) ($section['content'] ?? '');
    $labelOne = '';
    $labelTwo = '';

    if (preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches)) {
        $labelOne = cmsPlainText($matches[1]);
    }
    if (preg_match('/Class\s+PG.*?Cambridge/is', $content, $matches)) {
        $labelTwo = trim(html_entity_decode(strip_tags($matches[0])));
    } elseif (preg_match('/Classes?\s+.*?Curriculum/is', $content, $matches)) {
        $labelTwo = trim(html_entity_decode(strip_tags($matches[0])));
    } elseif (preg_match('/Class\s+PG.*?Scholarships/is', $content, $matches)) {
        $labelTwo = trim($matches[0]);
    } elseif (preg_match('/<div[^>]*flex[^>]*>(.*?)<\/div>/is', $content, $matches)) {
        $labelTwo = cmsPlainText($matches[1]);
    }

    return cmsWrapData([[
        'labelonetext' => $labelOne,
        'labeltwotext' => $labelTwo,
    ]]);
}

function landingAdvanceLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'title' => cmsPlainText((string) ($section['content_heading'] ?? $section['title'] ?? '')),
        'details' => landingCardDetails($section),
    ]]);
}

function landingAboutThumbnailUrl(?array $videoSection): string
{
    $staticImage = 'https://allenhousepanki.com/assets/images/achievement-bg.webp';
    if (!is_array($videoSection)) {
        return $staticImage;
    }

    $thumb = trim((string) ($videoSection['resolved_content']['thumbnail_url'] ?? ''));
    if ($thumb === '' || stripos($thumb, 'placeholder-video') !== false) {
        return $staticImage;
    }

    return cmsAssetUrl($thumb);
}

function landingAboutLegacy(?array $videoSection, ?array $textSection): array
{
    $videoUrl = '';
    if (is_array($videoSection)) {
        $media = $videoSection['resolved_content']['media'][0] ?? null;
        if (is_array($media)) {
            $videoUrl = (string) ($media['media_url'] ?? $media['media_file'] ?? '');
        }
    }

    $description = is_array($textSection) ? (string) ($textSection['content'] ?? '') : '';

    return cmsWrapData([[
        'title' => 'About Us',
        'desctiption' => $description,
        'url' => $videoUrl,
    ]]);
}

function landingShapingLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'title' => cmsPlainText((string) ($section['content_heading'] ?? $section['title'] ?? '')),
        'description' => (string) ($section['content'] ?? ''),
        'details' => landingCarouselDetails($section),
    ]]);
}

function landingWhyLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'heading' => (string) ($section['content_heading'] ?? ''),
        'intro' => (string) ($section['content'] ?? ''),
        'details' => landingCardDetails($section),
    ]]);
}

function landingAchievementLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'title' => cmsPlainText((string) ($section['content_heading'] ?? $section['title'] ?? '')),
        'description' => (string) ($section['content'] ?? ''),
        'details' => landingCardDetails($section),
    ]]);
}

function landingCenturyLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    $details = [];
    foreach (landingCardDetails($section) as $item) {
        $details[] = [
            'media' => $item['media'],
            'description' => cmsPlainText((string) ($item['description'] ?? '')),
        ];
    }

    return cmsWrapData([[
        'description' => (string) ($section['content_heading'] ?? ''),
        'details' => $details,
    ]]);
}

function landingTestimonialCardLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    $details = [];
    foreach ($section['resolved_content']['media'] ?? [] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $details[] = [
            'media' => ['urls' => (string) ($item['media_url'] ?? $item['media_file'] ?? '')],
            'title' => cmsPlainText((string) ($item['heading'] ?? '')),
            'description' => (string) ($item['content'] ?? ''),
        ];
    }

    return cmsWrapData([[
        'heading' => (string) ($section['content_heading'] ?? ''),
        'details' => $details,
    ]]);
}

function landingLegacyIconSvg(int $index): string
{
    $icons = [
        '<svg xmlns="http://www.w3.org/2000/svg" class="lg:w-[45px] w-[30px]" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M14 18V14a10 10 0 0 1 20 0v4" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><rect x="8" y="18" width="32" height="22" rx="3" stroke="#fff" stroke-width="2.5"/><path d="M8 26h32" stroke="#fff" stroke-width="2.5"/><circle cx="34" cy="14" r="7" stroke="#fff" stroke-width="2.5"/><path d="M34 11v3l2 2" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" class="lg:w-[45px] w-[30px]" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M24 6L6 18v24h36V18L24 6Z" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/><path d="M20 42V28h8v14" stroke="#fff" stroke-width="2.5"/><path d="M6 42h36" stroke="#fff" stroke-width="2.5"/><path d="M24 6v6" stroke="#fff" stroke-width="2.5"/><path d="M21 9h6" stroke="#fff" stroke-width="2.5"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" class="lg:w-[45px] w-[30px]" viewBox="0 0 48 48" fill="none" aria-hidden="true"><circle cx="16" cy="14" r="5" stroke="#fff" stroke-width="2.5"/><path d="M6 38c0-6 4.5-10 10-10s10 4 10 10" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><circle cx="32" cy="16" r="4" stroke="#fff" stroke-width="2.5"/><path d="M24 38c0-5 3.5-8 8-8 2.2 0 4.2.8 5.6 2.2" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><circle cx="38" cy="18" r="3.5" stroke="#fff" stroke-width="2.5"/><path d="M33 38c.5-3.5 2.8-6 6-6" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" class="lg:w-[45px] w-[30px]" viewBox="0 0 48 48" fill="none" aria-hidden="true"><rect x="6" y="10" width="28" height="20" rx="2" stroke="#fff" stroke-width="2.5"/><path d="M14 38h12" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M20 30v8" stroke="#fff" stroke-width="2.5"/><circle cx="34" cy="16" r="5" stroke="#fff" stroke-width="2.5"/><path d="M28 34c0-5 2.7-8 6-8s6 3 6 8" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>',
        '<svg xmlns="http://www.w3.org/2000/svg" class="lg:w-[45px] w-[30px]" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M24 8 6 18l18 10 18-10L24 8Z" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/><path d="M12 22v10c0 4 5.4 8 12 8s12-4 12-8V22" stroke="#fff" stroke-width="2.5"/><path d="M40 18v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>',
    ];

    return $icons[$index] ?? $icons[0];
}

function landingLegacyStats(?array $section, ?array $statsResponse = null): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    $details = [];
    $statisticId = (string) ($section['content_item_id'] ?? $section['resolved_content']['id'] ?? '');

    if (is_array($statsResponse) && $statisticId !== '') {
        foreach ($statsResponse['data'] ?? [] as $group) {
            if (!is_array($group) || (string) ($group['id'] ?? '') !== $statisticId) {
                continue;
            }
            foreach ($group['items'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $count = trim((string) ($item['count'] ?? '') . (string) ($item['unit'] ?? ''));
                $heading = (string) ($item['heading'] ?? '');
                $details[] = [
                    'media' => ['urls' => ''],
                    'description' => '<h2 class="mt-2 text-white text-[18px] font-[700]">'
                        . htmlspecialchars($count, ENT_QUOTES, 'UTF-8')
                        . '</h2><span class="text-[13px] text-white">'
                        . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8')
                        . '</span>',
                ];
            }
            break;
        }
    }

    return cmsWrapData([[
        'title' => (string) ($section['content_heading'] ?? $section['title'] ?? ''),
        'details' => $details,
    ]]);
}

function landingHeaderMeta(?array $layoutParts, string $slug = 'header-landing-page-panki'): array
{
    $meta = cmsLayoutMeta($layoutParts, $slug);
    if ($meta === []) {
        $meta = cmsLayoutMeta($layoutParts, 'header-aps-panki');
    }

    return [
        'logo_url' => cmsAssetUrl((string) ($meta['logo_url'] ?? '')),
        'primary_cta_text' => trim((string) ($meta['primary_cta_text'] ?? 'Download E-Brochure')),
        'primary_cta_url' => trim((string) ($meta['primary_cta_url'] ?? '')),
        'secondary_cta_text' => trim((string) ($meta['secondary_cta_text'] ?? '')),
        'secondary_cta_url' => trim((string) ($meta['secondary_cta_url'] ?? '')),
    ];
}

function landingBrochureLegacy(array $headerMeta): array
{
    $url = $headerMeta['primary_cta_url'] ?? '';
    if ($url === '') {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'url' => $url,
        'label' => $headerMeta['primary_cta_text'] ?? 'Download E-Brochure',
    ]]);
}

function landingLifeLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'title' => cmsPlainText((string) ($section['content_heading'] ?? $section['title'] ?? '')),
        'details' => landingCarouselDetails($section),
    ]]);
}

function landingLifeCardLegacy(?array $section): array
{
    if (!is_array($section)) {
        return cmsEmptyResponse();
    }

    $details = [];
    foreach ($section['resolved_content']['media'] ?? [] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $url = trim((string) ($item['media_url'] ?? $item['media_file'] ?? ''));
        if ($url === '') {
            continue;
        }
        $details[] = [
            'media' => ['urls' => $url],
            'mediaid' => '',
        ];
    }

    return cmsWrapData([[
        'title' => '',
        'details' => $details,
    ]]);
}

function landingLifeStaticLegacy(): array
{
    $urls = [
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/v8rrDYgA475246RAWwPfyh1EigzEau1hhNuWr7d9.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/mPWce3Ljg4hIMKtL5U9PNSWzmHM6IZ7UEP7zk2pc.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/Cxbni0zQncrNFkomWAEqCMu7klgC7d1HgD5Vjb8u.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/lwNTAWAlvSKkgLHW0S9xFm7zjHyRoBnRY9zxsiWD.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/fQV4FAXakxz3NgID7qJHHCn6Hol4eAO8R7nfTTf3.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/TLjxHSGoDjY76A8CXQpdCXSjK38wotfzatlXEGBW.png',
        'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/EA6kPWO5qIWXvYGyp6jqCVGoRdbFIcNsQCLlwvfI.png',
    ];

    $details = [];
    foreach ($urls as $url) {
        $details[] = [
            'media' => ['urls' => $url],
            'mediaid' => '',
        ];
    }

    return cmsWrapData([[
        'title' => '',
        'details' => $details,
    ]]);
}

function landingAddressLegacy(?array $layoutParts): array
{
    $meta = cmsLayoutMeta($layoutParts, 'footer-landing-page-panki');
    if ($meta === []) {
        $meta = cmsLayoutMeta($layoutParts, 'footer-aps-panki');
    }

    $phonesRaw = (string) ($meta['footer_phones'] ?? $meta['phones'] ?? '');
    $phones = array_map('trim', explode(',', $phonesRaw));

    return cmsWrapData([[
        'addressline1' => (string) ($meta['footer_about'] ?? $meta['address_line1'] ?? ''),
        'addressline2' => (string) ($meta['address_line2'] ?? ''),
        'pincode' => (string) ($meta['postal_code'] ?? ''),
        'contact1' => $phones[0] ?? '',
        'contact2' => $phones[1] ?? '',
        'email' => (string) ($meta['footer_email'] ?? $meta['email'] ?? ''),
    ]]);
}

function landingSocialLegacy(?array $layoutParts): array
{
    $meta = cmsLayoutMeta($layoutParts, 'footer-landing-page-panki');
    if ($meta === []) {
        $meta = cmsLayoutMeta($layoutParts, 'footer-aps-panki');
    }

    $rows = [];
    $map = [
        ['footer_facebook', 'Facebook'],
        ['footer_youtube', 'YouTube'],
        ['footer_linkedin', 'Instagram'],
        ['footer_instagram', 'Instagram'],
        ['facebook_url', 'Facebook'],
        ['youtube_url', 'YouTube'],
        ['instagram_url', 'Instagram'],
    ];

    $seen = [];
    foreach ($map as [$key, $name]) {
        $url = trim((string) ($meta[$key] ?? ''));
        if ($url === '' || isset($seen[$url])) {
            continue;
        }
        $seen[$url] = true;
        $rows[] = ['name' => $name, 'url' => $url];
    }

    return cmsWrapData($rows);
}

function landingWhatsappLegacy(?array $layoutParts): array
{
    $meta = cmsLayoutMeta($layoutParts, 'footer-landing-page-panki');
    if (empty($meta['whatsapp_number'])) {
        $meta = cmsLayoutMeta($layoutParts, 'footer-aps-panki');
    }
    if (empty($meta['whatsapp_number'])) {
        return cmsEmptyResponse();
    }

    return cmsWrapData([[
        'number' => $meta['whatsapp_number'],
        'text' => $meta['whatsapp_cta_text'] ?? 'WhatsApp',
    ]]);
}
