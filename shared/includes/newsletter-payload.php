<?php

declare(strict_types=1);

require_once __DIR__ . '/school-root.php';
$sharedRoot = cms_school_root(__DIR__);
require_once $sharedRoot . '/shared/proxy/form-validation.php';
require_once $sharedRoot . '/shared/proxy/cms-http.php';

if (!function_exists('cms_subscriber_name_from_email')) {
    function cms_subscriber_name_from_email(string $email): string
    {
        $local = strstr($email, '@', true);
        if ($local === false) {
            return 'Newsletter Subscriber';
        }
        $local = trim(str_replace(['.', '_', '-'], ' ', $local));
        if ($local === '') {
            return 'Newsletter Subscriber';
        }

        return ucwords($local);
    }
}

if (!function_exists('cms_normalize_subscriber_payload')) {
    /**
     * @return array<string, mixed>
     */
    function cms_normalize_subscriber_payload(array $input): array
    {
        $email = trim((string) ($input['email'] ?? ''));
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            $name = cms_subscriber_name_from_email($email);
        }
        if ($name === '') {
            $name = 'Newsletter Subscriber';
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'school_id' => (int) (defined('SCHOOL_ID') ? SCHOOL_ID : 1),
            'branch_id' => (int) (defined('BRANCH_ID') ? BRANCH_ID : 1),
            'status' => 'active',
            'language_id' => (int) (defined('LANGUAGE_ID') ? LANGUAGE_ID : 1),
            'subscription_source' => trim((string) ($input['subscription_source'] ?? $input['source'] ?? 'website')),
        ];

        $ip = trim((string) ($input['ip_address'] ?? ''));
        if ($ip !== '') {
            $payload['ip_address'] = $ip;
        }
        $userAgent = trim((string) ($input['user_agent'] ?? ''));
        if ($userAgent !== '') {
            $payload['user_agent'] = $userAgent;
        }

        return $payload;
    }
}

if (!function_exists('cms_post_subscriber')) {
    /**
     * @return array{status: int, body: string}
     */
    function cms_post_subscriber(array $input): array
    {
        if (!defined('CMS_API_URL')) {
            return [
                'status' => 500,
                'body' => json_encode(['success' => false, 'message' => 'CMS API is not configured']),
            ];
        }

        $payload = cms_normalize_subscriber_payload($input);
        if ($payload['email'] === '') {
            return cms_form_error_response('Email is required.');
        }
        if (!cms_validate_email($payload['email'])) {
            return cms_form_error_response('Please enter a valid email address.');
        }

        $url = rtrim(CMS_API_URL, '/') . '/subscribers';
        $result = cms_curl_post_json($url, $payload, 'Unable to subscribe. Please try again.');

        if ($result['status'] === 422 || $result['status'] === 409) {
            $decoded = json_decode($result['body'], true);
            $msg = cms_api_error_message(is_array($decoded) ? $decoded : null, '');
            $emailErrors = '';
            if (is_array($decoded['errors']['email'] ?? null)) {
                $emailErrors = implode(' ', $decoded['errors']['email']);
            }
            $combined = strtolower($msg . ' ' . $emailErrors);
            if (
                str_contains($combined, 'already been taken')
                || str_contains($combined, 'already exists')
                || str_contains($combined, 'already subscribed')
            ) {
                return [
                    'status' => 200,
                    'body' => json_encode([
                        'success' => true,
                        'message' => 'Subscription successful',
                        'already_subscribed' => true,
                    ]),
                ];
            }
        }

        if ($result['status'] >= 500) {
            $decoded = json_decode($result['body'], true);
            $message = cms_api_error_message(is_array($decoded) ? $decoded : null, '');
            if ($message === '') {
                $message = 'Unable to subscribe right now. Please try again shortly.';
            }

            return [
                'status' => 502,
                'body' => json_encode([
                    'success' => false,
                    'message' => $message,
                ]),
            ];
        }

        return $result;
    }
}
