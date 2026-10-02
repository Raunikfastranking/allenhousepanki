<?php

/**
 * Minimal parallel prefetch for inner CMS pages (about-us, etc.).
 * Marks full prefetch as done so header does not repeat 10+ API calls.
 */
function cmsPrefetchPankiLite(array $extraEndpoints = []): void
{
    if (!defined('BRANCH_ID') || !function_exists('cms_prefetch_json_urls') || !defined('CMS_API_URL')) {
        return;
    }

    $branch = (string) BRANCH_ID;
    $cms = rtrim(CMS_API_URL, '/');
    $urls = [
        $cms . '/pages?branch_id=' . $branch,
        $cms . '/menus/branch/' . $branch,
        $cms . '/public/branches/' . $branch . '/layout-parts/',
        $cms . '/public/link-groups/position/footer/hierarchical?branch_id=' . $branch,
        $cms . '/meta-data/' . $branch,
        $cms . '/scrolling-texts/branch/' . $branch,
        $cms . '/all-address-details/' . $branch,
        $cms . '/header-contact/' . $branch,
        $cms . '/erp-login/' . $branch,
        $cms . '/alumni-portal/' . $branch,
    ];

    foreach ($extraEndpoints as $endpoint) {
        $endpoint = ltrim((string) $endpoint, '/');
        if ($endpoint !== '') {
            $urls[] = $cms . '/' . $endpoint;
        }
    }

    cms_prefetch_json_urls($urls);
    $GLOBALS['cms_prefetch_panki_done'] = true;
}
