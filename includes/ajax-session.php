<?php
require_once dirname(__DIR__) . '/proxy/config.php';

$sessions = include __DIR__ . '/session-api.php';
if (!is_array($sessions)) {
    exit;
}

foreach ($sessions as $item) {
    $session = trim((string) ($item['session'] ?? ''));
    if ($session === '') {
        continue;
    }
    echo '<option value="' . htmlspecialchars($session, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($session, ENT_QUOTES, 'UTF-8')
        . '</option>';
}
