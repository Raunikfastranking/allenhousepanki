<?php

declare(strict_types=1);

/**
 * Contact Us page SEO overrides — used when CMS API meta fields are empty or stale.
 */

if (!function_exists('cmsContactPageSeoMap')) {
    /** @return array<string, array{meta_title:string,title:string,meta_description:string,meta_keywords:string}> */
    function cmsContactPageSeoMap(): array
    {
        return [
            'contact-us-page-lucknow' => [
                'meta_title' => 'Best CBSE School Contact Details in Lucknow | APS Lucknow',
                'title' => 'Best CBSE School Contact Details in Lucknow | APS Lucknow',
                'meta_description' => 'Looking for the best CBSE school contact details in Lucknow? Reach APS Lucknow for school admission enquiry, counselling, and quick response support.',
                'meta_keywords' => 'Best CBSE school contact details in Lucknow, Contact number best CBSE school Lucknow, School admission enquiry in Lucknow, Online School admission enquiry in Lucknow, Allenhouse Lucknow Contact Us Details',
            ],
            'contact-us-page-jhansi' => [
                'meta_title' => 'Best CBSE School Contact Details in Jhansi | APS Jhansi',
                'title' => 'Best CBSE School Contact Details in Jhansi | APS Jhansi',
                'meta_description' => 'Looking for the contact number of the best CBSE school in Jhansi? Connect with APS Jhansi for school admission enquiries and counselling today.',
                'meta_keywords' => 'Best CBSE school contact details in Jhansi, Contact number best CBSE school Jhansi, School admission enquiry in Jhansi, Online School admission enquiry in Jhansi, Allenhouse Jhansi Contact Us Detail',
            ],
            'contact-us-page-ghaziabad' => [
                'meta_title' => 'APS Ghaziabad Contact Details | Top Schools in Ghaziabad',
                'title' => 'APS Ghaziabad Contact Details | Top Schools in Ghaziabad',
                'meta_description' => 'Get contact details of the best CBSE school in Ghaziabad. Call APS Ghaziabad for admission enquiries, phone number, address & quick school support.',
                'meta_keywords' => 'Top CBSE school contact details in Ghaziabad, Contact number best CBSE school in Ghaziabad, School admission enquiry in Ghaziabad, Online School admission enquiry in Ghaziabad, Allenhouse Ghaziabad Contact Us Detail',
            ],
            'contact-us-page-agra' => [
                'meta_title' => 'Best ICSE School Contact Details in Agra | APS Agra',
                'title' => 'Best ICSE School Contact Details in Agra | APS Agra',
                'meta_description' => 'Looking for the best ICSE school contact details in Agra? APS Agra offers quick admission enquiry, contact number, and online school admission assistance.',
                'meta_keywords' => 'Best ICSE school contact details in Agra, Contact number best ICSE school Agra, School admission enquiry in Agra, Online School admission enquiry in Agra, Allenhouse Agra Contact Us Detail',
            ],
            'contact-us-page-khalasiline' => [
                'meta_title' => 'Best CBSE Schools Contact Details in Kanpur | Allenhouse Khalasi Line',
                'title' => 'Best CBSE Schools Contact Details in Kanpur | Allenhouse Khalasi Line',
                'meta_description' => 'Looking for top school admissions in Kanpur? APS Khalasi Line offers CBSE admissions with a clear process, eligibility criteria and easy online registration.',
                'meta_keywords' => 'Best CBSE schools contact details in Kanpur, School admission enquiry in Khalasi line Kanpur, Online School admission enquiry in Kanpur, Allenhouse Khalasi line Contact Us',
            ],
            'contact-us-page-panki' => [
                'meta_title' => 'Best CBSE School Contact Details in Kanpur | APS Panki',
                'title' => 'Best CBSE School Contact Details in Kanpur | APS Panki',
                'meta_description' => 'Get contact details of the best CBSE school in Kanpur. Call APS Panki for school admission enquiry, fees, campus visit & student enrollment support.',
                'meta_keywords' => 'CBSE school contact details in Kanpur, School admission enquiry in Kanpur, Allenhouse Public School Panki Contact Details',
            ],
            'contact-us-page-rooma' => [
                'meta_title' => 'Best CBSE Schools Contact Details in Kanpur | APS Rooma',
                'title' => 'Best CBSE Schools Contact Details in Kanpur | APS Rooma',
                'meta_description' => 'Get contact details of the best CBSE school in Kanpur. Call Allenhouse Rooma for school admission enquiry, fees, and online admission support.',
                'meta_keywords' => 'Best CBSE schools contact details in Kanpur, Contact number best CBSE schools Kanpur, School admission enquiry in Kanpur, Allenhouse Public School Rooma Contact Us',
            ],
        ];
    }
}

if (!function_exists('cmsApplyContactPageSeoOverride')) {
    function cmsApplyContactPageSeoOverride(?array $page): array
    {
        if (!is_array($page)) {
            $page = ['data' => []];
        }
        if (!is_array($page['data'] ?? null)) {
            $page['data'] = [];
        }

        $slug = trim((string) ($page['data']['slug'] ?? ($GLOBALS['cms_page_slug'] ?? '')));
        $seo = cmsContactPageSeoMap()[$slug] ?? null;
        if (!is_array($seo)) {
            return $page;
        }

        foreach (['meta_title', 'title', 'meta_description', 'meta_keywords'] as $key) {
            $value = trim((string) ($seo[$key] ?? ''));
            if ($value !== '') {
                $page['data'][$key] = $value;
            }
        }

        return $page;
    }
}
