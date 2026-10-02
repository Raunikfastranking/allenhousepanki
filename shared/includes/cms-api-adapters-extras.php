<?php

declare(strict_types=1);

if (!function_exists('cmsPageSectionByOrder')) {
    function cmsPageSectionByOrder(?array $page, int $order): ?array
    {
        if (!is_array($page)) {
            return null;
        }

        foreach ($page['data']['sections'] ?? [] as $section) {
            if (!is_array($section)) {
                continue;
            }
            if ((int) ($section['order'] ?? 0) === $order) {
                return $section;
            }
        }

        return null;
    }
}

if (!function_exists('cmsStatisticHeadingToLegacyKey')) {
    function cmsStatisticHeadingToLegacyKey(string $heading): string
    {
        $normalized = strtolower(trim(strip_tags($heading)));
        if ($normalized === '') {
            return '';
        }

        $map = [
            'years of experience' => 'years',
            'year of experience' => 'years',
            'campuses' => 'campus',
            'campus' => 'campus',
            'students' => 'student',
            'student' => 'student',
            'staff' => 'staff',
            'teachers' => 'staff',
            'faculty' => 'staff',
            'alumni' => 'alumni',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        foreach ($map as $needle => $key) {
            if (str_contains($normalized, $needle)) {
                return $key;
            }
        }

        return '';
    }
}

if (!function_exists('cmsLegacyStatisticsRow')) {
    /**
     * @return array{years: string, campus: string, student: string, staff: string, alumni: string}
     */
    function cmsLegacyStatisticsRow(?array $response): array
    {
        $defaults = [
            'years' => '',
            'campus' => '',
            'student' => '',
            'staff' => '',
            'alumni' => '',
        ];

        if (!is_array($response)) {
            return $defaults;
        }

        $groups = $response['data'] ?? [];
        if (!is_array($groups)) {
            return $defaults;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            foreach ($group['items'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $key = cmsStatisticHeadingToLegacyKey((string) ($item['heading'] ?? ''));
                if ($key === '') {
                    continue;
                }
                $count = trim((string) ($item['count'] ?? ''));
                $unit = trim((string) ($item['unit'] ?? ''));
                $defaults[$key] = $count . $unit;
            }
        }

        return $defaults;
    }
}

if (!function_exists('cmsJobsListFromResponse')) {
    /**
     * @return list<array<string, mixed>>
     */
    function cmsJobsListFromResponse(?array $response): array
    {
        if (!is_array($response)) {
            return [];
        }

        $jobs = $response['data'] ?? $response;
        if (!is_array($jobs)) {
            return [];
        }
        if (isset($jobs['data']) && is_array($jobs['data'])) {
            $jobs = $jobs['data'];
        }

        return array_values(array_filter($jobs, 'is_array'));
    }
}

if (!function_exists('cmsFindJobInResponse')) {
    /** @return array<string, mixed>|null */
    function cmsFindJobInResponse(?array $response, int|string|null $jobId): ?array
    {
        if ($jobId === null || trim((string) $jobId) === '') {
            return null;
        }

        $needle = (string) $jobId;
        foreach (cmsJobsListFromResponse($response) as $job) {
            if ((string) ($job['id'] ?? '') === $needle) {
                return $job;
            }
        }

        return null;
    }
}

if (!function_exists('cmsJobDisplayTitle')) {
    function cmsJobDisplayTitle(?array $job): string
    {
        if (!is_array($job)) {
            return '';
        }

        foreach (['job_title', 'title', 'name'] as $key) {
            $value = trim((string) ($job[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
