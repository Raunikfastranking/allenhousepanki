<?php
require dirname(__DIR__) . '/proxy/config.php';
$pages = cms_curl_get_json(CMS_API_URL . '/pages?branch_id=' . BRANCH_ID, 20);
$root = dirname(__DIR__);
foreach ($pages['data'] ?? [] as $p) {
    if (!is_array($p)) continue;
    $slug = (string)($p['slug'] ?? '');
    if ($slug === '') continue;
    $path = preg_replace('/-page-panki$/', '', $slug);
    $path = preg_replace('/-panki$/', '', $path);
    $php = $root . '/' . str_replace('_', '-', $path) . '.php';
    // derive url from slug like rooma
    require_once dirname(__DIR__) . '/includes/cms-bootstrap.php';
    $urlPath = cmsPathFromSlug($slug);
    $php2 = $root . '/' . $urlPath . '.php';
    if (!is_file($php2)) {
        echo "NO STUB: path=$urlPath slug=$slug title=" . ($p['title'] ?? '') . PHP_EOL;
    }
}
