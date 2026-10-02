<?php

declare(strict_types=1);

/**
 * Transfer certificate lookup via CMS GET /api/tc-details/{branch_id}.
 * Requires config.php (CMS_API_URL, BRANCH_ID) before include.
 */
if (!function_exists('cms_normalize_tc_dob')) {
    function cms_normalize_tc_dob(string $dob): string
    {
        $dob = trim($dob);
        if ($dob === '') {
            return '';
        }
        $timestamp = strtotime($dob);

        return $timestamp === false ? $dob : date('Y-m-d', $timestamp);
    }
}

if (!function_exists('cms_extract_tc_records')) {
    /**
     * @return list<array<string, mixed>>
     */
    function cms_extract_tc_records(array $payload): array
    {
        $data = $payload['data'] ?? null;
        if (!is_array($data)) {
            return [];
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return array_values($data['data']);
        }

        return array_values($data);
    }
}

if (!function_exists('cms_find_tc_record')) {
    /**
     * @param list<array<string, mixed>> $records
     * @return array<string, mixed>|null
     */
    function cms_find_tc_record(array $records, string $admissionNo, string $dob): ?array
    {
        $admissionNo = trim($admissionNo);
        $dobNorm = cms_normalize_tc_dob($dob);
        if ($admissionNo === '' || $dobNorm === '') {
            return null;
        }

        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }
            $recordAdmission = trim((string) ($record['admission_no'] ?? ''));
            $recordDob = cms_normalize_tc_dob((string) ($record['dob'] ?? ''));
            if (strcasecmp($recordAdmission, $admissionNo) === 0 && $recordDob === $dobNorm) {
                return $record;
            }
        }

        return null;
    }
}

if (!function_exists('cms_handle_tc_proxy_request')) {
    function cms_handle_tc_proxy_request(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!defined('CMS_API_URL') || !defined('BRANCH_ID')) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'CMS API is not configured']);
            return;
        }

        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = [];
        }

        $admissionNo = trim((string) ($input['admission_no'] ?? $input['admissionNo'] ?? $_GET['admission_no'] ?? $_GET['admissionNo'] ?? ''));
        $dob = trim((string) ($input['dob'] ?? $_GET['dob'] ?? ''));

        if ($admissionNo === '' || $dob === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Admission number and date of birth are required.',
            ]);
            return;
        }

        $apiUrl = rtrim(CMS_API_URL, '/') . '/tc-details/' . (int) BRANCH_ID;
        $payload = null;
        if (function_exists('cms_curl_get_json')) {
            $payload = cms_curl_get_json($apiUrl, 30);
        }
        if (!is_array($payload)) {
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

            if ($response === false || $status < 200 || $status >= 300) {
                http_response_code(502);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to fetch transfer certificate data. Please try again.',
                ]);
                return;
            }

            $payload = json_decode((string) $response, true);
        }
        if (!is_array($payload)) {
            http_response_code(502);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid response from transfer certificate service.',
            ]);
            return;
        }

        $records = cms_extract_tc_records($payload);
        $match = cms_find_tc_record($records, $admissionNo, $dob);

        if ($match === null) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No transfer certificate found for the given details.',
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'student_name' => (string) ($match['student_name'] ?? ''),
                'admission_no' => (string) ($match['admission_no'] ?? ''),
                'class' => (string) ($match['class'] ?? ''),
                'parent_name' => (string) ($match['parent_name'] ?? ''),
                'dob' => (string) ($match['dob'] ?? ''),
                'url' => (string) ($match['url'] ?? ''),
            ],
        ]);
    }
}
