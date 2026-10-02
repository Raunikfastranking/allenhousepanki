<?php
require_once dirname(__DIR__) . '/includes/environment.php';
require_once dirname(__DIR__) . '/proxy/config.php';
require_once dirname(__DIR__) . '/includes/api.php';
require_once dirname(__DIR__) . '/includes/api-scoped-loader.php';
require_once __DIR__ . '/landing-helpers.php';


$branchId = (string) BRANCH_ID;
$endpoints = [
    'landing_data' => '/pages/landing-page-panki',
    'header_footer_data' => '/public/branches/' . $branchId . '/layout-parts/',
    'statistics_data' => '/statistics/branch/' . $branchId,
];

$data = cmsApiFetchScopedEndpoints($endpoints);
$landing_data = $data['landing_data'];
$header_footer_data = $data['header_footer_data'];
$statistics_data = $data['statistics_data'];

$header_meta = landingHeaderMeta($header_footer_data);

$heroSection = landingSection($landing_data, 0);
$advanceSection = landingSection($landing_data, 1);
$videoSection = landingSection($landing_data, 2);
$videoTextSection = landingSection($landing_data, 3);
$shapingSection = landingSection($landing_data, 4);
$excellenceAwaitsSection = landingSection($landing_data, 5);
$whySection = landingSection($landing_data, 6);
$achievementSection = landingSection($landing_data, 7);
$worldClassSection = landingSection($landing_data, 8);
$centurySection = landingSection($landing_data, 9);
$visitCampusSection = landingSection($landing_data, 10);
$legacySection = landingSection($landing_data, 11);
$lifeSection = landingSection($landing_data, 12);
$testimonialSection = landingSection($landing_data, 13);

$hero_banner = cmsEmptyResponse();
$hero_text = landingHeroTextLegacy($heroSection);
$advance_data = landingAdvanceLegacy($advanceSection);
$about_us = landingAboutLegacy($videoSection, $videoTextSection);
$shaping_data = landingShapingLegacy($shapingSection);
$why_data = landingWhyLegacy($whySection);
$achievement_data = landingAchievementLegacy($achievementSection);
$century_data = landingCenturyLegacy($centurySection);
$legacy_data = landingLegacyStats($legacySection, $statistics_data);
$life_data = landingLifeLegacy($lifeSection);
$testimonial_data = landingTestimonialCardLegacy($testimonialSection);
$address_data = landingAddressLegacy($header_footer_data);
$social_data = landingSocialLegacy($header_footer_data);
$whatsapp = landingWhatsappLegacy($header_footer_data);
$brochure = landingBrochureLegacy($header_meta);
$world_class = cmsEmptyResponse();
$cta_data = cmsEmptyResponse();
$fee = cmsEmptyResponse();
