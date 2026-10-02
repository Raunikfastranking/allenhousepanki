<?php

declare(strict_types=1);

/**
 * Shared full-page CMS renderer (Rooma gold standard).
 * Each school's includes/cms-dynamic-page.php loads APIs then sets
 * $GLOBALS['cms_dynamic_page_config'] and requires this file.
 */

require_once __DIR__ . '/cms-page-content.php';
require_once __DIR__ . '/cms-page-seo.php';
require_once __DIR__ . '/cms-gallery-detail.php';

$config = is_array($GLOBALS['cms_dynamic_page_config'] ?? null) ? $GLOBALS['cms_dynamic_page_config'] : [];

if (!function_exists('cmsDynamicPageNormalizeTitles')) {
    function cmsDynamicPageNormalizeTitles(array $page, string $slug, array $config): array
    {
        $normalizeFn = trim((string) ($config['normalize_fn'] ?? ''));
        if ($normalizeFn === '' || !function_exists($normalizeFn)) {
            $fetchFn = trim((string) ($config['fetch_fn'] ?? ''));
            if ($fetchFn !== '' && preg_match('/^cmsFetch([A-Za-z]+)Page$/', $fetchFn, $matches)) {
                $candidate = 'cms' . $matches[1] . 'NormalizePageTitles';
                if (function_exists($candidate)) {
                    $normalizeFn = $candidate;
                }
            }
        }

        if ($normalizeFn === '' || !function_exists($normalizeFn)) {
            return $page;
        }

        return $normalizeFn($page, $slug);
    }
}

$siteName = (string) ($config['site_name'] ?? 'Allenhouse');
$canonicalHost = rtrim((string) ($config['canonical_host'] ?? ''), '/');
$includesDir = (string) ($config['includes_dir'] ?? '');
$slugFn = (string) ($config['slug_fn'] ?? '');
$fetchFn = (string) ($config['fetch_fn'] ?? '');
$hasFn = (string) ($config['has_fn'] ?? '');
$useMetaForInner = (bool) ($config['use_meta_for_inner'] ?? false);
$renderFn = (string) ($config['render_fn'] ?? '');

if ($includesDir === '' || !is_dir($includesDir)) {
    throw new RuntimeException('cms_dynamic_page_config[includes_dir] must point to school includes/');
}

$slug = trim((string) ($cms_page_slug ?? ''));
if ($slug === '' && $slugFn !== '' && function_exists($slugFn)) {
    $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
    $slug = (string) $slugFn($script);
}
if ($slug === '' && function_exists('cmsGuessSlugFromScript')) {
    $slug = (string) (cmsGuessSlugFromScript() ?? '');
}

if ($slug === '') {
    http_response_code(404);
    $notFound = dirname($includesDir) . '/404.php';
    if (is_file($notFound)) {
        include $notFound;
    }
    return;
}

// Current page must use live CMS API — bulk prefetch cache can lag behind new sections.
$GLOBALS['cms_page_slug'] = $slug;

$page = ['data' => []];
if ($fetchFn !== '' && function_exists($fetchFn)) {
    $page = $fetchFn($slug);
    $page = cmsDynamicPageNormalizeTitles($page, $slug, $config);
}

$hasContent = false;
if ($hasFn !== '' && function_exists($hasFn)) {
    $hasContent = (bool) $hasFn($page);
} else {
    $hasContent = cmsPageHasRenderableContent($page);
}

if (!$hasContent && function_exists('cmsFetchPageBySlug')) {
    $page = cmsFetchPageBySlug($slug, true);
    $page = cmsDynamicPageNormalizeTitles($page, $slug, $config);
    if ($hasFn !== '' && function_exists($hasFn)) {
        $hasContent = (bool) $hasFn($page);
    } else {
        $hasContent = cmsPageHasRenderableContent($page);
    }
}

$publishedPage = is_array($page['data'] ?? null)
    && ($page['data'] ?? []) !== []
    && function_exists('cmsPageIsPublished')
    && cmsPageIsPublished($page);
if (!$hasContent && $publishedPage) {
    $hasContent = true;
}

$galleryMeta = function_exists('cmsPageGalleryMeta') ? cmsPageGalleryMeta($slug) : null;
$isBlogListing = function_exists('cmsIsBlogListingSlug') && cmsIsBlogListingSlug($slug);
if (!$hasContent && $isBlogListing && function_exists('cmsPageHasBlogPosts') && cmsPageHasBlogPosts()) {
    $hasContent = true;
}
if (!$hasContent && $galleryMeta === null && empty($cms_allow_empty_page)) {
    http_response_code(404);
    $notFound = dirname($includesDir) . '/404.php';
    if (is_file($notFound)) {
        include $notFound;
    }
    return;
}

