<?php

declare(strict_types=1);

if (!function_exists('cmsPankiCampusTourFallbackVideoSection')) {
    /** @return array<string, mixed> */
    function cmsPankiCampusTourFallbackVideoSection(): array
    {
        if (!function_exists('cmsHomeVideoUrl')) {
            require_once __DIR__ . '/home-page-data.php';
        }

        $videoUrl = cmsHomeVideoUrl();
        if ($videoUrl === '') {
            $videoUrl = 'https://youtu.be/Vb-_k6SIV34';
        }

        return [
            'layout' => 'video',
            'section_type' => 'video',
            'title' => 'Campus Tour Video',
            'resolved_content' => [
                'title' => 'Campus Tour',
                'video_url' => $videoUrl,
                'media' => [
                    ['media_url' => $videoUrl],
                ],
            ],
        ];
    }
}

if (!function_exists('cmsRenderCampusTourPage')) {
    function cmsRenderCampusTourPage(?array $page): void
    {
        if (function_exists('cmsPageHasRenderableContent') && cmsPageHasRenderableContent($page)) {
            cmsRenderPageSections($page, ['skip_hero' => false]);
            return;
        }

        $fallbackPage = [
            'data' => [
                'page_heading' => 'Campus Tour',
                'title' => 'Campus Tour',
                'sections' => [
                    [
                        'layout' => 'content',
                        'section_type' => 'content',
                        'title' => 'Hero Banner Section',
                        'content_heading' => '<h1>Campus Tour</h1>',
                    ],
                ],
            ],
        ];

        cmsRenderPageBanner($fallbackPage, []);
        cmsRenderPageBreadcrumbs($fallbackPage, []);

        echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') . '">';
        cmsRenderSectionVideo(cmsPankiCampusTourFallbackVideoSection());
        echo '</div>';

        if (function_exists('cmsMarkAllPageSectionsRendered')) {
            cmsMarkAllPageSectionsRendered($page);
        }
    }
}
