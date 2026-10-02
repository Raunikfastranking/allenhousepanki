<?php

declare(strict_types=1);

require_once __DIR__ . '/cms-page-content.php';

if (!function_exists('cmsExtractBlogItems')) {
    function cmsExtractBlogItems(?array $response): array
    {
        if (!is_array($response)) {
            return [];
        }

        if (isset($response['data']['data']) && is_array($response['data']['data'])) {
            return $response['data']['data'];
        }

        if (isset($response['data']) && is_array($response['data']) && array_is_list($response['data'])) {
            return $response['data'];
        }

        return [];
    }
}

if (!function_exists('cmsNormalizeBlogItem')) {
    function cmsNormalizeBlogItem(array $blog): array
    {
        $firstDetail = null;
        foreach ($blog['blogdetails'] ?? [] as $detail) {
            if (is_array($detail)) {
                $firstDetail = $detail;
                break;
            }
        }

        $title = function_exists('cmsBlogPostTitle')
            ? cmsBlogPostTitle($blog, $firstDetail)
            : (string) ($blog['title'] ?? $blog['main_title'] ?? $blog['post_title'] ?? '');
        if ($title === 'Blog') {
            $title = '';
        }

        $mainTitle = trim((string) ($blog['main_title'] ?? ''));
        if ($mainTitle === '') {
            $mainTitle = $title;
        }
        $mainDescription = trim((string) ($blog['main_description'] ?? ''));
        if ($mainDescription === '') {
            $mainDescription = (string) ($blog['description'] ?? $blog['excerpt'] ?? '');
        }

        $postImage = function_exists('cmsBlogDetailFirstMediaRaw') ? cmsBlogDetailFirstMediaRaw($blog) : (string) ($blog['image_url'] ?? '');
        $postImageResolved = $postImage !== '' && function_exists('cmsBlogDetailImageUrl') ? cmsBlogDetailImageUrl($postImage) : '';
        $details = function_exists('cmsNormalizeBlogDetailsForDisplay')
            ? cmsNormalizeBlogDetailsForDisplay($blog)
            : ($blog['blogdetails'] ?? []);

        $date = (string) ($blog['date'] ?? $blog['created_at'] ?? '');
        $timestamp = $date !== '' ? strtotime($date) : false;
        $dateFormatted = $timestamp ? date('d F Y', $timestamp) : '';

        return array_merge($blog, [
            'title' => $title !== '' ? $title : $mainTitle,
            'slug' => cmsBlogPostSlug($blog),
            'description' => $mainDescription,
            'main_title' => $mainTitle,
            'main_description' => $mainDescription,
            'image_url' => $postImage !== '' ? $postImage : (string) ($blog['image_url'] ?? ''),
            'media' => ['urls' => $postImageResolved, 'url' => $postImageResolved],
            'date' => $date,
            'date_formatted' => $dateFormatted,
            'blogdetails' => $details !== [] ? $details : ($blog['blogdetails'] ?? []),
        ]);
    }
}

if (!function_exists('cmsBlogDetailContext')) {
    /**
     * @return array{matched: ?array, others: list<array>}
     */
    function cmsBlogDetailContext(string $slug): array
    {
        $slug = trim(rawurldecode($slug));
        $allBlogs = function_exists('cmsBlogListForDisplay') ? cmsBlogListForDisplay() : cmsBlogPostsListResolved();
        $matched = null;

        foreach ($allBlogs as $blog) {
            if (cmsBlogPostSlug($blog) === $slug) {
                $matched = $blog;
                break;
            }
        }

        if (function_exists('cmsFetchBlogBySlug')) {
            $full = cmsFetchBlogBySlug($slug);
            if (is_array($full)) {
                $matched = is_array($matched) ? array_merge($matched, $full) : $full;
            }
        }

        $others = [];
        foreach ($allBlogs as $blog) {
            if (cmsBlogPostSlug($blog) === $slug) {
                continue;
            }
            if (($blog['title'] ?? '') === '') {
                continue;
            }
            $others[] = $blog;
        }

        return [
            'matched' => $matched,
            'others' => $others,
        ];
    }
}

if (!function_exists('cmsFindBlogBySlug')) {
    function cmsFindBlogBySlug(string $slug): ?array
    {
        $slug = trim(rawurldecode($slug));
        if ($slug === '') {
            return null;
        }

        if (function_exists('cmsFetchBlogBySlug')) {
            $blog = cmsFetchBlogBySlug($slug);
            if (is_array($blog)) {
                return $blog;
            }
        }

        $source = function_exists('cmsBlogListForDisplay') ? cmsBlogListForDisplay() : cmsBlogPostsListResolved();
        foreach ($source as $item) {
            if (cmsBlogPostSlug($item) === $slug) {
                return $item;
            }
        }

        return null;
    }
}
