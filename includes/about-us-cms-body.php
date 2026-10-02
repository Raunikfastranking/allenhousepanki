<?php

declare(strict_types=1);

require_once __DIR__ . '/encoding.php';

/** @var array|null $s_welcome */
/** @var array|null $s_vision */
/** @var array|null $s_mission */
/** @var array|null $s_principal */
/** @var array|null $s_carousel1 */
/** @var array|null $s_carousel2 */
/** @var array|null $s_carousel3 */

if (!function_exists('cmsAboutCarouselImage')) {
    function cmsAboutCarouselImage(array $item): string
    {
        return cmsAssetUrl((string) ($item['image_url'] ?? $item['media_url'] ?? $item['media_file'] ?? $item['urls'] ?? ''));
    }
}

if (!function_exists('cmsAboutCarouselItems')) {
    /**
     * Carousel sections use resolved_content.items; card sections store slides in resolved_content.media.
     *
     * @return list<array<string, mixed>>
     */
    function cmsAboutCarouselItems(array $section): array
    {
        $resolved = is_array($section['resolved_content'] ?? null) ? $section['resolved_content'] : [];
        if (is_array($resolved['items'] ?? null) && $resolved['items'] !== []) {
            return $resolved['items'];
        }
        if (is_array($resolved['media'] ?? null) && $resolved['media'] !== []) {
            return $resolved['media'];
        }
        foreach (['gallery_json_data'] as $key) {
            $raw = $section[$key] ?? $resolved[$key] ?? null;
            if (!is_string($raw) || trim($raw) === '' || trim($raw) === '[]') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return $decoded;
            }
        }

        return [];
    }
}

if (!function_exists('cmsAboutHasVisibleText')) {
    function cmsAboutHasVisibleText(string $content): bool
    {
        return trim(strip_tags($content)) !== '';
    }
}

if (!function_exists('cmsAboutPrepareHtml')) {
    function cmsAboutPrepareHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($html) : $html;
    }
}

if (!function_exists('cmsAboutIsInternalCarouselHeading')) {
    /** CMS titles like "Our Story 2 Rooma" should not render publicly. */
    function cmsAboutIsInternalCarouselHeading(string $plain): bool
    {
        return (bool) preg_match('/^\s*our\s+story\s*2\b/i', $plain);
    }
}

if (!function_exists('cmsAboutCarouselHeadingHtml')) {
    function cmsAboutCarouselHeadingHtml(array $section, array $resolved = [], bool $allowResolvedHeading = true): string
    {
        $heading = trim((string) ($section['content_heading'] ?? ''));
        $plainFromHeading = function_exists('cmsPrepareCmsPlainText')
            ? cmsPrepareCmsPlainText($heading)
            : trim(strip_tags($heading));

        if ($heading !== '' && preg_match('/<h[1-6]\b/i', $heading)) {
            if (cmsAboutIsInternalCarouselHeading($plainFromHeading)) {
                return '';
            }

            return cmsAboutPrepareHtml($heading);
        }

        $plain = $allowResolvedHeading ? trim((string) ($resolved['heading'] ?? '')) : '';
        if ($plain === '') {
            $plain = $plainFromHeading;
        }
        if ($plain === '' || cmsAboutIsInternalCarouselHeading($plain)) {
            return '';
        }

        return '<h2 class="text-[30px] font-[700] sm:text-white text-blue-main sm:text-left text-center mt-4 hr-line relative">'
            . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</h2>';
    }
}

if (!function_exists('cmsAboutSanitizeCarouselDesc')) {
    function cmsAboutSanitizeCarouselDesc(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (preg_match('/<div[^>]*\bql-editor\b[^>]*>(.*?)<\/div>/is', $html, $match)) {
            $inner = trim($match[1]);
            if (cmsAboutHasVisibleText($inner)) {
                $html = $inner;
            } else {
                return '';
            }
        }

        // Heading is output separately via cmsAboutCarouselHeadingHtml().
        $html = preg_replace('/<h[1-6]\b[^>]*>[\s\S]*?<\/h[1-6]>/i', '', $html) ?? $html;
        $html = trim($html);
        if ($html === '' || !cmsAboutHasVisibleText($html)) {
            return '';
        }

        return cmsAboutPrepareHtml($html);
    }
}

if (!function_exists('cmsAboutWidthWrapClass')) {
    function cmsAboutWidthWrapClass(): string
    {
        return '2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-4';
    }
}
?>

<?php if ($s_welcome): ?>
<div class="<?= cmsAboutWidthWrapClass() ?> mx-4 sm:py-5 py-0 sm:p-20 p-0">
    <div class="text-center">
        <?php if (!empty($s_welcome['content_heading'])): ?>
            <?= cmsAboutPrepareHtml((string) $s_welcome['content_heading']) ?>
        <?php endif; ?>
        <?php if (!empty($s_welcome['content'])): ?>
        <div class="ql-snow mt-4">
            <div class="ql-editor">
                <?= cmsAboutPrepareHtml((string) $s_welcome['content']) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($s_vision && !empty($s_vision['columns'])):
    $vis_text = $s_vision['columns'][0] ?? null;
    $vis_image = $s_vision['columns'][1] ?? null;
