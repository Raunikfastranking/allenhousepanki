<?php
declare(strict_types=1);

require_once __DIR__ . '/testimonial-card-ui.php';
require_once __DIR__ . '/home-page-data.php';

if (!function_exists('roomaTestimonialTabStyles')) {
    function roomaTestimonialTabStyles(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        testimonialCardStyles();
        ?>
        <style>
            .rooma-testimonials-wrap ul.tabs {
                padding: 0;
                list-style: none;
                text-align: center;
                margin: 0 0 12px;
            }
            .rooma-testimonials-wrap ul.tabs li {
                background: #ededed;
                color: #053B7A;
                display: inline-block;
                padding: 10px 15px;
                cursor: pointer;
                margin: 0 4px 8px;
                border-radius: 4px;
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
            .rooma-video-testimonials-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 1.5rem;
            }
            @media (min-width: 768px) {
                .rooma-video-testimonials-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (min-width: 1024px) {
                .rooma-video-testimonials-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            .rooma-video-testimonial-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            }
            .rooma-video-testimonial-card .aspect-video {
                aspect-ratio: 16 / 9;
                background: #000;
            }
            .rooma-video-testimonial-card iframe {
                width: 100%;
                height: 100%;
                border: 0;
            }
            .rooma-video-testimonial-card h3 {
                font-size: 1rem;
                font-weight: 600;
                color: #053B7A;
                padding: 12px 14px;
                margin: 0;
            }
        </style>
        <?php
    }
}

if (!function_exists('roomaStaticVideoTestimonials')) {
    /** Static video testimonials (CMS video sections override these embed IDs). */
    function roomaStaticVideoTestimonials(): array
    {
        require_once dirname(__DIR__) . '/shared/includes/testimonial-video-items.php';

        return cmsResolveVideoTestimonials(
            ['home-page-panki'],
            [
                ['title' => 'Allenhouse Panki', 'video_id' => 'LfBDFMjfSew'],
                ['title' => 'Parent Testimonial', 'video_id' => '1Vzi5P3yE7c'],
                ['title' => 'Life at Allenhouse', 'video_id' => 'Vb-_k6SIV34'],
            ]
        );
    }
}

if (!function_exists('roomaTestimonialItems')) {
    /**
     * Same written/image testimonials as the home page (cmsHomeTestimonialItems).
     *
     * @return list<array<string, mixed>>
     */
    function roomaTestimonialItems(): array
    {
        return cmsHomeTestimonialItems();
    }
}

if (!function_exists('pankiRenderTestimonialsBlock')) {
    /**
     * @param array{show_heading?: bool, wrapper_class?: string} $options
     */
    function pankiRenderTestimonialsBlock(array $options = []): void
    {
        require_once dirname(__DIR__) . '/shared/includes/testimonials-page-tabs-shared.php';
        cmsRenderTestimonialsPageTabsBlock(
            roomaTestimonialItems(),
            roomaStaticVideoTestimonials(),
            $options
        );
    }
}

if (!function_exists('roomaTestimonialsScripts')) {
    function roomaTestimonialsScripts(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
        <script>
        (function () {
            function initRoomaTestimonialTabs() {
                var wrap = document.querySelector('.rooma-testimonials-wrap');
                if (!wrap) return;
                wrap.querySelectorAll('.rooma-testimonial-tabs .tab-link').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        var tabId = tab.getAttribute('data-tab');
                        if (!tabId) return;
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
                        if (panel) panel.classList.add('current');
                    });
                });
            }

            window.initRoomaWrittenTestimonialsGlide = function () {
                if (typeof window.safeMountGlide !== 'function' || typeof Glide === 'undefined') {
                    return false;
                }
                var root = document.querySelector('.rooma-written-testimonials');
                if (!root) {
                    return true;
                }
                var mounted = window.safeMountGlide('.rooma-written-testimonials', {
                    remount: true,
                    type: 'carousel',
                    focusAt: 0,
                    perView: 2,
                    gap: 16,
                    autoplay: 3500,
                    animationDuration: 700,
                    breakpoints: {
                        767: {
                            type: 'slider',
                            perView: 1,
                            gap: 0,
                            peek: 0,
                            focusAt: 0
                        }
                    }
                });
                if (!mounted && root.getAttribute('data-cms-glide-mounted') !== '1') {
                    return false;
                }
                if (typeof window.initTestimonialCards === 'function') {
                    requestAnimationFrame(function () {
                        window.initTestimonialCards(true);
                    });
                }
                return true;
            };

            function scheduleWrittenTestimonials() {
                if (typeof window.initRoomaWrittenTestimonialsGlide !== 'function') {
                    setTimeout(scheduleWrittenTestimonials, 50);
                    return;
                }
                if (!window.initRoomaWrittenTestimonialsGlide()) {
                    setTimeout(scheduleWrittenTestimonials, 50);
                }
            }

            function initAll() {
                initRoomaTestimonialTabs();
                scheduleWrittenTestimonials();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAll, { once: true });
            } else {
                initAll();
            }
            window.addEventListener('load', scheduleWrittenTestimonials);
            window.addEventListener('ah:tailwind-ready', scheduleWrittenTestimonials);
        })();
        </script>
        <?php
    }
}
