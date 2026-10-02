<?php

require_once dirname(__DIR__) . '/proxy/config.php';

if (!function_exists('cmsFetchBranchCityNames')) {
    /**
     * @return list<string>
     */
    function cmsFetchBranchCityNames(): array
    {
        $branchId = (int) BRANCH_ID;
        $schoolId = (string) SCHOOL_ID;
        $rows = [];

        $json = null;
        if (function_exists('cmsBulkApiCacheGet')) {
            $json = cmsBulkApiCacheGet('cities_data');
        }
        if ($json === null) {
            $json = cms_fetch_json_endpoint('cities/' . $branchId);
        }
        if (is_array($json) && isset($json['data']) && is_array($json['data'])) {
            $rows = $json['data'];
        }

        if ($rows === []) {
            $json = cms_fetch_json_endpoint('cities/' . $schoolId);
            if (is_array($json) && isset($json['data']) && is_array($json['data'])) {
                foreach ($json['data'] as $city) {
                    if (!is_array($city)) {
                        continue;
                    }
                    if ((int) ($city['branch_id'] ?? 0) === $branchId) {
                        $rows[] = $city;
                    }
                }
            }
        }

        $uniqueCities = [];
        foreach ($rows as $city) {
            $cityName = trim((string) ($city['name'] ?? ''));
            if ($cityName === '') {
                continue;
            }
            $key = strtolower($cityName);
            if (isset($uniqueCities[$key])) {
                continue;
            }
            $uniqueCities[$key] = $cityName;
        }

        $cityNames = array_values($uniqueCities);
        sort($cityNames, SORT_NATURAL | SORT_FLAG_CASE);

        return $cityNames;
    }
}

if (!function_exists('cmsEchoCityOptions')) {
    /** @param list<string> $cityNames */
    function cmsEchoCityOptions(array $cityNames): void
    {
        if ($cityNames === []) {
            echo "<option value=''>Cities unavailable</option>";

            return;
        }

        foreach ($cityNames as $cityName) {
            $safe = htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8');
            echo "<option value=\"{$safe}\">{$safe}</option>";
        }
    }
}

$cmsGetCityMode = $cmsGetCityMode ?? 'echo';

if ($cmsGetCityMode === 'array') {
    return cmsFetchBranchCityNames();
}

cmsEchoCityOptions(cmsFetchBranchCityNames());
