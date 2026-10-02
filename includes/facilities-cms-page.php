<?php

declare(strict_types=1);

require_once __DIR__ . '/pedagogy-cms-page.php';

if (!function_exists('cmsFacilitiesPrepareHtml')) {
    function cmsFacilitiesPrepareHtml(string $html): string
    {
        return cmsPedagogyPrepareHtml($html);
    }
}

if (!function_exists('cmsFacilitiesWidthWrapClass')) {
    function cmsFacilitiesWidthWrapClass(): string
    {
        return '2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-3';
    }
}

if (!function_exists('cmsFacilitiesSectionKind')) {
    function cmsFacilitiesSectionKind(array $section): string
    {
        $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
        if ($layout === 'carousel' || str_contains($layout, 'carousel')) {
            return 'carousel';
        }
        if ($layout === 'card' || str_contains($layout, 'card')) {
            return 'card';
        }
        $columns = $section['columns'] ?? [];
        if (is_array($columns) && $columns !== []) {
            return 'columns';
        }

        return 'content';
    }
}

if (!function_exists('cmsFacilitiesMapSections')) {
    /**
     * @param list<array<string, mixed>> $sections
     * @return array<string, mixed>
     */
    function cmsFacilitiesMapSections(array $sections): array
    {
        $nonHero = [];
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            if (function_exists('cmsSectionIsHero') && cmsSectionIsHero($section)) {
                continue;
            }
            $nonHero[] = $section;
        }

        $introCarousel = null;
        $plcIntro = null;
        $plcColumns = null;
        $cardsHeading = null;
        $cardSections = [];

        foreach ($nonHero as $section) {
            $kind = cmsFacilitiesSectionKind($section);
            $title = strtolower(trim((string) ($section['title'] ?? '')));

            if ($kind === 'carousel' && $introCarousel === null) {
                $introCarousel = $section;
                continue;
            }
            if ($kind === 'columns') {
                if ($plcColumns === null) {
                    $plcColumns = $section;
                }
                if ($plcIntro === null && str_contains($title, 'personalised')) {
                    $plcIntro = $section;
                }
                continue;
            }
            if ($kind === 'card') {
                $cardSections[] = $section;
                if ($cardsHeading === null) {
                    $heading = trim((string) ($section['content_heading'] ?? ''));
                    $content = trim((string) ($section['content'] ?? ''));
                    if ($heading !== '' || $content !== '') {
                        $cardsHeading = $section;
                    }
                }
                continue;
            }
            if ($kind !== 'content') {
                continue;
            }

            if ($plcIntro === null && str_contains($title, 'personalised')) {
                $plcIntro = $section;
                continue;
            }
            if ($cardsHeading === null && str_contains($title, 'facilit')) {
                $cardsHeading = $section;
            }
        }

        if ($introCarousel === null) {
            $introCarousel = $nonHero[0] ?? null;
        }

        return [
            'intro_carousel' => $introCarousel,
            'plc_intro' => $plcIntro,
            'plc_columns' => $plcColumns,
            'cards_heading' => $cardsHeading,
            'card_sections' => $cardSections,
            'all_sections' => $nonHero,
        ];
    }
}

if (!function_exists('cmsFacilitiesCarouselMedias')) {
    /** @return list<array<string, mixed>> */
    function cmsFacilitiesCarouselMedias(?array $section): array
    {
        if (!is_array($section)) {
            return [];
        }

        if (function_exists('cmsCarouselMedias')) {
            $medias = cmsCarouselMedias($section);
            if ($medias !== []) {
                return $medias;
            }
        }

        $items = $section['resolved_content']['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $medias = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $rawUrl = (string) ($item['image_url'] ?? $item['media_url'] ?? '');
            $url = $rawUrl !== '' && function_exists('cmsAssetUrl') ? cmsAssetUrl($rawUrl) : $rawUrl;
            if ($url === '') {
                continue;
            }
            $medias[] = [
                'urls' => $url,
                'image_url' => $url,
                'title' => $item['title'] ?? '',
                'image_alt' => $item['image_alt'] ?? null,
            ];
        }

        return $medias;
    }
}

