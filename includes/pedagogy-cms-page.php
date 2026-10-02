<?php

declare(strict_types=1);

require_once __DIR__ . '/readmore.php';
require_once dirname(__DIR__) . '/shared/includes/pedagogy-curriculum.php';

if (!function_exists('cmsPedagogySanitizeHtml')) {
    function cmsPedagogyNormalizePlain(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    }

    function cmsPedagogySanitizeHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        if (preg_match('/^(.*?)(<div class="ql-snow">\s*<div class="ql-editor"[^>]*>([\s\S]*?)<\/div>\s*<\/div>\s*)$/i', $html, $match)) {
            $prefix = trim($match[1]);
            $inner = trim($match[3]);
            if ($prefix !== '' && cmsPedagogyNormalizePlain($prefix) === cmsPedagogyNormalizePlain($inner)) {
                $html = $inner;
            }
        }

        $html = preg_replace('/<span[^>]*\bclass="[^"]*\bql-ui\b[^"]*"[^>]*>\s*<\/span>/i', '', $html) ?? $html;

        while (preg_match('/<div class="ql-snow">\s*<div class="ql-editor"[^>]*>([\s\S]*?)<\/div>\s*<\/div>/i', $html, $match)) {
            $html = str_replace($match[0], $match[1], $html);
        }

        $html = preg_replace('/<p[^>]*>\s*(?:&nbsp;|\xC2\xA0|<br\s*\/?>)?\s*<\/p>/i', '', $html) ?? $html;
        $html = preg_replace('/<ol[^>]*>\s*(?:<li[^>]*>\s*<\/li>\s*)+<\/ol>/i', '', $html) ?? $html;
        $html = preg_replace('/<ul[^>]*>\s*(?:<li[^>]*>\s*<\/li>\s*)+<\/ul>/i', '', $html) ?? $html;

        return trim($html);
    }
}

if (!function_exists('cmsPedagogyPrepareHtml')) {
    function cmsPedagogyPrepareHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($html) : $html;

        return cmsPedagogySanitizeHtml($html);
    }
}

if (!function_exists('cmsPedagogySplitModelHeading')) {
    /**
     * @return array{0: string, 1: string} title html, subtitle html below carousel
     */
    function cmsPedagogySplitModelHeading(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return ['', ''];
        }

        if (!preg_match('/^(<h[12][^>]*>[\s\S]*?<\/h[12]>)([\s\S]*)$/i', $html, $match)) {
            return [$html, ''];
        }

        return [trim($match[1]), cmsPedagogySanitizeHtml(trim($match[2]))];
    }
}

if (!function_exists('cmsPedagogyWidthWrapClass')) {
    function cmsPedagogyWidthWrapClass(): string
    {
        return '2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-3';
    }
}

if (!function_exists('cmsPedagogySectionKey')) {
    function cmsPedagogySectionKey(string $title): string
    {
        $key = strtolower(trim($title));
        $key = (string) preg_replace('/\s+section.*$/i', '', $key);

        return trim($key);
    }
}

if (!function_exists('cmsPedagogyIsCarouselSection')) {
    function cmsPedagogyIsCarouselSection(array $section): bool
    {
        $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
        if ($layout === 'carousel' || str_contains($layout, 'carousel')) {
            return true;
        }

        $items = $section['resolved_content']['items'] ?? [];

        return is_array($items) && $items !== [] && empty($section['columns']);
    }
}

if (!function_exists('cmsPedagogyIsColumnSection')) {
    function cmsPedagogyIsColumnSection(array $section): bool
    {
        $columns = $section['columns'] ?? [];

        return is_array($columns) && count($columns) >= 2;
    }
}

if (!function_exists('cmsPedagogyIsIntroOnlySection')) {
    function cmsPedagogyIsIntroOnlySection(array $section): bool
    {
        if (function_exists('cmsSectionIsHero') && cmsSectionIsHero($section)) {
            return false;
        }
        if (cmsPedagogyIsCarouselSection($section) || cmsPedagogyIsColumnSection($section)) {
            return false;
        }

        $content = trim((string) ($section['content'] ?? ''));
        $heading = trim((string) ($section['content_heading'] ?? ''));

        return $content !== '' || $heading !== '';
    }
}

