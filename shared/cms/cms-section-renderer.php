<?php

declare(strict_types=1);

require_once __DIR__ . '/cms-core.php';
require_once dirname(__DIR__) . '/includes/cms-image-alt.php';
require_once dirname(__DIR__) . '/includes/cms-media-url.php';

if (!function_exists('cmsFixMediaUrl')) {
    function cmsFixMediaUrl(?string $url): string
    {
        return cmsResolveMediaUrl($url);
    }
}

if (!function_exists('cmsSectionIsHero')) {
    function cmsSectionIsHero(array $section): bool
    {
        $columns = $section['columns'] ?? [];
        if (is_array($columns) && count($columns) >= 2) {
            return false;
        }

        $body = trim((string) ($section['content'] ?? ''));
        if ($body !== '') {
            $plain = trim(strip_tags(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (strlen($plain) > 80) {
                return false;
            }
        }

        $title = strtolower((string) ($section['title'] ?? ''));
        $layout = strtolower((string) ($section['layout'] ?? ''));
        return str_contains($title, 'hero') || str_contains($title, 'banner') || $layout === 'hero';
    }
}

if (!function_exists('cmsSectionInnerHtml')) {
    function cmsSectionInnerHtml(array $section): string
    {
        $html = '';
        if (function_exists('cmsSectionHtml')) {
            $html = cmsSectionHtml($section);
        }

        if ($html === '') {
            foreach (['content', 'content_heading', 'description'] as $key) {
                if (!empty($section[$key]) && is_string($section[$key])) {
                    $html = $section[$key];
                    break;
                }
            }
        }

        if ($html === '') {
            $resolved = $section['resolved_content'] ?? null;
            if (is_array($resolved)) {
                foreach (['content', 'heading', 'description', 'content_text'] as $key) {
                    if (!empty($resolved[$key]) && is_string($resolved[$key])) {
                        $html = $resolved[$key];
                        break;
                    }
                }
            }
        }

        if ($html === '') {
            return '';
        }

        return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($html) : $html;
    }
}

if (!function_exists('cmsStaticInnerBannerUrl')) {
    function cmsStaticInnerBannerUrl(): string
    {
        return 'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/BjZfX4GkghaM07Y0OjEHqMRC1lCMhvgk95d6M5Lf.webp';
    }
}

if (!function_exists('cmsSectionHeroImageUrl')) {
    function cmsSectionHeroImageUrl(array $section): string
    {
        $directKeys = ['media_url', 'image_url', 'image_path', 'url', 'media_file', 'hero_image'];
        foreach ($directKeys as $key) {
            $raw = trim((string) ($section[$key] ?? ''));
            if ($raw !== '') {
                $url = cmsFixMediaUrl($raw);
                if ($url !== '') {
                    return $url;
                }
            }
        }

        $resolved = $section['resolved_content'] ?? null;
        if (is_array($resolved)) {
            foreach ($directKeys as $key) {
                $raw = trim((string) ($resolved[$key] ?? ''));
                if ($raw !== '') {
                    $url = cmsFixMediaUrl($raw);
                    if ($url !== '') {
                        return $url;
                    }
                }
            }
        }

        $items = cmsSectionGroupedEntries($section, 'media');
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = cmsSectionEntryImageUrl($item);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }
}

if (!function_exists('cmsFirstHeroImageUrl')) {
    function cmsFirstHeroImageUrl(?array $page): string
    {
        if (!is_array($page)) {
            return '';
        }

        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            return '';
        }

        foreach ($sections as $section) {
            if (!is_array($section) || !cmsSectionIsHero($section)) {
                continue;
            }
            $url = cmsSectionHeroImageUrl($section);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }
}

if (!function_exists('cmsPageHeroSectionImageUrl')) {
    function cmsPageHeroSectionImageUrl(?array $page): string
    {
        $url = cmsFirstHeroImageUrl($page);
        if ($url !== '') {
            return $url;
        }

        $homeSlug = function_exists('cmsApiHomePageSlug') ? cmsApiHomePageSlug() : 'home-page';
        $homePage = cmsFetchPageBySlug($homeSlug);
        $url = cmsFirstHeroImageUrl($homePage);
        if ($url !== '') {
            return $url;
        }

        return cmsStaticInnerBannerUrl();
    }
}

if (!function_exists('cmsRenderSectionHero')) {
    function cmsRenderSectionHero(array $section): void
    {
        $html = cmsSectionInnerHtml($section);
        if ($html === '') {
            return;
        }
        $bannerStyle = ' style="background-image:url(\'' . htmlspecialchars(cmsSectionHeroImageUrl($section) ?: cmsStaticInnerBannerUrl(), ENT_QUOTES, 'UTF-8') . '\');background-size:cover;height:350px;"';
        echo '<div class="cms-section cms-section-hero common-banner flex items-center text-center"' . $bannerStyle . '>';
        echo '<div class="w-full cms-hero-inner">' . $html . '</div>';
        echo '</div>';
    }
}

if (!function_exists('cmsRenderSectionCarousel')) {
    function cmsRenderSectionCarousel(array $section): void
    {
        $items = cmsSectionGroupedEntries($section);
        if ($items === []) {
            return;
        }

        cmsRenderSectionIntro($section);
        echo '<section class="cms-section cms-section-carousel mt-10">';
        echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $img = cmsSectionEntryImageUrl($item);
            if ($img === '') {
                continue;
            }
            $alt = cms_image_alt($item, cmsSectionEntryTitle($item));
            echo '<div class="overflow-hidden rounded-xl border border-gray-100 shadow-sm">';
            echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" class="w-full h-56 object-cover" loading="lazy" />';
            echo '</div>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('cmsColumnTextHtml')) {
    function cmsColumnTextHtml(array $column): string
    {
        $candidates = [];
        foreach (['content', 'content_heading', 'title'] as $key) {
            $value = trim((string) ($column[$key] ?? ''));
            if ($value !== '') {
                $candidates[] = $value;
            }
        }

        $resolved = $column['resolved_content'] ?? null;
        if (is_array($resolved)) {
            foreach (['content', 'content_heading', 'heading'] as $key) {
                $value = trim((string) ($resolved[$key] ?? ''));
                if ($value !== '') {
                    $candidates[] = $value;
                }
            }
        }

        foreach ($candidates as $html) {
            if (trim(strip_tags($html)) !== '') {
                return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($html) : $html;
            }
        }

        return '';
    }
}

if (!function_exists('cmsSectionDecodeJsonList')) {
    /**
     * @return list<array<string, mixed>>
     */
    function cmsSectionDecodeJsonList(mixed $raw): array
    {
        if (is_array($raw)) {
            if (isset($raw['items']) && is_array($raw['items'])) {
                return array_values(array_filter($raw['items'], 'is_array'));
            }

            return array_values(array_filter($raw, 'is_array'));
        }
        if (!is_string($raw) || trim($raw) === '' || trim($raw) === '[]') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        if (isset($decoded['items']) && is_array($decoded['items'])) {
            return array_values(array_filter($decoded['items'], 'is_array'));
        }

        return array_values(array_filter($decoded, 'is_array'));
    }
}

if (!function_exists('cmsSectionGroupedEntries')) {
    /**
     * Grouped Elements from CMS — items, media, gallery_json_data, accordion_json_data.
     *
     * @return list<array<string, mixed>>
     */
    function cmsSectionGroupedEntries(array $section, ?string $prefer = null): array
    {
        $resolved = is_array($section['resolved_content'] ?? null) ? $section['resolved_content'] : [];
        $pools = [
            'items' => cmsSectionDecodeJsonList($resolved['items'] ?? $section['items'] ?? $section['section_items'] ?? []),
            'media' => cmsSectionDecodeJsonList($resolved['media'] ?? $section['media'] ?? $section['section_media'] ?? []),
            'gallery' => cmsSectionDecodeJsonList($section['gallery_json_data'] ?? $resolved['gallery_json_data'] ?? null),
            'accordion' => cmsSectionDecodeJsonList($section['accordion_json_data'] ?? $resolved['accordion_json_data'] ?? null),
        ];

        $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
        $order = ['items', 'media', 'gallery', 'accordion'];
        if ($prefer === 'media' || str_contains($layout, 'gallery') || str_contains($layout, 'card')) {
            $order = ['media', 'gallery', 'items', 'accordion'];
        } elseif ($prefer === 'accordion' || str_contains($layout, 'accordion')) {
            $order = ['accordion', 'items', 'media', 'gallery'];
        }

        foreach ($order as $key) {
            if ($pools[$key] !== []) {
                return $pools[$key];
            }
        }

        return [];
    }
}

if (!function_exists('cmsSectionEntryTitle')) {
    function cmsSectionEntryTitle(array $item): string
    {
        foreach (['title', 'heading', 'name', 'label'] as $key) {
            $value = trim((string) ($item[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}

if (!function_exists('cmsSectionEntryBody')) {
    function cmsSectionEntryBody(array $item): string
    {
        foreach (['content', 'description', 'body', 'text', 'content_text'] as $key) {
            $value = trim((string) ($item[$key] ?? ''));
            if ($value !== '') {
                return function_exists('cmsPrepareCmsHtml') ? cmsPrepareCmsHtml($value) : $value;
            }
        }

        return '';
    }
}

if (!function_exists('cmsSectionEntryImageUrl')) {
    function cmsSectionEntryImageUrl(array $item): string
    {
        foreach (['media_url', 'media_file', 'image_url', 'image_path', 'url', 'urls'] as $key) {
            $raw = trim((string) ($item[$key] ?? ''));
            if ($raw === '') {
                continue;
            }
            $url = cmsFixMediaUrl($raw);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }
}

if (!function_exists('cmsRenderSectionIntro')) {
    function cmsRenderSectionIntro(array $section, string $wrapClass = 'mb-6'): void
    {
        $html = cmsSectionInnerHtml($section);
        if ($html === '') {
            return;
        }

        echo '<div class="' . htmlspecialchars($wrapClass, ENT_QUOTES, 'UTF-8') . ' ql-snow"><div class="ql-editor">' . $html . '</div></div>';
    }
}

if (!function_exists('cmsRenderSectionColumns')) {
    function cmsRenderSectionColumns(array $section): void
    {
        $columns = $section['columns'] ?? [];
        if (!is_array($columns) || $columns === []) {
            return;
        }

        echo '<section class="cms-section cms-section-columns mt-10">';
        echo '<div class="md:flex flex-wrap gap-8">';
        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }
            $type = strtolower((string) ($column['content_type'] ?? $column['column_type'] ?? ''));
            echo '<div class="cms-column flex-1 min-w-[280px] mb-6">';
            if ($type === 'image') {
                $img = cmsFixMediaUrl($column['image_url'] ?? $column['image_path'] ?? $column['media_url'] ?? '');
                if ($img !== '') {
                    $alt = cms_image_alt($column, trim(strip_tags((string) ($column['title'] ?? ''))));
                    echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" class="w-full rounded-lg" loading="lazy" />';
                }
            } else {
                $content = cmsColumnTextHtml($column);
                if ($content !== '') {
                    echo '<div class="ql-snow"><div class="ql-editor">' . $content . '</div></div>';
                }
            }
            echo '</div>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('cmsRenderSectionCards')) {
    function cmsRenderSectionCards(array $section): void
    {
        $items = cmsSectionGroupedEntries($section, 'media');
        if ($items === []) {
            $html = cmsSectionInnerHtml($section);
            if ($html !== '') {
                echo '<section class="cms-section cms-section-card mt-10"><div class="ql-snow"><div class="ql-editor">' . $html . '</div></div></section>';
            }

            return;
        }

        echo '<section class="cms-section cms-section-cards mt-10">';
        cmsRenderSectionIntro($section);
        echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $titleRaw = cmsSectionEntryTitle($item);
            $title = htmlspecialchars(strip_tags($titleRaw), ENT_QUOTES, 'UTF-8');
            $img = cmsSectionEntryImageUrl($item);
            $body = cmsSectionEntryBody($item);
            echo '<article class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">';
            if ($img !== '') {
                echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" alt="' . cms_image_alt($item, strip_tags($titleRaw)) . '" class="w-full h-48 object-cover" loading="lazy" />';
            }
            echo '<div class="p-5">';
            if ($title !== '') {
                echo '<h3 class="text-blue-main font-bold text-lg mb-2">' . $title . '</h3>';
            }
            if ($body !== '') {
                echo '<div class="ql-snow text-sm"><div class="ql-editor">' . $body . '</div></div>';
            }
            echo '</div></article>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('cmsRenderSectionGallery')) {
    function cmsRenderSectionGallery(array $section): void
    {
        $items = cmsSectionGroupedEntries($section, 'media');
        if ($items === []) {
            cmsRenderSectionContent($section);

            return;
        }

        echo '<section class="cms-section cms-section-gallery mt-10">';
        cmsRenderSectionIntro($section);
        echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $img = cmsSectionEntryImageUrl($item);
            if ($img === '') {
                continue;
            }
            $titleRaw = cmsSectionEntryTitle($item);
            $title = htmlspecialchars(strip_tags($titleRaw), ENT_QUOTES, 'UTF-8');
            $body = cmsSectionEntryBody($item);
            echo '<figure class="overflow-hidden rounded-xl border border-gray-100 shadow-sm bg-white">';
            echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" alt="' . cms_image_alt($item, strip_tags($titleRaw) ?: 'Gallery image') . '" class="w-full h-56 object-cover" loading="lazy" />';
            if ($title !== '' || $body !== '') {
                echo '<figcaption class="p-4">';
                if ($title !== '') {
                    echo '<h3 class="text-blue-main font-semibold text-lg mb-1">' . $title . '</h3>';
                }
                if ($body !== '') {
                    echo '<div class="ql-snow text-sm text-gray-600"><div class="ql-editor">' . $body . '</div></div>';
                }
                echo '</figcaption>';
            }
            echo '</figure>';
        }
        echo '</div></section>';
    }
}

if (!function_exists('cmsRenderSectionAccordion')) {
    function cmsRenderSectionAccordion(array $section): void
    {
        $items = cmsSectionGroupedEntries($section, 'accordion');
        if ($items === []) {
            cmsRenderSectionContent($section);

            return;
        }

        static $accordionCount = 0;
        $accordionCount++;
        $accordionId = 'cms-accordion-' . $accordionCount;

        echo '<section class="cms-section cms-section-accordion mt-10">';
        cmsRenderSectionIntro($section);
        echo '<div id="' . htmlspecialchars($accordionId, ENT_QUOTES, 'UTF-8') . '" class="space-y-3">';
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $titleRaw = cmsSectionEntryTitle($item);
            if ($titleRaw === '') {
                continue;
            }
            $title = htmlspecialchars(strip_tags($titleRaw), ENT_QUOTES, 'UTF-8');
            $body = cmsSectionEntryBody($item);
            $panelId = $accordionId . '-panel-' . $index;
            echo '<div class="bg-[#002a5b] rounded-md shadow overflow-hidden">';
            echo '<button type="button" class="cms-accordion-btn w-full flex items-center justify-between px-4 py-3 text-left focus:outline-none" aria-expanded="false" aria-controls="' . htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8') . '">';
            echo '<span class="font-medium text-white">' . $title . '</span>';
            echo '<span class="cms-accordion-icon text-white text-xl leading-none" aria-hidden="true">+</span>';
            echo '</button>';
            echo '<div id="' . htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8') . '" class="cms-accordion-panel hidden bg-white px-4 py-3 text-gray-700">';
            if ($body !== '') {
                echo '<div class="ql-snow"><div class="ql-editor">' . $body . '</div></div>';
            }
            echo '</div></div>';
        }
        echo '</div>';
        echo '<script>(function(){var root=document.getElementById(' . json_encode($accordionId) . ');if(!root)return;root.querySelectorAll(".cms-accordion-btn").forEach(function(btn){btn.addEventListener("click",function(){var panel=btn.nextElementSibling;var icon=btn.querySelector(".cms-accordion-icon");var open=btn.getAttribute("aria-expanded")==="true";root.querySelectorAll(".cms-accordion-btn").forEach(function(other){other.setAttribute("aria-expanded","false");var p=other.nextElementSibling;if(p)p.classList.add("hidden");var i=other.querySelector(".cms-accordion-icon");if(i)i.textContent="+";});if(!open){btn.setAttribute("aria-expanded","true");if(panel)panel.classList.remove("hidden");if(icon)icon.textContent="−";}});});})();</script>';
        echo '</section>';
    }
}

if (!function_exists('cmsSectionShouldShowTitle')) {
    /** CMS section titles are internal labels — never show as page headings. */
    function cmsSectionShouldShowTitle(array $section): bool
    {
        return false;
    }
}

if (!function_exists('cmsSectionIsAdmissionEnquiryCmsBlock')) {
    /** Placeholder enquiry blocks in CMS are replaced by the PHP admission form. */
    function cmsSectionIsAdmissionEnquiryCmsBlock(array $section): bool
    {
        $title = strtolower(trim((string) ($section['title'] ?? '')));

        return str_contains($title, 'enquiry form');
    }
}

if (!function_exists('cmsMaybeRenderAdmissionProcedureEnquiryForm')) {
    function cmsMaybeRenderAdmissionProcedureEnquiryForm(): void
    {
        if (!empty($GLOBALS['cms_admission_procedure_enquiry_rendered'])) {
            return;
        }

        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($slug === '' || !preg_match('/admission-procedure-page-/i', $slug)) {
            return;
        }

        $includesDir = '';
        if (is_array($GLOBALS['cms_dynamic_page_config'] ?? null)) {
            $includesDir = (string) ($GLOBALS['cms_dynamic_page_config']['includes_dir'] ?? '');
        }
        if ($includesDir === '' || !is_dir($includesDir)) {
            return;
        }

        $formFile = dirname($includesDir) . '/shared/includes/admission-procedure-enquiry-form.php';
        if (!is_file($formFile)) {
            $formFile = dirname($includesDir, 2) . '/shared/includes/admission-procedure-enquiry-form.php';
        }
        if (!is_file($formFile)) {
            return;
        }

        require_once $formFile;
        if (function_exists('cmsRenderAdmissionProcedureEnquiryForm')) {
            cmsRenderAdmissionProcedureEnquiryForm($includesDir);
            $GLOBALS['cms_admission_procedure_enquiry_rendered'] = true;
        }
    }
}

if (!function_exists('cmsSectionVideoEntries')) {
    /**
     * @return list<array{video_id: string, title: string}>
     */
    function cmsSectionVideoEntries(array $section): array
    {
        $entries = [];
        $seen = [];
        $resolved = is_array($section['resolved_content'] ?? null) ? $section['resolved_content'] : [];

        $append = static function (string $url, string $title = '') use (&$entries, &$seen): void {
            $url = trim($url);
            if ($url === '') {
                return;
            }

            $videoId = '';
            if (function_exists('cmsYoutubeExtractVideoId')) {
                $videoId = cmsYoutubeExtractVideoId($url);
            } elseif (function_exists('cmsYoutubeVideoIdFromUrl')) {
                $videoId = cmsYoutubeVideoIdFromUrl($url);
            } elseif (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{11})#i', $url, $matches)) {
                $videoId = $matches[1];
            }

            if ($videoId === '' || isset($seen[$videoId])) {
                return;
            }

            $seen[$videoId] = true;
            $entries[] = [
                'video_id' => $videoId,
                'title' => trim($title),
            ];
        };

        foreach ($resolved['media'] ?? [] as $media) {
            if (!is_array($media)) {
                continue;
            }
            $url = (string) ($media['media_url'] ?? $media['url'] ?? $media['video_url'] ?? '');
            $title = (string) ($media['caption'] ?? $media['media_alt_text'] ?? $media['title'] ?? '');
            $append($url, $title);
        }

        foreach ($resolved['items'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = (string) ($item['media_url'] ?? $item['url'] ?? $item['video_url'] ?? '');
            $title = (string) ($item['title'] ?? $item['heading'] ?? '');
            $append($url, $title);
        }

        $append(
            (string) ($resolved['video_url'] ?? ''),
            (string) ($resolved['title'] ?? $section['content_heading'] ?? $section['title'] ?? '')
        );

        return $entries;
    }
}

if (!function_exists('cmsSectionHasVideoContent')) {
    function cmsSectionHasVideoContent(array $section): bool
    {
        return cmsSectionVideoEntries($section) !== [];
    }
}

if (!function_exists('cmsRenderSectionVideo')) {
    function cmsRenderSectionVideo(array $section): void
    {
        $entries = cmsSectionVideoEntries($section);
        if ($entries === []) {
            return;
        }

        $defaultTitle = trim((string) ($section['resolved_content']['title'] ?? $section['title'] ?? 'Campus tour video'));
        echo '<section class="cms-section cms-section-video mt-8 sm:mt-10">';

        foreach ($entries as $entry) {
            $videoId = (string) ($entry['video_id'] ?? '');
            if ($videoId === '') {
                continue;
            }

            $title = (string) ($entry['title'] ?? '');
            if ($title === '') {
                $title = $defaultTitle !== '' ? $defaultTitle : 'Campus tour video';
            }

            $embedUrl = '';
            if (function_exists('cmsYoutubeEmbedUrl')) {
                $embedUrl = cmsYoutubeEmbedUrl($videoId);
            } else {
                $embedUrl = 'https://www.youtube-nocookie.com/embed/' . rawurlencode($videoId);
            }

            if ($embedUrl === '') {
                continue;
            }

            echo '<div class="o-video mx-auto max-w-4xl w-full">';
            echo '<iframe width="560" height="315" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"';
            echo ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"';
            echo ' class="w-full aspect-video" frameborder="0"';
            echo ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"';
            echo ' allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>';
            echo '</div>';
        }

        echo '</section>';
    }
}

if (!function_exists('cmsRenderSectionContent')) {
    function cmsRenderSectionContent(array $section): void
    {
        if (!empty($section['columns']) && is_array($section['columns']) && count($section['columns']) >= 2) {
            cmsRenderSectionColumns($section);
            return;
        }

        $html = cmsSectionInnerHtml($section);
        if ($html === '') {
            return;
        }

        $title = trim((string) ($section['title'] ?? ''));
        echo '<section class="cms-section cms-section-content mt-10">';
        if (cmsSectionShouldShowTitle($section)) {
            echo '<h2 class="text-[28px] font-bold text-blue-main mb-4">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
        }
        echo '<div class="ql-snow"><div class="ql-editor">' . $html . '</div></div>';
        echo '</section>';
    }
}

if (!function_exists('cmsRenderPageSection')) {
    function cmsRenderPageSection(array $section, array $options = []): void
    {
        if (empty($section['is_active']) && array_key_exists('is_active', $section) && !$section['is_active']) {
            return;
        }

        $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? 'content'));
        $skipHero = !empty($options['skip_hero']);

        if ($skipHero && cmsSectionIsHero($section)) {
            return;
        }

        if (!$skipHero && cmsSectionIsHero($section)) {
            cmsRenderSectionHero($section);
            return;
        }

        if ($layout === 'carousel' || str_contains($layout, 'carousel')) {
            cmsRenderSectionCarousel($section);
            return;
        }

        if ($layout === 'gallery' || str_contains($layout, 'gallery')) {
            cmsRenderSectionGallery($section);
            return;
        }

        if ($layout === 'accordion' || str_contains($layout, 'accordion')) {
            cmsRenderSectionAccordion($section);
            return;
        }

        if ($layout === 'card' || str_contains($layout, 'card')) {
            cmsRenderSectionCards($section);
            return;
        }

        if ($layout === 'video' || str_contains($layout, 'video')) {
            cmsRenderSectionVideo($section);
            return;
        }

        cmsRenderSectionContent($section);
    }
}

if (!function_exists('cmsPageUsesInnerLayout')) {
    function cmsPageUsesInnerLayout(array $options): bool
    {
        return empty($options['full_width']) && empty($options['is_home']);
    }
}

if (!function_exists('cmsPageHeroSectionTitleLooksGeneric')) {
    function cmsPageHeroSectionTitleLooksGeneric(string $title): bool
    {
        $normalized = strtolower(trim($title));
        if ($normalized === '') {
            return true;
        }

        foreach (['hero banner', 'page banner', 'inner banner', 'banner section', 'hero section'] as $generic) {
            if ($normalized === $generic || str_contains($normalized, $generic)) {
                return true;
            }
        }

        if ($normalized === 'hero' || $normalized === 'banner') {
            return true;
        }

        return preg_match('/\bhero\b.*\b(rooma|agra|page)\b/i', $title) === 1;
    }
}

if (!function_exists('cmsPageHeroSectionPlainText')) {
    function cmsPageHeroSectionPlainText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (function_exists('cmsPrepareCmsPlainText')) {
            return trim(cmsPrepareCmsPlainText($value));
        }

        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

if (!function_exists('cmsPageHeroSectionHeading')) {
    /** Plain-text heading from the first CMS hero section (inner pages only). */
    function cmsPageHeroSectionHeading(?array $page): string
    {
        if (!is_array($page)) {
            return '';
        }

        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            return '';
        }

        foreach ($sections as $section) {
            if (!is_array($section) || !cmsSectionIsHero($section)) {
                continue;
            }

            $candidates = [];
            foreach (['content_heading', 'content'] as $key) {
                $value = trim((string) ($section[$key] ?? ''));
                if ($value !== '') {
                    $candidates[] = $value;
                }
            }

            $resolved = $section['resolved_content'] ?? null;
            if (is_array($resolved)) {
                foreach (['content_heading', 'heading', 'content'] as $key) {
                    $value = trim((string) ($resolved[$key] ?? ''));
                    if ($value !== '') {
                        $candidates[] = $value;
                    }
                }
            }

            foreach ($candidates as $candidate) {
                $plain = cmsPageHeroSectionPlainText($candidate);
                if ($plain !== '' && !cmsPageHeroSectionTitleLooksGeneric($plain)) {
                    return $plain;
                }
            }

            $sectionTitle = cmsPageHeroSectionPlainText((string) ($section['title'] ?? ''));
            if ($sectionTitle !== '' && !cmsPageHeroSectionTitleLooksGeneric($sectionTitle)) {
                return $sectionTitle;
            }
        }

        return '';
    }
}

if (!function_exists('cmsPageBannerTitle')) {
    function cmsPageBannerTitle(?array $page): string
    {
        if (function_exists('cmsPageDisplayTitle')) {
            $title = trim(cmsPageDisplayTitle($page, ''));
            if ($title !== '') {
                return $title;
            }
        }

        if (!is_array($page)) {
            return '';
        }
        $data = is_array($page['data'] ?? null) ? $page['data'] : [];
        $title = trim((string) ($data['page_heading'] ?? $data['title'] ?? ''));
        if ($title !== '' && function_exists('cmsPrepareCmsPlainText')) {
            $title = trim(cmsPrepareCmsPlainText($title));
        }

        return $title;
    }
}

if (!function_exists('cmsPageBannerDisplayTitle')) {
    /** Inner-page hero banner label: CMS hero section heading, then page title. */
    function cmsPageBannerDisplayTitle(?array $page): string
    {
        $heroHeading = cmsPageHeroSectionHeading($page);
        if ($heroHeading !== '') {
            return $heroHeading;
        }

        $pageTitle = cmsPageBannerTitle($page);
        if ($pageTitle !== '') {
            return $pageTitle;
        }

        if (function_exists('cmsPageBreadcrumbFallbackTitle')) {
            return cmsPageBreadcrumbFallbackTitle($page);
        }

        return '';
    }
}

if (!function_exists('cmsRenderImageHeroBanner')) {
    /**
     * Image-only hero (no title). $banner: mobile, desktop (or mobile_url, desktop_url).
     *
     * @param array<string, string> $banner
     */
    function cmsRenderImageHeroBanner(array $banner, string $extraClass = ''): void
    {
        $mobile = trim((string) ($banner['mobile'] ?? $banner['mobile_url'] ?? ''));
        $desktop = trim((string) ($banner['desktop'] ?? $banner['desktop_url'] ?? $mobile));
        if ($mobile === '' && $desktop === '') {
            return;
        }
        if ($mobile === '') {
            $mobile = $desktop;
        }
        if ($desktop === '') {
            $desktop = $mobile;
        }

        $mobileEsc = htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8');
        $desktopEsc = htmlspecialchars($desktop, ENT_QUOTES, 'UTF-8');
        $bannerId = 'cms-hero-img-' . substr(md5($mobile . '|' . $desktop), 0, 10);
        $extraClass = trim($extraClass);

        echo '<style>';
        echo '#' . $bannerId . '{background-image:url(\'' . $mobileEsc . '\');}';
        echo '@media (min-width:640px){#' . $bannerId . '{background-image:url(\'' . $desktopEsc . '\');}}';
        echo '</style>';
        echo '<div id="' . $bannerId . '" class="cms-page-image-banner w-full flex items-center text-left bg-center bg-cover';
        if ($extraClass !== '') {
            echo ' ' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8');
        }
        echo '" aria-hidden="true"></div>';
    }
}

if (!function_exists('cmsRenderPageBanner')) {
    /** Full-width inner-page banner from CMS hero section heading (not used on home). */
    function cmsRenderPageBanner(?array $page, array $options = []): void
    {
        if (!cmsPageUsesInnerLayout($options)) {
            return;
        }
        $title = cmsPageBannerDisplayTitle($page);
        if ($title === '') {
            return;
        }
        $safe = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $bannerStyle = 'background-image: url(' . htmlspecialchars(cmsStaticInnerBannerUrl(), ENT_QUOTES, 'UTF-8') . '); background-size: cover; background-repeat: no-repeat; background-position: center center !important; height: 350px;';
        echo '<div class="cms-page-banner w-full flex items-center text-left comman-banner common-banner" style="' . $bannerStyle . '">';
        echo '<div class="w-full px-4">';
        echo '<h1 class="text-[32px] font-[700] text-white text-left sm:ml-[7rem] leading-9">' . $safe . '</h1>';
        echo '</div></div>';
    }
}

if (!function_exists('cmsRenderPageBreadcrumbs')) {
    /** Full-width breadcrumb row — CMS data or static Home > page title. */
    function cmsRenderPageBreadcrumbs(?array $page, array $options = []): void
    {
        if (!cmsPageUsesInnerLayout($options)) {
            return;
        }
        if (!function_exists('cmsResolvePageBreadcrumbs') || !function_exists('cmsRenderBreadcrumbsNav')) {
            return;
        }
        cmsRenderBreadcrumbsNav(cmsResolvePageBreadcrumbs($page, $options));
    }
}

if (!function_exists('cmsSectionIsFullBleed')) {
    /** Home page blocks that must span full viewport width. */
    function cmsSectionIsFullBleed(array $section): bool
    {
        if (cmsSectionIsHero($section)) {
            return true;
        }
        $layout = strtolower((string) ($section['layout'] ?? $section['section_type'] ?? ''));
        if (str_contains($layout, 'carousel')) {
            return true;
        }
        $title = strtolower((string) ($section['title'] ?? ''));
        if (str_contains($title, 'hero') || str_contains($title, 'banner')) {
            return true;
        }
        if (str_contains($title, 'gallery') || str_contains($title, 'testimonial')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('cmsRenderPageSections')) {
  /**
   * @param array<string, mixed> $options skip_hero, start_index, end_index, full_width, is_home
   */
    function cmsRenderPageSections(?array $page, array $options = []): void
    {
        if (!is_array($page)) {
            return;
        }

        $sections = $page['data']['sections'] ?? [];
        if (!is_array($sections)) {
            $sections = [];
        }

        $start = max(0, (int) ($options['start_index'] ?? 0));
        $end = isset($options['end_index']) ? (int) $options['end_index'] : count($sections) - 1;
        $isHome = !empty($options['full_width']) || !empty($options['is_home']);
        $innerLayout = !$isHome;
        $skipPageBanner = !empty($options['skip_page_banner']);
        $skipBreadcrumbs = !empty($options['skip_breadcrumbs']);
        $hasPageBanner = $skipPageBanner;

        if ($innerLayout) {
            if (!$skipPageBanner) {
                cmsRenderPageBanner($page, $options);
                $hasPageBanner = cmsPageBannerDisplayTitle($page) !== '';
            }
            if (!$skipBreadcrumbs) {
                cmsRenderPageBreadcrumbs($page, $options);
            }
        }

        if ($sections === []) {
            return;
        }

        $contentWrapClass = function_exists('cmsPageContentWrapClass')
            ? cmsPageContentWrapClass()
            : 'cms-page-content mx-auto max-w-[1280px] w-full px-3 sm:px-5 mt-6 mb-10';

        if ($isHome) {
            echo '<div class="cms-home-sections w-full">';
            foreach ($sections as $index => $section) {
                if (!is_array($section) || $index < $start || $index > $end) {
                    continue;
                }
                if (cmsSectionIsFullBleed($section)) {
                    cmsRenderPageSection($section, $options);
                } else {
                    echo '<div class="' . $contentWrapClass . ' cms-home-section-inner">';
                    cmsRenderPageSection($section, $options);
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="' . $contentWrapClass . '">';
            foreach ($sections as $index => $section) {
                if (!is_array($section) || $index < $start || $index > $end) {
                    continue;
                }
                if ($hasPageBanner && cmsSectionIsHero($section)) {
                    continue;
                }
                if (function_exists('cmsSectionIsAdmissionEnquiryCmsBlock') && cmsSectionIsAdmissionEnquiryCmsBlock($section)) {
                    continue;
                }
                $sectionOptions = array_merge($options, ['skip_hero' => true]);
                cmsRenderPageSection($section, $sectionOptions);
            }
            echo '</div>';
            if (function_exists('cmsMaybeRenderAdmissionProcedureEnquiryForm')) {
                cmsMaybeRenderAdmissionProcedureEnquiryForm();
            }
        }

        if (function_exists('cmsMarkAllPageSectionsRendered')) {
            cmsMarkAllPageSectionsRendered($page);
        }
    }
}

