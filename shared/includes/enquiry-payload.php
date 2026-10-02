<?php

declare(strict_types=1);

require_once __DIR__ . '/school-root.php';

if (!function_exists('cms_shared_root_path')) {
    function cms_shared_root_path(): string
    {
        static $root = null;
        if ($root !== null) {
            return $root;
        }

        $root = cms_school_root(__DIR__);

        return $root;
    }
}

$sharedRoot = cms_shared_root_path();
require_once $sharedRoot . '/shared/proxy/form-validation.php';
require_once $sharedRoot . '/shared/proxy/cms-http.php';

if (!function_exists('cms_enquiry_pick')) {
    function cms_enquiry_pick(array $data, array $keys, string $default = ''): string
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

if (!function_exists('cms_enquiry_source_fields')) {
    /**
     * @return array{source: string, source_type: string}
     */
    function cms_enquiry_source_fields(string $sourceRaw, string $sourceTypeRaw = ''): array
    {
        $sourceType = strtolower(trim($sourceTypeRaw));
        if (in_array($sourceType, ['organic', 'paid', 'referral', 'social', 'direct'], true)) {
            return [
                'source' => 'website',
                'source_type' => $sourceType,
            ];
        }

        $source = strtolower(trim($sourceRaw));
        if ($source === '' || $source === 'website' || $source === 'landing page') {
            return ['source' => 'website', 'source_type' => 'organic'];
        }
        if (
            str_contains($source, 'google')
            || str_contains($source, 'ads')
            || str_contains($source, 'facebook')
            || str_contains($source, 'instagram')
        ) {
            return ['source' => 'website', 'source_type' => 'paid'];
        }

        return ['source' => 'website', 'source_type' => 'organic'];
    }
}

if (!function_exists('cms_normalize_enquiry_payload')) {
    /**
     * Map form payloads to CMS POST /api/enquiries schema.
     *
     * @return array<string, mixed>
     */
    function cms_normalize_enquiry_payload(array $input): array
    {
        $studentName = cms_enquiry_pick($input, ['student_name', 'studentName']);
        $parentName = cms_enquiry_pick($input, ['parent_name', 'parentName']);
        $email = cms_enquiry_pick($input, ['email']);
        $phone = cms_enquiry_pick($input, ['phone', 'mobile']);
        $grade = cms_enquiry_pick($input, ['grade']);
        $city = cms_enquiry_pick($input, ['city']);
        $session = cms_enquiry_pick($input, ['session']);
        $pincode = cms_enquiry_pick($input, ['pincode']);
        $board = cms_enquiry_pick($input, ['board']);

        $sourceRaw = cms_enquiry_pick($input, ['source'], 'website');
        $sourceTypeRaw = cms_enquiry_pick($input, ['source_type', 'sourceType']);
        $sourceInfo = cms_enquiry_source_fields($sourceRaw, $sourceTypeRaw);

        $message = cms_enquiry_pick($input, ['message']);
        if ($message === '') {
            $gradeLabel = $grade !== '' ? $grade : 'the selected grade';
            $message = 'I want admission details for ' . $gradeLabel . '.';
            if ($session !== '') {
                $message = 'I want admission details for ' . $gradeLabel . ' (Session ' . $session . ').';
            }
        }
        if ($board !== '' && stripos($message, 'board:') === false) {
            $message = 'Board: ' . $board . '. ' . $message;
        }

        $contactName = cms_enquiry_pick($input, ['name']);
        if ($contactName === '') {
            $contactName = $parentName !== '' ? $parentName : $studentName;
        }

        return [
            'school_id' => (int) (defined('SCHOOL_ID') ? SCHOOL_ID : 1),
            'branch_id' => (int) (defined('BRANCH_ID') ? BRANCH_ID : 1),
            'name' => $contactName,
            'email' => $email,
            'phone' => $phone,
            'student_name' => $studentName,
            'parent_name' => $parentName,
            'grade' => $grade,
            'city' => $city,
            'enquiry_type' => cms_enquiry_pick($input, ['enquiry_type'], 'admission'),
            'subject' => cms_enquiry_pick($input, ['subject'], 'Admission Enquiry'),
            'message' => $message,
            'priority' => cms_enquiry_pick($input, ['priority'], 'medium'),
            'status' => cms_enquiry_pick($input, ['status'], 'new'),
            'source' => $sourceInfo['source'],
            'source_type' => $sourceInfo['source_type'],
            'session' => $session,
            'pincode' => $pincode,
        ];
    }
}

if (!function_exists('cms_post_enquiry')) {
    /**
     * @return array{status: int, body: string}
     */
    function cms_post_enquiry(array $input): array
    {
        if (!defined('CMS_API_URL')) {
            return [
                'status' => 500,
                'body' => json_encode(['success' => false, 'message' => 'CMS API is not configured']),
            ];
        }

        if (array_key_exists('board', $input) && $input['board'] !== null && trim((string) $input['board']) === '') {
            return cms_form_error_response('Please select a board.');
        }

        $payload = cms_normalize_enquiry_payload($input);
        $validationError = cms_validate_enquiry_payload($payload);
        if ($validationError !== null) {
            return cms_form_error_response($validationError);
        }

        $url = rtrim(CMS_API_URL, '/') . '/enquiries';

        return cms_curl_post_json($url, $payload, 'Unable to submit enquiry. Please try again.');
    }
}
