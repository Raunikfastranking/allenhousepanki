<?php

declare(strict_types=1);

/**
 * CMS page SEO (DPS Amrapali pattern): title, meta_description, meta_keywords from page/blog API data.
 */

if (!function_exists('cmsPageSeoData')) {
    /** @return array<string, mixed> */
    function cmsPageSeoData(?array $page): array
    {
        if (!is_array($page) || !isset($page['data']) || !is_array($page['data'])) {
            return [];
        }

        return $page['data'];
    }
}

if (!function_exists('cmsPageSeoExtract')) {
    /**
     * @param array<string, mixed> $options site_name, fallback_title, canonical_url, og_image_default
     * @return array{document_title:string,meta_title:string,meta_description:string,meta_keywords:string,og_image:string,canonical_url:string}
     */
    function cmsPageSeoExtract(?array $page, array $options = []): array
    {
        $data = cmsPageSeoData($page);

        // SEO fields from CMS page API only (meta_* first; `title` when meta_title is unset in CMS).
        $metaTitle = trim((string) ($data['meta_title'] ?? ''));
        if ($metaTitle === '') {
            $metaTitle = trim((string) ($data['title'] ?? ''));
        }

        $documentTitle = $metaTitle;

        $description = trim((string) ($data['meta_description'] ?? ''));
        $keywords = trim((string) ($data['meta_keywords'] ?? ''));

        $ogImage = trim((string) ($data['meta_image_url'] ?? $data['meta_image'] ?? $data['og_image'] ?? $data['featured_image'] ?? $data['banner_image'] ?? $data['image_url'] ?? ''));
        if ($ogImage !== '' && function_exists('cmsAssetUrl')) {
            $ogImage = cmsAssetUrl($ogImage);
        } elseif ($ogImage !== '' && function_exists('fixCmsAssetUrl')) {
            $ogImage = fixCmsAssetUrl($ogImage);
        }
        if ($ogImage === '') {
            $ogImage = trim((string) ($options['og_image_default'] ?? ''));
        }

        if (function_exists('cmsPrepareCmsPlainText')) {
            $metaTitle = cmsPrepareCmsPlainText($metaTitle);
            $documentTitle = cmsPrepareCmsPlainText($documentTitle);
            $description = cmsPrepareCmsPlainText($description);
            $keywords = cmsPrepareCmsPlainText($keywords);
        }

        return [
            'document_title' => $documentTitle,
            'meta_title' => $metaTitle,
            'meta_description' => $description,
            'meta_keywords' => $keywords,
            'og_image' => $ogImage,
            'canonical_url' => trim((string) ($options['canonical_url'] ?? '')),
        ];
    }
}

if (!function_exists('cmsBlogSeoExtract')) {
    /** @param array<string, mixed> $blog */
    function cmsBlogSeoExtract(array $blog, array $options = []): array
    {
        $firstDetail = is_array($blog['blogdetails'][0] ?? null) ? $blog['blogdetails'][0] : [];
        $wrapped = ['data' => [
            'meta_title' => $blog['meta_title']
                ?? ($firstDetail['meta_title'] ?? null)
                ?? $blog['title']
                ?? ($firstDetail['title'] ?? null)
                ?? $blog['heading']
                ?? ($firstDetail['heading'] ?? null)
                ?? '',
            'meta_description' => $blog['meta_description']
                ?? ($firstDetail['meta_description'] ?? null)
                ?? $blog['description']
                ?? ($firstDetail['description'] ?? null)
                ?? '',
            'meta_keywords' => $blog['meta_keywords']
                ?? ($firstDetail['meta_keywords'] ?? null)
                ?? ($blog['keywords'] ?? null)
                ?? ($firstDetail['keywords'] ?? null)
                ?? '',
            'meta_image' => $blog['image_url']
                ?? $blog['image']
                ?? $blog['featured_image']
                ?? $blog['media_url']
                ?? ($firstDetail['image_url'] ?? null)
                ?? '',
        ]];

        return cmsPageSeoExtract($wrapped, $options);
    }
}

