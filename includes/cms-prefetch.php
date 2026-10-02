<?php

declare(strict_types=1);

if (!defined('BRANCH_ID')) {
    require_once dirname(__DIR__) . '/proxy/config.php';
}

if (!function_exists('cmsPrefetchPankiGlobal')) {
    function cmsPrefetchPankiGlobal(): void
    {
    }
}
