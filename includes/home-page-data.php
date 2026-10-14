<?php

declare(strict_types=1);

require_once __DIR__ . '/cms-page-helpers.php';
require_once __DIR__ . '/api-adapters.php';
require_once dirname(__DIR__) . '/shared/includes/cms-home-excellence-link.php';

function cmsPankiHomePage(): array
{
    static $memo = null;
    if ($memo !== null) {
        return $memo;
    }

    if (
        empty($GLOBALS['school_is_index_page'])
        && isset($GLOBALS['api_cache']['home_data'])
        && is_array($GLOBALS['api_cache']['home_data'])
        && !empty($GLOBALS['api_cache']['home_data']['data'])
    ) {
        $memo = $GLOBALS['api_cache']['home_data'];

        return $memo;
    }

    $memo = cmsFetchSchoolHomePage('home-page-panki', 'cmsFetchPankiPage');

    return $memo;
}

function cmsHomeSections(): array
{
    $page = cmsPankiHomePage();
    $sections = $page['data']['sections'] ?? [];

    return is_array($sections) ? $sections : [];
}

function cmsHomeSectionByOrder(int $order): ?array
{
    foreach (cmsHomeSections() as $section) {
        if ((int) ($section['order'] ?? 0) === $order) {
            return $section;
        }
    }

    return null;
}

function cmsHomeSectionItems(?array $section): array
{
    if (!is_array($section)) {
        return [];
    }
    $items = $section['resolved_content']['items'] ?? [];

    return is_array($items) ? $items : [];
}

/**
 * Legacy hero shape: [ ['device'=>'desktop','medias'=>[]], ['device'=>'mobile','medias'=>[]] ]
 *
 * @return list<array<string, mixed>>
 */
function cmsHomeHeroLegacyDevices(): array
{
    $items = cmsHomeSectionItems(cmsHomeSectionByOrder(1));
    $desktop = [];
    $mobile = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $desktopUrl = cmsAssetUrl($item['image_url'] ?? '');
        $mobileUrl = cmsAssetUrl($item['mobile_image_url'] ?? '') ?: $desktopUrl;
        if ($desktopUrl === '') {
            continue;
        }
        $alt = function_exists('cms_media_alt_plain')
            ? cms_media_alt_plain($item, 'Hero banner')
            : trim((string) ($item['image_alt'] ?? $item['media_alt_text'] ?? 'Hero banner'));
        $meta = [
            'urls' => $desktopUrl,
            'image_alt' => $alt,
            'image_alt_text' => $alt,
            'media_alt_text' => $alt,
        ];
        $desktop[] = $meta;
        $mobile[] = array_merge($meta, ['urls' => $mobileUrl]);
    }

    return [
        ['device' => 'desktop', 'medias' => $desktop],
        ['device' => 'mobile', 'medias' => $mobile],
    ];
}

function cmsHomeCtaCardLabel(array $card): string
{
    $heading = trim(strip_tags((string) ($card['heading'] ?? '')));
    if ($heading !== '') {
        return $heading;
    }

    $url = strtolower((string) ($card['redirect_url'] ?? $card['page_link'] ?? $card['heading_link'] ?? ''));
    if (str_contains($url, 'onlineregistration') || str_contains($url, 'online-registration')) {
        return 'Online Registration';
    }
    if (str_contains($url, 'campus-tour')) {
        return 'Campus Tour';
    }
    if (str_contains($url, 'admission-procedure') || str_contains($url, '/admission')) {
        return 'Admission Enquiry';
    }
    if (preg_match('#superhouseerp\.com/?$#i', $url) || str_contains($url, 'payment')) {
        return 'Online Payment';
    }

    $defaults = [
        0 => 'Campus Tour',
        1 => 'Admission Enquiry',
        2 => 'Online Payment',
        3 => 'Online Registration',
    ];
    $order = (int) ($card['sort_order'] ?? -1);
    if (isset($defaults[$order])) {
        return $defaults[$order];
    }

    $name = trim(strip_tags((string) ($card['title'] ?? $card['content'] ?? $card['media_alt_text'] ?? '')));

    return $name !== '' ? $name : 'Learn More';
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeCtaItems(): array
{
    $section = cmsHomeSectionByOrder(2);
    $media = $section['resolved_content']['media'] ?? [];
    if (!is_array($media)) {
        return [];
    }

    usort($media, static function (array $a, array $b): int {
        return ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
    });

    $rows = [];
    foreach ($media as $card) {
        if (!is_array($card)) {
            continue;
        }
        $image = cmsAssetUrl($card['media_url'] ?? $card['media_file'] ?? '');
        if ($image === '') {
            continue;
        }
        $rows[] = [
            'name' => cmsHomeCtaCardLabel($card),
            'url' => (string) ($card['redirect_url'] ?? $card['page_link'] ?? $card['heading_link'] ?? '#'),
            'media' => ['urls' => $image],
        ];
    }

    return $rows;
}

/**
 * Plain text for fields shown inside a single <p> (no visible HTML tags).
 */
function cmsPlainTextFromHtml(string $html): string
{
    if ($html === '') {
        return '';
    }

    return cmsPrepareCmsPlainText($html);
}

/**
 * Parse excellence card copy from CMS HTML (matches Allenhouse Agra index).
 *
 * @return array{firstLine: string, secondLine: string, cleanDesc: string, fullTitle: string}
 */
function cmsParseExcellenceDescription(string $description): array
{
    preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $description, $titleMatch);
    $fullTitle = strip_tags($titleMatch[1] ?? '');
    $titleParts = preg_split('/<br\s*\/?>/i', $fullTitle, 2);
    $firstLine = trim($titleParts[0] ?? '');
    $secondLine = trim(strip_tags($titleParts[1] ?? ''));
    $cleanDesc = preg_replace('/<h2[^>]*>.*?<\/h2>/is', '', $description);
    $cleanDesc = strip_tags($cleanDesc, '<p><br><strong><em>');
    $cleanDesc = trim($cleanDesc);

    return [
        'firstLine' => $firstLine,
        'secondLine' => $secondLine,
        'cleanDesc' => $cleanDesc,
        'fullTitle' => $fullTitle,
    ];
}