?>
<div class="mt-[-80px] <?= cmsAboutWidthWrapClass() ?> sm:mt-3 bg-center sm:mb-10 mb-0 sm:px-20 p-0 relative">
    <div class="sm:flex gap-10 items-center">
        <div class="pb-0 sm:pt-0 pt-[100px] sm:w-[50%]">
            <div class="sm:text-left text-center">
                <?php if (is_array($vis_text) && !empty($vis_text['content_heading'])): ?>
                    <?= cmsAboutPrepareHtml((string) $vis_text['content_heading']) ?>
                <?php endif; ?>
                <?php if (is_array($vis_text) && !empty($vis_text['content'])): ?>
                <div class="ql-snow">
                    <div class="ql-editor">
                        <?= cmsAboutPrepareHtml((string) $vis_text['content']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="sm:w-[50%]">
            <?php if (is_array($vis_image) && !empty($vis_image['image_url'])): ?>
            <img src="<?= htmlspecialchars(cmsAssetUrl((string) $vis_image['image_url']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($vis_text['title'] ?? 'Vision'), ENT_QUOTES, 'UTF-8') ?>" class="w-full" loading="lazy">
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($s_mission && !empty($s_mission['columns'])):
    $mis_image = $s_mission['columns'][0] ?? null;
    $mis_text = $s_mission['columns'][1] ?? null;
?>
<div class="mt-[-80px] <?= cmsAboutWidthWrapClass() ?> sm:mt-0 bg-center sm:mb-10 mb-0 sm:px-20 p-0 relative">
    <div class="flex sm:flex-row flex-col-reverse items-center gap-10">
        <div class="sm:w-[50%]">
            <?php if (is_array($mis_image) && !empty($mis_image['image_url'])): ?>
            <img src="<?= htmlspecialchars(cmsAssetUrl((string) $mis_image['image_url']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($mis_text['title'] ?? 'Mission'), ENT_QUOTES, 'UTF-8') ?>" class="w-full" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="pb-0 sm:pt-0 pt-[100px] sm:w-[50%]">
            <div class="sm:text-left text-center">
                <?php if (is_array($mis_text) && !empty($mis_text['content_heading'])): ?>
                    <?= cmsAboutPrepareHtml((string) $mis_text['content_heading']) ?>
                <?php endif; ?>
                <?php if (is_array($mis_text) && !empty($mis_text['content'])): ?>
                <div class="ql-snow">
                    <div class="ql-editor">
                        <?= cmsAboutPrepareHtml((string) $mis_text['content']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($s_principal && !empty($s_principal['columns'])):
    $pr_text = $s_principal['columns'][0] ?? null;
    $pr_image = $s_principal['columns'][1] ?? null;
?>
<div class="mt-8 <?= cmsAboutWidthWrapClass() ?>">
    <div class="mt-10 relative">
        <div class="sm:flex gap-10">
            <div class="sm:w-[50%]">
                <?php if (is_array($pr_text) && !empty($pr_text['content_heading'])): ?>
                    <?= cmsAboutPrepareHtml((string) $pr_text['content_heading']) ?>
                <?php endif; ?>
                <?php if (is_array($pr_text) && !empty($pr_text['content'])): ?>
                <div class="ql-snow">
                    <div class="ql-editor">
                        <?= cmsAboutPrepareHtml((string) $pr_text['content']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="sm:w-[50%] mt-4">
                <?php if (is_array($pr_image) && !empty($pr_image['image_url'])): ?>
                <img src="<?= htmlspecialchars(cmsAssetUrl((string) $pr_image['image_url']), ENT_QUOTES, 'UTF-8') ?>"
                    class="border-[1px] border-gray-100 w-full" alt="Principal" loading="lazy">
                <?php endif; ?>
                <?php if (is_array($pr_image) && !empty($pr_image['content'])): ?>
                <div class="mt-1">
                    <?= cmsAboutPrepareHtml((string) $pr_image['content']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($s_carousel1 && !empty($s_carousel1['resolved_content']['items'])):
    $c1 = $s_carousel1['resolved_content'];
    $c1_items = $c1['items'];
    $c1_desc = cmsAboutSanitizeCarouselDesc((string) ($s_carousel1['content'] ?? $c1['content'] ?? ''));
    $c1_has_desc = cmsAboutHasVisibleText($c1_desc);
    $c1_heading = cmsAboutCarouselHeadingHtml($s_carousel1, is_array($c1) ? $c1 : []);
?>
<div class="mb-5 ab-cr-bg sm:mt-10 sm:p-4 cms-about-story-band">
    <div class="<?= cmsAboutWidthWrapClass() ?>">
        <div class="glide relative about-carousel1 opne-hide-circle cms-about-glide" data-cms-dedicated-glide="1">
            <?php if ($c1_heading !== ''): ?>
            <div class="mx-4"><?= $c1_heading ?></div>
            <?php endif; ?>
            <div class="glide__track overflow-hidden mt-5 mx-4" data-glide-el="track">
                <ul class="glide__slides">
                    <?php foreach ($c1_items as $item):
                        $img_url = cmsAboutCarouselImage(is_array($item) ? $item : []);
                        if ($img_url === '') {
                            continue;
                        }
                    ?>
                    <li class="glide__slide">
                        <img src="<?= htmlspecialchars($img_url, ENT_QUOTES, 'UTF-8') ?>" class="w-full max-w-full m-auto" alt="" loading="lazy">
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                <button class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </div>
        <?php if ($c1_has_desc): ?>
        <div class="bg-transparent sm:bg-blue-main px-4 mt-0 pt-5 sm:mt-[-140px] sm:pt-[160px] pb-5 text-inherit sm:text-white cms-about-story-copy">
            <div class="ql-snow"><div class="ql-editor about-carousel-desc"><?= $c1_desc ?></div></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($s_carousel2 && !empty($s_carousel2['resolved_content']['items'])):
    $c2 = $s_carousel2['resolved_content'];
    $c2_items = $c2['items'];
    $c2_desc = cmsAboutSanitizeCarouselDesc((string) ($s_carousel2['content'] ?? ''));
    $c2_has_desc = cmsAboutHasVisibleText($c2_desc);
    $c2_heading = cmsAboutCarouselHeadingHtml($s_carousel2, is_array($c2) ? $c2 : [], false);
?>
<div class="<?= cmsAboutWidthWrapClass() ?> mb-5">
    <div class="glide relative about-carousel2 opne-hide-circle cms-about-glide" data-cms-dedicated-glide="1">
        <?php if ($c2_heading !== ''): ?>
        <div><?= $c2_heading ?></div>
        <?php endif; ?>
        <div class="glide__track overflow-hidden mt-5" data-glide-el="track">
            <ul class="glide__slides">
                <?php foreach ($c2_items as $item):
                    $img_url = cmsAboutCarouselImage(is_array($item) ? $item : []);
                    if ($img_url === '') {
                        continue;
                    }
                ?>
                <li class="glide__slide">
                    <img src="<?= htmlspecialchars($img_url, ENT_QUOTES, 'UTF-8') ?>" class="w-full max-w-full m-auto" alt="" loading="lazy">
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
            <button class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                <i class="fa-solid fa-angle-left"></i>
            </button>
            <button class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                <i class="fa-solid fa-angle-right"></i>
            </button>
        </div>
    </div>
    <?php if ($c2_has_desc): ?>
    <div class="mt-3 ql-snow"><div class="ql-editor about-carousel-desc"><?= $c2_desc ?></div></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($s_carousel3):
    $c3 = is_array($s_carousel3['resolved_content'] ?? null) ? $s_carousel3['resolved_content'] : [];
    $c3_items = cmsAboutCarouselItems($s_carousel3);
    $c3_desc = cmsAboutSanitizeCarouselDesc((string) ($s_carousel3['content'] ?? ''));
    $c3_has_desc = cmsAboutHasVisibleText($c3_desc);
    // Second innovative-spaces band is a continuation carousel — no public heading in CMS.
    $c3_heading = trim((string) ($s_carousel3['content_heading'] ?? '')) !== ''
        ? cmsAboutCarouselHeadingHtml($s_carousel3, $c3)
        : '';
    $c3_has_items = $c3_items !== [];
    if ($c3_has_items || $c3_has_desc || $c3_heading !== ''):
?>
<div class="mb-5 ab-cr-bg sm:mt-10 sm:p-4 cms-about-beyond-band">
    <div class="<?= cmsAboutWidthWrapClass() ?>">
        <?php if ($c3_has_items): ?>
        <?php if ($c3_heading !== ''): ?>
        <div class="px-4"><?= $c3_heading ?></div>
        <?php endif; ?>
        <div class="glide relative glide-03 opne-hide-circle cms-about-glide px-4" data-cms-dedicated-glide="1">
            <div class="glide__track overflow-hidden mt-5" data-glide-el="track">
                <ul class="glide__slides">
                    <?php foreach ($c3_items as $item):
                        $img_url = cmsAboutCarouselImage(is_array($item) ? $item : []);
                        if ($img_url === '') {
                            continue;
                        }
                    ?>
                    <li class="glide__slide">
                        <img src="<?= htmlspecialchars($img_url, ENT_QUOTES, 'UTF-8') ?>" class="w-full max-w-full m-auto" alt="" loading="lazy">
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                <button class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($c3_has_desc): ?>
        <div class="bg-transparent sm:bg-blue-main sm:px-0 px-4 mt-0 pt-5 sm:mt-5 sm:pt-0 pb-5 text-inherit sm:text-white cms-about-beyond-copy">
            <div class="ql-snow"><div class="ql-editor about-carousel-desc"><?= $c3_desc ?></div></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; endif; ?>
