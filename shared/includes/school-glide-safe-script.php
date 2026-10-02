<?php

declare(strict_types=1);

/**
 * Safe Glide mount + CMS auto-init — include after glide.js (e.g. in foot.php).
 */
function glideSafeScript(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
    (function () {
        if (window.safeMountGlide) {
            return;
        }

        /** Glide breakpoints use max-width — force 1 slide on mobile only (never override desktop perView). */
        window.cmsNormalizeGlideMobilePerView = function (options, selector) {
            var opts = options || {};
            var mobileMax = 767;
            var root = selector ? document.querySelector(selector) : null;

            if (root && typeof window.cmsIsIndexGlideRoot === 'function' && window.cmsIsIndexGlideRoot(root)) {
                return opts;
            }
            if (root && typeof window.cmsIsDedicatedGlideRoot === 'function' && window.cmsIsDedicatedGlideRoot(root)) {
                return opts;
            }

            if (!opts.breakpoints || typeof opts.breakpoints !== 'object') {
                opts.breakpoints = {};
            }
            Object.keys(opts.breakpoints).forEach(function (key) {
                if (parseInt(key, 10) <= mobileMax && opts.breakpoints[key]) {
                    opts.breakpoints[key].perView = 1;
                }
            });
            opts.breakpoints[String(mobileMax)] = Object.assign({}, opts.breakpoints[String(mobileMax)] || {}, {
                perView: 1
            });
            if (typeof window.matchMedia !== 'undefined' && window.matchMedia('(max-width: ' + mobileMax + 'px)').matches) {
                opts.perView = 1;
            }
            return opts;
        };

        /** Home/testimonials carousels: one full card on mobile only (desktop keeps 2 per view). */
        window.cmsNormalizeTestimonialsGlideOptions = function (selector, options) {
            var opts = options || {};
            var root = document.querySelector(selector);
            if (!root) {
                return opts;
            }
            var isTestimonials = root.classList.contains('Testimonials')
                || root.classList.contains('Testimonials2')
                || root.id === 'tab-1'
                || root.classList.contains('rooma-written-testimonials')
                || root.classList.contains('rooma-video-testimonials');
            if (!isTestimonials) {
                return opts;
            }

            opts.focusAt = 0;
            opts.peek = 0;
            if (!opts.breakpoints || typeof opts.breakpoints !== 'object') {
                opts.breakpoints = {};
            }
            ['820', '767', '640'].forEach(function (bp) {
                opts.breakpoints[bp] = Object.assign({
                    type: 'carousel',
                    perView: 1,
                    gap: 0,
                    peek: 0,
                    focusAt: 0
                }, opts.breakpoints[bp] || {});
            });

            if (typeof window.matchMedia !== 'undefined' && window.matchMedia('(max-width: 819px)').matches) {
                opts.perView = 1;
                opts.gap = 0;
                opts.peek = 0;
                opts.focusAt = 0;
            }

            return opts;
        };

        /** Home hero: lock slides to container width; desktop uses 1920×601 aspect box. */
        window.syncHeroSlideWidths = function () {
            var hero = document.querySelector('.heroSlider');
            if (!hero) {
                return;
            }
            var width = hero.offsetWidth;
            if (!width) {
                return;
            }
            var isDesktop = window.matchMedia('(min-width: 640px)').matches;
            var widthPx = width + 'px';
            var track = hero.querySelector('[data-glide-el="track"]');
            var slidesList = hero.querySelector('.glide__slides') || (track ? track.querySelector('ul') : null);

            if (isDesktop) {
                hero.style.height = '';
                hero.style.aspectRatio = '';
                if (track) {
                    track.style.height = '100%';
                }
                if (slidesList) {
                    slidesList.style.height = '100%';
                }
            } else {
                hero.style.height = 'auto';
                hero.style.aspectRatio = 'auto';
                if (track) {
                    track.style.height = 'auto';
                }
                if (slidesList) {
                    slidesList.style.height = 'auto';
                }
            }

            hero.querySelectorAll('.glide__slide, [data-glide-el="track"] ul > li').forEach(function (slide) {
                slide.style.width = widthPx;
                slide.style.minWidth = widthPx;
                slide.style.maxWidth = widthPx;
                slide.style.height = isDesktop ? '100%' : 'auto';
                slide.style.marginRight = '0px';
            });

            if (!isDesktop) {
                hero.querySelectorAll('.hero-slide-img, .heroSlider img').forEach(function (img) {
                    img.style.height = 'auto';
                    img.style.objectFit = '';
                });
            }
        };

        /** Home hero: full-width carousel — natural image height like DPS reference. */
        window.cmsNormalizeHeroGlideOptions = function (selector, options) {
            var opts = options || {};
            var root = document.querySelector(selector);
            if (!root || !root.classList.contains('heroSlider')) {
                return opts;
            }

            opts.type = 'slider';
            opts.rewind = true;
            opts.focusAt = 0;
            opts.perView = 1;
            opts.gap = 0;
            opts.peek = 0;
            if (!opts.breakpoints || typeof opts.breakpoints !== 'object') {
                opts.breakpoints = {};
            }
            ['1280', '1024', '767', '640', '480'].forEach(function (bp) {
                opts.breakpoints[bp] = Object.assign({
                    type: 'slider',
                    rewind: true,
                    perView: 1,
                    gap: 0,
                    peek: 0,
                    focusAt: 0
                }, opts.breakpoints[bp] || {});
            });

            return opts;
        };

        window.safeMountGlide = function (selector, options) {
            if (typeof Glide === 'undefined') {
                return null;
            }
            var root = document.querySelector(selector);
            if (!root) {
                return null;
            }
            var opts = JSON.parse(JSON.stringify(options || {}));
            var remount = !!opts.remount;
            delete opts.remount;
            opts = window.cmsNormalizeGlideMobilePerView(opts, selector);
            opts = window.cmsNormalizeTestimonialsGlideOptions(selector, opts);
            opts = window.cmsNormalizeHeroGlideOptions(selector, opts);
            if (remount && root._cmsGlideInstance) {
                try {
                    root._cmsGlideInstance.destroy();
                } catch (destroyErr) {
                    /* ignore */
                }
                root._cmsGlideInstance = null;
                root.removeAttribute('data-cms-glide-mounted');
            }
            if (root.getAttribute('data-cms-glide-mounted') === '1') {
                return root._cmsGlideInstance || null;
            }
            var track = root.querySelector('[data-glide-el="track"]');
            if (!track) {
                return null;
            }
            var list = track.querySelector('ul');
            if (list && !list.classList.contains('glide__slides')) {
                list.classList.add('glide__slides');
            }
            if (list) {
                list.querySelectorAll(':scope > li').forEach(function (li) {
                    li.classList.add('glide__slide');
                });
            }
            var slides = track.querySelectorAll('.glide__slide');
            var count = slides.length;
            if (!count && list) {
                count = list.querySelectorAll(':scope > li').length;
            }
            if (count < 1) {
                return null;
            }
            var nav = root.querySelector('[data-glide-el="controls[nav]"]');
            if (nav) {
                var isCampusCarousel = root.id === 'home-campus-glide';
                if (isCampusCarousel) {
                    var maxCampusDots = 5;
                    var campusPerView = typeof opts.perView === 'number' ? opts.perView : 1;
                    if (opts.breakpoints && window.innerWidth) {
                        var campusBpKeys = Object.keys(opts.breakpoints).map(function (key) {
                            return parseInt(key, 10);
                        }).filter(function (key) {
                            return !isNaN(key);
                        }).sort(function (a, b) {
                            return b - a;
                        });
                        for (var campusBpIndex = 0; campusBpIndex < campusBpKeys.length; campusBpIndex++) {
                            if (window.innerWidth <= campusBpKeys[campusBpIndex]) {
                                var campusBp = opts.breakpoints[campusBpKeys[campusBpIndex]];
                                if (campusBp && typeof campusBp.perView === 'number') {
                                    campusPerView = campusBp.perView;
                                }
                                break;
                            }
                        }
                    }
                    campusPerView = Math.max(1, Math.min(campusPerView, count));
                    var campusPageCount = Math.ceil(count / campusPerView);
                    var campusDotCount = Math.min(maxCampusDots, campusPageCount);
                    if (campusDotCount <= 1) {
                        nav.style.display = 'none';
                        nav.setAttribute('aria-hidden', 'true');
                    } else {
                        nav.style.display = '';
                        nav.classList.remove('hidden');
                        nav.removeAttribute('aria-hidden');
                        nav.innerHTML = '';
                        for (var dotIndex = 0; dotIndex < campusDotCount; dotIndex++) {
                            var campusSlideIndex;
                            if (campusPageCount <= maxCampusDots) {
                                campusSlideIndex = Math.min(dotIndex * campusPerView, count - 1);
                            } else {
                                campusSlideIndex = campusDotCount <= 1
                                    ? 0
                                    : Math.round((dotIndex / (campusDotCount - 1)) * (count - 1));
                            }
                            var dotBtn = document.createElement('button');
                            dotBtn.type = 'button';
                            dotBtn.className = 'group glide__bullet p-0 border-0 bg-transparent cursor-pointer';
                            dotBtn.setAttribute('data-glide-dir', '=' + campusSlideIndex);
                            dotBtn.setAttribute('aria-label', 'Go to slide ' + (dotIndex + 1));
                            var dotSpan = document.createElement('span');
                            dotSpan.className = 'block w-[10px] h-[10px] transition-colors duration-300 rounded-full bg-gray-300 focus:outline-none';
                            dotBtn.appendChild(dotSpan);
                            nav.appendChild(dotBtn);
                        }
                    }
                } else {
                    var maxNavDots = 4;
                    if (count > maxNavDots || count <= 1) {
                        nav.style.display = 'none';
                        nav.setAttribute('aria-hidden', 'true');
                    } else {
                        nav.querySelectorAll('button[data-glide-dir^="="]').forEach(function (btn, index) {
                            if (index >= count) {
                                btn.remove();
                            }
                        });
                    }
                }
            }
            function cap(v) {
                return typeof v === 'number' ? Math.min(v, count) : v;
            }
            if (typeof opts.perView === 'number') {
                opts.perView = cap(opts.perView);
            }
            if (opts.breakpoints) {
                Object.keys(opts.breakpoints).forEach(function (key) {
                    var bp = opts.breakpoints[key];
                    if (bp && typeof bp.perView === 'number') {
                        bp.perView = cap(bp.perView);
                    }
                });
            }
            if (count <= 1) {
                opts.autoplay = false;
                opts.type = 'slider';
            }
            try {
                var instance = new Glide(selector, opts);
                instance.mount();
                root._cmsGlideInstance = instance;
                root.setAttribute('data-cms-glide-mounted', '1');
                root.classList.add('cms-glide-ready');
                if (root.classList.contains('heroSlider') && typeof window.syncHeroSlideWidths === 'function') {
                    window.syncHeroSlideWidths();
                }
                return instance;
            } catch (err) {
                console.error('Glide failed for', selector, err);
                return null;
            }
        };

        /** Home-page carousels use indexCarouselInitScript with explicit perView settings. */
        window.cmsIsIndexGlideRoot = function (root) {
            if (!root || !root.classList) {
                return false;
            }
            if (root.id === 'tab-1' && root.classList.contains('Testimonials')) {
                return true;
            }
            var homeRoots = ['heroSlider', 'excellance-glide', 'glide-0222', 'glide-0333', 'Images', 'home-philosophy-glide', 'home-campus-glide', 'home-gallery-glide'];
            for (var i = 0; i < homeRoots.length; i++) {
                if (root.classList.contains(homeRoots[i])) {
                    return true;
                }
            }
            return !!root.closest('.heroSlider, .excellance-glide, .glide-0222, .glide-0333, .Images, #tab-1.Testimonials, .tab-content.Testimonials, #home-philosophy-glide, #home-campus-glide, #home-gallery-glide');
        };

        /** Carousels with explicit perView init elsewhere (blog detail, about-us, etc.). */
        window.cmsIsDedicatedGlideRoot = function (root) {
            if (!root || !root.classList) {
                return false;
            }
            var dedicated = [
                'cms-about-glide',
                'cms-other-blogs-carousel',
                'rooma-written-testimonials',
                'rooma-video-testimonials',
                'about-carousel1',
                'about-carousel2',
                'about-carousel3',
                'about-carousel4',
                'about-carousel5',
                'about-carousel6',
                'glide-03',
                'cms-facilities-carousel',
                'cms-facilities-glide',
                'cms-pedagogy-carousel',
                'pedagogy-glide-3view',
                'pedagogy-glide-4view'
            ];
            for (var i = 0; i < dedicated.length; i++) {
                if (root.classList.contains(dedicated[i])) {
                    return true;
                }
            }
            return root.getAttribute('data-cms-dedicated-glide') === '1';
        };

        window.cmsGlideSlideCount = function (track) {
            if (!track) {
                return 0;
            }
            var slides = track.querySelectorAll('.glide__slide');
            if (slides.length) {
                return slides.length;
            }
            var list = track.querySelector('ul');
            return list ? list.querySelectorAll(':scope > li').length : 0;
        };

        /** Mount any CMS/legacy Glide root not yet initialized. */
        window.cmsAutoInitAllGlides = function () {
            if (typeof Glide === 'undefined' || typeof window.safeMountGlide !== 'function') {
                return;
            }
            var seen = new WeakSet();
            document.querySelectorAll('[data-glide-el="track"]').forEach(function (track) {
                var root = track.closest('.glide') || track.parentElement;
                if (!root || seen.has(root)) {
                    return;
                }
                seen.add(root);
                if (window.cmsIsIndexGlideRoot(root) || window.cmsIsDedicatedGlideRoot(root)) {
                    return;
                }
                if (root.getAttribute('data-cms-glide-mounted') === '1') {
                    return;
                }
                if (!root.id) {
                    root.id = 'cms-glide-auto-' + Math.random().toString(36).slice(2, 9);
                }
                var selector = '#' + root.id;
                var slideCount = window.cmsGlideSlideCount(track);
                var perView = Math.min(4, Math.max(1, slideCount || 1));
                var mounted = window.safeMountGlide(selector, {
                    type: 'carousel',
                    perView: perView,
                    gap: 16,
                    autoplay: slideCount > 1 ? 3500 : false,
                    animationDuration: 700,
                    breakpoints: {
                        1280: { perView: Math.min(4, perView) },
                        1024: { perView: Math.min(3, perView) },
                        640: { perView: 1 }
                    }
                });
                if (mounted) {
                    root.setAttribute('data-cms-glide-mounted', '1');
                }
            });
        };

        var cmsAutoGlidesInitialized = false;
        function runCmsGlides() {
            if (cmsAutoGlidesInitialized) {
                return;
            }
            if (document.querySelector('.heroSlider, .excellance-glide, #home-philosophy-glide, #home-campus-glide, #home-gallery-glide, .glide-0222, .glide-0333, .Images')) {
                cmsAutoGlidesInitialized = true;
                return;
            }
            if (typeof window.cmsAutoInitAllGlides !== 'function') {
                return;
            }
            cmsAutoGlidesInitialized = true;
            window.cmsAutoInitAllGlides();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runCmsGlides, { once: true });
        } else {
            runCmsGlides();
        }
    })();
    </script>
    <?php
    if (!empty($GLOBALS['school_is_index_page'])) {
        $indexCarouselInit = dirname(__DIR__, 2) . '/includes/index-carousel-init.php';
        if (is_file($indexCarouselInit)) {
            require_once $indexCarouselInit;
            indexCarouselInitScript();
        }
    }
}
