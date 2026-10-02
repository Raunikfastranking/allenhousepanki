<?php

declare(strict_types=1);

/**
 * Portable school root resolution for standalone AWS deploy (school/shared/... layout).
 */

if (!function_exists('cms_school_root')) {
    /**
     * Site root directory (parent of shared/, includes/, proxy/).
     * Works from school/includes, school/proxy, school/shared/includes, or school/shared/cms.
     */
    function cms_school_root(?string $fromDir = null): string
    {
        static $cache = [];
        $fromDir = $fromDir ?? __DIR__;
        $key = realpath($fromDir) ?: $fromDir;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $dir = str_replace('\\', '/', $fromDir);
        if (preg_match('#/shared/includes$#', $dir) || preg_match('#/shared/cms$#', $dir)) {
            $root = dirname($fromDir, 2);
        } elseif (preg_match('#/(includes|proxy)$#', $dir)) {
            $root = dirname($fromDir);
        } else {
            $root = dirname($fromDir);
        }

        $root = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/');
        if (!is_file($root . '/shared/includes/encoding.php')) {
            throw new RuntimeException('School root not found (missing shared/includes/encoding.php): ' . $root);
        }

        $cache[$key] = $root;

        return $root;
    }
}

if (!function_exists('cms_school_shared_path')) {
    function cms_school_shared_path(string $relative = ''): string
    {
        $base = cms_school_root() . '/shared';
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        return $relative === '' ? $base : $base . '/' . $relative;
    }
}
