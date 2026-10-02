<?php
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/environment.php';
require_once __DIR__ . '/newsletter-helper.php';

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = [];
$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
$rawInput = file_get_contents('php://input');

if (str_contains($contentType, 'application/json') && is_string($rawInput) && $rawInput !== '') {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

if ($data === [] && !empty($_POST) && is_array($_POST)) {
    $data = $_POST;
}

if ($data === [] && is_string($rawInput) && $rawInput !== '' && str_contains($contentType, 'application/x-www-form-urlencoded')) {
    $parsed = [];
    parse_str($rawInput, $parsed);
    if (is_array($parsed) && $parsed !== []) {
        $data = $parsed;
    }
}

$data['ip_address'] = trim((string) ($data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? ''));
$data['user_agent'] = trim((string) ($data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? ''));

$result = cms_post_subscriber($data);

$status = (int) $result['status'];
if ($status >= 200 && $status < 300 && $status !== 200) {
    $status = 200;
}

http_response_code($status);
echo (string) $result['body'];