if (!function_exists('cmsPageSeoFromMetaApi')) {
    /** Fallback: /meta-data/{branch} + cms-webpage-map (legacy). */
    function cmsPageSeoFromMetaApi(array $options = []): ?array
    {
        if (!defined('BRANCH_ID') || !function_exists('fetchApiData')) {
            return null;
        }

        $fallbackTitle = trim((string) ($options['fallback_title'] ?? 'Allenhouse'));
        $fallbackDescription = trim((string) ($options['fallback_description'] ?? ''));

        $metaData = fetchApiData('/meta-data/' . BRANCH_ID);
        $metaItems = is_array($metaData['data'] ?? null) ? $metaData['data'] : [];
        $currentFile = basename($_SERVER['PHP_SELF'] ?? '', '.php');
        $pageMapFile = ($options['includes_dir'] ?? '') . '/cms-webpage-map.php';
        $pageMap = is_file($pageMapFile) ? include $pageMapFile : [];
        $currentPageName = is_array($pageMap) ? ($pageMap[$currentFile] ?? null) : null;

        if ($currentPageName === null) {
            return null;
        }

        foreach ($metaItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = (string) ($item['webpage']['name'] ?? '');
            if ($name !== '' && strcasecmp(trim($name), trim((string) $currentPageName)) === 0) {
                return cmsPageSeoExtract([
                    'data' => [
                        'meta_title' => $item['meta_title'] ?? $item['title'] ?? $fallbackTitle,
                        'title' => $item['title'] ?? $fallbackTitle,
                        'meta_description' => $item['meta_description'] ?? $item['description'] ?? $fallbackDescription,
                        'meta_keywords' => $item['meta_keywords'] ?? $item['keywords'] ?? '',
                    ],
                ], $options);
            }
        }

        return null;
    }
}

if (!function_exists('cmsRenderPageSeoHead')) {
    /** Echo <title>, description, keywords (DPS-style). Sets $GLOBALS['cms_seo']. */
    function cmsRenderPageSeoHead(array $seo): void
    {
        $GLOBALS['cms_seo'] = $seo;
        $GLOBALS['cms_seo_rendered'] = true;

        $title = htmlspecialchars((string) ($seo['document_title'] ?? ''), ENT_QUOTES, 'UTF-8');
        echo "<title>{$title}</title>\n";

        $desc = (string) ($seo['meta_description'] ?? '');
        echo '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n";

        $keywords = (string) ($seo['meta_keywords'] ?? '');
        echo '<meta name="keywords" content="' . htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
}

if (!function_exists('cmsRenderOpenGraphTags')) {
    function cmsRenderOpenGraphTags(array $seo, string $siteName = '', string $domain = ''): void
    {
        $url = (string) ($seo['canonical_url'] ?? '');
        $title = (string) ($seo['meta_title'] ?? $seo['document_title'] ?? '');
        $desc = (string) ($seo['meta_description'] ?? '');
        $image = (string) ($seo['og_image'] ?? '');

        if ($url === '' && $title === '' && $desc === '') {
            return;
        }

        if ($url !== '') {
            echo '<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
            echo '<meta property="twitter:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
        echo '<meta property="og:type" content="website">' . "\n";
        if ($title !== '') {
            echo '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n";
            echo '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
        if ($desc !== '') {
            echo '<meta property="og:description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n";
            echo '<meta name="twitter:description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
        if ($image !== '') {
            echo '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '">' . "\n";
            echo '<meta name="twitter:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
        if ($siteName !== '') {
            echo '<meta property="og:site_name" content="' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        if ($domain !== '') {
            echo '<meta property="twitter:domain" content="' . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }
    }
}

if (!function_exists('cmsResolveAndRenderPageSeo')) {
    function cmsResolveAndRenderPageSeo(?array $page, array $options = []): void
    {
        $contactSeoFile = __DIR__ . '/cms-contact-page-seo.php';
        if (!is_file($contactSeoFile)) {
            $contactSeoFile = dirname(__DIR__, 3) . '/shared/includes/cms-contact-page-seo.php';
        }
        if (is_file($contactSeoFile)) {
            require_once $contactSeoFile;
            if (function_exists('cmsApplyContactPageSeoOverride')) {
                $page = cmsApplyContactPageSeoOverride($page);
            }
        }
        cmsRenderPageSeoHead(cmsPageSeoExtract($page, $options));
    }
}

if (!function_exists('cmsLandingSeoRender')) {
    /** Admissions landing pages: title, meta_description, meta_keywords from $landing_data. */
    function cmsLandingSeoRender(array $landingData, array $options = []): void
    {
        cmsRenderPageSeoHead(cmsPageSeoExtract($landingData, $options));
    }
}
