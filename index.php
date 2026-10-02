<?php
require_once __DIR__ . '/includes/environment.php';
$page = "index";
$GLOBALS['school_is_index_page'] = true;
$GLOBALS['cms_page_slug'] = 'home-page-panki';
require_once __DIR__ . '/includes/apis.php';
require_once __DIR__ . '/includes/cms-page-helpers.php';
require_once __DIR__ . '/includes/testimonial-card-ui.php';
require_once __DIR__ . '/shared/includes/testimonials-page-tabs-shared.php';
require_once __DIR__ . '/includes/home-page-data.php';
$home_testimonials = ['data' => cmsHomeTestimonialItems()];
$hero_devices = cmsHomeHeroLegacyDevices();
$cta_items = cmsHomeCtaItems();
$excellence_items = cmsHomeExcellenceItems();
$approach_description = cmsHomeApproachDescription();
$content_heading = cmsHomeApproachHeading();
$content_heading = trim($content_heading) !== '' ? $content_heading : 'The Allenhouse Approach';
$show_approach_section = $content_heading !== '' || trim($approach_description) !== '';
$legacy_row = cmsHomeLegacyRow();
$philosophy_items = cmsHomePhilosophyItems();
$future_ready_items = cmsHomeFutureReadySkillsItems();
$home_video_url = cmsHomeVideoUrl();
$campus_items = cmsHomeCampusItems();
$gallery_items = cmsHomeGalleryImages();
$life_at_items = cmsHomeLifeAtAllenhouseItems();
$life_at_heading = cmsHomeLifeAtAllenhouseHeading();
$pop_data = cmsHomePopupLegacy();

/**
 * Extract alt text from API data regardless of field name
 * Supports: image_alt_text, image_alt, media_alt_text, alt_text, alt, title, name
 */
function getApiAltText($item, $defaultText = 'Image') {
    // Array of possible alt text field names (in order of priority)
    $possibleFields = [
        'image_alt_text',
        'image_alt', 
        'media_alt_text',
        'alt_text',
        'alt',
        'title',
        'name',
        'heading'
    ];
    
    // Check each possible field
    foreach ($possibleFields as $field) {
        if (isset($item[$field]) && !empty(trim($item[$field]))) {
            return trim($item[$field]);
        }
    }
    
    // Check nested in media array
    if (isset($item['media']) && is_array($item['media'])) {
        foreach ($possibleFields as $field) {
            if (isset($item['media'][$field]) && !empty(trim($item['media'][$field]))) {
                return trim($item['media'][$field]);
            }
        }
    }
    
    // If no alt text found, generate from image URL or use default
    if (isset($item['media']['urls'])) {
        return generateAltFromUrl($item['media']['urls'], $defaultText);
    }
    if (isset($item['urls'])) {
        return generateAltFromUrl($item['urls'], $defaultText);
    }
    if (isset($item['image'])) {
        return generateAltFromUrl($item['image'], $defaultText);
    }
    
    return $defaultText;
}

/**
 * Generate alt text from image URL as fallback
 */
function generateAltFromUrl($url, $defaultText = 'Image') {
    if (empty($url)) {
        return $defaultText;
    }
    
    // Extract filename from URL
    $filename = basename($url);
    // Remove extension
    $filename = pathinfo($filename, PATHINFO_FILENAME);
    // Replace hyphens, underscores, and numbers with spaces
    $alt = preg_replace('/[-_][0-9]+/', ' ', $filename);
    $alt = str_replace(['-', '_'], ' ', $alt);
    // Remove extra spaces
    $alt = preg_replace('/\s+/', ' ', $alt);
    // Capitalize words
    $alt = ucwords(trim($alt));
    
    return !empty($alt) && strlen($alt) > 1 ? $alt : $defaultText;
}

