<?php

declare(strict_types=1);

/**
 * Rooma pages that need API blocks or forms after CMS sections.
 */

if (!function_exists('cmsPankiPageShellSlugs')) {
    /** @return list<string> */
    function cmsPankiPageShellSlugs(): array
    {
        return [
            'cambridge-assessment-page-panki',
            'bus-route-page-panki',
            'testimonials-page-panki',
            'transfer-certificate-page-panki',
            'contact-us-page-panki',
            'campus-tour-page-panki',
        ];
    }
}

if (!function_exists('cmsPankiHasPageContent')) {
    function cmsPankiHasPageContent(?array $page): bool
    {
        if (cmsPageHasRenderableContent($page)) {
            return true;
        }

        $slug = trim((string) ($GLOBALS['cms_page_slug'] ?? ''));
        if ($slug !== '' && function_exists('cmsPageGalleryMeta') && cmsPageGalleryMeta($slug) !== null) {
            return true;
        }

        if ($slug === '' || !is_array($page['data'] ?? null) || ($page['data'] ?? []) === []) {
            return false;
        }

        if (function_exists('cmsPageIsPublished') && cmsPageIsPublished($page)) {
            return true;
        }

        if (in_array($slug, cmsPankiPageShellSlugs(), true)) {
            return true;
        }

        if ($slug === 'bus-route-page-panki' && function_exists('cmsFetchBusRoutes') && cmsFetchBusRoutes() !== []) {
            return true;
        }

        if ($slug === 'testimonials-page-panki') {
            return true;
        }

        // Careers route should resolve even when jobs API is empty,
        // so the page can show its own empty state instead of 404.
        if ($slug === 'careers-page-panki') {
            return true;
        }

        return false;
    }
}

if (!function_exists('cmsPankiTestimonialItems')) {
    /** @return list<array<string, mixed>> */
    function cmsPankiTestimonialItems(): array
    {
        require_once __DIR__ . '/home-page-data.php';

        return cmsHomeTestimonialItems();
    }
}

