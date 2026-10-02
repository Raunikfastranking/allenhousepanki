<?php

declare(strict_types=1);

/**
 * Legacy meta.php body — only runs when CMS runners have not already rendered SEO.
 */

if (!empty($GLOBALS['cms_seo_rendered'])) {
    return;
}

require_once __DIR__ . '/cms-page-seo.php';
require_once __DIR__ . '/cms-seo-resolve.php';

$options = is_array($GLOBALS['cms_meta_legacy_config'] ?? null) ? $GLOBALS['cms_meta_legacy_config'] : [];
$includesDir = (string) ($options['includes_dir'] ?? '');

$page = cmsSeoResolveCurrentPage();
if (is_array($page)) {
    cmsResolveAndRenderPageSeo($page, [
        'includes_dir' => $includesDir,
        'canonical_url' => trim((string) ($options['canonical_url'] ?? '')),
        'og_image_default' => trim((string) ($options['og_image_default'] ?? '')),
    ]);
}
