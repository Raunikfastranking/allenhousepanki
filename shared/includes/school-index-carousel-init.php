<?php

/**
 * Initializes home-page Glide carousels (requires school-glide-safe-script.php before this script).
 * Include only on the school index/home page — not from global foot.php.
 */
if (!function_exists('indexCarouselInitScript')) {
function indexCarouselInitScript(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
    (function () {
        function isHomePage() {
            return !!document.querySelector('.heroSlider');
        }

        function initIndexCarousels() {
            if (!isHomePage() || typeof window.safeMountGlide !== 'function') {
                return;
            }
            var mount = window.safeMountGlide;

            function mountHome(selector, options) {
                var root = document.querySelector(selector);
                if (!root) {
                    return null;
                }
                var opts = Object.assign({ remount: true }, options || {});
                return mount(selector, opts);
            }

            function syncHeroSlideWidths() {
                if (typeof window.syncHeroSlideWidths === 'function') {
                    window.syncHeroSlideWidths();
                }
            }

            mountHome('.heroSlider', {
                type: 'slider',
                rewind: true,
                focusAt: 0,
                perView: 1,
                gap: 0,
                peek: 0,
                autoplay: 3500,
                animationDuration: 700,
                breakpoints: {
                    1024: { type: 'slider', rewind: true, perView: 1, gap: 0, peek: 0, focusAt: 0 },
                    767: { type: 'slider', rewind: true, perView: 1, gap: 0, peek: 0, focusAt: 0 },
                    640: { type: 'slider', rewind: true, perView: 1, gap: 0, peek: 0, focusAt: 0 }
                }
            });

            function syncHeroAfterImages() {
                syncHeroSlideWidths();
                document.querySelectorAll('.heroSlider .hero-slide-img, .heroSlider img').forEach(function (img) {
                    if (img.complete) {
                        syncHeroSlideWidths();
                    } else {
                        img.addEventListener('load', syncHeroSlideWidths, { once: true });
                    }
                });
            }

            requestAnimationFrame(syncHeroAfterImages);
            setTimeout(syncHeroAfterImages, 50);
            var heroResizeTimer = null;
            function debouncedHeroSync() {
                clearTimeout(heroResizeTimer);
                heroResizeTimer = setTimeout(syncHeroAfterImages, 120);
            }
            window.addEventListener('resize', debouncedHeroSync);
            window.addEventListener('ah:home-glides-ready', syncHeroAfterImages);

            /* Excellence — 4 per view (desktop) */
            mountHome('.excellance-glide', {
                type: 'carousel',
                perView: 4,
                gap: 20,
                autoplay: 3500,
                animationDuration: 700,
                breakpoints: {
                    1024: { perView: 3, gap: 16 },
                    768: { perView: 2, gap: 12 },
                    640: { perView: 1, gap: 0 }
                }
            });

            /* Our Philosophy — 3 per view (desktop) */
            mountHome('#home-philosophy-glide', {
                type: 'carousel',
                focusAt: 0,
                perView: 3,
                gap: 24,
                autoplay: 3500,
                animationDuration: 700,
                breakpoints: {
                    900: { perView: 2, gap: 20 },
                    640: { perView: 1, gap: 12 }
                }
            });

            /* Our Campuses — 3 per view (desktop) */
            mountHome('#home-campus-glide', {
                type: 'carousel',
                perView: 3,
                gap: 24,
                autoplay: 3500,
                animationDuration: 700,
                breakpoints: {
                    900: { perView: 2, gap: 16 },
                    640: { perView: 1, gap: 0 }
                }
            });

            /* Gallery — 4 per view (desktop) */
            mountHome('#home-gallery-glide', {
                type: 'carousel',
                perView: 4,
                gap: 16,
                autoplay: 3500,
                animationDuration: 700,
                breakpoints: {
                    900: { perView: 3, gap: 12 },
                    640: { perView: 2, gap: 8 },
                    480: { perView: 1.2, gap: 8 }
                }
            });

            window.dispatchEvent(new Event('ah:home-glides-ready'));
            if (typeof window.ahPageLoaderPoll === 'function') {
                window.ahPageLoaderPoll();
            }
        }

        window.bootIndexHomeCarousels = initIndexCarousels;

        function scheduleInit() {
            if (!isHomePage()) {
                return;
            }
            if (typeof window.safeMountGlide !== 'function') {
                setTimeout(scheduleInit, 30);
                return;
            }
            initIndexCarousels();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleInit);
        } else {
            scheduleInit();
        }
        window.addEventListener('ah:tailwind-ready', scheduleInit);

        var homeResizeTimer = null;
        function remountOnViewportChange() {
            if (!isHomePage()) {
                return;
            }
            clearTimeout(homeResizeTimer);
            homeResizeTimer = setTimeout(function () {
                initIndexCarousels();
            }, 180);
        }

        window.addEventListener('resize', remountOnViewportChange);
        window.addEventListener('orientationchange', remountOnViewportChange);

        if (typeof window.matchMedia === 'function') {
            ['(max-width: 767px)', '(max-width: 640px)', '(max-width: 900px)', '(max-width: 1024px)'].forEach(function (query) {
                var mq = window.matchMedia(query);
                if (typeof mq.addEventListener === 'function') {
                    mq.addEventListener('change', remountOnViewportChange);
                } else if (typeof mq.addListener === 'function') {
                    mq.addListener(remountOnViewportChange);
                }
            });
        }
    })();
    </script>
    <?php
}
}
