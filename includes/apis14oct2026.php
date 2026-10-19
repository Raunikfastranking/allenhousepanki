<?php

require_once __DIR__ . '/environment.php';

if (!headers_sent()) {
    header('Cache-Control: private, max-age=60, must-revalidate');
}

require_once dirname(__DIR__) . '/proxy/config.php';
require_once __DIR__ . '/cms-panki-slugs.php';
require_once __DIR__ . '/api-adapters.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/api-scoped-loader.php';
require_once __DIR__ . '/cms-bootstrap.php';

$branchId = (int) BRANCH_ID;

if (!defined('ALLENHOUSE_GALLERY_BRANCH_ID')) {
    define('ALLENHOUSE_GALLERY_BRANCH_ID', $branchId);
}

if (!isset($GLOBALS['api_cache'])) {
    $apiFlags = cmsApiResolveLoadFlags();
    $GLOBALS['cms_api_load_flags_resolved'] = $apiFlags;
    $endpoints = cmsApiBuildEndpoints($apiFlags, $branchId);
    $GLOBALS['api_cache'] = cmsApiFetchScopedEndpoints($endpoints);
    cmsApiSeedCurlCacheFromBulk($GLOBALS['api_cache']);
}

$data = $GLOBALS['api_cache'];
$apiFlags = $GLOBALS['cms_api_load_flags_resolved'] ?? cmsApiResolveLoadFlags();

require_once dirname(__DIR__) . '/shared/includes/cms-layout-bootstrap.php';
$footer_link_data = cmsBuildFooterLinkDataFromBulk($data);
$GLOBALS['footer_link_data'] = $footer_link_data;

$jobs_data = cmsEmptyResponse();
if (!empty($apiFlags['careers'])) {
    $jobs_data = $data['jobs_data'] ?? cmsEmptyResponse();
}

$header_footer_data = $data['header_footer_data'] ?? [];
$GLOBALS['header_footer_data'] = $header_footer_data;
$menu_data = $data['menu_data'] ?? cmsEmptyResponse();
