<?php

declare(strict_types=1);

/**
 * Standard CSS/JS includes for all Allenhouse schools (precompiled Tailwind + site styles).
 * Uses site_asset_url() + <base href> so assets resolve on XAMPP subpaths and friendly URLs.
 */

if (!function_exists('site_asset_url')) {
    throw new RuntimeException('school-head-assets.php requires environment.php (site_asset_url) to be loaded first.');
}

$cssVersion = (string) ($GLOBALS['school_assets_css_version'] ?? '0.05');
$assetBase = function_exists('site_asset_url') ? site_asset_url('') : '/';
$GLOBALS['apsCssBase'] = $assetBase;
$GLOBALS['apsAssetBase'] = $assetBase;

?>
<base href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>">
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://ajax.googleapis.com" crossorigin>
<link rel="preconnect" href="https://myschool-assets.s3.ap-south-1.amazonaws.com" crossorigin>
<link rel="stylesheet" href="<?= htmlspecialchars(site_asset_url('assets/css/tailwind.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= htmlspecialchars($cssVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars(site_asset_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= htmlspecialchars($cssVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars(site_asset_url('assets/css/responsive.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= htmlspecialchars($cssVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.0.2/css/glide.core.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.0.2/css/glide.theme.min.css">
<style>
    /* Critical utilities (header / popup visibility before full CSS) */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
    .hidden { display: none !important; }
    /* Mobile top bar: force single horizontal flex row */
    @media (max-width: 767px) {
      .allen-top-header-bar--mobile {
        width: 100% !important;
        max-width: 100% !important;
      }
      .allen-top-header-bar--mobile .allen-top-header-cta-row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
      }
      .allen-top-header-bar--mobile .allen-top-header-cta-links {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
      }
      .allen-top-header-bar--mobile .allen-top-header-cta-links li {
        display: flex !important;
        flex: 0 0 auto !important;
      }
    }
    #popupForm.hidden, #popupForm:not(.flex) { display: none !important; }
    #popupForm {
        position: fixed;
        inset: 0;
        z-index: 100002;
    }
    .desktopEnquiry { display: none !important; }
    @media (min-width: 1300px) {
        .desktopEnquiry { display: block !important; }
    }
    @media (min-width: 640px) {
        .sm\:hidden { display: none !important; }
        .sm\:flex { display: flex !important; }
        .sm\:block { display: block !important; }
    }
    @media (min-width: 1024px) {
        .lg\:hidden { display: none !important; }
    }
    @media (min-width: 1280px) {
        .xl\:hidden { display: none !important; }
        .xl\:block { display: block !important; }
        .xl\:flex { display: flex !important; }
    }
    @media (max-width: 1299px) {
        .allen-mobile-enquire-tab {
            position: fixed !important;
            right: 28px !important;
            top: 50% !important;
            
            transform: translateY(-50%) rotate(90deg) !important;
            transform-origin: center right;
            z-index: 700 !important;
            pointer-events: none;
            isolation: isolate;
        }

        .allen-mobile-enquire-tab > a {
            pointer-events: auto;
            display: inline-block;
            padding: 0.625rem 1rem !important;
            font-size: 0.95rem !important;
            line-height: 1.2;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
        }
    }

    @media (max-width: 991px) and (max-height: 520px) {
        .allen-mobile-enquire-tab {
            top: 58% !important;
        }

        .allen-mobile-enquire-tab > a {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .allen-top-header-bar--desktop {
            padding-right: 5.5rem !important;
        }

        .allen-top-header-bar--desktop > .shrink-0 {
            margin-right: 0.75rem;
        }
    }
    .main.relative { margin-bottom: 0 !important; }
    footer.site-footer { margin-bottom: 0; }
    .site-footer-block { margin-bottom: 0; padding-bottom: 0; }
    html, body {
        margin: 0;
        padding-bottom: 0 !important;
        min-height: 0;
    }
    body { padding-bottom: 0; }
    footer,
    footer + .bg-gray-200,
    .bg-gray-200.p-3.text-center {
        margin-bottom: 0 !important;
    }
    .cms-home-sections:last-of-type {
        margin-bottom: 0 !important;
    }
    footer ~ div:has(> .whatsapp-button) {
        height: 0;
        margin: 0;
        padding: 0;
        overflow: visible;
    }
    body > footer:last-of-type,
    body > .bg-gray-200:last-of-type {
        margin-bottom: 0 !important;
    }
    #NewsPopup.fixed {
        margin-bottom: 0 !important;
    }
    footer #newsSubmit {
        background-color: #ef4444 !important;
        color: #fff !important;
        border: none;
    }
    footer #newsSubmit:hover:not(:disabled) {
        background-color: #dc2626 !important;
    }
    html {
        overflow-x: hidden;
        max-width: 100%;
    }
    body {
        overflow-x: hidden;
        max-width: 100%;
        width: 100%;
    }
    .main,
    .site-footer-block {
        max-width: 100%;
        overflow-x: hidden;
    }
    [data-cms-dedicated-glide="1"]:not([data-cms-glide-mounted="1"]):not(.rooma-written-testimonials):not(.rooma-video-testimonials),
    .pedagogy-glide-3view:not([data-cms-glide-mounted="1"]),
    .pedagogy-glide-4view:not([data-cms-glide-mounted="1"]) {
        opacity: 0;
        visibility: hidden;
        min-height: 160px;
    }
    [data-cms-dedicated-glide="1"][data-cms-glide-mounted="1"],
    .pedagogy-glide-3view[data-cms-glide-mounted="1"],
    .pedagogy-glide-4view[data-cms-glide-mounted="1"],
    .cms-glide-ready {
        opacity: 1;
        visibility: visible;
        transition: opacity 0.2s ease;
    }
    [data-cms-dedicated-glide="1"]:not([data-cms-glide-mounted="1"]) [data-glide-el="track"],
    .pedagogy-glide-3view:not([data-cms-glide-mounted="1"]) [data-glide-el="track"],
    .pedagogy-glide-4view:not([data-cms-glide-mounted="1"]) [data-glide-el="track"] {
        overflow: hidden;
    }
    .heroSlider {
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        overflow: hidden;
    }
    .heroSlider [data-glide-el="track"],
    .heroSlider .glide__track {
        overflow: hidden !important;
        width: 100%;
        max-width: 100%;
    }
    .heroSlider [data-glide-el="track"] > ul,
    .heroSlider .glide__slides {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        margin: 0;
        padding: 0;
        list-style: none;
        overflow: hidden;
    }
    .heroSlider [data-glide-el="track"] ul > li,
    .heroSlider .glide__slide {
        margin: 0;
        padding: 0;
        flex-shrink: 0;
        width: 100%;
        min-width: 100%;
        max-width: 100%;
    }
    .heroSlider picture.hero-slide-picture,
    .heroSlider .hero-slide-img,
    .heroSlider img {
        display: block;
        width: 100% !important;
        max-width: none;
        height: auto;
        vertical-align: top;
    }
    @media (min-width: 640px) {
        .heroSlider {
            aspect-ratio: 1920 / 601;
        }
        .heroSlider [data-glide-el="track"],
        .heroSlider .glide__track,
        .heroSlider [data-glide-el="track"] > ul,
        .heroSlider .glide__slides {
            height: 100%;
        }
        .heroSlider [data-glide-el="track"] ul > li,
        .heroSlider .glide__slide {
            height: 100%;
        }
        .heroSlider picture.hero-slide-picture,
        .heroSlider .hero-slide-img,
        .heroSlider img {
            height: 100%;
            object-fit: cover;
        }
    }
    @media (max-width: 639px) {
        .heroSlider {
            aspect-ratio: auto !important;
            height: auto !important;
        }
        .heroSlider [data-glide-el="track"],
        .heroSlider .glide__track,
        .heroSlider [data-glide-el="track"] > ul,
        .heroSlider .glide__slides {
            height: auto !important;
        }
        .heroSlider [data-glide-el="track"] ul > li,
        .heroSlider .glide__slide {
            height: auto !important;
        }
        .heroSlider picture.hero-slide-picture,
        .heroSlider .hero-slide-img,
        .heroSlider img {
            height: auto !important;
            object-fit: initial !important;
        }
    }
    .cms-page-banner,
    .cms-page-image-banner,
    .cms-section-hero.common-banner,
    .main .comman-banner.common-banner {
        height: 350px !important;
        min-height: 350px !important;
        max-height: 350px !important;
        aspect-ratio: auto !important;
        overflow: hidden;
        background-size: cover;
    }
    .excellance-glide:not([data-cms-glide-mounted="1"]),
    #home-philosophy-glide:not([data-cms-glide-mounted="1"]),
    #home-campus-glide:not([data-cms-glide-mounted="1"]),
    #home-gallery-glide:not([data-cms-glide-mounted="1"]),
    #tab-1.Testimonials:not([data-cms-glide-mounted="1"]),
    .tab-content.Testimonials:not(.rooma-written-testimonials):not([data-cms-glide-mounted="1"]) {
        opacity: 0;
        visibility: hidden;
    }
    .excellance-glide[data-cms-glide-mounted="1"],
    #home-philosophy-glide[data-cms-glide-mounted="1"],
    #home-campus-glide[data-cms-glide-mounted="1"],
    #home-gallery-glide[data-cms-glide-mounted="1"],
    #tab-1.Testimonials[data-cms-glide-mounted="1"],
    .tab-content.Testimonials:not(.rooma-written-testimonials)[data-cms-glide-mounted="1"],
    .tab-content.Testimonials.rooma-written-testimonials {
        opacity: 1;
        visibility: visible;
        transition: opacity 0.2s ease;
    }
</style>
<?php
require_once __DIR__ . '/cms-nav-nested-flyout.php';
cmsNavNestedFlyoutCss();

