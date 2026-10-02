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

if (!function_exists('cms_curl_post_json')) {
    /**
     * @param array<string, mixed> $payload
     * @return array{status: int, body: string}
     */
    function cms_curl_post_json(string $url, array $payload, string $failMessage = 'Request failed'): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => function_exists('api_auth_headers')
                ? api_auth_headers(['Content-Type: application/json', 'Accept: application/json'])
                : ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);
        if (function_exists('apply_curl_ssl_options')) {
            apply_curl_ssl_options($ch);
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [
                'status' => 502,
                'body' => json_encode(['success' => false, 'message' => $failMessage]),
            ];
        }

        $decoded = json_decode((string) $response, true);
        if ($status < 200 || $status >= 300) {
            return [
                'status' => $status > 0 ? $status : 502,
                'body' => json_encode([
                    'success' => false,
                    'message' => cms_api_error_message(is_array($decoded) ? $decoded : null, $failMessage),
                ]),
            ];
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
}
