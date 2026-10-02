<?php

if (!function_exists('cmsPankiPageSlugs')) {
    /** @return array<string, string> cache_key => CMS page slug */
    function cmsPankiPageSlugs(): array
    {
        return [
            'home_data' => 'home-page-panki',
            'about_us' => 'about-us-page-panki',
            'celebrating_excellence_page' => 'celebrating-excellence-page-panki',
            'excellence_in_action_page' => 'excellence-in-action-page-panki',
            'embark_excellence_page' => 'embark-on-a-journey-of-excellence-panki',
            'excellence_spotlight_page' => 'excellence-in-the-spotlight-panki',
            'online_registration_page' => 'online-registration-page-panki',
            'media_events_page' => 'media-events-page-panki',
            'beyond_academics_page' => 'beyond-academics-page-panki',
            'academics_page' => 'academics-page-panki',
            'admission_page' => 'admission-page-panki',
            'other_information_page' => 'other-information-page-panki',
            'terms_and_conditions_page' => 'terms-and-conditions-page-panki',
            'faq_page' => 'faqs-page-panki',
            'blogs_page' => 'blogs-page-panki',
            'testimonials_page' => 'testimonials-page-panki',
            'cbse_information_page' => 'cbse-information-page-panki',
            'school_infrastructure_page' => 'school-infrastructure-page-panki',
            'staff_teaching_page' => 'staff-teaching-page-panki',
            'results_academics_page' => 'results-and-academics-page-panki',
            'documents_information_page' => 'documents-information-page-panki',
            'mandatory_disclosure_page' => 'mpd-page-panki',
            'campus_tour_page' => 'campus-tour-page-panki',
            'careers_page' => 'careers-page-panki',
            'general_information_page' => 'general-information-page-panki',
            'pocso_committee_page' => 'pocso-committee-page-panki',
            'teachers_details_page' => 'teachers-details-page-panki',
            'sexual_harassment_page' => 'sexual-harassment-committee-page-panki',
            'pta_page' => 'parent-teacher-association-page-panki',
            'management_committee_page' => 'management-committee-page-panki',
            'our_story_data' => 'our-story-page-panki',
            'our_curriculum_data' => 'our-curriculum-page-panki',
            'events_page' => 'events-page-panki',
            'print_media_page' => 'print-media-page-panki',
            'video_gallery_page' => 'video-gallery-page-panki',
            'photo_gallery_page' => 'photo-gallery-page-panki',
            'xii_board_toppers_page' => 'xii-board-toppers-page-panki',
            'x_board_toppers_page' => '-x-board-toppers-page-panki',
            'awards_page' => 'awards-page-panki',
            'achievements_page' => 'achievements-page-panki',
            'oluxi_smart_skills_data' => 'oluxi-smart-skills-page-panki',
            'entrepreneurs_hub_data' => 'entrepreneurs-dream-hub-page-panki',
            'club_page' => 'clubs-page-panki',
            'sports_academics_data' => 'sports-academy-page-panki',
            'robotics_academics_data' => 'robotics-academy-page-panki',
            'animation_master_class_data' => 'animation-master-class-page-panki',
            'coding_academics_data' => 'coding-academy-page-panki',
            'innovation_hub_page' => 'innovation-hub-page-panki',
            'cambridge_page' => 'cambridge-assessment-page-panki',
            'facilities_data' => 'facilities-page-panki',
            'pedagogy_data' => 'pedagogy-page-panki',
            'contact_data' => 'contact-us-page-panki',
            'view_tc_data' => 'transfer-certificate-page-panki',
            'transferpolicy_data' => 'transfer-policy-page-panki',
            'book_list_info_data' => 'book-list-info-page-panki',
            'academic_calendar_data' => 'academic-calendar-page-panki',
            'fee_structure_data' => 'fee-structure-page-panki',
            'bus_route_page' => 'bus-route-page-panki',
            'school_brochure_data' => 'school-brochure-page-panki',
            'admission_procedure_data' => 'admission-procedure-page-panki',
        ];
    }
}

if (!function_exists('cmsPankiPageCacheKeyForSlug')) {
    function cmsPankiPageCacheKeyForSlug(string $slug): ?string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        foreach (cmsPankiPageSlugs() as $cacheKey => $cachedSlug) {
            if ($cachedSlug === $slug) {
                return (string) $cacheKey;
            }
        }

        return null;
    }
}

