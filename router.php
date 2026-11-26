<?php
/**
 * Router for PHP's built-in server (php -S).
 * Mirrors .htaccess / web.config rules when Apache/IIS rewrite is unavailable.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$root = realpath(__DIR__);

if ($uri === '/' || $uri === '') {
    return false;
}

$decodedPath = str_replace('\\', '/', $uri);
if (str_contains($decodedPath, '..')) {
    http_response_code(404);
    echo '404 Not Found';
    return true;
}

$requested = realpath($root . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $uri), DIRECTORY_SEPARATOR));
if ($requested && is_file($requested) && str_starts_with($requested, $root)) {
    return false;
}

$path = trim($uri, '/');

$serve = function (string $script, array $extraGet = []) use ($root) {
    $scriptFile = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $script);
    $real = realpath($scriptFile);
    if (!$real || !is_file($real) || !str_starts_with($real, $root)) {
        http_response_code(404);
        $notFound = $root . DIRECTORY_SEPARATOR . '404.php';
        if (is_file($notFound)) {
            require $notFound;
        } else {
            echo '404 Not Found';
        }
        return true;
    }

    foreach ($extraGet as $key => $value) {
        $_GET[$key] = $value;
    }
    if ($extraGet !== []) {
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);
    }

    $_SERVER['SCRIPT_NAME'] = '/' . $script;
    $_SERVER['SCRIPT_FILENAME'] = $real;
    $_SERVER['PHP_SELF'] = '/' . $script;
    chdir($root);
    require $real;
    return true;
};

if (strlen($uri) > 1 && str_ends_with($uri, '/')) {
    $dir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, rtrim($path, '/'));
    if (!is_dir($dir)) {
        $target = '/' . rtrim($path, '/');
        if (!empty($_SERVER['QUERY_STRING'])) {
            $target .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: ' . $target, true, 301);
        return true;
    }
}

if (preg_match('#^proxy/([A-Za-z0-9_-]+)$#', $path, $match)) {
    return $serve('proxy/' . $match[1] . '.php');
}

if ($path !== '' && is_dir($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path))) {
    $index = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($index)) {
        return $serve($path . '/index.php');
    }
}

if (preg_match('#^home$#i', $path)) {
    return $serve('index.php');
}

if (preg_match('#^blog/([A-Za-z0-9_-]+)$#', $path, $match)) {
    return $serve('cms-page.php', ['cms_path' => 'blog/' . $match[1]]);
}

if (preg_match('#^view/([A-Za-z0-9_-]+)$#', $path, $match)) {
    return $serve('detail-view.php', ['slug' => $match[1]]);
}

if ($path !== '' && is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path) . '.php')) {
    return $serve($path . '.php');
}

if (
    $path !== ''
    && !str_contains($path, '/')
    && !preg_match('/\.(jpg|jpeg|png|gif|css|js|pdf|svg|ico|webp|apk|zip|rar|txt|xml|json|mp4|webm|ogg|map|woff2?|ttf|eot)$/i', $path)
) {
    return $serve('cms-page.php', ['cms_path' => $path]);
}

return $serve('index.php');