if (!function_exists('cmsFacilitiesCollectCards')) {
    /**
     * @param list<array<string, mixed>> $sections
     * @return list<array<string, mixed>>
     */
    function cmsFacilitiesCollectCards(array $sections): array
    {
        $cards = [];
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            if (function_exists('cmsFacilitiesSectionKind') && cmsFacilitiesSectionKind($section) !== 'card') {
                continue;
            }

            $media = $section['resolved_content']['media'] ?? [];
            if (!is_array($media) || $media === []) {
                continue;
            }
            foreach ($media as $item) {
                if (is_array($item)) {
                    $cards[] = $item;
                }
            }
        }

        return $cards;
    }
}

if (!function_exists('cmsFacilitiesResolvePostCarouselBody')) {
    function cmsFacilitiesResolvePostCarouselBody(array $section, string $pageSlug = ''): string
    {
        foreach (['content', 'description'] as $field) {
            $raw = trim((string) ($section[$field] ?? ''));
            if ($raw !== '') {
                return cmsFacilitiesPrepareHtml($raw);
            }
        }

        $resolved = trim((string) ($section['resolved_content']['content'] ?? ''));
        if ($resolved !== '') {
            return cmsFacilitiesPrepareHtml($resolved);
        }

        return '';
    }
}

if (!function_exists('cmsFacilitiesCardParts')) {
    /**
     * @return array{0: string, 1: string} title, body html
     */
    function cmsFacilitiesCardParts(array $item): array
    {
        $headingHtml = trim((string) ($item['heading'] ?? ''));
        $contentHtml = trim((string) ($item['content'] ?? ''));

        $title = '';
        if ($headingHtml !== '') {
            if (preg_match('/<strong>(.*?)<\/strong>/is', $headingHtml, $match)) {
                $title = trim(strip_tags($match[1]));
            } else {
                $title = trim(strip_tags($headingHtml));
            }
        }

        $bodyHtml = $contentHtml;
        if ($title === '' && $contentHtml !== '') {
            $plain = html_entity_decode(strip_tags($contentHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/^([^:\n]+):\s*(.+)$/s', $plain, $match)) {
                $title = trim($match[1]);
                $bodyPlain = trim($match[2]);
                $bodyHtml = $bodyPlain !== '' ? '<p>' . htmlspecialchars($bodyPlain, ENT_QUOTES, 'UTF-8') . '</p>' : '';
            }
        }

        return [$title, cmsFacilitiesPrepareHtml($bodyHtml)];
    }
}

if (!function_exists('cmsFacilitiesColumnImageUrl')) {
    function cmsFacilitiesColumnImageUrl(array $column): string
    {
        $url = (string) ($column['image_url'] ?? $column['image_path'] ?? $column['media_url'] ?? '');

        return $url !== '' && function_exists('cmsAssetUrl') ? cmsAssetUrl($url) : $url;
    }
}

if (!function_exists('cmsRenderFacilitiesPage')) {
    function cmsRenderFacilitiesPage(?array $page): void
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

        $mapped = cmsFacilitiesMapSections($sections);
        $s_intro = $mapped['intro_carousel'];
        $s_plc_intro = $mapped['plc_intro'];
        $s_plc_columns = $mapped['plc_columns'];
        $s_cards_heading = $mapped['cards_heading'];
        $s_card_sections = $mapped['card_sections'];
        $cms_facilities_all_sections = $mapped['all_sections'];
        $cms_facilities_page_slug = trim((string) ($page['data']['slug'] ?? $GLOBALS['cms_page_slug'] ?? ''));

        echo '<div class="cms-facilities-page w-full mt-6 mb-10">';
        require __DIR__ . '/facilities-cms-body.php';
        echo '</div>';

        if (function_exists('cmsMarkPartialPageSectionsRendered')) {
            $rendered = is_array($cms_facilities_all_sections ?? null) ? $cms_facilities_all_sections : array_values(array_filter($mapped));
            cmsMarkPartialPageSectionsRendered($page, $rendered);
        }
    }
}
