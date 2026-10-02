<?php

declare(strict_types=1);

if (!function_exists('cmsMediaS3Base')) {
    function cmsMediaS3Base(): string
    {
        return 'https://myschool-assets.s3.ap-south-1.amazonaws.com';
    }
}

if (!function_exists('cmsMediaCmsBase')) {
    function cmsMediaCmsBase(): string
    {
        return rtrim(defined('API_BASE_URL') ? API_BASE_URL : 'https://cms.allenhouseschools.com/', '/');
    }
}

if (!function_exists('cmsMediaEditorImagesBase')) {
    /**
     * Public host for Quill/editor uploads under upload/images/*.
     * Section and page editor files are stored on cms.allenhouseschools.com.
     */
    function cmsMediaEditorImagesBase(): string
    {
        return 'https://cms.allenhouseschools.com';
    }
}

if (!function_exists('cmsNormalizePublicUploadImageUrl')) {
    function cmsNormalizePublicUploadImageUrl(string $path): string
    {
        if (preg_match('#^https://cms\.allenhouseschools\.com/upload/images/(?:sections|pages)/#i', $path)) {
            return (string) preg_replace(
                '#^https://cms\.allenhouseschools\.com#i',
                cmsMediaEditorImagesBase(),
                $path
            );
        }

        return $path;
    }
}

if (!function_exists('cmsNormalizeRawMediaPath')) {
    function cmsNormalizeRawMediaPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        // API JSON sometimes stores slashes as \/
        $path = str_replace(['\\/', '\\\\/'], '/', $path);

        return $path;
    }
}

if (!function_exists('cmsResolveMediaUrl')) {
    /**
     * Resolve CMS/API media paths to a public URL (S3, CMS host, or inline data URI).
     */
    function cmsResolveMediaUrl(?string $url): string
    {
        if ($url === null || trim($url) === '') {
            return '';
        }

        $path = cmsNormalizeRawMediaPath($url);
        if ($path === '') {
            return '';
        }

        // Quill/editor inline images — must never be prefixed with a host.
        if (preg_match('#^data:#i', $path)) {
            return $path;
        }

        if (preg_match('#^blob:#i', $path)) {
            return '';
        }

        $cmsHosts = array_unique([
            cmsMediaCmsBase(),
            'https://cms.allenhouseschools.com',
            'https://cms.allenhouseschools.com',
        ]);
        foreach ($cmsHosts as $host) {
            $prefix = $host . '/';
            if (str_starts_with($path, $prefix)) {
                $rest = substr($path, strlen($prefix));
                if (preg_match('#^https?://#i', $rest)) {
                    return cmsResolveMediaUrl($rest);
                }
            }
        }

        if (preg_match('#^https?://#i', $path)) {
            return cmsNormalizePublicUploadImageUrl($path);
        }

        $relative = ltrim($path, '/');

        // Blog/card/carousel assets stored on S3 (aps/, dps/, all_media/, uploads/).
        if (preg_match('#^(?:aps|dps|all_media|uploads)/#i', $relative)) {
            return cmsMediaS3Base() . '/' . $relative;
        }

        // Editor/section inline images are served from the public CMS CDN host.
        if (preg_match('#^upload/images/#i', $relative)) {
            return cmsMediaEditorImagesBase() . '/' . $relative;
        }

        // Editor uploads sometimes omit leading slash.
        if (preg_match('#^upload/images/#i', $path)) {
            return cmsMediaEditorImagesBase() . '/' . ltrim($path, '/');
        }

        return cmsMediaCmsBase() . '/' . $relative;
    }
}

if (!function_exists('cmsBlogInlineDataUriMaxBytes')) {
    function cmsBlogInlineDataUriMaxBytes(): int
    {
        return 350000;
    }
}

if (!function_exists('cmsBlogIsKeepableInlineDataUri')) {
    function cmsBlogIsKeepableInlineDataUri(string $src): bool
    {
        if (!preg_match('#^data:#i', $src)) {
            return false;
        }

        return strlen($src) <= cmsBlogInlineDataUriMaxBytes();
    }
}

if (!function_exists('cmsBlogPrepareInlineImageSrc')) {
    /**
     * Resolve editor/content inline image src for blog detail HTML.
     */
    function cmsBlogPrepareInlineImageSrc(string $src): ?string
    {
        return cmsPrepareEditorInlineImageSrc($src);
    }
}

if (!function_exists('cmsPrepareEditorInlineImageSrc')) {
    /**
     * Resolve Quill/editor inline image src for any CMS HTML body.
     */
    function cmsPrepareEditorInlineImageSrc(string $src): ?string
    {
        $src = html_entity_decode(trim($src), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($src === '') {
            return null;
        }

        if (preg_match('#^data:#i', $src)) {
            return $src;
        }

        if (preg_match('#^blob:#i', $src)) {
            return null;
        }

        $fixed = cmsResolveMediaUrl($src);
        if ($fixed === '') {
            return null;
        }

        return $fixed;
    }
}
