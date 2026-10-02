<?php

declare(strict_types=1);

/**
 * Contact page form — POST JSON to /proxy/contact-proxy (same-origin relative path).
 */
if (!function_exists('cms_contact_form_init_script')) {
    function cms_contact_form_init_script(): void
    {
        if (!function_exists('cmsFormProxyPath')) {
            if (!function_exists('site_base_url')) {
                require_once dirname(__DIR__, 2) . '/includes/environment.php';
            }
            function cmsFormProxyPath(string $scriptBase): string
            {
                $scriptBase = trim($scriptBase, '/');
                $siteBase = rtrim(site_base_url(), '/');
                $prefix = ($siteBase === '' || $siteBase === '/') ? '' : $siteBase;

                if (!str_ends_with($scriptBase, '.php')) {
                    $scriptBase .= '.php';
                }

                return $prefix . '/proxy/' . $scriptBase;
            }
        }

        $contactProxyPath = cmsFormProxyPath('contact-proxy');
        ?>
<script>
(function () {
    var nameRegex = /^[A-Za-z\s]*$/;
    var mobileRegex = /^[6-9]\d{9}$/;
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var maxName = 50;
    var proxyPath = <?= json_encode($contactProxyPath, JSON_UNESCAPED_SLASHES) ?>;

    function contactProxyUrl() {
        if (window.CMS_FORM_PROXIES && window.CMS_FORM_PROXIES.contact) {
            return window.CMS_FORM_PROXIES.contact;
        }
        var path = proxyPath || '/proxy/contact-proxy.php';
        return path.charAt(0) === '/' ? path : '/' + path;
    }

    function parseContactResponse(response) {
        if (typeof window.cmsParseProxyJson === 'function') {
            return window.cmsParseProxyJson(response);
        }
        return response.text().then(function (text) {
            var data = {};
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid server response. Please try again.');
                }
            }
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Submission failed. Please try again.');
            }
            return data;
        });
    }

    function bindContactForm() {
        var form = document.getElementById('contactForm');
        var nameIn = document.getElementById('cname');
        var nameErr = document.getElementById('cname-error');
        var nameLenErr = document.getElementById('cname-length-error');
        var mobIn = document.getElementById('cmobile');
        var mobErr = document.getElementById('mobileErrorss');
        var emailIn = document.getElementById('cemail');
        var emailErr = document.getElementById('emailErrorss');
        var submitBtn = document.getElementById('contactSubmitBtn');
        var successPopup = document.getElementById('successPopup');
        var queryErr = document.getElementById('cquery-error');

        if (!form || !nameIn || !mobIn || !emailIn || !submitBtn) {
            return;
        }
        if (form.dataset.contactBound === '1') {
            return;
        }
        form.dataset.contactBound = '1';

        nameIn.addEventListener('input', function () {
            var v = this.value;
            if (!nameRegex.test(v)) {
                this.value = v.replace(/[^A-Za-z\s]/g, '');
                if (nameErr) nameErr.classList.remove('hidden');
            } else if (nameErr) {
                nameErr.classList.add('hidden');
            }
            if (this.value.length > maxName) {
                this.value = this.value.slice(0, maxName);
                if (nameLenErr) nameLenErr.classList.remove('hidden');
            } else if (nameLenErr) {
                nameLenErr.classList.add('hidden');
            }
        });

        nameIn.addEventListener('paste', function () {
            setTimeout(function () {
                nameIn.value = nameIn.value.replace(/[^A-Za-z\s]/g, '').slice(0, maxName);
                if (nameErr) nameErr.classList.toggle('hidden', nameRegex.test(nameIn.value));
                if (nameLenErr) nameLenErr.classList.toggle('hidden', nameIn.value.length <= maxName);
            }, 0);
        });

        mobIn.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
            if (mobErr) mobErr.classList.toggle('hidden', mobileRegex.test(this.value) || this.value === '');
        });

        emailIn.addEventListener('input', function () {
            if (emailErr) emailErr.classList.toggle('hidden', emailRegex.test(this.value) || this.value === '');
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            var name = nameIn.value.trim();
            var mobile = mobIn.value.trim();
            var email = emailIn.value.trim();
            var queryEl = document.getElementById('cquery');
            var messageEl = document.getElementById('cmessage');
            var query = queryEl ? queryEl.value : '';
            var message = messageEl ? messageEl.value.trim() : '';
            var ok = true;

            if (!nameRegex.test(name) || name === '') {
                if (nameErr) nameErr.classList.remove('hidden');
                ok = false;
            } else if (nameErr) {
                nameErr.classList.add('hidden');
            }

            if (name.length > maxName) {
                if (nameLenErr) nameLenErr.classList.remove('hidden');
                ok = false;
            } else if (nameLenErr) {
                nameLenErr.classList.add('hidden');
            }

            if (!mobileRegex.test(mobile)) {
                if (mobErr) mobErr.classList.remove('hidden');
                ok = false;
            } else if (mobErr) {
                mobErr.classList.add('hidden');
            }

            if (!emailRegex.test(email)) {
                if (emailErr) emailErr.classList.remove('hidden');
                ok = false;
            } else if (emailErr) {
                emailErr.classList.add('hidden');
            }

            if (message === '') {
                ok = false;
            }

            if (query === '') {
                if (queryErr) queryErr.classList.remove('hidden');
                ok = false;
            } else if (queryErr) {
                queryErr.classList.add('hidden');
            }

            if (!ok) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
                return;
            }

            fetch(contactProxyUrl(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    mobile: mobile,
                    email: email,
                    query: query,
                    query_type: query,
                    message: message
                })
            })
            .then(parseContactResponse)
            .then(function () {
                if (successPopup) {
                    successPopup.classList.remove('hidden');
                    setTimeout(function () { successPopup.classList.add('hidden'); }, 10000);
                }
                form.reset();
                [nameErr, nameLenErr, mobErr, emailErr, queryErr].forEach(function (el) {
                    if (el) el.classList.add('hidden');
                });
            })
            .catch(function (error) {
                alert(error.message || 'Submission failed. Please try again.');
                if (window.console && console.error) {
                    console.error('[contact] error:', error, 'proxy:', contactProxyUrl());
                }
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
            });
        });
    }

    function initContactForm() {
        bindContactForm();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initContactForm, { once: true });
    } else {
        initContactForm();
    }
    window.addEventListener('load', initContactForm, { once: true });
})();
</script>
        <?php
    }
}
