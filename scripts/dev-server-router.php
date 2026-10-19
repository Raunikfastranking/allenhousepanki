<?php
// Dev router for the PHP built-in server. Emulates the URL Rewrite rules in
// web.config so the site can run locally via:
//   php -S localhost:8000 -t . scripts/dev-server-router.php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
$docRootReal = str_replace('\\', '/', (string) realpath($docRoot));

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

$staticExt = 'jpg|jpeg|png|gif|css|js|pdf|svg|ico|webp|apk|zip|rar|txt|xml|json|mp4|webm|ogg|woff|woff2|ttf|eot|map|cdr';

$local = realpath($docRoot . $path);
$localNorm = $local === false ? null : str_replace('\\', '/', $local);

// Block path traversal outside the docroot.
if ($localNorm !== null && $localNorm !== $docRootReal && !str_starts_with($localNorm, $docRootReal . '/')) {
    http_response_code(403);
    return true;
}

// Existing files (including .php and static assets) -> let the built-in
// server handle them directly.
if ($localNorm !== null && is_file($localNorm)) {
    return false;
}

$trim = rtrim($path, '/');
if ($trim === '') {
    $trim = '/';
}

// Rule 2b: /proxy/name -> /proxy/name.php
if (preg_match('#^/proxy/([A-Za-z0-9_-]+)$#i', $trim, $m)) {
    $f = $docRoot . '/proxy/' . $m[1] . '.php';
    if (is_file($f)) {
        require $f;
        return true;
    }
}

// Rule 5: directory index support.
if ($localNorm !== null && is_dir($localNorm)) {
    $idx = $localNorm . '/index.php';
    if (is_file($idx)) {
        require $idx;
        return true;
    }
}

// Rule 6: clean URL -> {path}.php (skip static-looking requests).
if (!preg_match('#\.(?:' . $staticExt . ')$#i', $trim) && $trim !== '/') {
    $php = $docRoot . $trim . '.php';
    if (is_file($php)) {
        require $php;
        return true;
    }
}

// Rule 7: /home -> index.php
if ($trim === '/home') {
    require $docRoot . '/index.php';
    return true;
}

// Rule 8: /blog/{slug} -> cms-page.php?cms_path=blog/{slug}
if (preg_match('#^/blog/([A-Za-z0-9-]+)$#', $trim, $m)) {
    $_GET['cms_path'] = 'blog/' . $m[1];
    $_REQUEST['cms_path'] = $_GET['cms_path'];
    $_SERVER['QUERY_STRING'] = 'cms_path=' . rawurlencode($_GET['cms_path']);
    require $docRoot . '/cms-page.php';
    return true;
}

// Rule 9: /view/{slug} -> detail-view.php?slug={slug}
if (preg_match('#^/view/([A-Za-z0-9-]+)$#', $trim, $m)) {
    $_GET['slug'] = $m[1];
    $_REQUEST['slug'] = $m[1];
    $_SERVER['QUERY_STRING'] = 'slug=' . rawurlencode($m[1]);
    require $docRoot . '/detail-view.php';
    return true;
}

// Rule 10: single-segment CMS page when no local .php exists.
if (preg_match('#^/([^/]+)$#', $trim, $m) && !preg_match('#\.(?:' . $staticExt . ')$#i', $m[1])) {
    $_GET['cms_path'] = $m[1];
    $_REQUEST['cms_path'] = $m[1];
    $_SERVER['QUERY_STRING'] = 'cms_path=' . rawurlencode($m[1]);
    require $docRoot . '/cms-page.php';
    return true;
}

// Nothing matched -> site 404 page.
http_response_code(404);
$nf = $docRoot . '/404.php';
if (is_file($nf)) {
    require $nf;
}
return true;
