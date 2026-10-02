<?php

declare(strict_types=1);

if (!function_exists('cmsAboutMapSections')) {
    /**
     * @param list<array<string, mixed>> $sections
     * @return array<string, array<string, mixed>|null>
     */
    function cmsAboutMapSections(array $sections): array
    {
        $mapped = [
            'welcome' => null,
            'vision' => null,
            'mission' => null,
            'principal' => null,
            'carousel1' => null,
            'carousel2' => null,
            'carousel3' => null,
        ];

        $carouselIndex = 0;
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            if (function_exists('cmsSectionIsHero') && cmsSectionIsHero($section)) {
                continue;
            }

            $title = strtolower(trim((string) ($section['title'] ?? '')));
            $layout = strtolower(trim((string) ($section['layout'] ?? $section['section_type'] ?? '')));
            $items = $section['resolved_content']['items'] ?? [];
            $hasCarousel = $layout === 'carousel' || (is_array($items) && $items !== []);
            $hasColumns = is_array($section['columns'] ?? null) && $section['columns'] !== [];

            if (str_contains($title, 'welcome')) {
                $mapped['welcome'] = $section;
                continue;
            }
            // Word boundary — avoid matching carousel titles like "A Visionary Institution..."
            if (preg_match('/\bvision\b/i', $title) && $hasColumns) {
                $mapped['vision'] = $section;
                continue;
            }
            if (preg_match('/\bmission\b/i', $title) && $hasColumns) {
                $mapped['mission'] = $section;
                continue;
            }
            if (str_contains($title, 'principal') && $hasColumns) {
                $mapped['principal'] = $section;
                continue;
            }
            if (preg_match('/\bvisionary\b/i', $title) && $hasCarousel) {
                $mapped['carousel1'] = $section;
                continue;
            }
            if (preg_match('/\binnovative\b/i', $title) || str_contains($title, 'sports, arts')) {
                if ($mapped['carousel2'] === null) {
                    $mapped['carousel2'] = $section;
                } else {
                    $mapped['carousel3'] = $section;
                }
                continue;
            }
            if ($hasCarousel) {
                $carouselIndex++;
                if ($carouselIndex === 1 || (str_contains($title, 'our story') && !str_contains($title, '2'))) {
                    $mapped['carousel1'] = $section;
                } elseif ($carouselIndex === 2 || str_contains($title, 'story 2')) {
                    $mapped['carousel2'] = $section;
                } else {
                    $mapped['carousel3'] = $section;
                }
            }
        }

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

        $keys = ['welcome', 'vision', 'mission', 'principal', 'carousel1', 'carousel2', 'carousel3'];
        foreach ($keys as $i => $key) {
            if ($mapped[$key] === null && isset($nonHero[$i])) {
                $mapped[$key] = $nonHero[$i];
            }
        }

        return $mapped;
    }
}

if (!function_exists('cmsRenderAboutPage')) {
    function cmsRenderAboutPage(?array $page): void
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

        $mapped = cmsAboutMapSections($sections);
        $s_welcome = $mapped['welcome'];
        $s_vision = $mapped['vision'];
        $s_mission = $mapped['mission'];
        $s_principal = $mapped['principal'];
        $s_carousel1 = $mapped['carousel1'];
        $s_carousel2 = $mapped['carousel2'];
        $s_carousel3 = $mapped['carousel3'];

        echo '<div class="cms-about-page w-full mb-10">';
        require __DIR__ . '/about-us-cms-body.php';
        echo '</div>';

        if (function_exists('cmsMarkPartialPageSectionsRendered')) {
            cmsMarkPartialPageSectionsRendered($page, array_values($mapped));
        }
    }
}
