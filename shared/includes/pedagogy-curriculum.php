<?php

declare(strict_types=1);

if (!function_exists('renderReadMore')) {
    $includesDir = (string) (($GLOBALS['cms_dynamic_page_config']['includes_dir'] ?? '') ?: '');
    if ($includesDir !== '' && is_file($includesDir . '/readmore.php')) {
        require_once $includesDir . '/readmore.php';
    }
}

if (!function_exists('pedagogySplitCurriculumHtml')) {
    function pedagogySplitCurriculumHtml(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return ['', ''];
        }
        if (preg_match('/<[uo]l/i', $html) && preg_match('/^(.*?)(<[uo]l[\s\S]*)/is', $html, $m)) {
            return [trim($m[1]), trim($m[2])];
        }
        if (preg_match('/^(.*?)(<p>\s*<strong[^>]*>[^<]{2,}:?\s*<\/strong>)/is', $html, $m)) {
            return [trim($m[1]), trim(substr($html, strlen($m[1])))];
        }
        if (preg_match('/^[A-Z][^:\r\n]{0,80}:\s/m', strip_tags($html))) {
            return ['', $html];
        }

        return [$html, ''];
    }
}

if (!function_exists('pedagogyBalanceHtmlFragments')) {
    function pedagogyBalanceHtmlFragments(string $intro, string $bullets): array
    {
        $intro = trim($intro);
        $bullets = trim($bullets);

        if ($bullets !== '') {
            $bullets = preg_replace('/^(?:<\/div>\s*)+/i', '', $bullets);
            $openDivs = preg_match_all('/<div[\s>]/i', $bullets);
            $closeDivs = preg_match_all('/<\/div>/i', $bullets);
            while ($closeDivs > $openDivs && $closeDivs > 0) {
                $bullets = preg_replace('/<\/div>\s*$/s', '', $bullets, 1);
                $closeDivs--;
            }
        }

        if ($intro !== '') {
            $openDivs = preg_match_all('/<div[\s>]/i', $intro);
            $closeDivs = preg_match_all('/<\/div>/i', $intro);
            while ($openDivs > $closeDivs) {
                $intro .= '</div>';
                $closeDivs++;
            }
        }

        return [$intro, $bullets];
    }
}