$GLOBALS['cms_current_page'] = $page;
$fallbackTitle = (string) ($cms_page_title_fallback ?? '');
if ($fallbackTitle !== '' && function_exists('cmsApplyPageTitleFallback')) {
    $page = cmsApplyPageTitleFallback($page, $fallbackTitle);
    $GLOBALS['cms_current_page'] = $page;
}
$scriptBase = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
$canonicalPath = (string) ($cms_canonical_path ?? ('/' . ($scriptBase !== '' ? $scriptBase : '')));
$canonicalUrl = $canonicalHost !== '' ? $canonicalHost . $canonicalPath : '';

$galleryDetailId = trim((string) ($_GET['id'] ?? ''));
if ($galleryDetailId !== '' && $galleryMeta !== null) {
    $backUrl = function_exists('site_page_url') ? site_page_url($canonicalPath) : $canonicalPath;
    cmsRenderGalleryDetailPage($galleryDetailId, $page, $config, $backUrl, $galleryMeta);
    return;
}
$GLOBALS['cms_og_site_name'] = $siteName;
$GLOBALS['cms_og_domain'] = $canonicalHost !== '' ? (string) parse_url($canonicalHost, PHP_URL_HOST) : '';
$seoOptions = [
    'canonical_url' => $canonicalUrl,
    'includes_dir' => $includesDir,
    'og_image_default' => (string) ($config['og_image_default'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($canonicalUrl !== ''): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
    <?php endif; ?>
    <?php cmsResolveAndRenderPageSeo($page, $seoOptions); ?>
    <?php include $includesDir . '/head.php'; ?>
</head>
<body>
<?php include $includesDir . '/header.php'; ?>

<div class="main relative mb-0">
    <?php
    $pageData = $page['data'] ?? [];
    $sections = $pageData['sections'] ?? [];
    $sectionsEmpty = !is_array($sections) || $sections === [];
    $galleryShell = function_exists('cmsPageSectionsAreGalleryShell') && cmsPageSectionsAreGalleryShell($page);

    if (!empty($cms_banner_only)) {
        $GLOBALS['cms_skip_auto_sections'] = true;
        if ($fallbackTitle !== '' && is_array($page['data'] ?? null)) {
            $page['data']['page_heading'] = $fallbackTitle;
            $GLOBALS['cms_current_page'] = $page;
        }
        cmsRenderPageBanner($page, []);
        cmsRenderPageBreadcrumbs($page, []);
        if (function_exists('cmsMarkAllPageSectionsRendered')) {
            cmsMarkAllPageSectionsRendered($page);
        }
    } elseif ($galleryMeta !== null) {
        cmsRenderDynamicGalleryPage($page, $galleryMeta);
    } elseif ($isBlogListing && $sectionsEmpty && function_exists('cmsRenderBlogListFromCache')) {
        cmsRenderPageBanner($page, []);
        cmsRenderPageBreadcrumbs($page, []);
        echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') . '">';
        cmsRenderBlogListFromCache();
        echo '</div>';
        if (function_exists('cmsMarkAllPageSectionsRendered')) {
            cmsMarkAllPageSectionsRendered($page);
        }
    } elseif ($renderFn !== '' && function_exists($renderFn)) {
        $renderFn($page);
    } else {
        $sectionOptions = ['skip_hero' => false];
        $heroImageBanner = $cms_hero_image_banner ?? null;
        if (is_array($heroImageBanner) && $heroImageBanner !== [] && function_exists('cmsRenderImageHeroBanner')) {
            cmsRenderImageHeroBanner($heroImageBanner);
            cmsRenderPageBreadcrumbs($page, []);
            $sectionOptions = [
                'skip_hero' => true,
                'skip_page_banner' => true,
                'skip_breadcrumbs' => true,
            ];
        }
        if ($sectionsEmpty || !cmsPageHasRenderableContent($page)) {
            cmsRenderPageShell($page, $sectionOptions);
        } else {
            cmsRenderPageSections($page, $sectionOptions);
        }
    }

    if (!empty($cms_render_careers_jobs) || (preg_match('/career/i', $slug) && !empty($GLOBALS['api_cache']['jobs_data']))) {
        cmsRenderJobsListFromCache();
    }

    if (isset($cms_after_sections) && is_callable($cms_after_sections)) {
        $cms_after_sections($page, $slug);
    }

    if (
        preg_match('/admission-procedure-page-/i', $slug)
        && empty($GLOBALS['cms_admission_procedure_enquiry_rendered'])
        && function_exists('cmsMaybeRenderAdmissionProcedureEnquiryForm')
    ) {
        cmsMaybeRenderAdmissionProcedureEnquiryForm();
    }
    ?>
</div>

<?php
if (!empty($cms_include_contact_form_scripts)) {
    if (is_file($includesDir . '/form-proxy-client.php')) {
        include $includesDir . '/form-proxy-client.php';
    }
    if (is_file($includesDir . '/contact-form-script.php')) {
        include $includesDir . '/contact-form-script.php';
    }
}
include $includesDir . '/footer.php';
include $includesDir . '/foot.php';
?>
</body>
</html>