if (!function_exists('cmsPedagogyPrepareIntroMerge')) {
    /**
     * @param list<array<string, mixed>> $sections
     * @return array{skip: array<int, true>, intro_for: array<int, array<string, mixed>>}
     */
    function cmsPedagogyPrepareIntroMerge(array $sections): array
    {
        $skip = [];
        $introFor = [];

        foreach ($sections as $index => $section) {
            if (!is_array($section) || !cmsPedagogyIsIntroOnlySection($section)) {
                continue;
            }
            $key = cmsPedagogySectionKey((string) ($section['title'] ?? ''));
            if ($key === '') {
                continue;
            }
            for ($j = $index + 1, $count = count($sections); $j < $count; $j++) {
                $next = $sections[$j];
                if (!is_array($next)) {
                    continue;
                }
                if (cmsPedagogySectionKey((string) ($next['title'] ?? '')) !== $key) {
                    break;
                }
                if (cmsPedagogyIsColumnSection($next)) {
                    $skip[$index] = true;
                    $introFor[$j] = $section;
                    break;
                }
            }
        }

        return ['skip' => $skip, 'intro_for' => $introFor];
    }
}

if (!function_exists('cmsPedagogyCarouselMedias')) {
    /** @return list<array<string, mixed>> */
    function cmsPedagogyCarouselMedias(array $section): array
    {
        return function_exists('cmsCarouselMedias') ? cmsCarouselMedias($section) : [];
    }
}

