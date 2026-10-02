<?php

if (!function_exists('cms_form_error_response')) {
    /**
     * @return array{status: int, body: string}
     */
    function cms_form_error_response(string $message, int $status = 422): array
    {
        return [
            'status' => $status,
            'body' => json_encode(['success' => false, 'message' => $message]),
        ];
    }
}

if (!function_exists('cms_validate_email')) {
    function cms_validate_email(string $email): bool
    {
        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('cms_validate_mobile_in')) {
    function cms_validate_mobile_in(string $phone): bool
    {
        return (bool) preg_match('/^[6-9]\d{9}$/', $phone);
    }
}

if (!function_exists('cms_validate_name')) {
    function cms_validate_name(string $name): bool
    {
        return $name !== '' && (bool) preg_match('/^[A-Za-z\s]+$/', $name) && strlen($name) <= 50;
    }
}

if (!function_exists('cms_validate_pincode_in')) {
    function cms_validate_pincode_in(string $pincode): bool
    {
        return (bool) preg_match('/^[1-9][0-9]{5}$/', $pincode);
    }
}

if (!function_exists('cms_validate_enquiry_payload')) {
    /**
     * @param array<string, mixed> $payload Normalized enquiry payload from cms_normalize_enquiry_payload.
     */
    function cms_validate_enquiry_payload(array $payload): ?string
    {
        if (!cms_validate_name((string) ($payload['student_name'] ?? ''))) {
            return 'Please enter a valid student name (letters and spaces only, max 50 characters).';
        }
        if (!cms_validate_name((string) ($payload['parent_name'] ?? ''))) {
            return 'Please enter a valid parent name (letters and spaces only, max 50 characters).';
        }
        if (!cms_validate_email((string) ($payload['email'] ?? ''))) {
            return 'Please enter a valid email address.';
        }
        if (!cms_validate_mobile_in((string) ($payload['phone'] ?? ''))) {
            return 'Please enter a valid 10-digit Indian mobile number.';
        }
        if (!cms_validate_pincode_in((string) ($payload['pincode'] ?? ''))) {
            return 'Please enter a valid 6-digit pincode.';
        }
        if (trim((string) ($payload['grade'] ?? '')) === '') {
            return 'Please select a grade.';
        }
        if (trim((string) ($payload['city'] ?? '')) === '') {
            return 'Please select a city.';
        }
        if (trim((string) ($payload['session'] ?? '')) === '') {
            return 'Please select a session.';
        }

        return null;
    }
}
