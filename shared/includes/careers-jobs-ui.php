<?php

declare(strict_types=1);

if (!function_exists('cmsFetchJobsForBranch')) {
    /** @return list<array<string, mixed>> */
    function cmsFetchJobsForBranch(): array
    {
        if (!defined('CMS_API_URL') || !defined('BRANCH_ID')) {
            return [];
        }

        $url = rtrim((string) CMS_API_URL, '/') . '/jobs/branch/' . (int) BRANCH_ID;
        $decoded = null;
        if (function_exists('cms_curl_get_json')) {
            if (function_exists('cms_curl_json_cache_ref')) {
                $curlCache = &cms_curl_json_cache_ref();
                unset($curlCache[$url]);
            }
            $decoded = cms_curl_get_json($url, 20);
        } else {
            $ctx = stream_context_create([
                'http' => ['timeout' => 20, 'header' => "Accept: application/json\r\n"],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $json = @file_get_contents($url, false, $ctx);
            if (is_string($json) && $json !== '') {
                $decoded = json_decode($json, true);
            }
        }

        if (!is_array($decoded)) {
            return [];
        }

        if (isset($GLOBALS['api_cache']) && is_array($GLOBALS['api_cache'])) {
            $GLOBALS['api_cache']['jobs_data'] = $decoded;
        }

        $jobs = $decoded['data'] ?? $decoded;
        if (!is_array($jobs)) {
            return [];
        }
        if (isset($jobs['data']) && is_array($jobs['data'])) {
            $jobs = $jobs['data'];
        }

        return array_values(array_filter($jobs, 'is_array'));
    }
}

if (!function_exists('cmsJobsListResolved')) {
    /** @return list<array<string, mixed>> */
    function cmsJobsListResolved(): array
    {
        $fresh = cmsFetchJobsForBranch();
        if ($fresh !== []) {
            return $fresh;
        }

        return cmsJobsListFromCache();
    }
}

if (!function_exists('cmsJobsListFromCache')) {
    /** @return list<array<string, mixed>> */
    function cmsJobsListFromCache(): array
    {
        $raw = $GLOBALS['api_cache']['jobs_data'] ?? null;
        if (!is_array($raw)) {
            return [];
        }
        $jobs = $raw['data'] ?? $raw;
        if (!is_array($jobs)) {
            return [];
        }
        if (isset($jobs['data']) && is_array($jobs['data'])) {
            $jobs = $jobs['data'];
        }

        return array_values(array_filter($jobs, 'is_array'));
    }
}

if (!function_exists('cmsJobTimeAgo')) {
    function cmsJobTimeAgo(?string $datetime): string
    {
        if ($datetime === null || trim($datetime) === '') {
            return '';
        }
        try {
            $time = new DateTime($datetime, new DateTimeZone('UTC'));
            $time->setTimezone(new DateTimeZone('Asia/Kolkata'));
            $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
            $diff = $now->diff($time);
        } catch (Exception) {
            return '';
        }

        if ($diff->y) {
            return $diff->y . ' Year' . ($diff->y > 1 ? 's' : '') . ' Ago';
        }
        if ($diff->m) {
            return $diff->m . ' Month' . ($diff->m > 1 ? 's' : '') . ' Ago';
        }
        if ($diff->d) {
            return $diff->d . ' Day' . ($diff->d > 1 ? 's' : '') . ' Ago';
        }
        if ($diff->h) {
            return $diff->h . ' Hour' . ($diff->h > 1 ? 's' : '') . ' Ago';
        }
        if ($diff->i) {
            return $diff->i . ' Minute' . ($diff->i > 1 ? 's' : '') . ' Ago';
        }

        return 'Just Now';
    }
}

if (!function_exists('cmsJobApplyUrl')) {
    function cmsJobApplyUrl(int|string $jobId): string
    {
        if (function_exists('cmsJobApplyPageUrl')) {
            return cmsJobApplyPageUrl($jobId);
        }

        $base = function_exists('cmsBreadcrumbHref') ? cmsBreadcrumbHref('job-apply') : 'job-apply';

        return $base . '?job_id=' . rawurlencode((string) $jobId);
    }
}

if (!function_exists('cmsRenderJobOpeningCard')) {
    /** @param array<string, mixed> $job */
    function cmsRenderJobOpeningCard(array $job): void
    {
        $jobId = (string) ($job['id'] ?? '');
        $title = htmlspecialchars((string) ($job['job_title'] ?? $job['title'] ?? 'Opening'), ENT_QUOTES, 'UTF-8');
        $jobType = htmlspecialchars((string) ($job['job_type'] ?? ''), ENT_QUOTES, 'UTF-8');
        $minExp = htmlspecialchars((string) ($job['min_exp'] ?? ''), ENT_QUOTES, 'UTF-8');
        $maxExp = htmlspecialchars((string) ($job['max_exp'] ?? ''), ENT_QUOTES, 'UTF-8');
        $minSalary = trim((string) ($job['min_salary'] ?? ''));
        $maxSalary = trim((string) ($job['max_salary'] ?? ''));
        $salary = ($minSalary !== '' || $maxSalary !== '') ? htmlspecialchars($minSalary . ' - ' . $maxSalary, ENT_QUOTES, 'UTF-8') : '&nbsp;-';
        $address = htmlspecialchars((string) ($job['job_address'] ?? $job['branch']['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $qualification = htmlspecialchars((string) ($job['req_qualification'] ?? ''), ENT_QUOTES, 'UTF-8');
        $jd = trim((string) ($job['jd'] ?? $job['job_description_url'] ?? ''));
        $createdAt = cmsJobTimeAgo((string) ($job['created_at'] ?? ''));
        $titleSearch = strtolower((string) ($job['job_title'] ?? $job['title'] ?? ''));
        $typeSearch = strtolower((string) ($job['job_type'] ?? ''));
        $locationSearch = strtolower((string) ($job['job_address'] ?? $job['branch']['address'] ?? ''));
        $searchBlob = strtolower(implode(' ', [
            $titleSearch,
            $typeSearch,
            $locationSearch,
            (string) ($job['req_qualification'] ?? ''),
        ]));
        $applyUrl = $jobId !== '' ? cmsJobApplyUrl($jobId) : '#';
        ?>
        <div class="cms-job-card p-4 rounded-[12px] border border-gray-200"
            data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>"
            data-title="<?= htmlspecialchars($titleSearch, ENT_QUOTES, 'UTF-8') ?>"
            data-type="<?= htmlspecialchars($typeSearch, ENT_QUOTES, 'UTF-8') ?>"
            data-location="<?= htmlspecialchars($locationSearch, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($jd !== ''): ?>
            <a href="<?= htmlspecialchars($jd, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="text-blue-main justify-end flex items-center gap-1 font-semibold text-[14px]">
                <svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M2.12585 0.695312C1.50505 0.695312 1 1.19903 1 1.81815V14.1817C1 14.8008 1.50502 15.3045 2.12585 15.3045H10.5501C11.1709 15.3045 11.6759 14.8008 11.6759 14.1817V4.84366C11.6759 4.77101 11.6478 4.70119 11.5974 4.64883L7.87542 0.781416C7.82246 0.726403 7.74937 0.695312 7.67295 0.695312H2.12585ZM2.12585 1.25718H7.18081V4.90948C7.18081 5.06464 7.3066 5.19041 7.46174 5.19041H11.114V14.1817C11.114 14.491 10.8611 14.7426 10.5501 14.7426H2.12585C1.81486 14.7426 1.56187 14.491 1.56187 14.1817V1.81815C1.56187 1.50885 1.81486 1.25718 2.12585 1.25718ZM7.74267 1.45385L10.798 4.62854H7.74267V1.45385Z" fill="#FF2016" stroke="#FF2016" stroke-width="0.3"/>
                </svg>
                View Job Description
            </a>
            <?php endif; ?>

            <h2 class="mt-3 font-[700] text-[18px] sm:text-[24px] text-[#101828]"><?= $title ?></h2>
            <div class="mt-3 sm:flex items-center justify-between text-[14px] font-[600] text-[#474D6A] sm:tracking-[1px]">
                <p class="sm:mb-0 mb-2 text-[14px]"><?= $jobType ?></p>
                <p class="hidden sm:block">|</p>
                <p class="flex items-center gap-1 text-[#474D6A] sm:tracking-[1px] sm:mb-0 mb-2 text-[14px]">
                    <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12.04 2.46422H9.89333V1.81089C9.89333 1.37533 9.73 0.994219 9.40333 0.667552C9.07667 0.340885 8.69556 0.177552 8.26 0.177552H5.74C5.30444 0.177552 4.92333 0.340885 4.59667 0.667552C4.27 0.994219 4.10667 1.37533 4.10667 1.81089V2.46422H1.96C1.43111 2.46422 0.972222 2.65866 0.583333 3.04755C0.194444 3.43644 0 3.91088 0 4.47089V10.8642C0 11.3931 0.194444 11.852 0.583333 12.2409C0.972222 12.6298 1.43111 12.8242 1.96 12.8242H12.04C12.5689 12.8242 13.0278 12.6298 13.4167 12.2409C13.8056 11.852 14 11.3931 14 10.8642V4.47089C14 3.91088 13.8056 3.43644 13.4167 3.04755C13.0278 2.65866 12.5689 2.46422 12.04 2.46422Z" fill="#474D6A"/>
                    </svg>
                    Req. Exp. <?= $minExp ?>- <?= $maxExp ?> years
                </p>
                <p class="hidden sm:block">|</p>
                <p class="flex items-center gap-1 text-[#474D6A] sm:tracking-[1px] sm:mb-0 mb-2 text-[14px]">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M2.85276 2.03995C2.6972 2.03995 2.5572 2.09439 2.43276 2.20328C2.30832 2.31217 2.24609 2.45217 2.24609 2.62328C2.24609 2.79439 2.30832 2.94217 2.43276 3.06661C2.5572 3.19106 2.6972 3.25328 2.85276 3.25328H4.85943C5.32609 3.25328 5.73832 3.3855 6.09609 3.64995C6.45387 3.91439 6.71054 4.26439 6.86609 4.69995H2.85276C2.6972 4.69995 2.5572 4.76217 2.43276 4.88661C2.30832 5.01106 2.24609 5.15106 2.24609 5.30661C2.24609 5.46217 2.30832 5.60217 2.43276 5.72661C2.5572 5.85106 2.6972 5.91328 2.85276 5.91328H6.86609C6.71054 6.34884 6.45387 6.69884 6.09609 6.96328C5.73832 7.22773 5.32609 7.35995 4.85943 7.35995H2.85276C2.72832 7.35995 2.61943 7.39884 2.52609 7.47661C2.43276 7.55439 2.36276 7.64773 2.31609 7.75661C2.26943 7.8655 2.25387 7.98217 2.26943 8.10661C2.28498 8.23106 2.33943 8.33995 2.43276 8.43328L6.44609 12.4466C6.60165 12.6022 6.79609 12.6488 7.02943 12.5866C7.26276 12.5244 7.41054 12.3844 7.47276 12.1666C7.53498 11.9488 7.47276 11.7466 7.28609 11.5599L4.34609 8.57328H4.85943C5.66832 8.57328 6.37609 8.32439 6.98276 7.82661C7.58943 7.32884 7.95498 6.69106 8.07943 5.91328H10.2261C10.3816 5.91328 10.5217 5.85106 10.6461 5.72661C10.7705 5.60217 10.8328 5.46217 10.8328 5.30661C10.8328 5.15106 10.7705 5.01106 10.6461 4.88661C10.5217 4.76217 10.3816 4.69995 10.2261 4.69995H8.07943C7.98609 4.17106 7.76832 3.68884 7.42609 3.25328H10.2261C10.3816 3.25328 10.5217 3.19106 10.6461 3.06661C10.7705 2.94217 10.8328 2.79439 10.8328 2.62328C10.8328 2.45217 10.7705 2.31217 10.6461 2.20328C10.5217 2.09439 10.3816 2.03995 10.2261 2.03995H2.85276Z" fill="#474D6A" stroke="#474D6A" stroke-width="0.3"/>
                    </svg>
                    <?= $salary ?>
                </p>
            </div>
            <ul class="mt-3">
                <?php if ($address !== ''): ?>
                <li class="flex items-center gap-1 text-[14px] font-[600] mb-2 text-[#474D6A] sm:tracking-[1px]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M7.99939 3.77552C7.54072 3.77552 7.12206 3.89018 6.74339 4.11952C6.36472 4.34885 6.06339 4.65551 5.83939 5.03952C5.61539 5.42352 5.50339 5.84218 5.50339 6.29552C5.50339 6.74885 5.61539 7.16752 5.83939 7.55152C6.06339 7.93552 6.36472 8.23952 6.74339 8.46352C7.12206 8.68752 7.54072 8.79952 7.99939 8.79952C8.45806 8.79952 8.87939 8.68752 9.26339 8.46352C9.64739 8.23952 9.94872 7.93552 10.1674 7.55152C10.3861 7.16752 10.4954 6.74885 10.4954 6.29552C10.4954 5.84218 10.3861 5.42352 10.1674 5.03952C9.94872 4.65551 9.64739 4.34885 9.26339 4.11952C8.87939 3.89018 8.45806 3.77552 7.99939 3.77552ZM3.03939 6.28752C3.03939 5.37018 3.26872 4.51685 3.72739 3.72752C4.16472 2.97018 4.76206 2.36485 5.51939 1.91152C6.27672 1.45818 7.10339 1.23151 7.99939 1.23151C8.89539 1.23151 9.72206 1.45818 10.4794 1.91152C11.2367 2.36485 11.8341 2.97018 12.2714 3.72752C12.7301 4.51685 12.9594 5.37018 12.9594 6.28752C12.9594 7.20485 12.6874 8.15952 12.1434 9.15152C11.7061 9.97285 11.0981 10.7995 10.3194 11.6315C9.76472 12.2288 9.14606 12.8048 8.46339 13.3595C7.85006 13.6102 7.69539 13.4875 7.53539 13.3595C6.85272 12.8048 6.23406 12.2288 5.67939 11.6315C4.90072 10.7995 4.29272 9.97285 3.85539 9.15152C3.31139 8.15952 3.03939 7.20485 3.03939 6.28752Z" fill="#474D6A"/>
                    </svg>
                    <?= $address ?>
                </li>
                <?php endif; ?>
                <?php if ($qualification !== ''): ?>
                <li class="flex sm:items-center gap-1 text-[14px] font-[600] mb-2 text-[#474D6A] sm:tracking-[1px]">
                    <svg width="14" height="17" viewBox="0 0 12 17" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.09615 5.3557C5.06767 5.28738 5.0196 5.22901 4.95801 5.18796C4.89642 5.14691 4.82405 5.125 4.75003 5.125C4.67601 5.125 4.60364 5.14691 4.54205 5.18796C4.48045 5.22901 4.43239 5.28738 4.4039 5.3557L3.00065 8.7232L2.99428 8.73895L2.5289 9.8557C2.49061 9.9475 2.49036 10.0507 2.52819 10.1427C2.56603 10.2347 2.63886 10.3079 2.73065 10.3462C2.82245 10.3845 2.9257 10.3847 3.01769 10.3469C3.10967 10.3091 3.18286 10.2362 3.22115 10.1445L3.5939 9.25008H5.90615L6.2789 10.1445C6.31719 10.2362 6.39038 10.3091 6.48237 10.3469C6.57436 10.3847 6.67761 10.3845 6.7694 10.3462C6.8612 10.3079 6.93403 10.2347 6.97187 10.1427C7.0097 10.0507 7.00945 9.9475 6.97115 9.8557L6.50578 8.73895L6.4994 8.7232L5.09615 5.3557ZM4.75003 6.47508L5.59378 8.50008H3.90628L4.75003 6.47508Z" fill="#474D6A" stroke="#474D6A" stroke-width="0.3"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.5 5.125L7.75 1H2.5C2.10218 1 1.72064 1.15804 1.43934 1.43934C1.15804 1.72064 1 2.10218 1 2.5V14.5C1 14.8978 1.15804 15.2794 1.43934 15.5607C1.72064 15.842 2.10218 16 2.5 16H10C10.3978 16 10.7794 15.842 11.0607 15.5607C11.342 15.2794 11.5 14.8978 11.5 14.5V5.125ZM7.375 5.125C7.375 5.22446 7.41451 5.31984 7.48483 5.39016C7.55516 5.46049 7.65054 5.5 7.75 5.5H10.75V14.5C10.75 14.6989 10.671 14.8897 10.5303 15.0303C10.3897 15.171 10.1989 15.25 10 15.25H2.5C2.30109 15.25 2.11032 15.171 1.96967 15.0303C1.82902 14.8897 1.75 14.6989 1.75 14.5V2.5C1.75 2.30109 1.82902 2.11032 1.96967 1.96967C2.11032 1.82902 2.30109 1.75 2.5 1.75H7.375V5.125ZM10.1455 4.75L8.125 2.52737V4.75H10.1455Z" fill="#474D6A" stroke="#474D6A" stroke-width="0.3"/>
                    </svg>
                    <?= $qualification ?>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?= htmlspecialchars($applyUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="button" class="w-full rounded-full text-center text-white bg-blue-main p-2 mt-5">Apply Now</button>
                    </a>
                </li>
            </ul>
            <?php if ($createdAt !== ''): ?>
            <div class="text-end mt-3 text-[12px] text-[#A9A9A9] sm:tracking-[1px]"><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('cmsRenderJobsListFromCache')) {
    /** DPS-style careers listing: search bar, job cards grid, pagination. */
    function cmsRenderJobsListFromCache(): void
    {
        $jobs = cmsJobsListResolved();
        static $stylesPrinted = false;
        if (!$stylesPrinted) {
            $stylesPrinted = true;
            echo '<style>.cms-careers-jobs .page-prenext:hover .icon path{fill:#fff}</style>';
        }

        echo '<div class="cms-careers-jobs relative mb-[120px] -mt-16 sm:-mt-24 z-[5]">';
        echo '<div class="container mx-auto md:px-20 sm:px-0 px-10">';
        echo '<div class="px-7 py-12 bg-white rounded-[25px] sm:flex block items-center gap-9 border border-gray-200 relative z-[90] shadow-sm">';
        echo '<input id="cmsJobSearchTitle" class="rounded-full py-2 px-4 sm:w-1/4 w-full sm:mt-0 mt-2 border border-gray-200 outline-none" type="text" placeholder="Job Title" />';
        echo '<input id="cmsJobSearchType" class="rounded-full py-2 px-4 sm:w-1/4 w-full sm:mt-0 mt-2 border border-gray-200 outline-none" type="text" placeholder="Job Type" />';
        echo '<input id="cmsJobSearchLocation" class="rounded-full py-2 px-4 sm:w-1/4 w-full sm:mt-0 mt-2 border border-gray-200 outline-none" type="text" placeholder="Location" />';
        echo '<button type="button" id="cmsJobSearchBtn" class="flex items-center text-center justify-center gap-1 text-white font-semibold bg-blue-main rounded-full sm:w-1/4 w-full sm:mt-0 mt-2 py-2 px-4">';
        echo '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14.1667 14.1667L17.5 17.5M2.5 9.16667C2.5 10.9348 3.20238 12.6305 4.45262 13.8807C5.70286 15.131 7.39856 15.8333 9.16667 15.8333C10.9348 15.8333 12.6305 15.131 13.8807 13.8807C15.131 12.6305 15.8333 10.9348 15.8333 9.16667C15.8333 7.39856 15.131 5.70286 13.8807 4.45262C12.6305 3.20238 10.9348 2.5 9.16667 2.5C7.39856 2.5 5.70286 3.20238 4.45262 4.45262C3.20238 5.70286 2.5 7.39856 2.5 9.16667Z" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        echo 'Search</button></div>';
        echo '</div>';

        if ($jobs === []) {
            echo '<p class="text-center text-gray-600 mt-10 px-4">No job openings are available at the moment. Please check back later.</p>';
            echo '</div>';
            return;
        }

        echo '<div id="cmsJobsNoResults" class="text-center text-red-600 text-lg mt-10 hidden">No job openings found matching your search.</div>';
        echo '<div id="cmsJobsGrid" class="grid grid-cols-1 sm:grid-cols-2 bg-white container mx-auto mt-10 gap-7 md:px-20 sm:px-0 px-10">';
        foreach ($jobs as $job) {
            if (!is_array($job)) {
                continue;
            }
            cmsRenderJobOpeningCard($job);
        }
        echo '</div>';

        echo '<div id="cmsJobsPagination" class="mt-10"></div>';
        echo '</div>';

        echo <<<'JS'
<script>
document.addEventListener("DOMContentLoaded", function () {
    var grid = document.getElementById("cmsJobsGrid");
    var pagination = document.getElementById("cmsJobsPagination");
    var noResults = document.getElementById("cmsJobsNoResults");
    if (!grid || !pagination) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll(".cms-job-card"));
    var perPage = 6;
    var currentPage = 1;
    var filtered = cards.slice();

    function getSearchTerms() {
        return {
            title: (document.getElementById("cmsJobSearchTitle") || {}).value || "",
            type: (document.getElementById("cmsJobSearchType") || {}).value || "",
            location: (document.getElementById("cmsJobSearchLocation") || {}).value || ""
        };
    }

    function applyFilter() {
        var terms = getSearchTerms();
        var titleQ = terms.title.trim().toLowerCase();
        var typeQ = terms.type.trim().toLowerCase();
        var locQ = terms.location.trim().toLowerCase();

        filtered = cards.filter(function (card) {
            var title = (card.getAttribute("data-title") || "").toLowerCase();
            var type = (card.getAttribute("data-type") || "").toLowerCase();
            var location = (card.getAttribute("data-location") || "").toLowerCase();
            var blob = (card.getAttribute("data-search") || "").toLowerCase();
            if (titleQ && title.indexOf(titleQ) === -1) return false;
            if (typeQ && type.indexOf(typeQ) === -1) return false;
            if (locQ && location.indexOf(locQ) === -1 && blob.indexOf(locQ) === -1) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function render() {
        cards.forEach(function (card) { card.style.display = "none"; });

        if (!filtered.length) {
            if (noResults) noResults.classList.remove("hidden");
            pagination.innerHTML = "";
            return;
        }
        if (noResults) noResults.classList.add("hidden");

        var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        var start = (currentPage - 1) * perPage;
        filtered.slice(start, start + perPage).forEach(function (card) {
            card.style.display = "";
        });

        var html = '<ul class="font-[400] text-[#313131] text-[14px] flex flex-wrap items-center justify-center gap-[6px] mx-auto px-4">';
        for (var p = 1; p <= totalPages; p++) {
            var active = p === currentPage ? ' bg-[#223B71] text-white font-semibold' : '';
            html += '<li class="px-3 py-2 border border-gray-200 rounded-[4px] hover:bg-[#223B71] hover:text-white hover:font-semibold' + active + '">';
            html += '<button type="button" class="cms-jobs-page-btn" data-page="' + p + '">' + p + '</button></li>';
        }
        if (currentPage < totalPages) {
            html += '<li class="page-prenext px-3 py-2 border border-gray-200 rounded-[4px] hover:bg-[#223B71] hover:text-white hover:font-semibold">';
            html += '<button type="button" class="cms-jobs-page-btn flex items-center gap-2" data-page="' + (currentPage + 1) + '">Next';
            html += '<svg width="5" height="8" class="icon" viewBox="0 0 5 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.267599 7.81586C0.0379225 7.577 0.0453701 7.19718 0.284233 6.9675L3.43443 4L0.284233 1.0325C0.0453697 0.802823 0.0379222 0.422997 0.267598 0.184134C0.497274 -0.0547288 0.8771 -0.0621767 1.11596 0.167499L4.71596 3.5675C4.83361 3.68062 4.9001 3.83679 4.9001 4C4.9001 4.16321 4.83361 4.31938 4.71596 4.4325L1.11596 7.8325C0.877101 8.06218 0.497275 8.05473 0.267599 7.81586Z" fill="#626262"/></svg>';
            html += '</button></li>';
        }
        html += '</ul>';
        pagination.innerHTML = totalPages > 1 ? html : '';

        pagination.querySelectorAll(".cms-jobs-page-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                currentPage = parseInt(btn.getAttribute("data-page"), 10) || 1;
                render();
                grid.scrollIntoView({ behavior: "smooth", block: "start" });
            });
        });
    }

    var searchBtn = document.getElementById("cmsJobSearchBtn");
    if (searchBtn) searchBtn.addEventListener("click", applyFilter);
    ["cmsJobSearchTitle", "cmsJobSearchType", "cmsJobSearchLocation"].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener("keydown", function (e) {
            if (e.key === "Enter") { e.preventDefault(); applyFilter(); }
        });
    });

    render();
});
</script>
JS;
    }
}

