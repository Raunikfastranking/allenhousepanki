<?php

declare(strict_types=1);

/**
 * CMS blog detail — slug from ?slug= query; data from apis.php blogData cache.
 */

require_once __DIR__ . '/cms-page-content.php';
require_once __DIR__ . '/cms-page-seo.php';
require_once __DIR__ . '/cms-image-alt.php';

$config = is_array($GLOBALS['cms_blog_detail_config'] ?? null) ? $GLOBALS['cms_blog_detail_config'] : [];
$siteName = (string) ($config['site_name'] ?? 'Allenhouse');
$canonicalHost = rtrim((string) ($config['canonical_host'] ?? ''), '/');
$includesDir = (string) ($config['includes_dir'] ?? '');

$blogSlug = trim((string) ($_GET['slug'] ?? $_GET['blog'] ?? ''));
if ($blogSlug === '') {
    http_response_code(404);
    echo 'Blog not found.';
    return;
}

$currentBlog = null;
$otherBlogs = [];
if (function_exists('cmsBlogDetailContext')) {
    $ctx = cmsBlogDetailContext($blogSlug);
    $currentBlog = is_array($ctx['matched'] ?? null) ? $ctx['matched'] : null;
    $otherBlogs = is_array($ctx['others'] ?? null) ? $ctx['others'] : [];
}
if ($currentBlog === null && function_exists('cmsFindBlogBySlug')) {
    $currentBlog = cmsFindBlogBySlug($blogSlug);
}
if ($otherBlogs === []) {
    $blogSource = function_exists('cmsBlogPostsListResolved')
        ? cmsBlogPostsListResolved()
        : (function_exists('cmsBlogPostsList') ? cmsBlogPostsList() : []);
    foreach ($blogSource as $blog) {
        if (!is_array($blog)) {
            continue;
        }
        $slug = cmsBlogPostSlug($blog);
        if ($slug === '' || $slug === $blogSlug) {
            continue;
        }
        if (function_exists('cmsBlogPostTitle')) {
            $details = $blog['blogdetails'][0] ?? null;
            $title = cmsBlogPostTitle($blog, is_array($details) ? $details : null);
            if ($title === '' || $title === 'Blog') {
                continue;
            }
        }
        $otherBlogs[] = $blog;
    }
}

if ($currentBlog === null) {
    http_response_code(404);
    echo 'Blog not found.';
    return;
}

if (function_exists('cmsFetchBlogBySlug')) {
    $fullBlog = cmsFetchBlogBySlug($blogSlug);
    if (is_array($fullBlog)) {
        $currentBlog = array_merge($currentBlog, $fullBlog);
    }
}

$canonicalPath = '/blog/' . rawurlencode($blogSlug);
$GLOBALS['cms_canonical_path'] = $canonicalPath;
$GLOBALS['cms_current_blog'] = $currentBlog;
$GLOBALS['cms_og_site_name'] = $siteName;
$GLOBALS['cms_og_domain'] = $canonicalHost !== '' ? (string) parse_url($canonicalHost, PHP_URL_HOST) : '';
$seoOptions = [
    'canonical_url' => $canonicalHost !== '' ? $canonicalHost . $canonicalPath : '',
    'includes_dir' => $includesDir,
];
$blogSeo = cmsBlogSeoExtract($currentBlog, $seoOptions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    cmsRenderPageSeoHead($blogSeo);
?>
    <?php if ($canonicalHost !== ''): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalHost . $canonicalPath, ENT_QUOTES, 'UTF-8') ?>" />
    <?php endif; ?>
    <?php include $includesDir . '/head.php'; ?>
</head>
<body>
<?php include $includesDir . '/header.php'; ?>
<div class="main relative sm:top-[20px] sm:mb-[120px] mx-0 sm:mx-2 mb-0">
    <section class="mt-10 mb-10">
        <div class="xl:w-[1280px] lg:w-[1080px] sm:mx-auto mx-4 px-2">
            <?php if (function_exists('cmsRenderBlogDetailContent')) {
                cmsRenderBlogDetailContent($currentBlog);
            } ?>
        </div>
    </section>
    <?php if (function_exists('cmsRenderOtherBlogsSection')) {
        cmsRenderOtherBlogsSection($otherBlogs);
    } ?>
</div>
<?php include $includesDir . '/footer.php'; include $includesDir . '/foot.php'; ?>
</body>
</html>