if (!function_exists('pedagogyCurriculumParts')) {
    function pedagogyCurriculumParts(?array $section, bool $uppercaseHeading = true): array
    {
        if (!is_array($section)) {
            return ['heading' => '', 'intro' => '', 'bullets' => '', 'medias' => []];
        }

        $medias = function_exists('cmsCarouselMedias') ? cmsCarouselMedias($section) : [];
        $contentHeading = trim((string) ($section['content_heading'] ?? ''));
        $content = trim((string) ($section['content'] ?? ''));
        $description = function_exists('cmsSectionHtml') ? trim(cmsSectionHtml($section)) : '';
        if ($description === $content) {
            $description = '';
        }

        $heading = '';
        $intro = '';

        if (preg_match('/(<h2[^>]*>.*?<\/h2>)/is', $contentHeading, $m)) {
            $heading = $m[1];
            $intro = trim((string) preg_replace('/<h2[^>]*>.*?<\/h2>/is', '', $contentHeading));
        } else {
            $lines = preg_split('/\r?\n+/', $contentHeading);
            $title = trim((string) ($lines[0] ?? ''));
            $intro = trim(implode("\n", array_slice($lines, 1)));
            if ($title !== '') {
                $headingClass = $uppercaseHeading
                    ? 'text-[28px] font-[700] leading-7 text-blue-main uppercase'
                    : 'text-[28px] font-[700] leading-7 text-blue-main';
                $heading = '<h2 class="' . $headingClass . '">'
                    . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
            }
        }

        [$contentIntro, $contentBullets] = pedagogySplitCurriculumHtml($content);
        [$descIntro, $descBullets] = pedagogySplitCurriculumHtml($description);

        if ($medias !== []) {
            if ($contentBullets === '' && $contentIntro !== '') {
                $contentBullets = $contentIntro;
                $contentIntro = '';
            }
            if ($descBullets === '' && $descIntro !== '') {
                $descBullets = $descIntro;
                $descIntro = '';
            }
        }

        $introBlock = trim($intro . "\n\n" . $contentIntro . "\n\n" . $descIntro);
        $bullets = $contentBullets !== '' ? $contentBullets : $descBullets;
        [$introBlock, $bullets] = pedagogyBalanceHtmlFragments($introBlock, $bullets);

        if ($introBlock !== '' && strpos($introBlock, '<') === false) {
            $intro = '<p class="text-gray-500 text-[16px] mt-3">' . nl2br(htmlspecialchars($introBlock, ENT_QUOTES, 'UTF-8')) . '</p>';
        } elseif ($introBlock !== '') {
            $intro = '<div class="text-gray-500 text-[16px] mt-3">' . $introBlock . '</div>';
        } else {
            $intro = '';
        }

        if ($bullets !== '' && strpos($bullets, '<') === false) {
            $html = '';
            foreach (preg_split('/\r?\n+/', $bullets) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $html .= '<p class="text-gray-600 text-[16px] mt-2">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            $bullets = $html;
        }

        return [
            'heading' => $heading,
            'intro' => $intro,
            'bullets' => $bullets,
            'medias' => $medias,
        ];
    }
}

if (!function_exists('pedagogyRenderReadMoreHtml')) {
    function pedagogyRenderReadMoreHtml(string $html, int $maxHeight = 220, bool $light = false): void
    {
        if (trim(strip_tags($html)) === '') {
            return;
        }

        $wrapperClass = $light ? 'read-more-wrapper--light' : '';
        renderReadMore(
            '<div class="ql-snow"><div class="ql-editor" style="padding:0;">' . $html . '</div></div>',
            $maxHeight,
            $wrapperClass
        );
    }
}

if (!function_exists('pedagogyRenderCurriculumSection')) {
    function pedagogyRenderCurriculumSection(array $parts, string $glideClass, string $carouselAlt, bool $leftAlign = false): void
    {
        if ($parts['heading'] === '' && $parts['intro'] === '' && $parts['bullets'] === '' && $parts['medias'] === []) {
            return;
        }

        $sectionClass = $leftAlign ? 'pedagogy-curriculum-section--left' : '';
        $headerAlign = $leftAlign ? 'text-left' : 'text-center';
        $bulletsClass = $leftAlign
            ? 'pedagogy-curriculum-bullets pedagogy-curriculum-bullets--left'
            : 'pedagogy-curriculum-bullets';
        ?>
        <div class="mt-8 sm:mt-10 relative sm:mx-0 mx-3 <?= $sectionClass ?>">
            <div class="<?= $headerAlign ?>">
                <?= $parts['heading'] ?>
                <?php if ($parts['intro'] !== ''): ?>
                <div class="ql-snow">
                    <div class="ql-editor" style="padding:0;"><?= $parts['intro'] ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($parts['medias'] !== []): ?>
            <div class="glide relative w-full bg-white rounded <?= htmlspecialchars($glideClass, ENT_QUOTES, 'UTF-8') ?> cms-pedagogy-glide opne-hide-circle mt-4" data-cms-dedicated-glide="1">
                <div class="glide__track overflow-hidden" data-glide-el="track">
                    <ul class="glide__slides relative w-full overflow-hidden p-0 whitespace-no-wrap flex flex-no-wrap [backface-visibility: hidden] [transform-style: preserve-3d] [touch-action: pan-Y] [will-change: transform]">
                        <?php foreach ($parts['medias'] as $img_data):
                            $src = $img_data['urls'] ?? '';
                            if ($src === '') {
                                continue;
                            }
                            ?>
                        <li class="glide__slide mb-4">
                            <div class="text-center">
                                <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($carouselAlt, ENT_QUOTES, 'UTF-8') ?>"
                                     class="mb-2 mx-auto w-full"
                                     loading="lazy">
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                    <button class="inline-flex items-center justify-center relative sm:left-[-66px] hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button class="inline-flex items-center justify-center relative sm:right-[-66px] hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($parts['bullets'] !== ''): ?>
            <div class="mt-4 pb-5 <?= $bulletsClass ?>">
                <?php pedagogyRenderReadMoreHtml($parts['bullets'], 220); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
