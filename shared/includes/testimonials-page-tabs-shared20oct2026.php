<?php
declare(strict_types=1);

if (!function_exists('cmsTestimonialsPageTabStyles')) {
    function cmsTestimonialsPageTabStyles(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        if (function_exists('testimonialCardStyles')) {
            testimonialCardStyles();
        }
        ?>
        <style>
            .rooma-testimonials-wrap ul.tabs {
                padding: 0;
                list-style: none;
                margin: 0 0 12px;
            }
            .rooma-testimonials-wrap ul.tabs li {
                background: #ededed;
                color: #053B7A;
                display: inline-block;
                padding: 10px 15px;
                cursor: pointer;
            }
            .rooma-testimonials-wrap ul.tabs li.current {
                background: #053B7A;
                color: #fff;
            }
            .rooma-testimonials-wrap .tab-content {
                display: none;
                padding-top: 15px;
            }
            .rooma-testimonials-wrap .tab-content.current {
                display: block !important;
            }
            .ah-testimonials-wrap .rooma-written-testimonials,
            .ah-testimonials-wrap .rooma-video-testimonials,
            .ah-testimonials-wrap .ah-home-testimonials {
                width: 100%;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .ah-testimonials-wrap .ah-testimonials-viewport {
                overflow: hidden;
                width: 100%;
            }
            .ah-testimonials-wrap .ah-testimonials-track {
                display: flex;
                flex-wrap: nowrap;
                gap: 8px;
                padding: 0;
                margin: 0;
                list-style: none;
                transition: transform 0.55s ease;
                will-change: transform;
            }
            .ah-testimonials-wrap .ah-testimonials-item {
                flex: 0 0 100%;
                width: 100%;
                box-sizing: border-box;
            }
            .ah-testimonials-wrap li.home-testimonial-card.ah-page-testimonial-card {
                padding: 12px !important;
                box-sizing: border-box;
            }
            .ah-testimonials-wrap .home-testimonial-card .testimonial-text,
            .ah-testimonials-wrap .home-testimonial-card .testimonial-text.clamp-2 {
                color: #e5e7eb !important;
            }
            .ah-testimonials-wrap .clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 4;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .ah-testimonials-wrap .testimonial-text.expanded {
                max-height: 200px;
                overflow-y: auto;
                overflow-x: hidden;
                display: block;
                -webkit-line-clamp: unset;
                white-space: normal;
            }
            .ah-testimonials-wrap .home-testimonial-card .moreless-button {
                display: block;
                width: fit-content;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
            .ah-testimonials-wrap .ah-page-testimonial-card {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                width: 100% !important;
                box-sizing: border-box;
            }
            .ah-testimonials-wrap .ah-page-testimonial-card > div {
                width: 90% !important;
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
                box-sizing: border-box;
            }
            .ah-testimonials-wrap .ah-page-testimonial-card .testimonial-text {
                width: 100% !important;
                word-wrap: break-word;
                overflow-wrap: anywhere;
                white-space: normal;
                text-align: center !important;
            }
            @media (max-width: 819px) {
                .rooma-testimonials-wrap ul.tabs li {
                    font-size: 14px;
                    padding: 8px;
                }
            }
            .ah-testimonials-wrap .ah-home-testimonials,
            .ah-testimonials-wrap .rooma-written-testimonials,
            .ah-testimonials-wrap .rooma-video-testimonials {
                position: relative;
            }
            .ah-testimonials-wrap .ah-testimonials-controls[hidden] {
                display: none !important;
            }
            .ah-testimonials-wrap .ah-testimonials-controls:not([hidden]) {
                display: flex !important;
            }
            @media (min-width: 820px) {
                .ah-testimonials-wrap .ah-testimonials-item {
                    flex: 0 0 50%;
                    width: 50%;
                }
                .ah-testimonials-wrap .ah-page-testimonial-card {
                    flex-direction: row !important;
                    align-items: flex-start !important;
                    height: 236px;
                    min-height: 236px;
                    max-height: 236px;
                    overflow: hidden;
                }
                .ah-testimonials-wrap .ah-page-testimonial-card > div:first-child {
                    width: 30% !important;
                    flex-shrink: 0;
                }
                .ah-testimonials-wrap .ah-page-testimonial-card > div:last-child {
                    width: 50% !important;
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    min-width: 0;
                    min-height: 0;
                    overflow: hidden;
                    text-align: left;
                }
                .ah-testimonials-wrap .ah-page-testimonial-card .testimonial-text {
                    flex: 1 1 auto;
                    min-height: 0;
                    overflow: hidden;
                    text-align: left !important;
                }
                .ah-testimonials-wrap .ah-page-testimonial-card .moreless-button {
                    margin-left: 0;
                    margin-right: 0;
                    text-align: left;
                }
            }
        </style>
        <?php
    }
}

if (!function_exists('cmsRenderAhTestimonialsCarouselControls')) {
    function cmsRenderAhTestimonialsCarouselControls(): void
    {
        ?>
        <div class="ah-testimonials-controls absolute left-0 flex items-center justify-between w-full h-0 px-2 sm:px-4 top-1/2 pointer-events-none z-10" hidden>
            <button type="button"
                class="ah-testimonials-prev pointer-events-auto inline-flex items-center justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:border-slate-900 focus-visible:outline-none bg-white/90 shadow-md"
                aria-label="Previous testimonial">
                <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
            </button>
            <button type="button"
                class="ah-testimonials-next pointer-events-auto inline-flex items-center justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:border-slate-900 focus-visible:outline-none bg-white/90 shadow-md"
                aria-label="Next testimonial">
                <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
            </button>
        </div>
        <?php
    }
}

if (!function_exists('cmsRenderVideoTestimonialSlide')) {
    /**
     * @param array{title?: string, video_id?: string} $video
     */
    function cmsRenderVideoTestimonialSlide(array $video): void
    {
        $videoId = trim((string) ($video['video_id'] ?? ''));
        if ($videoId === '') {
            return;
        }

        $title = htmlspecialchars((string) ($video['title'] ?? 'Video Testimonial'), ENT_QUOTES, 'UTF-8');
        if (!function_exists('cmsYoutubeEmbedUrl')) {
            require_once __DIR__ . '/youtube-embed.php';
        }
        $embed = cmsYoutubeEmbedUrl($videoId);
        ?>
        <li class="ah-testimonials-item">
            <div class="w-full rounded-2xl shadow-lg sm:p-5 p-2">
                <iframe class="w-[100%] h-[300px]" src="<?= htmlspecialchars($embed, ENT_QUOTES, 'UTF-8') ?>"
                    title="<?= $title ?>"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
        </li>
        <?php
    }
}

if (!function_exists('cmsRenderTestimonialsPageTabsBlock')) {
    /**
     * @param list<array<string, mixed>> $items
     * @param list<array{title: string, video_id: string}> $videos
     * @param array{show_heading?: bool, wrapper_class?: string} $options
     */
    function cmsRenderTestimonialsPageTabsBlock(array $items, array $videos, array $options = []): void
    {
        $showHeading = !isset($options['show_heading']) || $options['show_heading'];
        $wrapperClass = trim((string) ($options['wrapper_class'] ?? 'relative sm:mt-10 mt-8'));
        $videos = array_values(array_filter($videos, static function ($video): bool {
            return is_array($video) && trim((string) ($video['video_id'] ?? '')) !== '';
        }));

        cmsTestimonialsPageTabStyles();
        ?>
        <div class="<?= htmlspecialchars($wrapperClass, ENT_QUOTES, 'UTF-8') ?> ah-testimonials-wrap rooma-testimonials-wrap" id="testimonials">
            <div class="2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 mx-3 px-3">
                <?php if ($showHeading): ?>
                <div class="text-center">
                    <h2 class="text-[28px] sm:text-[30px] font-[700] leading-10 text-blue-main relative">Testimonials</h2>
                </div>
                <?php endif; ?>

                <ul class="tabs rooma-testimonial-tabs" role="tablist">
                    <li class="tab-link current" data-tab="rooma-tab-written" role="tab" aria-selected="true">Image Testimonials</li>
                    <li class="tab-link" data-tab="rooma-tab-video" role="tab" aria-selected="false">Video Testimonials</li>
                </ul>

                <div class="relative sm:pt-5 pt-2">
                    <?php if ($items === []): ?>
                        <div id="rooma-tab-written" class="tab-content current rooma-written-testimonials">
                            <p class="text-center text-gray-500 py-12">Image testimonials will be published soon.</p>
                        </div>
                    <?php else: ?>
                        <div id="rooma-tab-written" class="tab-content current rooma-written-testimonials relative" data-ah-carousel="written">
                            <div class="ah-testimonials-viewport overflow-hidden mt-1">
                                <ul class="ah-testimonials-track">
                                    <?php foreach ($items as $row) {
                                        if (is_array($row)) {
                                            renderTestimonialCard($row, true);
                                        }
                                    } ?>
                                </ul>
                            </div>
                            <?php cmsRenderAhTestimonialsCarouselControls(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($videos === []): ?>
                        <div id="rooma-tab-video" class="tab-content rooma-video-testimonials">
                            <p class="text-center text-gray-500 py-12">Video testimonials will be published soon.</p>
                        </div>
                    <?php else: ?>
                        <div id="rooma-tab-video" class="tab-content rooma-video-testimonials relative" data-ah-carousel="video">
                            <div class="ah-testimonials-viewport overflow-hidden mt-1">
                                <ul class="ah-testimonials-track">
                                    <?php foreach ($videos as $video) {
                                        if (is_array($video)) {
                                            cmsRenderVideoTestimonialSlide($video);
                                        }
                                    } ?>
                                </ul>
                            </div>
                            <?php cmsRenderAhTestimonialsCarouselControls(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        cmsTestimonialsPageTabsScripts();
        if (function_exists('testimonialCardScripts')) {
            testimonialCardScripts();
        }
    }
}

if (!function_exists('cmsTestimonialsPageTabsScripts')) {
    function cmsTestimonialsPageTabsScripts(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
        <script>
        (function () {
            function perView() {
                return window.innerWidth >= 820 ? 2 : 1;
            }

            function initAhCarousel(root) {
                if (!root || root.dataset.ahCarouselReady === '1') {
                    return;
                }
                var track = root.querySelector('.ah-testimonials-track');
                var viewport = root.querySelector('.ah-testimonials-viewport');
                if (!track || !viewport) {
                    return;
                }
                var items = track.querySelectorAll('.ah-testimonials-item');
                if (!items.length) {
                    return;
                }

                root.dataset.ahCarouselReady = '1';
                var index = 0;
                var timer = null;
                var autoplay = root.getAttribute('data-ah-carousel') !== 'video';

                function maxIndex() {
                    return Math.max(0, items.length - perView());
                }

                function itemPct() {
                    return 100 / perView();
                }

                function applyLayout() {
                    var pct = itemPct();
                    items.forEach(function (item) {
                        item.style.flex = '0 0 ' + pct + '%';
                        item.style.width = pct + '%';
                    });
                    if (index > maxIndex()) {
                        index = 0;
                    }
                    track.style.transform = 'translateX(-' + (index * pct) + '%)';
                }

                function next() {
                    index = index >= maxIndex() ? 0 : index + 1;
                    applyLayout();
                }

                function prev() {
                    index = index <= 0 ? maxIndex() : index - 1;
                    applyLayout();
                }

                function updateControls() {
                    var controls = root.querySelector('.ah-testimonials-controls');
                    if (!controls) {
                        return;
                    }
                    if (items.length > perView()) {
                        controls.removeAttribute('hidden');
                    } else {
                        controls.setAttribute('hidden', '');
                    }
                }

                function startAutoplay() {
                    if (!autoplay || items.length <= perView()) {
                        return;
                    }
                    clearInterval(timer);
                    timer = setInterval(next, 3500);
                }

                applyLayout();
                updateControls();
                startAutoplay();

                var prevBtn = root.querySelector('.ah-testimonials-prev');
                var nextBtn = root.querySelector('.ah-testimonials-next');
                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        clearInterval(timer);
                        prev();
                        startAutoplay();
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        clearInterval(timer);
                        next();
                        startAutoplay();
                    });
                }

                window.addEventListener('resize', function () {
                    applyLayout();
                    updateControls();
                    startAutoplay();
                });

                root._ahCarousel = { goTo: function (i) { index = i; applyLayout(); }, next: next, prev: prev };
            }

            function initAllCarousels() {
                document.querySelectorAll('[data-ah-carousel]').forEach(initAhCarousel);
            }

            function initRoomaTestimonialTabs() {
                var wrap = document.querySelector('.rooma-testimonials-wrap');
                if (!wrap) {
                    return;
                }
                wrap.querySelectorAll('.rooma-testimonial-tabs .tab-link').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        var tabId = tab.getAttribute('data-tab');
                        if (!tabId) {
                            return;
                        }
                        wrap.querySelectorAll('.rooma-testimonial-tabs .tab-link').forEach(function (t) {
                            t.classList.remove('current');
                            t.setAttribute('aria-selected', 'false');
                        });
                        wrap.querySelectorAll('.tab-content').forEach(function (panel) {
                            panel.classList.remove('current');
                        });
                        tab.classList.add('current');
                        tab.setAttribute('aria-selected', 'true');
                        var panel = document.getElementById(tabId);
                        if (panel) {
                            panel.classList.add('current');
                            initAhCarousel(panel);
                        }
                        if (tabId !== 'rooma-tab-video') {
                            wrap.querySelectorAll('#rooma-tab-video iframe').forEach(function (frame) {
                                try {
                                    frame.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                                } catch (e) {}
                            });
                        }
                    });
                });
            }

            window.initRoomaWrittenTestimonialsGlide = function () {
                initAllCarousels();
                if (typeof window.initTestimonialCards === 'function') {
                    window.initTestimonialCards();
                }
                return true;
            };

            window.initRoomaVideoTestimonialsGlide = function () {
                var videoRoot = document.querySelector('#rooma-tab-video[data-ah-carousel]');
                if (videoRoot) {
                    initAhCarousel(videoRoot);
                }
                return true;
            };

            function initAll() {
                initRoomaTestimonialTabs();
                initAllCarousels();
                if (typeof window.initTestimonialCards === 'function') {
                    window.initTestimonialCards();
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAll, { once: true });
            } else {
                initAll();
            }
            window.addEventListener('load', initAll);
            window.addEventListener('ah:tailwind-ready', initAll);
        })();
        </script>
        <?php
    }
}

