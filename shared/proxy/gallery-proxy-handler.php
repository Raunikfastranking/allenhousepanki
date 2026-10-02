<?php

declare(strict_types=1);

/**
 * Gallery API proxy — JWT-secured upstream calls for branch/year/sub-type galleries.
 * Requires config.php (CMS_API_URL, BRANCH_ID) before include.
 */
if (!function_exists('cms_handle_gallery_proxy_request')) {
    function cms_handle_gallery_proxy_request(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            http_response_code(200);
            exit;
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        if (!defined('CMS_API_URL') || !defined('BRANCH_ID')) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'CMS API is not configured']);
            return;
        }

        $branchId = isset($_GET['branch']) ? (int) $_GET['branch'] : (int) BRANCH_ID;
        if ($branchId !== (int) BRANCH_ID) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid branch']);
            return;
        }

        $subTypeId = isset($_GET['sub_type']) ? (int) $_GET['sub_type'] : 0;
        $year = isset($_GET['year']) ? preg_replace('/\D/', '', (string) $_GET['year']) : '';

        if ($subTypeId > 0) {
            $apiUrl = rtrim(CMS_API_URL, '/') . '/galleries/sub-type/' . $subTypeId . '/branch/' . $branchId;
        } elseif (strlen($year) === 4) {
            $apiUrl = rtrim(CMS_API_URL, '/') . '/galleries/branch/' . $branchId . '/year/' . $year;
        } else {
            $apiUrl = rtrim(CMS_API_URL, '/') . '/galleries/branch/' . $branchId;
        }

        if (function_exists('cms_curl_get_json')) {
            $payload = cms_curl_get_json($apiUrl, 30);
            if (is_array($payload)) {
                echo json_encode($payload);
                return;
            }
        }

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => api_auth_headers(['Accept: application/json']),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
        ]);
        if (function_exists('apply_curl_ssl_options')) {
            apply_curl_ssl_options($ch);
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        http_response_code($status > 0 ? $status : 502);
        echo $response !== false ? $response : json_encode(['success' => false, 'message' => 'API request failed']);
    }
}
