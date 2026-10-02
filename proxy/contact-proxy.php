<?php
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/environment.php';
require_once __DIR__ . '/contact-helper.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$result = cms_post_contact_query(is_array($data) ? $data : []);

http_response_code((int) $result['status']);
header('Content-Type: application/json');
echo (string) $result['body'];
