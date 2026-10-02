<?php

declare(strict_types=1);

/**
 * Glide carousels for about-us (same mount pattern as allenhouseagra/about-us.php).
 */
function aboutUsGlideInitScript(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
    (function () {
        function mountAboutGlide(selector, options) {
            if (typeof Glide === 'undefined' || !document.querySelector(selector)) {
                return null;
            }
            try {
                var instance = new Glide(selector, options);
                instance.mount();
                return instance;
            } catch (err) {
                console.error('Glide mount failed:', selector, err);
                return null;
            }
        }

        function initAboutCarousels() {
            var common = {
                type: 'carousel',
                focusAt: 1,
                perView: 4,
                autoplay: 3500,
                animationDuration: 700,
                gap: 24
            };

            mountAboutGlide('.about-carousel1', Object.assign({}, common, {
                breakpoints: { 1024: { perView: 4 }, 640: { perView: 1 } }
            }));

            mountAboutGlide('.about-carousel2', Object.assign({}, common, {
                breakpoints: {
                    1680: { perView: 4 },
                    1024: { perView: 3 },
                    820: { perView: 2 },
                    640: { perView: 1 }
                }
            }));

            mountAboutGlide('.glide-03', Object.assign({}, common, {
                breakpoints: {
                    1680: { perView: 4 },
                    1024: { perView: 3 },
                    820: { perView: 2 },
                    640: { perView: 1 }
                }
            }));
        }

        function run() {
            if (typeof Glide === 'undefined') {
                setTimeout(run, 50);
                return;
            }
            initAboutCarousels();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run, { once: true });
        } else {
            run();
        }
    })();
    </script>
    <?php
}
