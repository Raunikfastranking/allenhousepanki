<?php

declare(strict_types=1);

/**
 * Single gallery / event detail view (?id= from listing pages).
 */

require_once __DIR__ . '/cms-page-content.php';

if (!function_exists('cmsFetchGalleryRecordById')) {
    /**
     * @return array<string, mixed>|null
     */
    function cmsFetchGalleryRecordById(int|string $id): ?array
    {
        $id = trim((string) $id);
        if ($id === '' || !ctype_digit($id)) {
            return null;
        }

        if (function_exists('cms_curl_get_json') && defined('CMS_API_URL')) {
            $response = cms_curl_get_json(rtrim(CMS_API_URL, '/') . '/galleries/' . rawurlencode($id), 20);
            if (is_array($response['data'] ?? null)) {
                return $response['data'];
            }
        }

        if (function_exists('cms_fetch_json_endpoint')) {
            $response = cms_fetch_json_endpoint('galleries/' . $id);
            if (is_array($response['data'] ?? null)) {
                return $response['data'];
            }
        }

        return null;
    }
}

if (!function_exists('cmsGalleryMediaIsPdf')) {
    function cmsGalleryMediaIsPdf(string $url): bool
    {
        $path = strtolower((string) (parse_url($url, PHP_URL_PATH) ?: $url));
        return str_ends_with($path, '.pdf');
    }
}

