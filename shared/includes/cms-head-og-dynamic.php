<?php

declare(strict_types=1);

/**
 * Per-page Open Graph / Twitter tags from CMS SEO ($GLOBALS['cms_seo']).
 * Included from each school's includes/head.php after page SEO is rendered.
 */

if (!empty($GLOBALS['cms_og_rendered'])) {
    return;
}

if (empty($GLOBALS['cms_seo']) || !is_array($GLOBALS['cms_seo'])) {
    return;
}

require_once __DIR__ . '/cms-page-seo.php';

$seo = $GLOBALS['cms_seo'];

if (($seo['og_image'] ?? '') === '' && !empty($GLOBALS['cms_og_image_default'])) {
    $seo['og_image'] = (string) $GLOBALS['cms_og_image_default'];
}

if (($seo['canonical_url'] ?? '') === '') {
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if ($host !== '') {
        $seo['canonical_url'] = 'https://' . preg_replace('/^www\./i', '', $host) . ($path ?: '/');
    }
}

$siteName = (string) ($GLOBALS['cms_og_site_name'] ?? '');
$domain = (string) ($GLOBALS['cms_og_domain'] ?? '');
if ($domain === '' && ($seo['canonical_url'] ?? '') !== '') {
    $domain = (string) parse_url((string) $seo['canonical_url'], PHP_URL_HOST);
}

cmsRenderOpenGraphTags($seo, $siteName, $domain);
$GLOBALS['cms_og_rendered'] = true;