/**
 * @return array{firstLine: string, secondLine: string, cleanDesc: string, fullTitle: string}
 */
function cmsParseExcellenceItem(array $item): array
{
    $description = (string) ($item['description'] ?? '');
    if (stripos($description, '<h2') !== false) {
        return cmsParseExcellenceDescription($description);
    }

    $fullTitle = trim(strip_tags((string) ($item['title'] ?? '')));
    if (preg_match('/^(.+?)\s+In\s+(.+)$/iu', $fullTitle, $match)) {
        $firstLine = trim($match[1]);
        $secondLine = 'In ' . trim($match[2]);
    } else {
        $parts = preg_split('/\s+/', $fullTitle, 2);
        $firstLine = trim($parts[0] ?? '');
        $secondLine = trim($parts[1] ?? '');
    }

    $cleanDesc = strip_tags($description, '<p><br><strong><em>');
    $cleanDesc = trim($cleanDesc);

    return [
        'firstLine' => $firstLine,
        'secondLine' => $secondLine,
        'cleanDesc' => $cleanDesc,
        'fullTitle' => $fullTitle,
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeExcellenceItems(): array
{
    $rows = [];
    foreach (cmsHomeSectionItems(cmsHomeSectionByOrder(3)) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $parsed = cmsParseExcellenceItem($item);
        $rows[] = array_merge($parsed, [
            'image_url' => cmsAssetUrl($item['image_url'] ?? ''),
            'link_url' => cmsHomeExcellenceLinkUrl($item['link_url'] ?? '#', (string) ($item['title'] ?? '')),
            'media' => ['urls' => cmsAssetUrl($item['image_url'] ?? '')],
        ]);
    }

    return $rows;
}

function cmsHomeApproachDescription(): string
{
    $section = cmsHomeSectionByOrder(4);
    $content = (string) ($section['content'] ?? '');
    if ($content === '' && is_array($section)) {
        $content = (string) ($section['resolved_content']['content'] ?? '');
    }

    return cmsPlainTextFromHtml($content);
}

function cmsHomeApproachHeading(): string
{
    $section = cmsHomeSectionByOrder(4);
    if (!is_array($section)) {
        return '';
    }

    $heading = trim((string) ($section['content_heading'] ?? $section['resolved_content']['content_heading'] ?? ''));
    if ($heading !== '') {
        return function_exists('cmsPlainTextFromHtml') ? cmsPlainTextFromHtml($heading) : trim(strip_tags($heading));
    }

    $title = trim((string) ($section['title'] ?? $section['resolved_content']['title'] ?? ''));

    return preg_replace('/\s+Section\s*$/i', '', $title);
}

function cmsHomeLifeAtAllenhouseHeading(): string
{
    $section = cmsHomeSectionByOrder(10);
    if (!is_array($section)) {
        return '';
    }

    $contentHeading = trim((string) ($section['content_heading'] ?? $section['resolved_content']['content_heading'] ?? ''));
    if ($contentHeading !== '') {
        if (preg_match('/<h[1-6]\b/i', $contentHeading)) {
            return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($contentHeading) : $contentHeading;
        }

        $plain = function_exists('cmsPlainTextFromHtml') ? cmsPlainTextFromHtml($contentHeading) : trim(strip_tags($contentHeading));

        return '<h2 class="sm:text-[30px] text-[28px] font-[700] leading-10 text-blue-main relative">' . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</h2>';
    }

    $heading = function_exists('cmsSectionInnerHtml') ? cmsSectionInnerHtml($section) : '';
    if (trim($heading) !== '') {
        return $heading;
    }

    $title = trim((string) ($section['title'] ?? $section['resolved_content']['title'] ?? ''));
    $title = preg_replace('/\s+Section\s*$/i', '', $title);
    if ($title === '') {
        return '';
    }

    return '<h2 class="sm:text-[30px] text-[28px] font-[700] leading-10 text-blue-main relative">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeLifeAtAllenhouseItems(): array
{
    $section = cmsHomeSectionByOrder(10);
    if (!is_array($section)) {
        return [];
    }

    $items = [];
    if (is_array($section['resolved_content']['media'] ?? null)) {
        $items = $section['resolved_content']['media'];
    } elseif (is_array($section['resolved_content']['items'] ?? null)) {
        $items = $section['resolved_content']['items'];
    } elseif (!empty($section['resolved_content']['gallery_json_data'])) {
        $decoded = json_decode((string) $section['resolved_content']['gallery_json_data'], true);
        if (is_array($decoded)) {
            $items = $decoded;
        }
    } elseif (!empty($section['gallery_json_data'])) {
        $decoded = json_decode((string) $section['gallery_json_data'], true);
        if (is_array($decoded)) {
            $items = $decoded;
        }
    }

    $rows = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $imageUrl = cmsAssetUrl((string) ($item['media_url'] ?? $item['media_file'] ?? $item['image_url'] ?? $item['urls'] ?? ''));
        if ($imageUrl === '') {
            continue;
        }

        $alt = function_exists('cms_media_alt_plain')
            ? cms_media_alt_plain($item, 'Life at Allenhouse')
            : trim((string) ($item['media_alt_text'] ?? $item['image_alt'] ?? $item['alt_text'] ?? $item['alt'] ?? $item['title'] ?? $item['heading'] ?? 'Life at Allenhouse'));
        $rows[] = [
            'url' => $imageUrl,
            'alt' => $alt !== '' ? $alt : 'Life at Allenhouse',
        ];
    }

    return $rows;
}

/**
 * @return array{years: string, campus: string, student: string, staff: string, alumni: string}
 */
function cmsHomeLegacyRow(): array
{
    if (!function_exists('cmsLegacyStatisticsRow')) {
        require_once __DIR__ . '/api-adapters.php';
    }

    $response = null;
    if (!function_exists('cmsBulkStatisticsResponse')) {
        require_once dirname(__DIR__) . '/shared/includes/cms-bulk-reuse.php';
    }
    $response = cmsBulkStatisticsResponse();

    if ($response === null && defined('CMS_API_URL') && defined('BRANCH_ID') && function_exists('cms_curl_get_json')) {
        $statsUrl = rtrim(CMS_API_URL, '/') . '/statistics/branch/' . BRANCH_ID;
        $response = cms_curl_get_json($statsUrl, 4);
    }

    return cmsLegacyStatisticsRow(is_array($response) ? $response : null);
}

function cmsPhilosophyItemIsPlaceholder(array $item): bool
{
    $description = trim(strip_tags((string) ($item['description'] ?? '')));
    if ($description !== '') {
        return false;
    }

    $title = trim(strip_tags((string) ($item['title'] ?? '')));

    return $title === '' || preg_match('/^\d+$/', $title) === 1;
}

/**
 * @return array<string, true>
 */
function cmsHomeExcellenceTitleKeys(): array
{
    static $keys = null;
    if (is_array($keys)) {
        return $keys;
    }

    $keys = [];
    foreach (cmsHomeSectionItems(cmsHomeSectionByOrder(3)) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = strtolower(trim(strip_tags((string) ($item['title'] ?? ''))));
        if ($title !== '') {
            $keys[$title] = true;
        }
    }

    return $keys;
}

function cmsPhilosophyItemDuplicatesExcellence(array $item): bool
{
    $title = strtolower(trim(strip_tags((string) ($item['title'] ?? ''))));
    if ($title === '') {
        return false;
    }

    return isset(cmsHomeExcellenceTitleKeys()[$title]);
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomePhilosophyItems(): array
{
    $rows = [];
    $section = cmsHomeSectionByOrder(6);
    
    // Get carousel items from section 6
    if (is_array($section)) {
        $items = $section['resolved_content']['items'] ?? $section['items'] ?? [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $image = cmsAssetUrl($item['image_url'] ?? '');
            if ($image === '') {
                continue;
            }

            $title = cmsPlainText((string) ($item['title'] ?? ''));
            $description = cmsPlainText((string) ($item['description'] ?? ''));
            
            if ($title === '' && $description === '') {
                $title = cmsPlainText((string) ($item['subtitle'] ?? ''));
            }

            if ($image === '') {
                continue;
            }

            $rows[] = [
                'title' => $title,
                'description' => $description,
                'media' => ['urls' => $image],
            ];
        }
    }

    return $rows;
}

/**
 * Future Ready Skills cards (home section order 7 — card layout media).
 *
 * @return list<array{url: string, image: string, alt: string}>
 */
function cmsHomeFutureReadySkillsItems(): array
{
    $section = cmsHomeSectionByOrder(7);
    $media = $section['resolved_content']['media'] ?? [];
    if (!is_array($media)) {
        return [];
    }

    $rows = [];
    foreach ($media as $card) {
        if (!is_array($card)) {
            continue;
        }
        $image = cmsAssetUrl($card['media_url'] ?? $card['media_file'] ?? '');
        if ($image === '') {
            continue;
        }
        $alt = function_exists('cms_media_alt_plain')
            ? cms_media_alt_plain($card, '')
            : trim((string) ($card['media_alt_text'] ?? $card['image_alt'] ?? $card['heading'] ?? ''));
        if ($alt === '') {
            $link = (string) ($card['redirect_url'] ?? $card['page_link'] ?? $card['heading_link'] ?? '');
            $path = trim((string) (parse_url($link, PHP_URL_PATH) ?: ''), '/');
            $alt = $path !== '' ? ucwords(str_replace(['-', '_'], ' ', basename($path))) : 'Future Ready Skills';
        }
        $url = cmsMenuUrl((string) ($card['redirect_url'] ?? $card['page_link'] ?? $card['heading_link'] ?? '#'));
        $rows[] = [
            'url' => $url,
            'image' => $image,
            'alt' => $alt,
        ];
    }

    return $rows;
}

function cmsHomeFutureReadySkillsHeadingHtml(): string
{
    $section = cmsHomeSectionByOrder(7);
    $heading = trim((string) ($section['content_heading'] ?? ''));
    if ($heading === '') {
        return '<h2 class="text-[28px] sm:text-[30px] font-[700] leading-10 text-blue-main relative text-center">Future Ready Skills</h2>';
    }

    return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($heading) : $heading;
}

function cmsHomeYoutubeEmbedUrl(string $videoUrl): string
{
    if (!function_exists('cmsYoutubeEmbedUrl')) {
        require_once dirname(__DIR__) . '/shared/includes/youtube-embed.php';
    }

    $videoId = cmsYoutubeExtractVideoId($videoUrl);
    if ($videoId === '') {
        return '';
    }

    return cmsYoutubeEmbedUrl($videoId, [
        'autoplay' => '1',
        'mute' => '1',
        'loop' => '1',
        'playlist' => $videoId,
    ]);
}

function cmsHomeVideoUrl(): string
{
    $section = cmsHomeSectionByOrder(8);
    $url = (string) ($section['resolved_content']['video_url'] ?? '');
    if ($url !== '') {
        return $url;
    }
    foreach (($section['resolved_content']['media'] ?? []) as $media) {
        if (!is_array($media)) {
            continue;
        }
        $candidate = (string) ($media['media_url'] ?? '');
        if ($candidate !== '') {
            return $candidate;
        }
    }

    return '';
}

/**
 * @return array{address: string, contact: string}
 */
function cmsCampusAddressPhone(string $description): array
{
    $address = '';
    $contact = '';

    if ($description === '') {
        return ['address' => $address, 'contact' => $contact];
    }

    if (preg_match('/href=["\']tel:([^"\']+)/i', $description, $phoneMatch)) {
        $contact = trim(html_entity_decode($phoneMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    if (preg_match('/<p[^>]*class="[^"]*text-gray-500[^"]*"[^>]*>(.*?)<\/p>/is', $description, $addressMatch)) {
        $address = trim(strip_tags(html_entity_decode(str_replace('&nbsp;', ' ', $addressMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    } elseif (preg_match('/<p[^>]*>(.*?)<\/p>/is', $description, $addressMatch)) {
        $address = trim(strip_tags(html_entity_decode(str_replace('&nbsp;', ' ', $addressMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    return ['address' => $address, 'contact' => $contact];
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeCampusItems(): array
{
    $rows = [];
    foreach (cmsHomeSectionItems(cmsHomeSectionByOrder(9)) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = trim(strip_tags((string) ($item['title'] ?? '')));
        if ($name === '' || $name === '1') {
            $name = 'Allenhouse Campus';
        }
        $meta = cmsCampusAddressPhone((string) ($item['description'] ?? ''));
        $rows[] = [
            'id' => $item['id'] ?? null,
            'name' => $name,
            'addressline1' => $meta['address'],
            'contact' => $meta['contact'],
            'media' => ['urls' => cmsAssetUrl($item['image_url'] ?? '')],
            'weburl' => (string) ($item['link_url'] ?? '#'),
        ];
    }

    return $rows;
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeGalleryImages(): array
{
    $rows = [];
    foreach (cmsHomeSectionItems(cmsHomeSectionByOrder(12)) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $url = cmsAssetUrl($item['image_url'] ?? '');
        if ($url === '') {
            continue;
        }
        $rows[] = [
            'media' => ['urls' => $url],
            'title' => (string) ($item['title'] ?? ''),
        ];
    }

    return $rows;
}

/**
 * @return list<array<string, mixed>>
 */
function cmsHomeTestimonialItemsFromPage(?array $page): array
{
    if (!is_array($page)) {
        return [];
    }

    $section = null;
    $sections = $page['data']['sections'] ?? [];
    if (is_array($sections)) {
        foreach ($sections as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            if ((int) ($candidate['order'] ?? 0) === 11) {
                $section = $candidate;
                break;
            }
        }
        if ($section === null) {
            foreach ($sections as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                $title = strtolower((string) ($candidate['title'] ?? ''));
                if (str_contains($title, 'testimonial')) {
                    $section = $candidate;
                    break;
                }
            }
        }
    }

    if (!is_array($section)) {
        return [];
    }

    if (function_exists('cmsTestimonialItemsLegacy')) {
        $legacy = cmsTestimonialItemsLegacy($section);
        $rows = is_array($legacy['data'] ?? null) ? $legacy['data'] : [];
        if ($rows !== []) {
            return $rows;
        }
    }

    $rows = [];
    foreach (cmsHomeSectionItems($section) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $rows[] = [
            'heading' => cmsPlainText((string) ($item['title'] ?? '')),
            'description' => (string) ($item['description'] ?? ''),
            'media' => ['urls' => cmsAssetUrl($item['image_url'] ?? '')],
        ];
    }

    return $rows;
}

function cmsHomeTestimonialItems(): array
{
    $rows = cmsHomeTestimonialItemsFromPage(cmsPankiHomePage());
    if ($rows !== []) {
        return $rows;
    }

    if (function_exists('cmsFetchPageBySlug')) {
        $fresh = cmsFetchPageBySlug('home-page-panki');
        $rows = cmsHomeTestimonialItemsFromPage($fresh);
        if ($rows !== []) {
            return $rows;
        }
    }

    return [];
}

if (!function_exists('cmsPankiPublishedFlyer')) {
    /**
     * @return array<string, mixed>|null
     */
    function cmsPankiPublishedFlyer(): ?array
    {
        if (!function_exists('cmsPankiFlyersResponse')) {
            require_once __DIR__ . '/cms-bootstrap.php';
        }

        $response = cmsPankiFlyersResponse();
        if (!is_array($response)) {
            return null;
        }

        $items = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (function_exists('cmsPankiCmsItemIsVisible') && !cmsPankiCmsItemIsVisible($item)) {
                continue;
            }

            $image = cmsAssetUrl($item['image_url'] ?? $item['image'] ?? '');
            if ($image === '') {
                continue;
            }

            $item['image_url'] = $image;

            return $item;
        }

        return null;
    }
}

/**
 * @return array{data: list<array<string, mixed>>}
 */
function cmsHomePopupLegacy(): array
{
    $flyer = cmsPankiPublishedFlyer();
    if ($flyer === null) {
        return ['data' => []];
    }

    $image = (string) ($flyer['image_url'] ?? '');
    $link = trim((string) ($flyer['link_url'] ?? ''));
    if ($link === '' || strtolower($link) === 'null') {
        $link = '#';
    }

    return [
        'data' => [[
            'image' => $image,
            'url' => $link,
            'text' => (string) ($flyer['title'] ?? ''),
        ]],
    ];
}