if (!function_exists('cmsPedagogyRenderCarousel')) {
    function cmsPedagogyRenderCarousel(array $medias, string $glideClass, string $alt): void
    {
        if ($medias === []) {
            return;
        }
        ?>
        <div class="glide relative w-full bg-white rounded <?= htmlspecialchars($glideClass, ENT_QUOTES, 'UTF-8') ?> cms-pedagogy-glide opne-hide-circle mt-4" data-cms-dedicated-glide="1">
            <div class="glide__track overflow-hidden" data-glide-el="track">
                <ul class="glide__slides relative w-full overflow-hidden p-0 whitespace-no-wrap flex flex-no-wrap [backface-visibility: hidden] [transform-style: preserve-3d] [touch-action: pan-Y] [will-change: transform]">
                    <?php foreach ($medias as $imgData):
                        $src = $imgData['urls'] ?? '';
                        if ($src === '') {
                            continue;
                        }
                        ?>
                    <li class="glide__slide mb-4">
                        <div class="text-center">
                            <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                                 class="mb-2 mx-auto w-full"
                                 loading="lazy">
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="absolute left-0 sm:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                <button class="inline-flex items-center justify-center relative sm:left-[-66px] hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button class="inline-flex items-center justify-center relative sm:right-[-66px] hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsPedagogyNormalizeBulletLists')) {
    /** Merge Quill-style separate <ol><li></li></ol> blocks into one list. */
    function cmsPedagogyNormalizeBulletLists(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (!preg_match_all('/<ol[^>]*>\s*<li[^>]*>[\s\S]*?<\/li>\s*<\/ol>/i', $html, $blocks)) {
            return $html;
        }
        if (count($blocks[0]) < 2) {
            return $html;
        }

        $items = [];
        foreach ($blocks[0] as $block) {
            if (preg_match('/<li[^>]*>([\s\S]*?)<\/li>/i', $block, $match)) {
                $item = trim($match[1]);
                if ($item !== '') {
                    $items[] = $item;
                }
            }
        }
        if (count($items) < 2) {
            return $html;
        }

        $merged = '<ul class="pl-4 list-disc pedagogy-highlights-list" style="list-style:disc !important;">';
        foreach ($items as $item) {
            $item = preg_replace('/<span class="ql-ui"><\/span>/i', '', $item) ?? $item;
            $item = preg_replace('/<span[^>]*>([\s\S]*?)<\/span>/i', '$1', $item) ?? $item;
            $merged .= '<li><p class="text-gray-600 text-[16px] mt-3">' . trim($item) . '</p></li>';
        }
        $merged .= '</ul>';

        $stripped = preg_replace(
            '/(?:<ol[^>]*>\s*<li[^>]*>[\s\S]*?<\/li>\s*<\/ol>\s*(?:<p>\s*(?:<br\s*\/?>)?\s*<\/p>\s*)*)+/i',
            '',
            $html
        );
        $stripped = trim((string) $stripped);

        return $merged . ($stripped !== '' ? "\n" . $stripped : '');
    }
}

if (!function_exists('cmsPedagogyRenderHighlightsBody')) {
    function cmsPedagogyRenderHighlightsBody(string $html): void
    {
        $html = cmsPedagogyNormalizeBulletLists($html);
        if (trim(strip_tags($html)) === '') {
            return;
        }

        $listHtml = '';
        $footer = '';
        if (preg_match('/^(<ul[^>]*>[\s\S]*?<\/ul>)([\s\S]*)$/i', $html, $parts)) {
            $listHtml = trim($parts[1]);
            $footer = trim($parts[2]);
        } else {
            $listHtml = $html;
        }

        if ($listHtml !== '' && trim(strip_tags($listHtml)) !== '') {
            echo '<div class="mt-3 sm:mt-1 pedagogy-curriculum-bullets pedagogy-highlights-body">';
            echo '<div class="ql-snow"><div class="ql-editor" style="padding:0;">' . $listHtml . '</div></div>';
            echo '</div>';
        }

        if ($footer !== '' && trim(strip_tags($footer)) !== '') {
            echo '<div class="mt-3 pb-5 pedagogy-curriculum-bullets pedagogy-highlights-readmore">';
            pedagogyRenderReadMoreHtml($footer, 24);
            echo '</div>';
        }
    }
}

if (!function_exists('cmsPedagogySplitReadMoreHtml')) {
    /**
     * @return array{0: string, 1: string} visible preview, collapsible remainder
     */
    function cmsPedagogySplitReadMoreHtml(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return ['', ''];
        }

        if (preg_match('/^(.*?<\/p>)([\s\S]+)$/is', $html, $match) && trim(strip_tags($match[2])) !== '') {
            return [trim($match[1]), trim($match[2])];
        }

        if (preg_match('/^(.*?<\/li>)([\s\S]+)$/is', $html, $match) && trim(strip_tags($match[2])) !== '') {
            $visible = trim($match[1]);
            $hidden = trim($match[2]);
            if (preg_match('/<li/i', $visible) && !preg_match('/<\/ol>/i', $visible)) {
                $visible .= '</ol>';
            }
            if (preg_match('/<li/i', $visible) && !preg_match('/<ol/i', $visible)) {
                $visible = '<ol class="pl-4 list-decimal" style="list-style:decimal;">' . $visible;
            }
            if (preg_match('/<li/i', $hidden)) {
                if (!preg_match('/^<ol/i', $hidden)) {
                    $hidden = '<ol class="pl-4 list-decimal" style="list-style:decimal;">' . $hidden;
                }
                if (!preg_match('/<\/ol>\s*$/i', $hidden)) {
                    $hidden .= '</ol>';
                }
            }

            return [$visible, $hidden];
        }

        return ['', $html];
    }
}

