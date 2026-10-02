<?php

require_once __DIR__ . '/environment.php';
require_once dirname(__DIR__) . '/proxy/config.php';
require_once __DIR__ . '/apis.php';
require_once __DIR__ . '/cms-bootstrap.php';
require_once __DIR__ . '/cms-page-helpers.php';

$GLOBALS['cms_index_page_config'] = [
    'site_name' => 'Allenhouse Panki',
    'canonical_host' => 'https://allenhousepanki.com',
    'includes_dir' => __DIR__,
    'home_slug' => 'home-page-panki',
    'fetch_fn' => 'cmsFetchPankiPage',
    'prefetch_fn' => 'cmsPrefetchIndexPage',
];

require_once dirname(__DIR__) . '/shared/includes/cms-index-page-runner.php';