<?php
$sessions = include dirname(__DIR__) . '/includes/session-api.php';

return is_array($sessions) ? $sessions : [];
