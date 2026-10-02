<?php

declare(strict_types=1);

/**
 * Full-page loader — hides FOUC / broken layout until Tailwind, carousels, and async CMS blocks are ready.
 */

function schoolPageLoaderHead(): void
{
    ?>
<script>
(function () {
    document.documentElement.classList.remove('ah-page-ready');

    function loaderMarkup() {
        return '<div class="ah-page-loader__inner"><div class="ah-page-loader__spinner" aria-hidden="true"></div><p class="ah-page-loader__text">Loading...</p></div>';
    }

    window.ahEnsurePageLoader = function () {
        var loader = document.getElementById('ah-page-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'ah-page-loader';
            loader.setAttribute('role', 'status');
            loader.setAttribute('aria-live', 'polite');
            loader.setAttribute('aria-label', 'Loading page');
            loader.innerHTML = loaderMarkup();
            document.documentElement.appendChild(loader);
        } else if (loader.parentNode !== document.documentElement) {
            document.documentElement.appendChild(loader);
        }
        return loader;
    };

    window.ahEnsurePageLoader();
})();
</script>
<style>
    html:not(.ah-page-ready),
    html.ah-navigating {
        overflow: hidden;
    }
    html:not(.ah-page-ready) > body,
    html.ah-navigating > body {
        overflow: hidden;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
    }
    html.ah-page-ready body {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.25s ease;
    }
    html:not(.ah-page-ready) #formOverlay,
    html:not(.ah-page-ready) #popupForm {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
    #ah-page-loader {
        position: fixed;
        inset: 0;
        z-index: 2147483646;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        opacity: 1;
        visibility: visible !important;
        pointer-events: auto;
        margin: 0;
        padding: 0;
        border: 0;
        transition: opacity 0.35s ease, visibility 0.35s ease;
    }
    html.ah-navigating #ah-page-loader {
        display: flex !important;
        position: fixed !important;
        inset: 0 !important;
        width: auto !important;
        height: auto !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        overflow: visible !important;
        z-index: 2147483646 !important;
    }
    html.ah-page-ready #ah-page-loader.ah-page-loader--hidden,
    html.ah-page-ready:not(.ah-navigating) #ah-page-loader {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        display: none !important;
        position: absolute !important;
        inset: auto !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    .ah-page-loader__inner {
        text-align: center;
    }
    .ah-page-loader__spinner {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        border: 4px solid #e8eef5;
        border-top-color: #053B7A;
        border-radius: 50%;
        animation: ah-page-loader-spin 0.8s linear infinite;
    }
    .ah-page-loader__text {
        margin: 0;
        font-family: Poppins, system-ui, -apple-system, sans-serif;
        font-size: 14px;
        font-weight: 500;
        color: #053B7A;
        letter-spacing: 0.02em;
    }
    @keyframes ah-page-loader-spin {
        to { transform: rotate(360deg); }
    }
    @media (prefers-reduced-motion: reduce) {
        .ah-page-loader__spinner {
            animation: none;
            border-top-color: #053B7A;
        }
    }
</style>
    <?php
}