$homePageApi = cmsPankiHomePage();
$GLOBALS['cms_current_page'] = $homePageApi;
$GLOBALS['cms_og_site_name'] = 'Allenhouse Panki';
$GLOBALS['cms_og_domain'] = 'allenhousepanki.com';
$GLOBALS['panki_hero_lcp_preload'] = [];
foreach ($hero_devices as $deviceBanner) {
    $firstUrl = trim((string) ($deviceBanner['medias'][0]['urls'] ?? ''));
    if ($firstUrl === '') {
        continue;
    }
    $device = (string) ($deviceBanner['device'] ?? '');
    if ($device === 'desktop') {
        $GLOBALS['panki_hero_lcp_preload'][] = ['url' => $firstUrl, 'media' => '(min-width: 640px)'];
    } elseif ($device === 'mobile') {
        $GLOBALS['panki_hero_lcp_preload'][] = ['url' => $firstUrl, 'media' => '(max-width: 639px)'];
    }
}
$approachBgUrl = 'https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/NEg8GvmpkpIMpsvOF33ni6DGlQiEsGhKb8BpEOo4.png';
if ($show_approach_section) {
    $GLOBALS['panki_approach_bg_preload'] = $approachBgUrl;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://allenhousepanki.com" />
    <?php include "includes/meta.php" ?>
    <?php include "includes/head.php" ?>
</head>

<body style="overflow-x: hidden;">
<main id="main-content">

    <style>
    .glide__track {
        overflow: hidden;
    }

    .heroSlider,
    .heroSlider [data-glide-el="track"],
    .heroSlider .glide__track,
    .heroSlider .glide__slides {
        overflow: hidden !important;
    }

    .heroSlider {
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
    }

    .heroSlider .glide__slide,
    .heroSlider [data-glide-el="track"] ul > li {
        flex-shrink: 0;
        margin: 0;
        padding: 0;
    }

    .heroSlider picture.hero-slide-picture,
    .heroSlider .hero-slide-img {
        width: 100%;
        display: block;
    }
    @media (min-width: 640px) {
        .heroSlider picture.hero-slide-picture,
        .heroSlider .hero-slide-img {
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
        .heroSlider .glide__slides {
            height: auto !important;
        }
        .heroSlider [data-glide-el="track"] ul > li,
        .heroSlider .glide__slide {
            height: auto !important;
        }
        .heroSlider picture.hero-slide-picture,
        .heroSlider .hero-slide-img {
            height: auto !important;
            width: 100% !important;
            max-width: none !important;
            object-fit: initial !important;
        }
    }


    .glide-0333 .glide__slide img.campus-card__image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    #home-campus-glide:not(.glide--swipeable) .glide__slide:nth-child(n+4),
    .glide-0333:not(.glide--swipeable) .glide__slide:nth-child(n+4) {
        display: none;
    }

    #home-philosophy-glide:not(.glide--swipeable) .glide__slide:nth-child(n+4),
    .glide-0222:not(.glide--swipeable) .glide__slide:nth-child(n+4) {
        display: none;
    }

    .excellance-glide:not(.glide--swipeable) .glide__slide:nth-child(n+5) {
        display: none;
    }

    #home-gallery-glide:not(.glide--swipeable) .glide__slide:nth-child(n+5),
    .Images:not(.glide--swipeable) .glide__slide:nth-child(n+5) {
        display: none;
    }

    .Images .glide__slide img,
    .Images [data-glide-el="track"] li img {
        width: 100%;
        max-width: 100%;
        height: auto;
        max-height: 220px;
        object-fit: cover;
        display: block;
    }

    .excellance-glide .card-top-peudo {
        overflow: hidden;
        border-radius: 8px;
        cursor: pointer;
    }

    .excellance-glide .top-hide .absolute {
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0) 100%);
        width: 100%;
        left: 0;
        top: 0;
        pointer-events: none;
    }

<?php require_once __DIR__ . '/shared/includes/excellence-card-hover-styles.php'; ?>

    ul.tabs {
        padding: 0px;
        list-style: none;
    }

    ul.tabs li {
        background: none;
        color: #053B7A;
        display: inline-block;
        padding: 10px 15px;
        cursor: pointer;

        background: #ededed;
    }

    @media (max-width: 640px) {
        ul.tabs li {
            font-size: 14px;
            padding: 8px;
            background: #ededed;
        }

    }

    ul.tabs li.current {
        background: #053B7A;
        color: #fff;
    }

    .tab-content {
        display: none;
        padding-top: 15px;
    }

    .tab-content.current {
        display: block !important;
    }

    .img-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: transparent;
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .popup-content {
        background: rgba(0, 0, 0, 0.85);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        animation: animatepopup 0.3s ease-in-out forwards;

        /* Desktop default */
        width: 50vw;
        height: 90vh;
    }

    .popup-content img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        opacity: 0;
        transform: translateY(-100px);
        animation: animatepopup 0.3s ease-in-out forwards;
        border-radius: 10px;
    }

    /* Responsive for mobile screens */
    @media (max-width: 640px) {
        .popup-content {
            width: 100vw;
            height: 50vh;
        }
    }

    .close-btn {
        width: 35px;
        height: 30px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        position: absolute;
        top: 20px;
        right: 20px;
        cursor: pointer;
        z-index: 20;
        pointer-events: auto;
    }

    #formOverlay.hidden {
        display: none !important;
    }

    .close-btn .bar {
        height: 4px;
        background: #fff;
        border-radius: 2px;
    }

    .close-btn .bar:nth-child(1) {
        transform: rotate(45deg);
    }

    .close-btn .bar:nth-child(2) {
        transform: translateY(-4px) rotate(-45deg);
    }

    .img-popup.opened {
        display: flex;
    }

    @keyframes animatepopup {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mySwiper .swiper-pagination-bullet {
        background-color: #053B7A !important;
    }

    .mySwiper .swiper-pagination-bullet-active {
        background-color: #002A5B !important;
    }

    @media (max-width: 640px) {
        .swiper {
            box-shadow: none !important;
        }

        .swiper-slide {
            box-shadow: none !important;
        }

        .swiper-wrapper {
            margin: 0 !important;
        }
    }

    .youtube-facade__activator {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
        padding: 0;
        margin: 0;
        cursor: pointer;
        background: #000;
    }

    .youtube-facade__activator img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .youtube-facade__play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 68px;
        height: 48px;
        background: rgba(0, 0, 0, 0.75);
        border-radius: 12px;
        pointer-events: none;
    }

    .youtube-facade__play::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 54%;
        transform: translate(-50%, -50%);
        border-style: solid;
        border-width: 10px 0 10px 18px;
        border-color: transparent transparent transparent #fff;
    }

    </style>
    <?php testimonialCardStyles(); ?>

    <?php include "includes/header.php" ?>
       <?php
// ensure $pop_data exists and is structured the way you expect
$showPopup = false; // default — prevents "undefined variable" warnings

