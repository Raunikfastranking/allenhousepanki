<?php

declare(strict_types=1);

/**
 * Safe Glide mount helper — include after glide.js (e.g. in foot.php).
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
        window.safeMountGlide = function (selector, options) {
            if (typeof Glide === 'undefined') {
                return null;
            }
            var root = document.querySelector(selector);
            if (!root) {
                return null;
            }
            var track = root.querySelector('[data-glide-el="track"]');
            if (!track) {
                return null;
            }
            var slides = track.querySelectorAll('.glide__slide');
            var count = slides.length;
            if (!count) {
                var list = track.querySelector('ul');
                count = list ? list.querySelectorAll(':scope > li').length : 0;
            }
            if (count < 1) {
                return null;
            }
            var nav = root.querySelector('[data-glide-el="controls[nav]"]');
            if (nav) {
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
            var opts = JSON.parse(JSON.stringify(options || {}));
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
                return instance;
            } catch (err) {
                console.error('Glide failed for', selector, err);
                return null;
            }
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
                if (root.getAttribute('data-cms-glide-mounted') === '1') {
                    return;
                }
                if (!root.id) {
                    root.id = 'cms-glide-auto-' + Math.random().toString(36).slice(2, 9);
                }
                var selector = '#' + root.id;
                var slides = track.querySelectorAll('.glide__slide');
                var perView = Math.min(4, Math.max(1, slides.length || 1));
                var mounted = window.safeMountGlide(selector, {
                    type: 'carousel',
                    perView: perView,
                    gap: 16,
                    autoplay: slides.length > 1 ? 3500 : false,
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
    })();
    </script>
    <?php
}
