<?php

declare(strict_types=1);

/**
 * Form proxy URLs + fetch shim — rewrites legacy direct CMS form POSTs to local JWT proxies.
 */
if (!function_exists('cmsFormProxyPath')) {
    function cmsFormProxyPath(string $scriptBase): string
    {
        if (!function_exists('site_base_url')) {
            require_once dirname(__DIR__, 2) . '/includes/environment.php';
        }

        $scriptBase = trim($scriptBase, '/');
        $siteBase = rtrim(site_base_url(), '/');
        $prefix = ($siteBase === '' || $siteBase === '/') ? '' : $siteBase;

        if (!str_ends_with($scriptBase, '.php')) {
            $scriptBase .= '.php';
        }

        return $prefix . '/proxy/' . $scriptBase;
    }
}

if (!function_exists('cmsFormProxyClientUrls')) {
    function cmsFormProxyClientUrls(): array
    {
        return [
            'admission' => cmsFormProxyPath('admission-proxy'),
            'contact' => cmsFormProxyPath('contact-proxy'),
            'newsletter' => cmsFormProxyPath('newsletter-proxy'),
            'tc' => cmsFormProxyPath('tc-proxy'),
        ];
    }
}

$cmsFormProxies = cmsFormProxyClientUrls();
?>
<script>
window.CMS_FORM_PROXIES = <?= json_encode($cmsFormProxies, JSON_UNESCAPED_SLASHES) ?>;
(function () {
    var proxies = window.CMS_FORM_PROXIES || {};
    if (typeof window.fetch !== 'function') {
        return;
    }

    var nativeFetch = window.fetch.bind(window);
    var cmsHost = /^https?:\/\/(?:cms\.allenhouseschools\.com)(?=\/|$)/i;

    function requestMethod(input, init) {
        if (init && init.method) {
            return String(init.method).toUpperCase();
        }
        if (input && typeof Request !== 'undefined' && input instanceof Request && input.method) {
            return String(input.method).toUpperCase();
        }
        return 'GET';
    }

    function rewriteCmsFormUrl(url, method) {
        if (!url || method !== 'POST') {
            return url;
        }
        if (/save-enqury-form/i.test(url) && proxies.admission) {
            return proxies.admission;
        }
        var apiMatch = url.match(/^https?:\/\/(?:cms\.allenhouseschools\.com)\/api\/(.+)$/i);
        if (!apiMatch) {
            return url;
        }
        var tail = apiMatch[1].replace(/^\/+/, '').split('?')[0];
        if (tail === 'enquiries' && proxies.admission) {
            return proxies.admission;
        }
        if (tail.indexOf('contact-queries') === 0 && proxies.contact) {
            return proxies.contact;
        }
        if (tail.indexOf('subscribers') === 0 && proxies.newsletter) {
            return proxies.newsletter;
        }
        if ((tail.indexOf('transfer-certificates') === 0 || tail.indexOf('tc-') === 0) && proxies.tc) {
            return proxies.tc;
        }
        return url;
    }

    window.fetch = function (input, init) {
        var url = typeof input === 'string'
            ? input
            : (input && typeof input.url === 'string' ? input.url : '');
        var method = requestMethod(input, init);
        if (cmsHost.test(url)) {
            var rewritten = rewriteCmsFormUrl(url, method);
            if (rewritten !== url) {
                if (typeof input === 'string') {
                    init = init || {};
                    if (!init.method) {
                        init.method = method;
                    }
                    input = rewritten;
                } else if (input && typeof Request !== 'undefined' && input instanceof Request) {
                    input = new Request(rewritten, input);
                } else {
                    input = rewritten;
                }
            }
        }
        return nativeFetch(input, init);
    };
})();
</script>