// safe checks: is there an array, first item, and an image string?
if (
    isset($pop_data) &&
    isset($pop_data['data']) &&
    is_array($pop_data['data']) &&
    isset($pop_data['data'][0]) &&
    is_array($pop_data['data'][0]) &&
    !empty(trim((string)($pop_data['data'][0]['image'] ?? '')))
) {
    $image = $pop_data['data'][0]['image'];
    $url   = $pop_data['data'][0]['url'] ?? '#';
    $text  = $pop_data['data'][0]['text'] ?? '';
    $showPopup = true;
} else {
    // ensure variables referenced later exist (avoid further notices)
    $image = '';
    $url = '#';
    $text = '';
}
?>
   <?php require_once __DIR__ . '/shared/includes/home-flyer-popup.php'; ?>


    <div class="relative w-full heroSlider opne-hide-circle">
                <div class="overflow-hidden" data-glide-el="track">
                    <ul class="glide__slides relative w-full overflow-hidden p-0 whitespace-no-wrap flex flex-no-wrap [backface-visibility: hidden] [transform-style: preserve-3d] [touch-action: pan-Y] [will-change: transform]">
                        <?php
                        $desktop_banners = [];
                        $mobile_banners = [];
                        foreach ($hero_devices as $deviceBanner) {
                            if (($deviceBanner['device'] ?? '') === 'desktop') {
                                $desktop_banners = $deviceBanner['medias'] ?? [];
                            } elseif (($deviceBanner['device'] ?? '') === 'mobile') {
                                $mobile_banners = $deviceBanner['medias'] ?? [];
                            }
                        }
                        ?>

                        <?php
                        // Ensure both desktop and mobile have the same number of banners
                        $banner_count = min(count($desktop_banners), count($mobile_banners));
                        ?>

                        <?php for ($i = 0; $i < $banner_count; $i++): ?>
                        <?php
                        $heroImgAttrs = $i === 0 ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"';
                        $heroMobileUrl = htmlspecialchars((string) ($mobile_banners[$i]['urls'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $heroDesktopUrl = htmlspecialchars((string) ($desktop_banners[$i]['urls'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $heroAlt = htmlspecialchars(getApiAltText($mobile_banners[$i], getApiAltText($desktop_banners[$i], 'Hero banner')), ENT_QUOTES, 'UTF-8');
                        ?>
                        <li class="relative glide__slide">
                            <picture class="hero-slide-picture block w-full">
                                <source media="(min-width: 640px)" srcset="<?= $heroDesktopUrl ?>">
                                <img src="<?= $heroMobileUrl ?>"
                                     alt="<?= $heroAlt ?>"
                                     class="hero-slide-img w-full block"
                                     decoding="async" <?= $heroImgAttrs ?>>
                            </picture>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <div class="absolute left-0 flex items-center justify-between w-full h-0 px-4 top-1/2 hide-circle"
                    data-glide-el="controls">
                    <button
                        class="inline-flex items-center justify-center relative right-[0px] hover:bg-[#FED72B] hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                        data-glide-dir="<" aria-label="prev slide">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button
                        class="inline-flex items-center justify-center relative left-[0px] hover:bg-[#FED72B] hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                        data-glide-dir=">" aria-label="next slide">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </div>

            </div>

    <div class="main relative">
        <div class="mt-5 sm:mt-[60px] 2xl:w-[1080px] lg:w-[824px] md:w-[567px] sm:w-[440px] sm:mx-auto sm:px-5 px-3">
            <div>
                <ul
                    class="grid 2xl:grid-cols-4 xl:grid-cols-4 lg:grid-cols-4 md:grid-cols-2 grid-cols-2 items-center gap-5">
                    <?php foreach ($cta_items as $data) { ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($data['url']) ?>" target="_blank"
                            class="w-full h-[110px] sm:h-auto border border-[#053B7A] rounded-[12px] bg-[#EFF6FF] sm:p-[20px]  transition-all duration-300 transform hover:scale-[1.03] hover:[box-shadow:0_5px_0_rgba(5,59,122,0.4),0_10px_0_rgba(5,59,122,0.3),0_15px_0_rgba(5,59,122,0.2),0_20px_0_rgba(5,59,122,0.1),0_25px_0_rgba(5,59,122,0.05)] flex-col sm:flex-row flex justify-center items-center">
                            <img src="<?php echo htmlspecialchars($data['media']['urls']) ?>" 
                                 alt="<?php echo htmlspecialchars(getApiAltText($data, $data['name'] ?? 'CTA icon')) ?>"
                                class="2xl:w-[60px] 2xl:h-[60px] xl:w-[60px] xl:h-[60px] lg:w-[45px] lg:h-[45px] sm:w-[45px] sm:h-[45px]  object-contain">
                            <span
                                class="font-[600] text-[13px] sm:text-[16px] text-[#053B7A] mt-2 sm:mt-0 sm:ml-2 text-center sm:text-left">
                                <?php echo htmlspecialchars($data['name']) ?>
                            </span>
                        </a>
                    </li>
                    <?php } ?>

                </ul>
            </div>
        </div>

        <?php if (!empty($excellence_items)): ?>
        <div class="mt-10 sm:mt-[60px] sm:mx-auto sm:px-5 px-3 excellance-container">
            <div class="glide relative w-full excellance-glide opne-hide-circle">
                <div class="glide__track overflow-hidden" data-glide-el="track">
                    <ul class="glide__slides">
                        <?php foreach ($excellence_items as $item):
                            $imageUrl = $item['image_url'] ?? $item['media']['urls'] ?? '';
                            $linkUrl = $item['link_url'] ?? '#';
                            $firstLine = $item['firstLine'] ?? '';
                            $secondLine = $item['secondLine'] ?? '';
                            $cleanDesc = $item['cleanDesc'] ?? '';
                            $fullTitle = $item['fullTitle'] ?? $firstLine;
                        ?>
                        <li class="glide__slide relative card-top-peudo rounded-[8px] cursor-pointer">
                            <div class="top-hide">
                                <div class="absolute z-10 p-3" style="left: 0; top: 0; width: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%);">
                                    <h2 class="text-[25px] 2xl:text-[28px] lg:text-[22px] leading-8 font-[700]" style="color: #B4D7FF;">
                                        <?= htmlspecialchars($firstLine, ENT_QUOTES, 'UTF-8') ?><br>
                                        <span class="text-[20px] 2xl:text-[20px] lg:text-[16px] font-[600]" style="color: rgba(255, 255, 255, 0.8);">
                                            <?= htmlspecialchars($secondLine, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </h2>
                                </div>
                            </div>
                            <div>
                                <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" class="w-[100%] h-[95%] rounded-[8px]" alt="<?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="absolute bottom-0 px-6 z-10 bottom-card-content bottom-open w-full">
                                <div class="relative bottom-[16px]">
                                    <h2 class="text-[28px] 2xl:text-[32px] lg:text-[22px] leading-7 font-[700]" style="color: #B4D7FF;">
                                        <?= htmlspecialchars($firstLine, ENT_QUOTES, 'UTF-8') ?><br>
                                        <span class="text-[20px] 2xl:text-[20px] lg:text-[16px] font-[600]" style="color: rgba(255, 255, 255, 0.8);">
                                            <?= htmlspecialchars($secondLine, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </h2>
                                    <div class="mt-3 text-white text-[16px] leading-relaxed"><?= $cleanDesc ?></div>
                                    <button class="w-[100%] relative bottom-[15px]">
                                        <a href="<?= htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-full text-blue-main p-2 font-[600] bg-white mt-5 flex justify-center items-center gap-2">
                                            Read More<span class="sr-only"> about <?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                            <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.929932 5.16199L12.6731 5.16199" stroke="#053B7A" stroke-width="1.5" stroke-linecap="round"></path>
                                                <path d="M9.42798 1.39856L13.1917 5.16174L9.42798 8.92542" stroke="#053B7A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </a>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                    <button class="inline-flex items-center justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide"><i class="fa-solid fa-angle-left"></i></button>
                    <button class="inline-flex items-center justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide"><i class="fa-solid fa-angle-right"></i></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($show_approach_section): ?>
        <div class="relative isolate overflow-hidden min-h-[275px] sm:min-h-[324px] aspect-[1440/275] sm:aspect-auto" role="img" aria-label="The Allenhouse Approach background">
            <img src="<?= htmlspecialchars($approachBgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" width="1440" height="275" class="absolute inset-0 w-full h-full object-cover object-center -z-10 pointer-events-none" loading="lazy" decoding="async" aria-hidden="true">
            <div class="relative mt-10 sm:mt-8 sm:py-16 py-4 2xl:w-[880px] lg:w-[624px] md:w-[467px] sm:w-[340px] sm:mx-auto mx-3">
                <div class="text-center">
                    <h1 class="text-[32px] font-[700] leading-9 text-blue-main relative"><?= htmlspecialchars($content_heading, ENT_QUOTES, 'UTF-8') ?></h1>
                    <?php if (trim($approach_description) !== ''): ?>
                    <p class="mt-4 text-[#808080] text-[18px]"><?= htmlspecialchars($approach_description, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div>

    <div class="mt-10">
        <div class="bg-[#132959] text-center pt-5 sm:pb-2 pb-5">
            <h2 class="text-[32px] font-[700] leading-9 text-white  relative text-center ">Legacy of Excellence
            </h2>
        </div>
        <div class="sm:py-8 py-5 bg-feature m-bg-feature ">
            <div class="legacyExcellence">
                <div class="flex justify-center gap-4 items-center sdfsd sm:py-0 py-6">
                    <span class="sm:w-[50%] w-[35%] inline-block "><img src="assets/images/icons/thump.png" alt="Years of excellence thumb icon"
                            class="w-[50px] h-[50px] object-contain" style="margin: 0 0 0 auto; aspect-ratio: 1 / 1;" width="50" height="50" loading="lazy" decoding="async"></span>
                    <div class="inline-block w-[50%]">
                        <h2 class="text-white font-[700] text-[35px] 2xl:text-[30px] lg:text-[20px] md:text-[20px]">
                            <?php echo htmlspecialchars($legacy_row['years'] ?? '') ?></h2>
                        <h3 class="text-white font-[700]  text-[22px] 2xl:text-[20px] lg:text-[15px] md:text-[15px] ">
                            Years</h3>
                    </div>
                </div>
                <div class="flex justify-center gap-4 items-center sm:pb-0 pb-10 sdfsd">
                    <span class="sm:w-[50%] w-[35%] inline-block "><img src="assets/images/icons/campus.png"
                            alt="Campus location icon" class="w-[50px] h-[50px] object-contain"
                            style="margin: 0 0 0 auto; aspect-ratio: 1 / 1;" width="50" height="50" loading="lazy" decoding="async"></span>
                    <div class="inline-block w-[50%]">
                        <h2 class="text-white font-[700] text-[35px] 2xl:text-[30px] lg:text-[20px] md:text-[20px] 
                        "><?php echo htmlspecialchars($legacy_row['campus'] ?? '') ?></h2>
                        <h3 class="text-white font-[700]  text-[22px] 2xl:text-[20px] lg:text-[15px] md:text-[15px]">
                            Campus</h3>
                    </div>
                </div>
                <div class="flex justify-center gap-4 items-center sm:pb-0 pb-10 sdfsd">
                    <span class="sm:w-[50%] w-[35%] inline-block "><img src="assets/images/icons/students.png"
                            alt="Students count icon" class="w-[50px] h-[50px] object-contain"
                            style="margin: 0 0 0 auto; aspect-ratio: 1 / 1;" width="50" height="50" loading="lazy" decoding="async"></span>
                    <div class="inline-block w-[50%]">
                        <h2 class="text-white font-[700] text-[35px] 2xl:text-[30px] lg:text-[20px] md:text-[20px] 
                        "><?php echo htmlspecialchars($legacy_row['student'] ?? '') ?></h2>
                        <h3 class="text-white font-[700]  text-[22px] 2xl:text-[20px] lg:text-[15px] md:text-[15px]">
                            Students</h3>
                    </div>
                </div>
                <div class="flex justify-center gap-4 items-center sm:pb-0 pb-10 sdfsd">
                    <span class="sm:w-[50%] w-[35%] inline-block "><img src="assets/images/icons/staff.png" alt="Staff members icon"
                            class="w-[50px] h-[50px] object-contain" style="margin: 0 0 0 auto; aspect-ratio: 1 / 1;" width="50" height="50" loading="lazy" decoding="async"></span>
                    <div class="inline-block w-[50%]">
                        <h2 class="text-white font-[700] text-[35px] 2xl:text-[30px] lg:text-[20px] md:text-[20px] 
                        "><?php echo htmlspecialchars($legacy_row['staff'] ?? '') ?></h2>
                        <h3 class="text-white font-[700]  text-[22px] 2xl:text-[20px] lg:text-[15px] md:text-[15px]">
                            Staff</h3>
                    </div>
                </div>
                <div class="flex justify-center gap-4 items-center sdfsd ">
                    <span class="sm:w-[50%] w-[35%] inline-block "><img src="assets/images/icons/allumi.png"
                            alt="Alumni network icon" class="w-[50px] h-[50px] object-contain"
                            style="margin: 0 0 0 auto; aspect-ratio: 1 / 1;" width="50" height="50" loading="lazy" decoding="async"></span>
                    <div class="inline-block w-[50%]">
                        <h2 class="text-white font-[700] text-[35px] 2xl:text-[30px] lg:text-[20px] md:text-[20px] ">
                            <?php echo htmlspecialchars($legacy_row['alumni'] ?? '') ?></h2>
                        <h3 class="text-white font-[700]  text-[22px] 2xl:text-[20px] lg:text-[15px] md:text-[15px]">
                            Alumni</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>


    <?php if (!empty($philosophy_items)): ?>
    <div class="2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-5 mt-10">
        <div class="text-center">
            <h2 class="text-[32px] font-[700] leading-8 text-blue-main">Our Philosophy <span class="sm:hidden"><br></span> Centres Around</h2>
        </div>
        <div id="home-philosophy-glide" class="glide glide-0222 relative w-full mt-5 opne-hide-circle" data-home-glide="philosophy">
            <div class="glide__track overflow-hidden" data-glide-el="track">
                <ul class="glide__slides">
                    <?php foreach ($philosophy_items as $opdata): ?>
                    <li class="glide__slide px-2">
                        <img src="<?php echo htmlspecialchars($opdata['media']['urls'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?php echo htmlspecialchars(getApiAltText($opdata, $opdata['title'] ?? 'Philosophy image'), ENT_QUOTES, 'UTF-8') ?>"
                            class="w-[100%] rounded-[8px]">
                        <div class="mt-4">
                            <h2 class="text-[18px] font-[700] leading-5 text-blue-main"><?php echo htmlspecialchars($opdata['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="text-gray-600 text-[16px] mt-1"><?php echo htmlspecialchars($opdata['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                <button class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide"><i class="fa-solid fa-angle-left"></i></button>
                <button class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide"><i class="fa-solid fa-angle-right"></i></button>
            </div>
            <div class="absolute bottom-[-20px] flex items-center justify-center w-full gap-2 hidden"
                data-glide-el="controls[nav]">
                <button class="group" data-glide-dir="=0" aria-label="goto slide 1"><span
                        class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-200 focus:outline-none"></span></button>
                <button class="group" data-glide-dir="=1" aria-label="goto slide 2"><span
                        class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-200 focus:outline-none"></span></button>
                <button class="group" data-glide-dir="=2" aria-label="goto slide 3"><span
                        class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-200 focus:outline-none"></span></button>
                <button class="group" data-glide-dir="=3" aria-label="goto slide 4"><span
                        class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-200 focus:outline-none"></span></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <style>
    .academic-box {
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .academic-box:hover {
        box-shadow: 5px 5px 15px rgba(17, 39, 89, 0.8);
        border-radius: 12px;
    }
    </style>

    <?php if (!empty($future_ready_items)): ?>
    <div style="background-image: url('https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/NEg8GvmpkpIMpsvOF33ni6DGlQiEsGhKb8BpEOo4.png'); background-repeat: no-repeat;"
        class="bg-cover sm:mt-20 mt-10 pt-5 pb-16"
        role="img"
        aria-label="Future Ready Skills background">
        <?= cmsHomeFutureReadySkillsHeadingHtml() ?>
        <div class="container mx-auto">
            <ul class="grid sm:grid-cols-3 grid-cols-2 sm:gap-5 gap-3 sm:mt-5 mt-10 sm:mx-[80px] sm:px-0 px-3">
                <?php foreach ($future_ready_items as $skillCard):
                    $skillUrl = trim((string) ($skillCard['url'] ?? ''));
                    $skillHref = ($skillUrl !== '' && $skillUrl !== '#') ? $skillUrl : '';
                    ?>
                <li class="mx-auto">
                    <?php if ($skillHref !== ''): ?>
                    <a href="<?= htmlspecialchars($skillHref, ENT_QUOTES, 'UTF-8') ?>" class="">
                        <img
                            class="border-[1px] border-blue-main academic-box sm:w-[80%] w-[]"
                            src="<?= htmlspecialchars($skillCard['image'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($skillCard['alt'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                    <?php else: ?>
                    <a class="">
                        <img
                            class="border-[1px] border-blue-main academic-box sm:w-[80%] w-[]"
                            src="<?= htmlspecialchars($skillCard['image'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($skillCard['alt'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>







    <div class="sm:mt-[100px] mt-10">
        <div class="2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto ">

            <div class="relative ">
                <div class="o-video mx-auto youtube-facade">
                    <?php
                    $embedUrl = cmsHomeYoutubeEmbedUrl($home_video_url);
                    if ($embedUrl !== ''):
                        if (!function_exists('cmsYoutubeExtractVideoId')) {
                            require_once __DIR__ . '/shared/includes/youtube-embed.php';
                        }
                        $homeVideoId = cmsYoutubeExtractVideoId($home_video_url);
                        if ($homeVideoId !== ''):
                            $facadeEmbedSrc = cmsYoutubeEmbedUrl($homeVideoId, [
                                'autoplay' => '1',
                                'mute' => '1',
                                'loop' => '1',
                                'playlist' => $homeVideoId,
                            ]);
                    ?>
                    <button type="button" class="youtube-facade__activator" data-embed-src="<?= htmlspecialchars($facadeEmbedSrc, ENT_QUOTES, 'UTF-8') ?>" aria-label="Play Allenhouse Panki promotional video">
                        <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($homeVideoId, ENT_QUOTES, 'UTF-8') ?>/hqdefault.jpg" alt="" width="560" height="315" loading="lazy" decoding="async">
                        <span class="youtube-facade__play" aria-hidden="true"></span>
                    </button>
                    <?php
                        endif;
                    endif;
                    ?>
                </div>
            </div>
        </div>

    </div>
    </div>
    <script>
    (function () {
        document.querySelectorAll('.youtube-facade__activator').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = btn.getAttribute('data-embed-src');
                var wrap = btn.closest('.youtube-facade');
                if (!src || !wrap) {
                    return;
                }
                var iframe = document.createElement('iframe');
                iframe.width = '560';
                iframe.height = '315';
                iframe.src = src;
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('allow', 'autoplay; encrypted-media');
                iframe.setAttribute('allowfullscreen', '');
                iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
                iframe.setAttribute('title', 'Allenhouse Panki promotional video');
                wrap.innerHTML = '';
                wrap.appendChild(iframe);
            });
        });
    })();
    </script>

   

    <?php if (!empty($campus_items)): ?>
    <div class="sm:mt-12 mt-8 2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto relative">

        <div class="mx-5">
            <div class="text-center">
                <h2 class="text-[30px] font-[700] leading-10 text-blue-main relative">Our Campuses</h2>
            </div>

            <div id="home-campus-glide" class="glide glide-0333 relative w-full mt-5 opne-hide-circle" data-home-glide="campus">
                <div class="glide__track overflow-hidden" data-glide-el="track">
                    <ul class="glide__slides">
                        <?php foreach ($campus_items as $data) { ?>
                        <li class="glide__slide px-2">
                            <div class="bg-white mb-5 rounded-[10px] h-full campus-card flex flex-col"
                                style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                                <img src="<?php echo htmlspecialchars($data['media']['urls'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?php echo htmlspecialchars(getApiAltText($data, $data['name'] ?? 'Campus image'), ENT_QUOTES, 'UTF-8') ?>"
                                    class="campus-card__image w-full rounded-t-[10px]">
                                <div class="campus-card__body mx-3 mt-4 pb-6 mb-2 flex flex-col flex-1">
                                    <h2 class="text-[22px] font-[700] leading-8 text-blue-main">
                                        <?php echo htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </h2>
                                    <div class="mt-3">
                                        <?php if (($data['addressline1'] ?? '') !== ''): ?>
                                        <div class="flex gap-3">
                                            <span class="mt-1 shrink-0">
                                                <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/Uk9fc8u92eMqkV3meFQNiUoRYB2DxzNi4d5T17Id.png" class="w-[20px] h-[20px]" alt="Location icon">
                                            </span>
                                            <p class="text-gray-500 capitalize text-[16px] w-[85%]"><?php echo htmlspecialchars($data['addressline1'], ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (($data['contact'] ?? '') !== ''): ?>
                                        <div class="flex gap-3 mt-2 items-center">
                                            <span class="shrink-0">
                                                <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/oJ6mhPYvNt2N9dgrQ8zY1IeW1ds6f8l9E9yvAiDS.png" class="w-[20px] h-[20px]" alt="Phone icon">
                                            </span>
                                            <p>
                                                <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $data['contact']), ENT_QUOTES, 'UTF-8') ?>" class="text-gray-500 capitalize text-[16px] w-[85%]"><?php echo htmlspecialchars($data['contact'], ENT_QUOTES, 'UTF-8') ?></a>
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="w-full rounded-full text-white bg-blue-main mt-5 shrink-0">
                                        <a href="<?php echo htmlspecialchars($data['weburl'] ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                                            class="flex items-center gap-2 p-2 justify-center">
                                            Visit Website
                                            <svg width="15" height="10" viewBox="0 0 15 10" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M1.36914 4.71826L13.1123 4.71826" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                                                <path d="M9.86719 0.955078L13.6309 4.71826L9.86719 8.48193" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <!-- Controls -->
                <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle"
                    data-glide-el="controls">
                    <button
                        class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                        data-glide-dir="<" aria-label="prev slide">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button
                        class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                        data-glide-dir=">" aria-label="next slide">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </div>
                <div class="home-campus-glide__bullets flex items-center justify-center w-full gap-2 mt-6 pb-2"
                    data-glide-el="controls[nav]"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>


     <?php if (!empty($life_at_items)): ?>
    <div class="relative sm:py-6 py-5 2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto mt-10">
        <div class="text-center"><?= $life_at_heading ?></div>
        <div class="mx-5 mt-7">
            <div class="sm:flex gap-3">
                <div class="sm:w-[33.33%]">
                    <?php if (isset($life_at_items[0])): ?>
                    <div><a><img src="<?= htmlspecialchars($life_at_items[0]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[0]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 object-cover hover:scale-90 transition delay-300" style="width:100%"></a></div>
                    <?php endif; ?>
                    <?php if (isset($life_at_items[1])): ?>
                    <div class="mt-3"><a><img src="<?= htmlspecialchars($life_at_items[1]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[1]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 object-cover hover:scale-90 transition delay-300" style="width:100%"></a></div>
                    <?php endif; ?>
                </div>
                <div class="sm:w-[33.33%]">
                    <?php if (isset($life_at_items[2])): ?>
                    <div><a><img src="<?= htmlspecialchars($life_at_items[2]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[2]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] object-cover hover:scale-90 transition delay-300" style="width:100%"></a></div>
                    <?php endif; ?>
                </div>
                <div class="w-[33.33%] sm:block hidden">
                    <?php if (isset($life_at_items[3])): ?>
                    <div><a><img src="<?= htmlspecialchars($life_at_items[3]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[3]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 hover:scale-90 transition delay-300" style="width:100%"></a></div>
                    <?php endif; ?>
                    <?php if (isset($life_at_items[4])): ?>
                    <div class="mt-3"><a><img src="<?= htmlspecialchars($life_at_items[4]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[4]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 hover:scale-90 transition delay-300" style="width:100%"></a></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (isset($life_at_items[5]) || isset($life_at_items[6])): ?>
            <div class="mt-[6px] sm:flex gap-3">
                <?php if (isset($life_at_items[5])): ?>
                <div class="sm:w-1/2"><a><img src="<?= htmlspecialchars($life_at_items[5]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[5]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 hover:scale-90 transition delay-300" style="width:100%"></a></div>
                <?php endif; ?>
                <?php if (isset($life_at_items[6])): ?>
                <div class="sm:w-1/2"><a><img src="<?= htmlspecialchars($life_at_items[6]['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($life_at_items[6]['alt'], ENT_QUOTES, 'UTF-8') ?>" class="popup-img rounded-[10px] mb-2 hover:scale-90 transition delay-300" style="width:100%"></a></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="img-popup"><div class="popup-content"><img src="" alt="Popup Image"><div class="close-btn"><div class="bar"></div><div class="bar"></div></div></div></div>
    <?php endif; ?>

    

    <?php cmsRenderHomeImageTestimonialsBlock($home_testimonials['data'] ?? []); ?>

    <div class="relative mt-8 mb-10">

                <div class="2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-3">
                    <div class="text-center">
                        <h2 class="text-[28px] sm:text-[32px] font-[700] leading-10  text-blue-main relative">Gallery
                        </h2>
                    </div>
                    <div id="home-gallery-glide" class="glide relative Images sm:pt-5 pt-2 opne-hide-circle" data-home-glide="gallery">
                        <div class="glide__track overflow-hidden mt-1" data-glide-el="track">
                            <ul class="glide__slides pb-5">
                                <?php foreach ($gallery_items as $galleryItem) {
                                    $galleryImage = $galleryItem['media']['urls'] ?? '';
                                    if ($galleryImage === '') {
                                        continue;
                                    }
                                ?>
                                <li class="glide__slide flex items-center mb-4">
                                    <div class="flex w-full">
                                        <div class="w-full">
                                            <img src="<?= htmlspecialchars($galleryImage, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars(getApiAltText($galleryItem, 'School gallery image'), ENT_QUOTES, 'UTF-8') ?>"
                                                class="rounded-[10px] w-full">
                                        </div>
                                    </div>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <!-- Controls -->
                        <div class="absolute left-0 hidden xl:flex items-center justify-between w-full h-0 px-4 top-1/2 hide-circle"
                            data-glide-el="controls">
                            <button
                                class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                                data-glide-dir="<" aria-label="prev slide">
                                <i class="fa-solid fa-angle-left"></i>
                            </button>
                            <button
                                class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20"
                                data-glide-dir=">" aria-label="next slide">
                                <i class="fa-solid fa-angle-right"></i>
                            </button>
                        </div>

                        <div class="absolute bottom-0 flex items-center justify-center w-full gap-2 hidden"
                            data-glide-el="controls[nav]">
                            <button class=" group" data-glide-dir="=0" aria-label="goto slide 1"><span
                                    class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-300 focus:outline-none"></span></button>
                            <button class=" group" data-glide-dir="=1" aria-label="goto slide 2"><span
                                    class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-300 focus:outline-non2"></span></button>
                            <button class=" group" data-glide-dir="=2" aria-label="goto slide 3"><span
                                    class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-300 focus:outline-none"></span></button>
                            <button class=" group" data-glide-dir="=3" aria-label="goto slide 4"><span
                                    class="block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-300 focus:outline-none"></span></button>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="photo-gallery"
                            class="text-[16px]font-[600] rounded-[20px] p-[5px] px-4 border-[1px] border-blue-main  hover:bg-red-500 hover:text-white hover:boder-red-500">View
                            All</a>
                    </div>
                </div>
            </div>

            <?php
            $GLOBALS['cms_rendered_section_count'] = cmsHomeLegacyRenderedStartIndex();
            ?>

        </div>
    </div>
    <?php include "includes/footer.php"; ?>


        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const videos = document.querySelectorAll('#tab-2 video');

            videos.forEach(video => {
                video.addEventListener('play', () => {
                    videos.forEach(otherVideo => {
                        if (otherVideo !== video) {
                            otherVideo.pause();
                        }
                    });
                });
            });
        });
        </script>

        <?php include "includes/foot.php" ?>
        <script>
        (function () {
            function withJq(fn) {
                function attempt() {
                    if (typeof window.jQuery !== 'undefined') {
                        fn(window.jQuery);
                        return true;
                    }
                    return false;
                }
                if (attempt()) {
                    return;
                }
                document.addEventListener('DOMContentLoaded', function () {
                    attempt();
                }, { once: true });
            }
            withJq(function ($) {
                $(document).ready(function() {
                    $('ul.tabs li').click(function() {
                        var tab_id = $(this).attr('data-tab');
                        $('ul.tabs li').removeClass('current');
                        $('.tab-content').removeClass('current');
                        $(this).addClass('current');
                        $("#" + tab_id).addClass('current');
                    });
                });
            });
        })();
        </script>
        <script>
        (function() {
            const counters = document.querySelectorAll(".count");
            if (!counters.length) {
                return;
            }

            function animateCounter(item) {
                const target = Number.parseInt(item.dataset.number || "0", 10);
                if (!Number.isFinite(target) || target <= 0) {
                    return;
                }
                const durationMs = 1200;
                const startAt = performance.now();
                function tick(now) {
                    const progress = Math.min((now - startAt) / durationMs, 1);
                    item.textContent = String(Math.floor(target * progress));
                    if (progress < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        item.textContent = String(target);
                    }
                }
                requestAnimationFrame(tick);
            }

            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            animateCounter(entry.target);
                            obs.unobserve(entry.target);
                        }
                    });
                }, { rootMargin: '50px' });
                counters.forEach(function (item) {
                    observer.observe(item);
                });
            } else {
                counters.forEach(animateCounter);
            }
        })();
        </script>

        <script>
        (function () {
            function withJq(fn) {
                function attempt() {
                    if (typeof window.jQuery !== 'undefined') {
                        fn(window.jQuery);
                        return true;
                    }
                    return false;
                }
                if (attempt()) {
                    return;
                }
                document.addEventListener('DOMContentLoaded', function () {
                    attempt();
                }, { once: true });
            }
            withJq(function ($) {
                $(document).ready(function() {
                    var imgPopup = $('.img-popup');
                    var popupImage = $('.img-popup img');
                    var closeBtn = $('.close-btn');

                    $('.popup-img').on('click', function() {
                        var img_src = $(this).attr('src');
                        popupImage.attr('src', img_src);
                        imgPopup.addClass('opened');
                    });

                    imgPopup.on('click', function() {
                        imgPopup.removeClass('opened');
                        popupImage.attr('src', '');
                    });

                    closeBtn.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        imgPopup.removeClass('opened');
                        popupImage.attr('src', '');
                    });

                    popupImage.on('click', function(e) {
                        e.stopPropagation();
                    });

                    $(document).on('keydown', function(e) {
                        if (e.key === "Escape") {
                            imgPopup.removeClass('opened');
                            popupImage.attr('src', '');
                        }
                    });
                });
            });
        })();
        </script>

        <?php testimonialCardScripts(); ?>

</main>
</body>

</html>