if (!function_exists('cmsRenderHomeImageTestimonialsBlock')) {
    /**
     * Home page image testimonials carousel (no video tab).
     *
     * @param list<array<string, mixed>> $items
     */
    function cmsRenderHomeImageTestimonialsBlock(array $items): void
    {
        if (!function_exists('renderTestimonialCard')) {
            require_once dirname(__DIR__, 2) . '/includes/testimonial-card-ui.php';
        }

        cmsTestimonialsPageTabStyles();
        ?>
        <div class="relative sm:mt-20 mt-10 ah-testimonials-wrap" id="testimonials">
            <div class="2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 mx-3 px-3">
                <div class="text-center">
                    <h2 class="text-[28px] sm:text-[30px] font-[700] leading-10 text-blue-main relative">Testimonials</h2>
                </div>
                <div class="mx-3 2xl:w-[1280px] lg:w-[1024px] md:w-[767px] sm:w-[640px] sm:mx-auto sm:px-5 px-3">
                    <div class="relative sm:pt-5 pt-2">
                        <?php if ($items === []): ?>
                            <p class="text-center text-gray-500 py-12">Testimonials will be published soon.</p>
                        <?php else: ?>
                            <div class="ah-home-testimonials relative" data-ah-carousel="written">
                                <div class="ah-testimonials-viewport overflow-hidden mt-1">
                                    <ul class="ah-testimonials-track">
                                        <?php foreach ($items as $row) {
                                            if (is_array($row)) {
                                                renderTestimonialCard($row, true);
                                            }
                                        } ?>
                                    </ul>
                                </div>
                                <?php cmsRenderAhTestimonialsCarouselControls(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        cmsTestimonialsPageTabsScripts();
        if (function_exists('testimonialCardScripts')) {
            testimonialCardScripts();
        }
    }
}

if (!function_exists('cmsHomeTestimonialItemsForPageSlug')) {
    /**
     * Image testimonials for /testimonials — always sourced from the school home page slug.
     *
     * @return list<array<string, mixed>>
     */
    function cmsHomeTestimonialItemsForPageSlug(string $homePageSlug): array
    {
        if (!function_exists('cmsHomeTestimonialItemsFromPage')) {
            return function_exists('cmsHomeTestimonialItems') ? cmsHomeTestimonialItems() : [];
        }

        if (function_exists('cmsFetchPageBySlug')) {
            $home = cmsFetchPageBySlug($homePageSlug);
            $rows = cmsHomeTestimonialItemsFromPage($home);
            if ($rows !== []) {
                return $rows;
            }
        }

        if (isset($GLOBALS['api_cache']['home_data']) && is_array($GLOBALS['api_cache']['home_data'])) {
            $rows = cmsHomeTestimonialItemsFromPage($GLOBALS['api_cache']['home_data']);
            if ($rows !== []) {
                return $rows;
            }
        }

        return function_exists('cmsHomeTestimonialItems') ? cmsHomeTestimonialItems() : [];
    }
}

if (!function_exists('cmsStaticVideoTestimonialsList')) {
    /**
     * Static video testimonials only (no CMS). Empty list hides the video tab.
     *
     * @param list<array{title: string, video_id: string}> $rows
     * @return list<array{title: string, video_id: string}>
     */
    function cmsStaticVideoTestimonialsList(array $rows): array
    {
        require_once __DIR__ . '/testimonial-video-items.php';

        return cmsDedupeVideoTestimonialRows($rows);
    }
}
