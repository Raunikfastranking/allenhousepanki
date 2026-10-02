<?php

/**
 * CMS-only pages (no local .php stub) — same pattern as Allenhouse Rooma/Agra.
 */

require_once __DIR__ . '/includes/environment.php';
require_once __DIR__ . '/proxy/config.php';

$GLOBALS['cms_skip_published_guard'] = true;

require_once __DIR__ . '/includes/apis.php';

$path = trim((string) ($_GET['cms_path'] ?? $_GET['slug'] ?? ''), '/');
if (cmsMaybeServeBlogDetail($path, __DIR__ . '/includes/cms-blog-detail-page.php')) {
    return;
}

require_once __DIR__ . '/includes/cms-panki-slugs.php';

// Prefer local PHP stubs, but resolve the slug from the URL path first.
// When cms-page.php includes a stub, SCRIPT_FILENAME is still "cms-page", so
// stubs without an explicit $cms_page_slug would otherwise map wrongly.
$pathStub = $path !== '' ? __DIR__ . '/' . $path . '.php' : '';
if ($pathStub !== '' && is_file($pathStub)) {
    if (trim((string) ($cms_page_slug ?? '')) === '') {
        $fromScript = cmsPankiSlugFromScript($path);
        $defaultGuess = str_replace('_', '-', $path) . '-page-panki';
        $resolved = cmsResolveSlugFromPath($path);
        if ($fromScript !== '' && $fromScript !== $defaultGuess) {
            $cms_page_slug = $fromScript;
        } elseif (is_string($resolved) && $resolved !== '') {
            $cms_page_slug = $resolved;
        } else {
            $cms_page_slug = $fromScript;
        }
        $GLOBALS['cms_page_slug'] = $cms_page_slug;
    }
    include $pathStub;
    return;
}
$slug = cmsResolveSlugFromPath($path);

if ($slug === null) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$cms_page_slug = $slug;
$GLOBALS['cms_page_slug'] = $slug;

require_once __DIR__ . '/shared/includes/cms-page-seo.php';
require_once __DIR__ . '/shared/includes/cms-page-content.php';
require_once __DIR__ . '/shared/cms/cms-gallery-dynamic.php';

$staticFile = __DIR__ . '/' . $slug . '.php';
if (is_file($staticFile)) {
    include $staticFile;
    return;
}
$page = cmsFetchPageBySlug($slug);
if (($page['data'] ?? []) === []) {
    // Newly published pages may miss file/index cache; fetch live once.
    $page = cmsFetchPageBySlug($slug, true);
}
if (($page['data'] ?? []) === [] && $path !== '' && $path !== $slug) {
    // Fallback when CMS slug does not use the "{path}-page-{branch}" convention.
    $exact = cmsFetchPageBySlug($path);
    if (($exact['data'] ?? []) === []) {
        $exact = cmsFetchPageBySlug($path, true);
    }
    if (($exact['data'] ?? []) !== []) {
        $page = $exact;
        $slug = $path;
        $cms_page_slug = $slug;
        $GLOBALS['cms_page_slug'] = $slug;
    }
}
$page = cmsApplyPageTitleFallback($page, $slug, $path);
$pageData = $page['data'] ?? [];

if ($pageData === [] || !cmsPageIsPublished($page)) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$GLOBALS['cms_current_page'] = $page;

$siteName = 'Allenhouse Panki';
$includesDir = __DIR__ . '/includes';
$pageTitle = cmsPageDisplayTitleSafe($page, cmsTitleFromPageSlug($slug));

$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$canonicalUrl = $host !== '' ? 'https://' . preg_replace('/^www\./i', '', $host) . (parse_url($requestUri, PHP_URL_PATH) ?: '') : '';

$GLOBALS['cms_og_site_name'] = $siteName;
$GLOBALS['cms_og_domain'] = $host !== '' ? (string) parse_url('https://' . $host, PHP_URL_HOST) : '';

$seoOptions = [
    'canonical_url' => $canonicalUrl,
    'includes_dir' => $includesDir,
    'og_image_default' => '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php cmsResolveAndRenderPageSeo($page, $seoOptions); ?>
    <?php include $includesDir . '/head.php'; ?>
</head>
<body>
<?php include $includesDir . '/header.php'; ?>

<div class="main relative mb-0">
    <?php
    $sections = $pageData['sections'] ?? [];
    $sectionsEmpty = !is_array($sections) || $sections === [];
    $galleryMeta = function_exists('cmsPageGalleryMeta') ? cmsPageGalleryMeta($slug) : null;

    if ($galleryMeta !== null && $sectionsEmpty) {
        cmsRenderDynamicGalleryPage($page, $galleryMeta);
    } elseif ($sectionsEmpty || !cmsPageHasRenderableContent($page)) {
        cmsRenderPageShell($page, []);
    } else {
        cmsRenderPageSections($page, ['skip_hero' => false]);
    }
    ?>
</div>

<?php include $includesDir . '/footer.php'; ?>
<?php include $includesDir . '/foot.php'; ?>
</body>
</html>