if (!function_exists('cmsPedagogyRenderReadMoreBlock')) {
    function cmsPedagogyRenderReadMoreBlock(string $html, int $maxHeight = 220, bool $splitFirst = true): void
    {
        if (trim(strip_tags($html)) === '') {
            return;
        }

        $visible = '';
        $hidden = $html;
        if ($splitFirst) {
            [$visible, $hidden] = cmsPedagogySplitReadMoreHtml($html);
        }

        if ($visible !== '') {
            echo '<div class="mt-3 sm:mt-1 pedagogy-curriculum-bullets">';
            echo '<div class="ql-snow"><div class="ql-editor" style="padding:0;">' . $visible . '</div></div>';
            echo '</div>';
        }

        if (trim(strip_tags($hidden)) === '') {
            return;
        }

        $collapseHeight = $visible !== '' ? 24 : $maxHeight;
        echo '<div class="mt-2 pb-5 pedagogy-curriculum-bullets">';
        pedagogyRenderReadMoreHtml($hidden, $collapseHeight);
        echo '</div>';
    }
}

if (!function_exists('cmsPedagogyRenderReadMoreIntro')) {
    function cmsPedagogyRenderReadMoreIntro(string $html, int $maxHeight = 150): void
    {
        if (trim(strip_tags($html)) === '') {
            return;
        }
        echo '<div class="pedagogy-section-intro mt-3">';
        pedagogyRenderReadMoreHtml($html, $maxHeight);
        echo '</div>';
    }
}

if (!function_exists('cmsPedagogyColumnImageUrl')) {
    function cmsPedagogyColumnImageUrl(array $column): string
    {
        if (!function_exists('cmsAssetUrl')) {
            return (string) ($column['image_url'] ?? $column['media_url'] ?? '');
        }

        return cmsAssetUrl($column['image_url'] ?? $column['image_path'] ?? $column['media_url'] ?? '');
    }
}