if (!function_exists('cmsPankiSlugFromScript')) {
    function cmsPankiSlugFromScript(string $script): string
    {
        $script = trim(strtolower($script), '/');
        $map = [
            'about-us' => 'about-us-page-panki',
            'contact-us' => 'contact-us-page-panki',
            'facilities' => 'facilities-page-panki',
            'pedagogy' => 'pedagogy-page-panki',
            'our-story' => 'our-story-page-panki',
            'our-curriculum' => 'our-curriculum-page-panki',
            'our-carriculam' => 'our-curriculum-page-panki',
            'school-brochure' => 'school-brochure-page-panki',
            'bus-route' => 'bus-route-page-panki',
            'fee-structure' => 'fee-structure-page-panki',
            'academic-calendar' => 'academic-calendar-page-panki',
            'transfer-policy' => 'transfer-policy-page-panki',
            'excellence-in-spotlight' => 'excellence-in-the-spotlight-panki',
            'excellence-in-action' => 'excellence-in-action-page-panki',
            'celebrating-excellence' => 'celebrating-excellence-page-panki',
            'embark-on-journey' => 'embark-on-a-journey-of-excellence-panki',
            'admission' => 'admission-page-panki',
            'admission-procedure' => 'admission-procedure-page-panki',
            'book-info' => 'book-list-info-page-panki',
            'online-registration' => 'online-registration-page-panki',
            'results-and-academics' => 'results-and-academics-page-panki',
            'general-information' => 'general-information-page-panki',
            'other-information' => 'other-information-page-panki',
            'termsandconditions' => 'terms-and-conditions-page-panki',
            'teachers-details' => 'teachers-details-page-panki',
            'parent-teacher-association' => 'parent-teacher-association-page-panki',
            'sexual-harassment-committee' => 'sexual-harassment-committee-page-panki',
            'pocso-committee' => 'pocso-committee-page-panki',
            'management-committee' => 'management-committee-page-panki',
            'awards' => 'awards-page-panki',
            'achievements' => 'achievements-page-panki',
            'x-toppers' => '-x-board-toppers-page-panki',
            'xii-toppers' => 'xii-board-toppers-page-panki',
            'topper-class-x' => '-x-board-toppers-page-panki',
            'topper-class-xii' => 'xii-board-toppers-page-panki',
            'oluxi-smart-class' => 'oluxi-smart-skills-page-panki',
            'faq' => 'faqs-page-panki',
            'testimonial' => 'testimonials-page-panki',
            'blog' => 'blogs-page-panki',
            'careers' => 'careers-page-panki',
            'apply-jobs' => 'careers-page-panki',
            'job-apply' => 'careers-page-panki',
            'cambridge' => 'cambridge-assessment-page-panki',
            'innovation' => 'innovation-hub-page-panki',
            'extra-carricular' => 'beyond-academics-page-panki',
            'extra-curricular' => 'beyond-academics-page-panki',
            'entrepreneur-dream-hub' => 'entrepreneurs-dream-hub-page-panki',
            'school-infrastructure' => 'school-infrastructure-page-panki',
            'infrastructure' => 'school-infrastructure-page-panki',
            'staff-teaching' => 'staff-teaching-page-panki',
            'teachers-training' => 'staff-teaching-page-panki',
            'mandatory-public-disclosure' => 'mpd-page-panki',
            'documents-and-information' => 'documents-information-page-panki',
            'animation-master-class' => 'animation-master-class-page-panki',
            'coding' => 'coding-academy-page-panki',
            'robotics' => 'robotics-academy-page-panki',
            'cbse-information' => 'cbse-information-page-panki',
            'about-the-clan' => 'about-the-clan-page-panki',
            'campus-tour' => 'campus-tour-page-panki',
            'our-campus' => 'campus-tour-page-panki',
            'meet-the-world' => 'meet-the-world-page-panki',
            'mun' => 'mun-page-panki',
            'clubs' => 'clubs-page-panki',
            'sports' => 'sports-academy-page-panki',
            'transfer-certificate' => 'transfer-certificate-page-panki',
            'event' => 'media-events-page-panki',
            'events' => 'media-events-page-panki',
            'photo-gallery' => 'photo-gallery-page-panki',
            'video-gallery' => 'video-gallery-page-panki',
            'print-media' => 'print-media-page-panki',
            'event-gallery' => 'media-events-page-panki',
            'achievement-gallery' => 'achievements-page-panki',
            'gallery' => 'photo-gallery-page-panki',
        ];

        if (isset($map[$script])) {
            return $map[$script];
        }

        return str_replace('_', '-', $script) . '-page-panki';
    }
}
