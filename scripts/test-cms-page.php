<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_GET['cms_path'] = $argv[1] ?? 'careers';
$_SERVER['HTTP_HOST'] = 'allenhousepanki.com';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/' . $_GET['cms_path'];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/cms-page.php';

try {
    ob_start();
    include dirname(__DIR__) . '/cms-page.php';
    $html = ob_get_clean();
    $code = http_response_code();
    echo "HTTP $code, bytes=" . strlen($html) . PHP_EOL;
    if ($code >= 500 || strlen($html) < 100) {
        echo substr($html, 0, 2000) . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'FATAL: ' . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
