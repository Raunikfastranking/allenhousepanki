<?php

/**
 * Beyond-academics two-column layout from CMS. Set $cms_page_slug and optional $cms_page_label before include.
 */

require_once __DIR__ . '/cms-page-helpers.php';

$slug = trim((string) ($cms_page_slug ?? ''));
if ($slug === '') {
    $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
    $slug = function_exists('cmsPankiSlugFromScript') ? cmsPankiSlugFromScript($script) : ($script . '-page-panki');
}

$GLOBALS['cms_page_slug'] = $slug;
$page = cmsFetchPankiPage($slug);
$label = (string) ($cms_page_label ?? cmsPageDisplayTitle($page, ucwords(str_replace('-', ' ', basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php')))));
$row = cmsBeyondAcademicsRow($slug);
$description = (string) ($row['description'] ?? '');
if ($description === '') {
    $description = cmsBeyondAcademicsHtml($slug, 1);
}
$breadcrumb = (string) ($cms_breadcrumb_label ?? $label);
$canonical = (string) ($cms_canonical_path ?? ('/' . basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php')));
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://allenhousepanki.com<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>" />
    <?php
    require_once dirname(__DIR__) . '/shared/includes/cms-page-seo.php';
    cmsResolveAndRenderPageSeo($page, [
        'includes_dir' => __DIR__,
    ]);
    ?>
    <?php include __DIR__ . '/head.php' ?>
</head>

<body>

    <?php include __DIR__ . '/header.php' ?>

    <div class="main relative mb-[40px] sm:mb-[120px]">
        <div class="bg-center flex items-center text-left h-[300px] comman-banner">
            <div>
                <h2 class="text-[32px] sm:hidden block font-[700] text-white text-left mb-5 sm:mb-8 hr-line relative leading-9 pl-4 ">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </h2>
            </div>
            <div class="md:w-[100%]">
                <h2 class="sm:text-[32px] sm:block hidden font-[700] text-white text-left sm:mb-1 hr-line relative leading-9 ml-[7rem]">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </h2>
            </div>
        </div>
        <?php
        if (function_exists('cmsRenderPageBreadcrumbs')) {
            cmsRenderPageBreadcrumbs($page, ['fallback_title' => $breadcrumb]);
        } else {
            cmsRenderBreadcrumbsNav(cmsResolvePageBreadcrumbs($page, ['fallback_title' => $breadcrumb]));
        }
        ?>

        <div class="<?= htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($description !== ''): ?>
                <div class="mt-10 relative">
                    <div class="sm:flex gap-9">
                        <?php if (!empty($row['media']['urls'])): ?>
                            <div class="sm:w-[40%]">
                                <img src="<?= htmlspecialchars((string) $row['media']['urls'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>" class="w-[100%]">
                            </div>
                        <?php endif; ?>
                        <div class="<?= !empty($row['media']['urls']) ? 'sm:w-[60%]' : 'w-full' ?>">
                            <div class="ql-snow cms-rich-text">
                                <div class="ql-editor"><?= strlen($description) > 1095 ? substr($description, 0, 1095) : $description ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (strlen($description) > 1095): ?>
                    <div class="mt-8 ql-snow cms-rich-text">
                        <div class="ql-editor"><?= substr($description, 1095) ?></div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php
                $GLOBALS['cms_page_slug'] = $slug;
                $GLOBALS['cms_skip_auto_sections'] = true;
                cmsRenderPageSections($page, ['skip_hero' => true]);
                ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php' ?>
    <?php include __DIR__ . '/foot.php' ?>

</body>

</html>