if (!function_exists('cmsPedagogyRenderAcademicSection')) {
    function cmsPedagogyRenderAcademicSection(array $section): void
    {
        $headingHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content_heading'] ?? '')));
        $bodyHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content'] ?? '')));
        $medias = cmsPedagogyCarouselMedias($section);
        $alt = function_exists('cmsPrepareCmsPlainText')
            ? cmsPrepareCmsPlainText((string) ($section['title'] ?? 'Academic Programmes'))
            : 'Academic Programmes';
        ?>
        <div class="bg-center mt-8 sm:mt-10">
            <?php if ($headingHtml !== ''): ?>
            <div class="text-left sm:text-center sm:mx-3 relative z-[1] <?= cmsPedagogyWidthWrapClass() ?>">
                <div class="ql-snow"><div class="ql-editor pedagogy-academic-heading" style="padding:0;"><?= $headingHtml ?></div></div>
            </div>
            <?php endif; ?>
            <div class="<?= cmsPedagogyWidthWrapClass() ?> mx-3">
                <?php cmsPedagogyRenderCarousel($medias, 'pedagogy-glide-3view', $alt); ?>
                <?php cmsPedagogyRenderReadMoreBlock($bodyHtml); ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsPedagogyRenderHighlightsSection')) {
    function cmsPedagogyRenderHighlightsSection(array $section): void
    {
        $headingHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content_heading'] ?? '')));
        $bodyHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content'] ?? '')));
        $medias = cmsPedagogyCarouselMedias($section);
        $alt = 'Highlights of the Curriculum';
        ?>
        <div class="bg-center mt-8 sm:mt-10 relative">
            <div class="text-center mx-3 <?= cmsPedagogyWidthWrapClass() ?>">
                <?php if ($headingHtml !== ''): ?>
                <div class="ql-snow"><div class="ql-editor pedagogy-section-heading" style="padding:0;"><?= $headingHtml ?></div></div>
                <?php endif; ?>
            </div>
            <div class="<?= cmsPedagogyWidthWrapClass() ?> mx-3">
                <?php cmsPedagogyRenderCarousel($medias, 'pedagogy-glide-4view', $alt); ?>
                <?php cmsPedagogyRenderHighlightsBody($bodyHtml); ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsPedagogyRenderModelSection')) {
    function cmsPedagogyRenderModelSection(array $section, string $glideClass): void
    {
        $headingHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content_heading'] ?? '')));
        [$titleHtml, $subtitleHtml] = cmsPedagogySplitModelHeading($headingHtml);
        $bodyHtml = cmsPedagogyPrepareHtml(trim((string) ($section['content'] ?? '')));
        $medias = cmsPedagogyCarouselMedias($section);
        $alt = function_exists('cmsPrepareCmsPlainText')
            ? cmsPrepareCmsPlainText((string) ($section['title'] ?? 'Curriculum Model'))
            : 'Curriculum Model';
        ?>
        <div class="mt-8 sm:mt-10 relative sm:mx-0 mx-3">
            <div class="text-center <?= cmsPedagogyWidthWrapClass() ?>">
                <?php if ($titleHtml !== ''): ?>
                <div class="ql-snow"><div class="ql-editor pedagogy-section-heading" style="padding:0;"><?= $titleHtml ?></div></div>
                <?php endif; ?>
                <?php if ($subtitleHtml !== '' && trim(strip_tags($subtitleHtml)) !== ''): ?>
                <div class="ql-snow mt-3 mx-3">
                    <div class="ql-editor pedagogy-section-intro pedagogy-section-subtitle" style="padding:0;"><?= $subtitleHtml ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="<?= cmsPedagogyWidthWrapClass() ?>">
                <?php cmsPedagogyRenderCarousel($medias, $glideClass, $alt); ?>
                <?php cmsPedagogyRenderReadMoreBlock($bodyHtml); ?>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsPedagogyRenderFeatureSection')) {
    function cmsPedagogyRenderFeatureSection(array $columnSection, ?array $introSection, bool $reverse = false, bool $plainText = false): void
    {
        $columns = $columnSection['columns'] ?? [];
        if (!is_array($columns) || $columns === []) {
            return;
        }

        $introHeading = '';
        $introBody = '';
        if (is_array($introSection)) {
            $introHeading = cmsPedagogyPrepareHtml(trim((string) ($introSection['content_heading'] ?? '')));
            $introBody = cmsPedagogyPrepareHtml(trim((string) ($introSection['content'] ?? '')));
        }
        if ($introHeading === '') {
            $introHeading = cmsPedagogyPrepareHtml(trim((string) ($columnSection['content_heading'] ?? '')));
        }

        $imageCol = null;
        $textCol = null;
        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }
            $type = strtolower((string) ($column['content_type'] ?? ''));
            if ($type === 'image' && $imageCol === null) {
                $imageCol = $column;
            } elseif ($textCol === null) {
                $textCol = $column;
            }
        }

        $imageUrl = is_array($imageCol) ? cmsPedagogyColumnImageUrl($imageCol) : '';
        $textHtml = '';
        if (is_array($textCol)) {
            $textHtml = cmsPedagogyPrepareHtml(trim((string) ($textCol['content'] ?? '')));
            if ($textHtml === '') {
                $textHtml = cmsPedagogyPrepareHtml(trim((string) ($textCol['content_heading'] ?? '')));
            }
        }

        $flexClass = $reverse ? 'sm:flex-row-reverse' : '';
        ?>
        <div class="mt-8 sm:mt-10">
            <div class="text-center mx-3 <?= cmsPedagogyWidthWrapClass() ?>">
                <?php if ($introHeading !== ''): ?>
                <div class="ql-snow"><div class="ql-editor pedagogy-section-heading" style="padding:0;"><?= $introHeading ?></div></div>
                <?php endif; ?>
                <?php if ($introBody !== ''): ?>
                <?php if ($plainText): ?>
                <div class="ql-snow mt-3"><div class="ql-editor pedagogy-section-intro" style="padding:0;"><?= $introBody ?></div></div>
                <?php else: ?>
                <?php cmsPedagogyRenderReadMoreIntro($introBody, 120); ?>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="mt-5 relative pedagogy-feature-band ab-cr-bg w-full py-8 sm:py-10">
                <div class="relative <?= cmsPedagogyWidthWrapClass() ?> pedagogy-feature-band__inner sm:flex gap-4 sm:gap-8 items-center justify-center <?= $flexClass ?>">
                    <?php if ($imageUrl !== ''): ?>
                    <div class="pedagogy-feature-band__media sm:w-1/2 w-full flex items-center justify-center">
                        <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-auto object-contain" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <?php if ($textHtml !== ''): ?>
                    <div class="pedagogy-feature-band__copy sm:w-1/2 w-full flex items-center">
                        <div class="py-2 sm:py-4 pedagogy-feature-copy w-full">
                            <?php if ($plainText): ?>
                            <div class="ql-snow"><div class="ql-editor text-white text-[16px]" style="padding:0;"><?= $textHtml ?></div></div>
                            <?php else: ?>
                            <?php pedagogyRenderReadMoreHtml($textHtml, 180, true); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsPedagogyRenderCarouselSection')) {
    function cmsPedagogyRenderCarouselSection(array $section, int $carouselIndex): void
    {
        $title = strtolower((string) ($section['title'] ?? ''));

        if ($carouselIndex === 1 || str_contains($title, 'academic programme')) {
            cmsPedagogyRenderAcademicSection($section);
            return;
        }
        if ($carouselIndex === 2 || str_contains($title, 'highlights')) {
            cmsPedagogyRenderHighlightsSection($section);
            return;
        }
        if (str_contains($title, 'brilliance')) {
            cmsPedagogyRenderModelSection($section, 'pedagogy-glide-4view');
            return;
        }
        if (str_contains($title, 'achiever')) {
            cmsPedagogyRenderModelSection($section, 'pedagogy-glide-4view');
            return;
        }

        $parts = pedagogyCurriculumParts($section, false);
        $glideClass = 'pedagogy-glide-4view';
        $alt = function_exists('cmsPrepareCmsPlainText')
            ? cmsPrepareCmsPlainText((string) ($section['title'] ?? 'Pedagogy'))
            : 'Pedagogy';
        pedagogyRenderCurriculumSection($parts, $glideClass, $alt);
    }
}

if (!function_exists('cmsRenderPedagogyPage')) {
    function cmsRenderPedagogyPage(?array $page): void
    {
        if (!is_array($page)) {
            return;
        }

        cmsRenderPageBanner($page, []);
        cmsRenderPageBreadcrumbs($page, []);

        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections) || $sections === []) {
            return;
        }

        $merge = cmsPedagogyPrepareIntroMerge($sections);
        $skip = $merge['skip'];
        $introFor = $merge['intro_for'];

        echo '<div class="cms-pedagogy-page w-full mt-6 mb-10">';

        $carouselIndex = 0;
        $renderedIndices = [];
        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            if (function_exists('cmsSectionIsHero') && cmsSectionIsHero($section)) {
                continue;
            }
            if (!empty($skip[$index])) {
                continue;
            }

            if (cmsPedagogyIsCarouselSection($section)) {
                $carouselIndex++;
                cmsPedagogyRenderCarouselSection($section, $carouselIndex);
                $renderedIndices[] = (int) $index;
                continue;
            }

            if (cmsPedagogyIsColumnSection($section)) {
                $key = cmsPedagogySectionKey((string) ($section['title'] ?? ''));
                $reverse = str_contains($key, 'online library');
                $plainText = str_contains($key, 'teaching and learning') || str_contains($key, 'online library');
                cmsPedagogyRenderFeatureSection($section, $introFor[$index] ?? null, $reverse, $plainText);
                $renderedIndices[] = (int) $index;
                continue;
            }

            $html = function_exists('cmsSectionInnerHtml') ? cmsSectionInnerHtml($section) : '';
            if ($html === '') {
                continue;
            }
            echo '<div class="mt-8 sm:mt-10 ' . cmsPedagogyWidthWrapClass() . ' ql-snow"><div class="ql-editor">' . cmsPedagogyPrepareHtml($html) . '</div></div>';
        }

        echo '</div>';

        if (!empty($renderedIndices) && function_exists('cmsSetRenderedSectionStartIndex')) {
            cmsSetRenderedSectionStartIndex(cmsRenderedStartIndexFromSectionIndices($renderedIndices));
        }
    }
}
