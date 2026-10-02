<?php

declare(strict_types=1);

require_once __DIR__ . '/school-root.php';
$sharedRoot = cms_school_root(__DIR__);
require_once $sharedRoot . '/shared/proxy/form-validation.php';
require_once $sharedRoot . '/shared/proxy/cms-http.php';

if (!function_exists('cms_contact_pick')) {
    function cms_contact_pick(array $data, array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === null) {
                continue;
            }
            $value = trim((string) $data[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return $default;
    }
}

if (!function_exists('cms_normalize_contact_query_type')) {
    function cms_normalize_contact_query_type(string $raw): string
    {
        $raw = trim($raw);
        $map = [
            'General' => 'General',
            'Admission' => 'Admission',
            'Admission Related' => 'Admission',
            'Job' => 'Job',
            'Job Related' => 'Job',
            'Feedback' => 'Feedback',
            'Suggestion/Feedback' => 'Feedback',
            'Suggestion' => 'Feedback',
        ];

        return $map[$raw] ?? ($raw !== '' ? $raw : 'General');
    }
}

if (!function_exists('cms_contact_subject_for_query_type')) {
    function cms_contact_subject_for_query_type(string $queryType): string
    {
        switch ($queryType) {
            case 'Admission':
                return 'Admission Inquiry';
            case 'Job':
                return 'Job Inquiry';
            case 'Feedback':
                return 'Suggestion/Feedback';
            default:
                return 'General Inquiry';
        }
    }
}

if (!function_exists('cms_normalize_contact_query_payload')) {
    /**
     * @return array<string, mixed>
     */
    function cms_normalize_contact_query_payload(array $input): array
    {
        $name = cms_contact_pick($input, ['name']);
        $email = cms_contact_pick($input, ['email']);
        $phone = cms_contact_pick($input, ['phone', 'mobile']);
        $message = cms_contact_pick($input, ['message']);
        $rawQueryType = cms_contact_pick($input, ['query_type', 'queryType', 'query'], 'General');
        $queryType = cms_normalize_contact_query_type($rawQueryType);
        $subject = cms_contact_pick($input, ['subject']);
        if ($subject === '' || $subject === $rawQueryType) {
            $subject = cms_contact_subject_for_query_type($queryType);
        }

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'school_id' => (int) (defined('SCHOOL_ID') ? SCHOOL_ID : 1),
            'branch_id' => (int) (defined('BRANCH_ID') ? BRANCH_ID : 1),
            'language_id' => (int) (defined('LANGUAGE_ID') ? LANGUAGE_ID : 1),
            'query_type' => $queryType,
        ];
    }
}

if (!function_exists('cms_post_contact_query')) {
    /**
     * POST /api/contact-queries
     *
     * @return array{status: int, body: string}
     */
    function cms_post_contact_query(array $input): array
    {
        if (!defined('CMS_API_URL')) {
            return [
                'status' => 500,
                'body' => json_encode(['success' => false, 'message' => 'CMS API is not configured']),
            ];
        }

        $payload = cms_normalize_contact_query_payload($input);
        if ($payload['name'] === '' || $payload['email'] === '' || $payload['phone'] === '' || $payload['message'] === '') {
            return cms_form_error_response('Please fill all required fields.');
        }
        if (!cms_validate_name($payload['name'])) {
            return cms_form_error_response('Please enter a valid name (letters and spaces only, max 50 characters).');
        }
        if (!cms_validate_email($payload['email'])) {
            return cms_form_error_response('Please enter a valid email address.');
        }
        if (!cms_validate_mobile_in($payload['phone'])) {
            return cms_form_error_response('Please enter a valid 10-digit Indian mobile number.');
        }
        if (trim((string) $payload['query_type']) === '' || trim((string) $payload['query_type']) === 'Select Query') {
            return cms_form_error_response('Please select a query type.');
        }

        $url = rtrim(CMS_API_URL, '/') . '/contact-queries';

        return cms_curl_post_json($url, $payload, 'Unable to submit contact form. Please try again.');
    }
}
