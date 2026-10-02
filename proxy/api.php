<?php
require_once 'config.php';
function fetchApiData($endpoint) {
    $normalized = trim((string) $endpoint);
    $normalized = preg_replace('#^https?://[^/]+/api/#i', '', $normalized) ?? $normalized;
    $normalized = ltrim($normalized, '/');
    $normalized = preg_replace('/SCLID\d+/i', (string) BRANCH_ID, $normalized) ?? $normalized;
    $data = cms_fetch_json_endpoint($normalized);
    if (!is_array($data)) {
        return null;
    }
    return $data;
}