if (!function_exists('cmsGalleryDetailMedias')) {
    /**
     * @param array<string, mixed> $gallery
     * @return list<array{url: string, is_cover: bool, is_pdf: bool}>
     */
    function cmsGalleryDetailMedias(array $gallery): array
    {
        $rows = [];
        $mediaList = $gallery['media'] ?? $gallery['medias'] ?? [];
        if (!is_array($mediaList)) {
            $mediaList = [];
        }

        foreach ($mediaList as $index => $media) {
            if (!is_array($media)) {
                continue;
            }
            $url = '';
            foreach (['media_url', 'media_file', 'urls', 'url', 'image_url', 'image_link'] as $key) {
                $candidate = trim((string) ($media[$key] ?? ''));
                if ($candidate === '') {
                    continue;
                }
                $url = function_exists('cmsAssetUrl') ? cmsAssetUrl($candidate) : $candidate;
                if ($url !== '') {
                    break;
                }
            }
            if ($url === '') {
                continue;
            }
            $isCover = ($media['pivot']['is_cover'] ?? null) === '1'
                || ($media['pivot']['is_cover'] ?? null) === 1
                || $index === 0;
            $rows[] = ['url' => $url, 'is_cover' => $isCover, 'is_pdf' => cmsGalleryMediaIsPdf($url)];
        }

        if ($rows === []) {
            $content = (string) ($gallery['content'] ?? $gallery['description'] ?? '');
            if ($content !== '' && preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
                foreach ($matches[1] as $index => $src) {
                    $url = function_exists('cmsAssetUrl') ? cmsAssetUrl(html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : $src;
                    if ($url !== '') {
                        $rows[] = ['url' => $url, 'is_cover' => $index === 0, 'is_pdf' => cmsGalleryMediaIsPdf($url)];
                    }
                }
            }
        }

        return $rows;
    }
}

if (!function_exists('cmsGalleryDetailPrepareContent')) {
    function cmsGalleryDetailPrepareContent(string $content): string
    {
        $html = function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($content) : $content;
        if ($html === '') {
            return '';
        }

        // CMS gallery albums often include a boilerplate heading — hide it on the site.
        $html = preg_replace(
            '/<h[1-6]\b[^>]*>\s*Album\s+Description\s*:?\s*<\/h[1-6]>/iu',
            '',
            $html
        ) ?? $html;
        $html = preg_replace(
            '/<(?:p|div|span)\b[^>]*>\s*<strong>\s*Album\s+Description\s*:?\s*<\/strong>\s*<\/(?:p|div|span)>/iu',
            '',
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/<img\b([^>]*?)>/i',
            static function (array $match): string {
                $attrs = $match[1];
                $width = 0;
                $height = 0;
                if (preg_match('/\bwidth=["\']?(\d+)/i', $attrs, $widthMatch)) {
                    $width = (int) $widthMatch[1];
                }
                if (preg_match('/\bheight=["\']?(\d+)/i', $attrs, $heightMatch)) {
                    $height = (int) $heightMatch[1];
                }
                $maxDim = max($width, $height);
                if ($maxDim > 0 && $maxDim <= 160) {
                    if (preg_match('/\bclass=["\']([^"\']*)["\']/i', $attrs, $classMatch)) {
                        $className = trim($classMatch[1] . ' cms-gallery-inline-icon');
                        $attrs = preg_replace(
                            '/\bclass=["\'][^"\']*["\']/i',
                            'class="' . $className . '"',
                            $attrs
                        ) ?? $attrs;
                    } else {
                        $attrs .= ' class="cms-gallery-inline-icon"';
                    }
                }

                return '<img' . $attrs . '>';
            },
            $html
        ) ?? $html;

        return trim($html);
    }
}

if (!function_exists('cmsGalleryRecordTitle')) {
    function cmsGalleryRecordTitle(array $record): string
    {
        foreach (['heading', 'gallery_title', 'achievementtitle', 'title', 'name'] as $key) {
            $value = trim((string) ($record[$key] ?? ''));
            if ($value !== '') {
                return function_exists('cmsPrepareCmsPlainText')
                    ? cmsPrepareCmsPlainText($value)
                    : trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        return '';
    }
}

if (!function_exists('cmsGalleryListingFallback')) {
    /**
     * @param array<string, mixed>|null $galleryMeta
     */
    function cmsGalleryListingFallback(?array $galleryMeta = null): string
    {
        $detailBase = trim((string) ($galleryMeta['detailBase'] ?? ''));
        $map = [
            'event-gallery' => 'Events',
            'photo-gallery' => 'Photo Gallery',
            'video-gallery' => 'Video Gallery',
            'achievement-gallery' => 'Achievements',
            'print-media' => 'Print Media',
            'awards' => 'Awards',
            'x-toppers' => 'Class X Toppers',
            'xii-toppers' => 'Class XII Toppers',
        ];
        if (isset($map[$detailBase])) {
            return $map[$detailBase];
        }

        if (!empty($GLOBALS['cms_page_title_fallback'])) {
            return trim((string) $GLOBALS['cms_page_title_fallback']);
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
        $scriptMap = [
            'event-gallery' => 'Events',
            'detail-view' => 'Events',
            'achievement-gallery' => 'Achievements',
            'print-media' => 'Print Media',
            'print-media-detail' => 'Print Media',
            'magazines' => 'Magazines',
            'gallery' => 'Photo Gallery',
            'photo-gallery' => 'Photo Gallery',
        ];

        return $scriptMap[$script] ?? 'Gallery';
    }
}

if (!function_exists('cmsGalleryListingNavPath')) {
    /**
     * Menu/listing path for gallery detail breadcrumbs (not the detail script name).
     *
     * @param array<string, mixed>|null $galleryMeta
     */
    function cmsGalleryListingNavPath(?array $galleryMeta = null): string
    {
        $detailBase = trim((string) ($galleryMeta['detailBase'] ?? ''));
        $map = [
            'event-gallery' => 'event',
            'detail-view' => 'event',
            'photo-gallery' => 'photo-gallery',
            'gallery' => 'photo-gallery',
            'video-gallery' => 'video-gallery',
            'achievement-gallery' => 'achievements',
            'print-media' => 'print-media',
            'print-media-detail' => 'print-media',
            'magazines' => 'print-media',
            'awards' => 'awards',
            'x-toppers' => 'x-toppers',
            'xii-toppers' => 'xii-toppers',
        ];
        if (isset($map[$detailBase])) {
            return $map[$detailBase];
        }

        $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');

        return $map[$script] ?? $detailBase;
    }
}

if (!function_exists('cmsGalleryListingTitle')) {
    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed>|null $galleryMeta
     */
    function cmsGalleryListingTitle(array $page, ?array $galleryMeta = null): string
    {
        $fallback = cmsGalleryListingFallback($galleryMeta);
        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ($page['data']['slug'] ?? '')));

        if (function_exists('cmsPageDisplayTitle')) {
            $listingTitle = cmsPageDisplayTitle($page, $fallback);
        } elseif (function_exists('cmsPageBannerTitle')) {
            $listingTitle = cmsPageBannerTitle($page);
        } else {
            $listingTitle = cmsPageTitle($page, $fallback);
        }

        if ($listingTitle === '') {
            return $fallback;
        }

        if (function_exists('cmsPageTitleLooksAutoGenerated') && cmsPageTitleLooksAutoGenerated($listingTitle, $slug)) {
            return $fallback;
        }

        return $listingTitle;
    }
}

if (!function_exists('cmsRenderGalleryDetailPage')) {
    /**
     * @param array<string, mixed> $config cms_dynamic_page_config
     * @param array<string, mixed>|null $galleryMeta
     */
    function cmsRenderGalleryDetailPage(
        string $galleryId,
        array $page,
        array $config,
        string $backUrl = '',
        ?array $galleryMeta = null
    ): void {
        $includesDir = (string) ($config['includes_dir'] ?? '');
        $siteName = (string) ($config['site_name'] ?? 'Allenhouse');
        $canonicalHost = rtrim((string) ($config['canonical_host'] ?? ''), '/');
        $record = cmsFetchGalleryRecordById($galleryId);

        if ($record === null) {
            http_response_code(404);
            $notFound = dirname($includesDir) . '/404.php';
            if (is_file($notFound)) {
                include $notFound;
            }
            return;
        }

        if (defined('BRANCH_ID')) {
            $branchId = (int) ($record['branch_id'] ?? 0);
            if ($branchId > 0 && $branchId !== (int) BRANCH_ID) {
                http_response_code(404);
                $notFound = dirname($includesDir) . '/404.php';
                if (is_file($notFound)) {
                    include $notFound;
                }
                return;
            }
        }

        $title = cmsGalleryRecordTitle($record);
        if ($title === '') {
            $title = 'Gallery';
        }
        $content = (string) ($record['content'] ?? $record['description'] ?? '');
        $dateRaw = $record['date'] ?? $record['created_at'] ?? null;
        $dateLabel = '';
        if ($dateRaw) {
            $ts = strtotime((string) $dateRaw);
            if ($ts !== false) {
                $dateLabel = date('j M Y', $ts);
            }
        }

        $medias = cmsGalleryDetailMedias($record);
        $listingTitle = cmsGalleryListingTitle($page, $galleryMeta);
        $bannerTitle = $title;

        if ($backUrl === '') {
            $backUrl = function_exists('site_page_url') ? site_page_url('/') : '/';
        }

        $GLOBALS['cms_page_title'] = $title;
        if (function_exists('cmsBreadcrumbSchemaItemsFromCrumbs') && function_exists('cmsResolveDetailPageBreadcrumbs')) {
            $listingNavPath = cmsGalleryListingNavPath($galleryMeta);
            $listingNavUrl = $backUrl !== '' ? $backUrl : (function_exists('cmsBreadcrumbHref') ? cmsBreadcrumbHref($listingNavPath) : $listingNavPath);
            $GLOBALS['cms_breadcrumb_schema_items'] = cmsBreadcrumbSchemaItemsFromCrumbs(
                cmsResolveDetailPageBreadcrumbs($page, $listingTitle, $listingNavUrl, $title)
            );
        }
        $GLOBALS['cms_og_site_name'] = $siteName;
        $seoOptions = [
            'canonical_url' => $canonicalHost !== '' ? $canonicalHost . ($_SERVER['REQUEST_URI'] ?? '') : '',
            'includes_dir' => $includesDir,
            'og_image_default' => (string) ($config['og_image_default'] ?? ''),
        ];
        $seoPage = ['data' => ['title' => $title, 'meta_title' => $title, 'meta_description' => strip_tags($content)]];
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php cmsResolveAndRenderPageSeo($seoPage, $seoOptions); ?>
    <?php include $includesDir . '/head.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <style>
        .cms-gallery-detail-thumb { width: 100%; max-width: 384px; height: 443px; margin: 0 auto; }
        .cms-gallery-detail-img { width: 100%; height: 443px; object-fit: cover; display: block; }
        .cms-gallery-detail-content .ql-editor img,
        .cms-gallery-detail-content .ql-editor img.cms-gallery-inline-icon {
            width: auto;
            height: auto;
            max-width: none;
            object-fit: unset;
            display: inline;
            vertical-align: middle;
        }
        .cms-gallery-detail-content .ql-editor img.ql-image:not(.cms-gallery-inline-icon) {
            width: 384px;
            max-width: 100%;
            height: 443px;
            object-fit: cover;
            display: block;
        }
        .cms-gallery-pdf-detail {
            width: 100%;
            height: 443px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .cms-gallery-pdf-detail:hover {
            background: #e9ecef;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .cms-gallery-pdf-detail__icon {
            width: 80px;
            height: 100px;
            background: #dc3545;
            color: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        .cms-gallery-pdf-detail__label {
            font-size: 0.95rem;
            color: #495057;
            text-align: center;
            padding: 0 8px;
        }
    </style>
</head>
<body>
<?php include $includesDir . '/header.php'; ?>
<div class="main relative mb-0">
    <?php
    cmsRenderPageBanner(['data' => ['page_heading' => $bannerTitle, 'title' => $bannerTitle]], []);
    ?>
    <?php
    $listingNavPath = cmsGalleryListingNavPath($galleryMeta);
    $listingNavUrl = $backUrl !== '' ? $backUrl : cmsBreadcrumbHref($listingNavPath);
    cmsRenderBreadcrumbsNav(cmsResolveDetailPageBreadcrumbs($page, $listingTitle, $listingNavUrl, $title));
    ?>
    <div class="<?= htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') ?>">
        <p class="mb-4"><a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-blue-main font-semibold hover:underline">&larr; Back to gallery</a></p>
        <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-blue-main mb-2"><?= function_exists('cmsEscPlain') ? cmsEscPlain($title) : htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ($dateLabel !== ''): ?>
            <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($content !== ''): ?>
            <div class="prose max-w-none ql-snow mb-6 cms-gallery-detail-content"><div class="ql-editor"><?= cmsGalleryDetailPrepareContent($content) ?></div></div>
            <?php endif; ?>
            <?php if ($medias !== []): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 cms-gallery-detail-grid">
                <?php foreach ($medias as $media): ?>
                <?php if (!empty($media['is_pdf'])): ?>
                <a href="<?= htmlspecialchars($media['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="cms-gallery-detail-thumb block overflow-hidden rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="cms-gallery-pdf-detail">
                        <div class="cms-gallery-pdf-detail__icon">PDF</div>
                        <div class="cms-gallery-pdf-detail__label">View PDF Document</div>
                    </div>
                </a>
                <?php else: ?>
                <a href="<?= htmlspecialchars($media['url'], ENT_QUOTES, 'UTF-8') ?>" data-fancybox="gallery-detail" class="cms-gallery-detail-thumb block overflow-hidden rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                    <img src="<?= htmlspecialchars($media['url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="cms-gallery-detail-img" width="384" height="443" loading="lazy" />
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </article>
    </div>
</div>
<?php
        include $includesDir . '/footer.php';
        include $includesDir . '/foot.php';
        ?>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>Fancybox.bind('[data-fancybox="gallery-detail"]', { autoFocus: false, placeFocusBack: false });</script>
</body>
</html>
        <?php
    }
}
