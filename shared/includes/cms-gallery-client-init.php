<?php

declare(strict_types=1);

/**
 * Client gallery API: absolute JWT proxy URL + fetch shim for legacy direct CMS calls.
 */
if (!function_exists('cmsGalleryProxyClientUrl')) {
    function cmsGalleryProxyClientUrl(): string
    {
        // Use concrete PHP entrypoint so nginx works even without rewrite rules.
        return rtrim(site_origin_url(), '/') . '/proxy/gallery-proxy.php';
    }
}

?>
<script>
var galleryProxyUrlBase = <?= json_encode(cmsGalleryProxyClientUrl(), JSON_UNESCAPED_SLASHES) ?>;
(function () {
    var proxyBase = galleryProxyUrlBase;
    if (!proxyBase || typeof window.fetch !== 'function') {
        return;
    }

    var nativeFetch = window.fetch.bind(window);

    function rewriteCmsGalleryUrl(url) {
        if (!url) {
            return url;
        }
        var match = url.match(/^https?:\/\/(?:cms\.allenhouseschools\.com)\/api\/galleries\/(.+)$/i);
        if (!match) {
            return url;
        }
        var tail = match[1];
        var query = '';
        var subType = tail.match(/^sub-type\/(\d+)\/branch\/\d+/);
        if (subType) {
            query = 'sub_type=' + encodeURIComponent(subType[1]);
        } else {
            var yearMatch = tail.match(/^branch\/\d+\/year\/(\d{4})/);
            if (yearMatch) {
                query = 'year=' + encodeURIComponent(yearMatch[1]);
            } else if (!/^branch\/\d+\/?$/.test(tail)) {
                console.warn('[gallery] Unmapped CMS gallery URL — use proxy:', url);
                return proxyBase;
            }
        }
        if (!query) {
            return proxyBase;
        }
        return proxyBase + (proxyBase.indexOf('?') >= 0 ? '&' : '?') + query;
    }

    window.fetch = function (input, init) {
        var url = typeof input === 'string'
            ? input
            : (input && typeof input.url === 'string' ? input.url : '');
        var rewritten = rewriteCmsGalleryUrl(url);
        if (rewritten !== url) {
            if (typeof input === 'string') {
                input = rewritten;
            } else if (input && typeof Request !== 'undefined' && input instanceof Request) {
                input = new Request(rewritten, input);
            } else {
                input = rewritten;
            }
        }
        return nativeFetch(input, init);
    };
})();
</script>
