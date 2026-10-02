<?php

declare(strict_types=1);

/**
 * CMS-driven home page (Phase 2) — all content from home page sections API.
 */

require_once __DIR__ . '/cms-page-content.php';
require_once __DIR__ . '/cms-page-seo.php';
require_once __DIR__ . '/cms-flyer-popup.php';

$config = is_array($GLOBALS['cms_index_page_config'] ?? null) ? $GLOBALS['cms_index_page_config'] : [];
$siteName = (string) ($config['site_name'] ?? 'Allenhouse');
$canonicalHost = rtrim((string) ($config['canonical_host'] ?? ''), '/');
$includesDir = (string) ($config['includes_dir'] ?? '');
$homeSlug = trim((string) ($config['home_slug'] ?? ''));
$fetchFn = (string) ($config['fetch_fn'] ?? '');
$prefetchFn = (string) ($config['prefetch_fn'] ?? '');

if ($includesDir === '' || $homeSlug === '') {
    throw new RuntimeException('cms_index_page_config requires includes_dir and home_slug');
}

if ($prefetchFn !== '' && function_exists($prefetchFn)) {
    $prefetchFn();
}

$GLOBALS['cms_skip_auto_sections'] = true;
$GLOBALS['cms_page_slug'] = $homeSlug;

$page = ['data' => []];
if ($fetchFn !== '' && function_exists($fetchFn)) {
    $page = $fetchFn($homeSlug);
}
if (!cmsPageHasRenderableContent($page) && function_exists('cmsFetchPageBySlug')) {
    $page = cmsFetchPageBySlug($homeSlug);
}

$GLOBALS['cms_current_page'] = $page;
$GLOBALS['cms_og_site_name'] = $siteName;
$GLOBALS['cms_og_domain'] = $canonicalHost !== '' ? (string) parse_url($canonicalHost, PHP_URL_HOST) : '';
$seoOptions = [
    'canonical_url' => $canonicalHost !== '' ? $canonicalHost . '/' : '',
    'includes_dir' => $includesDir,
    'og_image_default' => (string) ($config['og_image_default'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($canonicalHost !== ''): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalHost . '/', ENT_QUOTES, 'UTF-8') ?>" />
    <?php endif; ?>
    <?php cmsResolveAndRenderPageSeo($page, $seoOptions); ?>
    <?php include $includesDir . '/head.php'; ?>
</head>
<body class="cms-home-page">
<?php include $includesDir . '/header.php'; ?>

<div class="main relative mb-0">
    <?php cmsRenderPageSections($page, ['full_width' => true, 'is_home' => true, 'skip_hero' => false]); ?>
</div>

<?php
cmsRenderFlyerPopupFromCache();
include $includesDir . '/footer.php';
include $includesDir . '/foot.php';
?>
</body>
</html>