if (!function_exists('cmsRenderBusRoutesTable')) {
    function cmsRenderBusRoutesTable(): void
    {
        if (!function_exists('cmsFetchBusRoutes')) {
            return;
        }

        $routes = cmsFetchBusRoutes();
        echo '<div class="cms-bus-routes mt-8 overflow-x-auto">';
        echo '<table class="table-auto w-full border border-collapse border-gray-300 text-sm">';
        echo '<thead><tr class="bg-gray-200 text-left">';
        echo '<th class="border border-gray-300 px-4 py-2">S.No.</th>';
        echo '<th class="border border-gray-300 px-4 py-2">Vehicle No.</th>';
        echo '<th class="border border-gray-300 px-4 py-2">Driver Name</th>';
        echo '<th class="border border-gray-300 px-4 py-2">Driver Contact</th>';
        echo '<th class="border border-gray-300 px-4 py-2">Route Description</th>';
        echo '</tr></thead><tbody>';

        if ($routes === []) {
            echo '<tr><td colspan="5" class="border border-gray-300 px-4 py-6 text-center text-gray-500">Bus route information is not available at the moment.</td></tr>';
        } else {
            $serial = 1;
            foreach ($routes as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $source = [];
                $destination = [];
                $normal = [];
                foreach ($row['bus_routes'] ?? [] as $route) {
                    if (!is_array($route)) {
                        continue;
                    }
                    $label = (string) ($route['routes'] ?? '');
                    if ($label === '') {
                        continue;
                    }
                    if (!empty($route['is_source'])) {
                        $source[] = $label;
                    } elseif (!empty($route['is_destination'])) {
                        $destination[] = $label;
                    } else {
                        $normal[] = $label;
                    }
                }
                $ordered = array_merge($source, $normal, $destination);
                $driver = $row['busdriver_details'][0] ?? [];
                $driverName = is_array($driver) ? trim((string) ($driver['name'] ?? '')) : '';
                $driverPhone = is_array($driver) ? trim((string) ($driver['contact_value'] ?? '')) : '';
                echo '<tr class="hover:bg-gray-50">';
                echo '<td class="border border-gray-300 px-4 py-2">' . $serial++ . '</td>';
                echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars((string) ($row['busnumber'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($driverName !== '' ? $driverName : 'N/A', ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars($driverPhone !== '' ? $driverPhone : 'N/A', ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td class="border border-gray-300 px-4 py-2">' . htmlspecialchars(implode(' → ', $ordered), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';
    }
}

if (!function_exists('cmsRenderTransferCertificateForm')) {
    function cmsRenderTransferCertificateForm(): void
    {
        ?>
        <div class="cms-tc-search max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md mt-10 border border-gray-100">
            <form id="transfer-Form" class="block">
                <div class="flex flex-col">
                    <label for="admissionNo" class="text-gray-700">Admission No.</label>
                    <input type="text" id="admissionNo" name="admissionNo" class="mt-1 px-3 py-2 border border-gray-300 rounded-md w-full" required>
                </div>
                <div class="flex flex-col sm:mt-5 mt-3">
                    <label for="tcDob" class="text-gray-700">Date of Birth</label>
                    <input type="date" id="tcDob" name="dob" class="mt-1 px-3 py-2 border border-gray-300 rounded-md w-full" required>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-3 py-2 bg-blue-main text-white rounded-md w-full font-semibold">Search</button>
                </div>
            </form>
        </div>
        <div id="resultContainer" class="mt-6 hidden overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                    <tr>
                        <th class="px-6 py-4">Student Name</th>
                        <th class="px-6 py-4">Admission No.</th>
                        <th class="px-6 py-4">Class</th>
                        <th class="px-6 py-4">Parent Name</th>
                        <th class="px-6 py-4">DOB</th>
                        <th class="px-6 py-4">Download TC</th>
                    </tr>
                </thead>
                <tbody id="resultTableBody"></tbody>
            </table>
        </div>
        <p id="noResult" class="text-center text-red-500 mt-4 hidden">No data found.</p>
        <?php
        $tcSearchScript = dirname(__DIR__) . '/shared/includes/tc-search-script.php';
        if (is_file($tcSearchScript)) {
            include $tcSearchScript;
        }
    }
}

if (!function_exists('cmsRenderTestimonialsListing')) {
    /** Carousel data is sourced from home-page-panki only (see roomaTestimonialItems). */
    function cmsRenderTestimonialsListing(): void
    {
        require_once __DIR__ . '/panki-testimonials-ui.php';
        echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass('px-0 sm:px-0'), ENT_QUOTES, 'UTF-8') . '">';
        pankiRenderTestimonialsBlock(['show_heading' => false, 'wrapper_class' => 'relative mt-6 mb-10']);
        echo '</div>';
    }
}

if (!function_exists('cmsRenderCambridgePlaceholder')) {
    function cmsRenderCambridgePlaceholder(): void
    {
        echo '<div class="cms-cambridge-placeholder mt-10 ql-snow"><div class="ql-editor text-gray-600">';
        echo '<p>Cambridge Assessment International Education content for Allenhouse Panki is being updated. ';
        echo 'Please contact the school office or check back shortly for programme details, curriculum guides, and assessment information.</p>';
        echo '</div></div>';
    }
}

if (!function_exists('cmsPankiAfterSections')) {
    function cmsPankiAfterSections(array $page, string $slug): void
    {
        switch ($slug) {
            case 'bus-route-page-panki':
                echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') . '">';
                cmsRenderBusRoutesTable();
                echo '</div>';
                break;
            case 'transfer-certificate-page-panki':
                echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') . '">';
                cmsRenderTransferCertificateForm();
                echo '</div>';
                $GLOBALS['cms_include_contact_form_scripts'] = false;
                break;
            case 'testimonials-page-panki':
                cmsRenderTestimonialsListing();
                break;
            case 'cambridge-assessment-page-panki':
                if (!cmsPageHasRenderableContent($page)) {
                    echo '<div class="' . htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') . '">';
                    cmsRenderCambridgePlaceholder();
                    echo '</div>';
                }
                break;
            case 'admission-procedure-page-panki':
                if (function_exists('cmsMaybeRenderAdmissionProcedureEnquiryForm')) {
                    cmsMaybeRenderAdmissionProcedureEnquiryForm();
                }
                break;
        }
    }
}
