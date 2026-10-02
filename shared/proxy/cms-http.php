<?php

if (!function_exists('cms_api_error_message')) {
    function cms_api_error_message(?array $decoded, string $fallback): string
    {
        if (!is_array($decoded)) {
            return $fallback;
        }
        if (!empty($decoded['message']) && is_string($decoded['message'])) {
            return $decoded['message'];
        }
        if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
            foreach ($decoded['errors'] as $msgs) {
                if (is_array($msgs) && isset($msgs[0]) && is_string($msgs[0])) {
                    return $msgs[0];
                }
                if (is_string($msgs) && $msgs !== '') {
                    return $msgs;
                }
            }
        }

        return $fallback;
    }
}

if (!function_exists('cms_curl_post_json_urls')) {
    /**
     * @return list<string>
     */
    function cms_curl_post_json_urls(string $url): array
    {
        $urls = [rtrim($url, '/')];
        if (defined('LEGACY_CMS_API_URL') && defined('CMS_API_URL')) {
            $legacy = str_replace(rtrim(CMS_API_URL, '/'), rtrim(LEGACY_CMS_API_URL, '/'), $urls[0]);
            if ($legacy !== $urls[0]) {
                $urls[] = $legacy;
            }
        }

        return array_values(array_unique($urls));
    }
}

if (!function_exists('cms_curl_should_verify_ssl')) {
    function cms_curl_should_verify_ssl(): bool
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');

        return !(
            $host === ''
            || $host === 'localhost'
            || str_starts_with($host, '127.0.0.1')
            || str_starts_with($host, 'localhost:')
        );
    }
}

if (!function_exists('cms_curl_post_json_attempt')) {
    /**
     * @param array<string, mixed> $payload
     * @return array{status: int, body: string, curl_error: string, failed: bool}
     */
    function cms_curl_post_json_attempt(string $url, array $payload, bool $verifySsl): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => api_auth_headers(['Content-Type: application/json', 'Accept: application/json']),
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => $response === false ? '' : (string) $response,
            'curl_error' => $curlError,
            'failed' => $response === false,
        ];
    }
}

if (!function_exists('cms_curl_post_json')) {
    /**
     * @param array<string, mixed> $payload
     * @return array{status: int, body: string}
     */
    function cms_curl_post_json(string $url, array $payload, string $failMessage = 'Request failed'): array
    {
        if (!function_exists('api_auth_headers')) {
            return [
                'status' => 500,
                'body' => json_encode(['success' => false, 'message' => 'API auth is not configured']),
            ];
        }

        $verifySsl = cms_curl_should_verify_ssl();
        $lastFailure = [
            'status' => 502,
            'body' => json_encode(['success' => false, 'message' => $failMessage]),
        ];

        foreach (cms_curl_post_json_urls($url) as $tryUrl) {
            $attempt = cms_curl_post_json_attempt($tryUrl, $payload, $verifySsl);
            if ($attempt['failed'] && $verifySsl) {
                $attempt = cms_curl_post_json_attempt($tryUrl, $payload, false);
            }
            if ($attempt['failed']) {
                continue;
            }

            $response = $attempt['body'];
            $status = (int) $attempt['status'];
            $decoded = json_decode($response, true);

            if ($status >= 400 && $status < 500) {
                return [
                    'status' => $status,
                    'body' => json_encode([
                        'success' => false,
                        'message' => cms_api_error_message(is_array($decoded) ? $decoded : null, $failMessage),
                        'errors' => is_array($decoded['errors'] ?? null) ? $decoded['errors'] : null,
                    ]),
                ];
            }

            if ($status < 200 || $status >= 300) {
                $lastFailure = [
                    'status' => $status > 0 ? $status : 502,
                    'body' => json_encode([
                        'success' => false,
                        'message' => cms_api_error_message(is_array($decoded) ? $decoded : null, $failMessage),
                    ]),
                ];
                continue;
            }

            if (!is_array($decoded)) {
                $decoded = [];
            }
            if (!array_key_exists('success', $decoded)) {
                $decoded['success'] = true;
            }
            if (empty($decoded['message']) && !empty($decoded['data'])) {
                $decoded['message'] = 'Submitted successfully.';
            }

            return [
                'status' => $status,
                'body' => json_encode($decoded),
            ];
        }

        return $lastFailure;
    }
}
