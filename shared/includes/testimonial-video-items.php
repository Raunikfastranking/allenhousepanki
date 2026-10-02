<?php

declare(strict_types=1);

if (!function_exists('cmsYoutubeVideoIdFromUrl')) {
    function cmsYoutubeVideoIdFromUrl(string $videoUrl): string
    {
        $videoUrl = trim($videoUrl);
        if ($videoUrl === '') {
            return '';
        }

        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})#i', $videoUrl, $matches)) {
            return $matches[1];
        }

        $path = (string) parse_url($videoUrl, PHP_URL_PATH);
        $candidate = trim(basename($path), '/');
        if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $candidate)) {
            return $candidate;
        }

        return '';
    }
}

if (!function_exists('cmsVideoTestimonialRowFromMedia')) {
    /**
     * @param array<string, mixed> $media
     * @return array{title: string, video_id: string}|null
     */
    function cmsVideoTestimonialRowFromMedia(array $media): ?array
    {
        $url = trim((string) ($media['media_url'] ?? $media['url'] ?? $media['video_url'] ?? ''));
        $videoId = cmsYoutubeVideoIdFromUrl($url);
        if ($videoId === '') {
            $videoId = trim((string) ($media['video_id'] ?? ''));
        }
        if ($videoId === '') {
            return null;
        }

        $title = trim((string) ($media['title'] ?? $media['heading'] ?? $media['media_alt_text'] ?? $media['alt_text'] ?? ''));

        return [
            'title' => $title !== '' ? $title : 'Video Testimonial',
            'video_id' => $videoId,
        ];
    }
}

if (!function_exists('cmsDedupeVideoTestimonialRows')) {
    /**
     * @param list<array{title: string, video_id: string}> $rows
     * @return list<array{title: string, video_id: string}>
     */
    function cmsDedupeVideoTestimonialRows(array $rows): array
    {
        $seen = [];
        $out = [];

        foreach ($rows as $row) {
            $videoId = trim((string) ($row['video_id'] ?? ''));
            if ($videoId === '' || isset($seen[$videoId])) {
                continue;
            }

            $seen[$videoId] = true;
            $out[] = [
                'title' => trim((string) ($row['title'] ?? 'Video Testimonial')),
                'video_id' => $videoId,
            ];
        }

        return $out;
    }
}

if (!function_exists('cmsVideoTestimonialSectionLooksLikeVideo')) {
    /**
     * @param array<string, mixed> $section
     */
    function cmsVideoTestimonialSectionLooksLikeVideo(array $section): bool
    {
        $layout = strtolower(trim((string) ($section['layout'] ?? '')));
        if ($layout === 'video' || str_contains($layout, 'video')) {
            return true;
        }

        $title = strtolower(trim((string) ($section['title'] ?? '')));

        return str_contains($title, 'video testimonial')
            || ($title !== '' && str_contains($title, 'video section'));
    }
}

if (!function_exists('cmsVideoTestimonialRowsFromSection')) {
    /**
     * @param array<string, mixed>|null $section
     * @return list<array{title: string, video_id: string}>
     */
    function cmsVideoTestimonialRowsFromSection(?array $section): array
    {
        if (!is_array($section) || $section === []) {
            return [];
        }

        $rows = [];
        $content = is_array($section['resolved_content'] ?? null) ? $section['resolved_content'] : [];

        foreach ($content['media'] ?? [] as $media) {
            if (!is_array($media)) {
                continue;
            }
            $row = cmsVideoTestimonialRowFromMedia($media);
            if ($row !== null) {
                $rows[] = $row;
            }
        }

        foreach ($content['items'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $row = cmsVideoTestimonialRowFromMedia($item);
            if ($row !== null) {
                $rows[] = $row;
            }
        }

        $singleUrl = trim((string) ($content['video_url'] ?? ''));
        if ($singleUrl !== '') {
            $videoId = cmsYoutubeVideoIdFromUrl($singleUrl);
            if ($videoId !== '') {
                $title = trim((string) ($section['content_heading'] ?? $section['title'] ?? 'Video Testimonial'));
                $rows[] = [
                    'title' => $title !== '' ? $title : 'Video Testimonial',
                    'video_id' => $videoId,
                ];
            }
        }

        return cmsDedupeVideoTestimonialRows($rows);
    }
}

if (!function_exists('cmsVideoTestimonialRowsFromPage')) {
    /**
     * @param array<string, mixed>|null $page
     * @return list<array{title: string, video_id: string}>
     */
    function cmsVideoTestimonialRowsFromPage(?array $page): array
    {
        if (!is_array($page) || $page === []) {
            return [];
        }

        $rows = [];
        foreach ($page['sections'] ?? [] as $section) {
            if (!is_array($section) || !cmsVideoTestimonialSectionLooksLikeVideo($section)) {
                continue;
            }

            $sectionRows = cmsVideoTestimonialRowsFromSection($section);
            if ($sectionRows !== []) {
                $rows = array_merge($rows, $sectionRows);
            }
        }

        return cmsDedupeVideoTestimonialRows($rows);
    }
}

if (!function_exists('cmsResolveVideoTestimonials')) {
    /**
     * Prefer CMS video sections; fall back to static embed IDs.
     *
     * @param list<string> $pageSlugs
     * @param list<array{title: string, video_id: string}> $fallback
     * @return list<array{title: string, video_id: string}>
     */
    function cmsResolveVideoTestimonials(array $pageSlugs, array $fallback): array
    {
        $rows = [];

        if (function_exists('cmsFetchPageBySlug')) {
            foreach ($pageSlugs as $slug) {
                $slug = trim($slug);
                if ($slug === '') {
                    continue;
                }

                $pageRows = cmsVideoTestimonialRowsFromPage(cmsFetchPageBySlug($slug));
                if ($pageRows !== []) {
                    $rows = array_merge($rows, $pageRows);
                }
            }
        }

        $rows = cmsDedupeVideoTestimonialRows($rows);
        if ($rows !== []) {
            return $rows;
        }

        return cmsDedupeVideoTestimonialRows($fallback);
    }
}
