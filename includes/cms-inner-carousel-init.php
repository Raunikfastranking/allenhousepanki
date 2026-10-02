<?php

/**
 * Glide carousels for CMS inner pages (about-us, facilities, pedagogy).
 */
function cmsInnerCarouselInitScript(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
    (function () {
        var facilitiesIntroCarousel = {
            type: 'carousel',
            focusAt: 0,
            startAt: 0,
            perView: 3,
            autoplay: 3500,
            animationDuration: 700,
            gap: 20,
            breakpoints: {
                768: { perView: 2, gap: 16 },
                640: { perView: 1, gap: 12 }
            }
        };

        var aboutCarouselCommon = {
            type: 'carousel',
            focusAt: 0,
            startAt: 0,
            perView: 4,
            autoplay: 3500,
            animationDuration: 700,
            gap: 16,
            breakpoints: {
                1024: { perView: 4, gap: 16 },
                768: { perView: 3, gap: 14 },
                640: { perView: 1, gap: 12 }
            }
        };

        var pedagogyCarousel3View = {
            type: 'carousel',
            focusAt: 0,
            startAt: 0,
            perView: 3,
            autoplay: 3500,
            animationDuration: 700,
            gap: 16,
            breakpoints: {
                1024: { perView: 3, gap: 16 },
                768: { perView: 2, gap: 14 },
                640: { perView: 1, gap: 12 }
            }
        };

        var pedagogyCarousel4View = {
            type: 'carousel',
            focusAt: 0,
            startAt: 0,
            perView: 4,
            autoplay: 3500,
            animationDuration: 700,
            gap: 16,
            breakpoints: {
                1024: { perView: 4, gap: 16 },
                768: { perView: 3, gap: 14 },
                640: { perView: 1, gap: 12 }
            }
        };

        function mountFacilitiesIntroCarousel(remount) {
            if (typeof window.safeMountGlide !== 'function') {
                return;
            }
            var mount = window.safeMountGlide;
            var options = Object.assign({ remount: !!remount }, facilitiesIntroCarousel);
            document.querySelectorAll('.cms-facilities-intro-carousel').forEach(function (root, index) {
                if (!root.classList.contains('glide')) {
                    return;
                }
                if (!remount && root.getAttribute('data-cms-glide-mounted') === '1') {
                    return;
                }
                if (!root.id) {
                    root.id = 'cms-facilities-intro-glide-' + index;
                }
                mount('#' + root.id, options);
            });
        }

        function mountAboutCarousels(remount) {
            if (typeof window.safeMountGlide !== 'function') {
                return;
            }
            var mount = window.safeMountGlide;
            var options = Object.assign({ remount: !!remount }, aboutCarouselCommon);
            document.querySelectorAll('.cms-about-glide').forEach(function (root, index) {
                if (!root.classList.contains('glide')) {
                    return;
                }
                if (!remount && root.getAttribute('data-cms-glide-mounted') === '1') {
                    return;
                }
                if (!root.id) {
                    root.id = 'cms-about-glide-' + index;
                }
                mount('#' + root.id, options);
            });
        }

        function mountPedagogyCarousels(remount) {
            if (typeof window.safeMountGlide !== 'function') {
                return;
            }
            var mount = window.safeMountGlide;
            [
                { selector: '.pedagogy-glide-3view', options: pedagogyCarousel3View, idPrefix: 'cms-pedagogy-glide-3-' },
                { selector: '.pedagogy-glide-4view', options: pedagogyCarousel4View, idPrefix: 'cms-pedagogy-glide-4-' }
            ].forEach(function (group) {
                document.querySelectorAll(group.selector).forEach(function (root, index) {
                    if (!root.classList.contains('glide')) {
                        return;
                    }
                    if (!remount && root.getAttribute('data-cms-glide-mounted') === '1') {
                        return;
                    }
                    if (!root.id) {
                        root.id = group.idPrefix + index;
                    }
                    mount('#' + root.id, Object.assign({ remount: !!remount }, group.options));
                });
            });
        }

        function initInnerCarousels() {
            if (typeof window.safeMountGlide !== 'function') {
                return;
            }
            var mount = window.safeMountGlide;
            function cleanupMisplacedAboutTrackGlides() {
                document.querySelectorAll('.cms-about-page [data-glide-el="track"], .cms-facilities-page [data-glide-el="track"]').forEach(function (track) {
                    if (track.getAttribute('data-cms-glide-mounted') !== '1') {
                        return;
                    }
                    if (track.classList.contains('glide')) {
                        return;
                    }
                    if (track._cmsGlideInstance) {
                        try {
                            track._cmsGlideInstance.destroy();
                        } catch (destroyErr) {
                            /* ignore */
                        }
                        track._cmsGlideInstance = null;
                    }
                    track.removeAttribute('data-cms-glide-mounted');
                });
            }
            function mountOnce(selector, options) {
                var root = document.querySelector(selector);
                if (!root || root.getAttribute('data-cms-glide-mounted') === '1') {
                    return;
                }
                mount(selector, options);
            }
            function mountAll(selector, options) {
                document.querySelectorAll(selector).forEach(function (root, index) {
                    if (root.getAttribute('data-cms-glide-mounted') === '1') {
                        return;
                    }
                    if (!root.id) {
                        root.id = 'cms-glide-' + selector.replace(/[^a-z0-9]+/gi, '-') + '-' + index;
                    }
                    mountOnce('#' + root.id, options);
                });
            }
            cleanupMisplacedAboutTrackGlides();
            mountAboutCarousels(false);
            mountFacilitiesIntroCarousel(false);
            mountAll('.cms-facilities-carousel', Object.assign({}, aboutCarouselCommon, {
                gap: 20,
                breakpoints: { 640: { perView: 1, gap: 12 } }
            }));
            mountAll('.cms-pedagogy-carousel', Object.assign({}, pedagogyCarousel4View));
            mountPedagogyCarousels(false);
            (function initOtherBlogsCarousel() {
                if (typeof window.initCmsOtherBlogsCarousel === 'function') {
                    if (!window.initCmsOtherBlogsCarousel()) {
                        setTimeout(initOtherBlogsCarousel, 50);
                    }
                    return;
                }
                if (document.querySelector('.cms-other-blogs-carousel')) {
                    setTimeout(initOtherBlogsCarousel, 50);
                }
            })();
            (function initRoomaTestimonialsCarousel() {
                if (typeof window.initRoomaWrittenTestimonialsGlide === 'function') {
                    if (!window.initRoomaWrittenTestimonialsGlide()) {
                        setTimeout(initRoomaTestimonialsCarousel, 50);
                    }
                    return;
                }
                if (document.querySelector('.rooma-written-testimonials')) {
                    setTimeout(initRoomaTestimonialsCarousel, 50);
                }
            })();
            if (document.querySelector('.rooma-written-testimonials')) {
                if (typeof window.initRoomaWrittenTestimonialsGlide === 'function') {
                    window.initRoomaWrittenTestimonialsGlide();
                }
            } else if (document.querySelector('#tab-1.Testimonials, .tab-content.Testimonials:not(.rooma-written-testimonials)') && !document.querySelector('.heroSlider') && !document.querySelector('.rooma-written-testimonials')) {
                mount('#tab-1.Testimonials', {
                    remount: true,
                    type: 'carousel',
                    focusAt: 0,
                    perView: 2,
                    gap: 10,
                    autoplay: 3500,
                    animationDuration: 700,
                    breakpoints: {
                        1024: { perView: 2, gap: 10 },
                        820: { perView: 1, gap: 0 },
                        640: { perView: 1, gap: 0 }
                    }
                });
                if (typeof window.initTestimonialCards === 'function') {
                    requestAnimationFrame(function () {
                        window.initTestimonialCards(true);
                    });
                }
                if (typeof window.bootTestimonialCarouselHeights === 'function') {
                    window.bootTestimonialCarouselHeights();
                }
            }
        }
        var cmsInnerCarouselsInitialized = false;
        function bootInnerCarousels() {
            if (cmsInnerCarouselsInitialized) {
                return;
            }
            if (typeof Glide === 'undefined' || typeof window.safeMountGlide !== 'function') {
                setTimeout(bootInnerCarousels, 16);
                return;
            }
            cmsInnerCarouselsInitialized = true;
            initInnerCarousels();
        }
        bootInnerCarousels();
        function remountWrittenTestimonials() {
            if (typeof window.initRoomaWrittenTestimonialsGlide !== 'function') {
                return;
            }
            if (!window.initRoomaWrittenTestimonialsGlide()) {
                setTimeout(remountWrittenTestimonials, 50);
            }
        }
        function remountLayoutCarousels() {
            mountAboutCarousels(true);
            mountPedagogyCarousels(true);
            mountFacilitiesIntroCarousel(true);
            remountWrittenTestimonials();
        }
        window.addEventListener('load', remountLayoutCarousels);
        window.addEventListener('ah:tailwind-ready', remountLayoutCarousels);
    })();
    </script>
    <?php
}
