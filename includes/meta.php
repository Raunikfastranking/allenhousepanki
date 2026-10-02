<?php

if (!isset($GLOBALS['api_cache'])) {
    require_once __DIR__ . '/apis.php';
}

$GLOBALS['cms_meta_legacy_config'] = [
    'includes_dir' => __DIR__,
];
require_once dirname(__DIR__) . '/shared/includes/cms-meta-legacy.php';