function schoolPageLoaderScript(): void
{
    ?>
<script>
(function () {
    var MIN_MS = 0;
    var MAX_MS = 2800;
    var TAILWIND_MAX_WAIT_MS = 800;
    var HERO_GLIDE_MAX_WAIT_MS = 1200;
    var started = Date.now();
    var revealed = false;
    var navigatingAway = false;
    var pollTimer = null;
    var domReady = false;
    var domReadyAt = 0;
    var tailwindScanned = false;

    function scanTailwind() {
        if (typeof tailwind === 'undefined') {
            return false;
        }
        if (typeof tailwind.refresh === 'function') {
            try { tailwind.refresh(); } catch (e) {}
        }
        tailwindScanned = true;
        return true;
    }

    function tailwindReady() {
        if (tailwindScanned && typeof tailwind !== 'undefined') {
            return true;
        }
        if (domReady && (Date.now() - domReadyAt) >= TAILWIND_MAX_WAIT_MS) {
            if (!tailwindScanned && typeof tailwind !== 'undefined') {
                scanTailwind();
            }
            return true;
        }
        return false;
    }

    function heroGlideReady() {
        var hero = document.querySelector('.heroSlider');
        if (!hero) {
            return true;
        }
        if (hero.getAttribute('data-cms-glide-mounted') === '1') {
            return true;
        }
        return domReady && (Date.now() - domReadyAt) >= HERO_GLIDE_MAX_WAIT_MS;
    }

    function allReady() {
        return domReady && tailwindReady() && heroGlideReady();
    }

    function markDomReady() {
        if (domReady) {
            return;
        }
        domReady = true;
        domReadyAt = Date.now();
        poll();
    }

    function reveal() {
        if (revealed || navigatingAway) return;
        revealed = true;
        if (pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
        var wait = Math.max(0, MIN_MS - (Date.now() - started));
        setTimeout(function () {
            var loader = typeof window.ahEnsurePageLoader === 'function'
                ? window.ahEnsurePageLoader()
                : document.getElementById('ah-page-loader');
            document.documentElement.classList.remove('ah-navigating');
            document.documentElement.classList.add('ah-page-ready');
            if (loader) {
                loader.classList.add('ah-page-loader--hidden');
                loader.setAttribute('aria-hidden', 'true');
            }
            document.documentElement.style.overflow = '';
            if (document.body) {
                document.body.style.overflow = '';
                document.body.style.visibility = '';
                document.body.style.opacity = '';
                document.body.style.pointerEvents = '';
            }
            window.dispatchEvent(new Event('ah:page-ready'));
        }, wait);
    }

    function poll() {
        if (revealed || navigatingAway) return;
        if (domReady && typeof tailwind !== 'undefined' && !tailwindScanned) {
            scanTailwind();
        }
        if (allReady()) {
            reveal();
            return;
        }
        if (Date.now() - started < MAX_MS) {
            pollTimer = setTimeout(poll, 40);
        } else {
            if (!tailwindScanned) scanTailwind();
            reveal();
        }
    }

    function paintLoaderOverlay() {
        document.documentElement.classList.remove('ah-page-ready');
        document.documentElement.classList.add('ah-navigating');
        document.documentElement.style.overflow = 'hidden';
        if (document.body) {
            document.body.style.overflow = 'hidden';
            document.body.style.visibility = 'hidden';
            document.body.style.opacity = '0';
            document.body.style.pointerEvents = 'none';
        }
        var loader = typeof window.ahEnsurePageLoader === 'function'
            ? window.ahEnsurePageLoader()
            : document.getElementById('ah-page-loader');
        if (loader) {
            loader.classList.remove('ah-page-loader--hidden');
            loader.removeAttribute('aria-hidden');
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            loader.style.visibility = 'visible';
            loader.style.pointerEvents = 'auto';
            void loader.offsetHeight;
        }
    }

    function showLoaderOverlay(options) {
        var opts = options || {};
        var isNavigation = !!opts.navigation;

        if (isNavigation) {
            navigatingAway = true;
            if (pollTimer) {
                clearTimeout(pollTimer);
                pollTimer = null;
            }
        } else {
            navigatingAway = false;
            revealed = false;
            started = Date.now();
        }

        paintLoaderOverlay();

        if (!isNavigation) {
            poll();
        }
    }

    function resetAsyncBlocks() {
        var nodes = document.querySelectorAll('[data-ah-async-load]');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].removeAttribute('data-ah-async-done');
        }
    }

    function initFlyerAfterPageReady() {
        window.addEventListener('ah:page-ready', function () {
            var popup = document.getElementById('formOverlay');
            if (!popup || popup.getAttribute('data-ah-flyer-disabled') === '1') return;
            if (!popup.classList.contains('hidden')) return;

            var img = popup.querySelector('img');
            function showFlyer() {
                if (popup.getAttribute('data-ah-flyer-disabled') === '1') return;
                popup.classList.remove('hidden');
                popup.removeAttribute('aria-hidden');
            }

            if (!img || !img.getAttribute('src')) {
                return;
            }

            if (img.complete && img.naturalWidth > 0) {
                setTimeout(showFlyer, 400);
                return;
            }

            var shown = false;
            function tryShow() {
                if (shown) return;
                shown = true;
                showFlyer();
            }

            img.addEventListener('load', function () {
                setTimeout(tryShow, 200);
            }, { once: true });
            img.addEventListener('error', function () {
                popup.setAttribute('data-ah-flyer-disabled', '1');
            }, { once: true });
        }, { once: true });
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;
        if (link.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        var url;
        try {
            url = new URL(link.href, window.location.href);
        } catch (err) {
            return;
        }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search) return;
        showLoaderOverlay({ navigation: true });
    }, true);

    window.addEventListener('beforeunload', function () {
        navigatingAway = true;
        paintLoaderOverlay();
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        resetAsyncBlocks();
        showLoaderOverlay();
    });

    function begin() {
        markDomReady();
    }

    window.ahShowPageLoader = showLoaderOverlay;

    window.ahMarkAsyncBlockDone = function (el) {
        if (!el) return;
        el.setAttribute('data-ah-async-done', '1');
    };

    window.ahPageLoaderPoll = poll;

    window.addEventListener('load', function () {
        scanTailwind();
        window.dispatchEvent(new Event('ah:tailwind-ready'));
        poll();
    }, { once: true });

    window.addEventListener('ah:tailwind-ready', poll);
    window.addEventListener('ah:async-ready', poll);
    window.addEventListener('ah:home-glides-ready', poll);

    initFlyerAfterPageReady();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', begin, { once: true });
    } else {
        begin();
    }

    setTimeout(function () {
        if (!tailwindScanned) {
            scanTailwind();
        }
        reveal();
    }, MAX_MS);
})();
</script>
    <?php
}
