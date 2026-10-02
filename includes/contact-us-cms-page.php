<?php

declare(strict_types=1);

if (!function_exists('cmsPankiContactDetails')) {
    /** @return array<string, string> */
    function cmsPankiContactDetails(): array
    {
        require_once dirname(__DIR__) . '/shared/includes/cms-contact-details.php';

        return cmsResolveSchoolContactDetails([
            'addressline1' => 'Allenhouse Public School Panki',
            'addressline2' => 'Kanpur',
            'pincode' => '',
            'contact1' => '',
            'contact2' => '',
            'email' => 'contact@allenhousepanki.com',
        ], 'footer-aps-panki');
    }
}

if (!function_exists('cmsContactWidthWrapClass')) {
    function cmsContactWidthWrapClass(): string
    {
        return '2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-5';
    }
}

if (!function_exists('cmsRenderContactUsPage')) {
    function cmsRenderContactUsPage(?array $page): void
    {
        cmsRenderPageBanner($page, []);
        cmsRenderPageBreadcrumbs($page, []);

        $introHtml = '';
        $contactUsedSectionIndex = null;
        if (is_array($page)) {
            foreach ($page['data']['sections'] ?? [] as $index => $section) {
                if (!is_array($section)) {
                    continue;
                }
                if (function_exists('cmsSectionIsHero') && cmsSectionIsHero($section)) {
                    continue;
                }
                $heading = trim((string) ($section['content_heading'] ?? ''));
                $content = trim((string) ($section['content'] ?? ''));
                if ($heading !== '' || $content !== '') {
                    if (function_exists('cmsPrepareCmsHtml')) {
                        $introHtml = cmsPrepareCmsHtml($heading !== '' ? $heading . $content : $content);
                    } else {
                        $introHtml = $heading . $content;
                    }
                    $contactUsedSectionIndex = (int) $index;
                    break;
                }
            }
        }

        $contact = cmsPankiContactDetails();
        echo '<div class="cms-contact-page w-full mb-10">';
        require __DIR__ . '/contact-us-cms-body.php';
        echo '</div>';

        if (function_exists('cmsSetRenderedSectionStartIndex') && is_array($page) && isset($contactUsedSectionIndex)) {
            cmsSetRenderedSectionStartIndex((int) $contactUsedSectionIndex + 1);
        }
    }
}
