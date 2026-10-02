<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/environment.php';

$slug = trim((string) ($_GET['slug'] ?? ''), '/');

if ($slug !== '') {
    require_once __DIR__ . '/includes/cms-bootstrap.php';
    require_once __DIR__ . '/shared/includes/cms-page-content.php';

    $requestPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    $isDirectDetailAccess = basename($requestPath) === 'detail.php';

    $isBlog = function_exists('cmsFindBlogBySlug') && cmsFindBlogBySlug($slug) !== null;

    // SEO redirect only for real blog posts opened as /detail.php?slug=...
    // Do NOT redirect CMS pages to the clean URL: on aps.fastranking.cloud,
    // Nginx has no catch-all rewrite, so /{slug} returns nginx 404 unless a .php stub exists.
    if ($isDirectDetailAccess && $isBlog) {
        header('Location: ' . cmsBlogDetailUrl($slug), true, 301);
        exit;
    }

    if ($isBlog || str_starts_with(strtolower($slug), 'blog/')) {
        require __DIR__ . '/includes/cms-blog-detail-page.php';
        return;
    }

    // Nginx may rewrite unknown SEO URLs to detail.php — serve CMS pages from here.
    $_GET['cms_path'] = $slug;
    require __DIR__ . '/cms-page.php';
    return;
}

require __DIR__ . '/includes/cms-blog-detail-page.php';
