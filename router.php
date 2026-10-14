<?php

/**
 * Local router for PHP's built-in server.
 * Mirrors the extensionless URL rules in .htaccess.
 *
 *   php -S 127.0.0.1:8000 router.php
 */

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = str_replace('\\', '/', urldecode(is_string($uriPath) ? $uriPath : '/'));
if ($uriPath === '') {
    $uriPath = '/';
}

if ($uriPath !== '/' && str_ends_with($uriPath, '/')) {
    $uriPath = rtrim($uriPath, '/') ?: '/';
}

$fullPath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uriPath);
if ($uriPath !== '/' && (is_file($fullPath) || is_dir($fullPath))) {
    return false;
}

$serve = static function (string $script, array $query = []) use ($uriPath): void {
    foreach ($query as $key => $value) {
        $_GET[$key] = $value;
        $_REQUEST[$key] = $value;
    }

    $relative = '/' . ltrim(str_replace('\\', '/', substr($script, strlen(__DIR__))), '/');
    $_SERVER['SCRIPT_FILENAME'] = $script;
    $_SERVER['SCRIPT_NAME'] = $relative;
    $_SERVER['PHP_SELF'] = $relative;
    if ($query !== []) {
        $qs = http_build_query($query);
        $existing = (string) ($_SERVER['QUERY_STRING'] ?? '');
        $_SERVER['QUERY_STRING'] = $existing === '' ? $qs : $existing . '&' . $qs;
    }

    chdir(dirname($script));
    require $script;
};

if (preg_match('#^/proxy/([A-Za-z0-9_-]+)$#', $uriPath, $m)) {
    $script = __DIR__ . '/proxy/' . $m[1] . '.php';
    if (is_file($script)) {
        $serve($script);
        return true;
    }
}

if (preg_match('#^/blog/blog/([A-Za-z0-9_-]+)$#', $uriPath, $m)) {
    header('Location: /blog/' . rawurlencode($m[1]), true, 301);
    return true;
}

if ($uriPath === '/home') {
    $serve(__DIR__ . '/index.php');
    return true;
}

if (preg_match('#^/blog/([A-Za-z0-9_-]+)$#', $uriPath, $m)) {
    $serve(__DIR__ . '/cms-page.php', ['cms_path' => 'blog/' . $m[1]]);
    return true;
}

if (preg_match('#^/view/([A-Za-z0-9_-]+)$#', $uriPath, $m)) {
    $serve(__DIR__ . '/detail-view.php', ['slug' => $m[1]]);
    return true;
}

$relative = ltrim($uriPath, '/');
$phpFile = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.php';
if ($relative !== '' && is_file($phpFile)) {
    $serve($phpFile);
    return true;
}

if (preg_match('#^/([A-Za-z0-9_-]+)$#', $uriPath, $m)) {
    $serve(__DIR__ . '/cms-page.php', ['cms_path' => $m[1]]);
    return true;
}

$serve(__DIR__ . '/index.php');
return true;
