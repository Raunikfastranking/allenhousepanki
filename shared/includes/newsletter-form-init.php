<?php

declare(strict_types=1);

/**
 * Newsletter footer form — XHR + FormData to /proxy/newsletter-proxy.php (bypasses fetch shims; IIS-safe).
 */
if (!function_exists('cms_newsletter_form_init_script')) {
    function cms_newsletter_form_init_script(string $buttonLabel = 'Subscribe', ?string $siteBaseUrl = null): void
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

        $buttonLabel = $buttonLabel !== '' ? $buttonLabel : 'Subscribe';
        $newsletterProxyPath = cmsFormProxyPath('newsletter-proxy');
        ?>
<script>
(function () {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var buttonLabel = <?= json_encode($buttonLabel) ?>;
    var proxyPath = <?= json_encode($newsletterProxyPath, JSON_UNESCAPED_SLASHES) ?>;
    var submitting = false;

    function newsletterProxyUrl() {
        var path = '';
        if (window.CMS_FORM_PROXIES && window.CMS_FORM_PROXIES.newsletter) {
            path = window.CMS_FORM_PROXIES.newsletter;
        } else {
            var form = document.getElementById('newsForm');
            var formAction = form ? String(form.getAttribute('action') || '').trim() : '';
            if (formAction === '#' || formAction === '') {
                formAction = '';
            }
            path = formAction || proxyPath || '/proxy/newsletter-proxy.php';
        }
        if (!/\.php$/i.test(path)) {
            path = path.replace(/\/?$/, '') + '.php';
        }
        if (/^https?:\/\//i.test(path)) {
            return path;
        }
        return path.charAt(0) === '/' ? path : '/' + path;
    }

    function subscriberNameFromEmail(email) {
        var local = String(email || '').split('@')[0] || '';
        local = local.replace(/[._-]+/g, ' ').trim();
        return local ? local.replace(/\b\w/g, function (ch) { return ch.toUpperCase(); }) : 'Newsletter Subscriber';
    }

    function parseNewsletterXhr(xhr) {
        var data = {};
        if (xhr.responseText) {
            try {
                data = JSON.parse(xhr.responseText);
            } catch (e) {
                throw new Error('Invalid server response. Please try again.');
            }
        }

        var failed = xhr.status < 200 || xhr.status >= 300;
        if (!failed && data.success === false) {
            failed = true;
        }
        if (!failed && data.status === 'error') {
            failed = true;
        }
        if (failed && xhr.status >= 200 && xhr.status < 300 && typeof data.message === 'string' && /subscribed|subscription successful/i.test(data.message)) {
            failed = false;
        }
        if (failed && xhr.status >= 200 && xhr.status < 300 && data.data && data.data.id) {
            failed = false;
        }
        if (failed) {
            var msg = data.message || ('Request failed (' + xhr.status + ')');
            if (!data.message && data.errors && typeof data.errors === 'object') {
                var keys = Object.keys(data.errors);
                if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                    msg = data.errors[keys[0]][0];
                }
            }
            throw new Error(msg);
        }

        return data;
    }

    function postNewsletter(email) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            var proxyUrl = newsletterProxyUrl();
            xhr.open('POST', proxyUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) {
                    return;
                }
                try {
                    resolve(parseNewsletterXhr(xhr));
                } catch (error) {
                    reject(error);
                }
            };
            xhr.onerror = function () {
                reject(new Error('Network error. Please try again.'));
            };

            xhr.send(JSON.stringify({
                email: email,
                name: subscriberNameFromEmail(email),
                subscription_source: 'website_footer'
            }));
        });
    }

    function getNewsletterEls() {
        var form = document.getElementById('newsForm');
        if (!form) {
            return null;
        }
        var emailInput = form.querySelector('#news-email');
        var submitBtn = form.querySelector('#newsSubmit');
        if (!emailInput || !submitBtn) {
            return null;
        }
        return {
            form: form,
            emailInput: emailInput,
            emailError: document.getElementById('news-email-error'),
            emailSuccess: document.getElementById('news-email-success'),
            submitBtn: submitBtn,
            popup: document.getElementById('NewsPopup')
        };
    }

    function showNewsletterSuccess(els, data) {
        if (els.emailSuccess) {
            els.emailSuccess.classList.remove('hidden');
            if (data && data.already_subscribed) {
                els.emailSuccess.textContent = 'You are already subscribed.';
            } else if (data && data.data && data.data.id) {
                els.emailSuccess.textContent = 'Thank you for subscribing!';
            } else {
                els.emailSuccess.textContent = 'Thank you for subscribing!';
            }
            setTimeout(function () { els.emailSuccess.classList.add('hidden'); }, 8000);
        }
        if (els.popup && data && data.data && data.data.id) {
            els.popup.classList.remove('hidden');
            setTimeout(function () { els.popup.classList.add('hidden'); }, 5000);
        }
        if (window.console && console.log) {
            var savedId = data && data.data && data.data.id ? data.data.id : null;
            console.log('[newsletter] saved', { email: els.emailInput ? els.emailInput.value : '', id: savedId, proxy: newsletterProxyUrl() });
        }
    }

    function handleNewsletterSubmit(evt) {
        if (evt && evt.preventDefault) {
            evt.preventDefault();
        }
        if (evt && evt.stopPropagation) {
            evt.stopPropagation();
        }
        if (submitting) {
            return false;
        }

        var els = getNewsletterEls();
        if (!els) {
            return false;
        }

        var email = els.emailInput.value.trim().toLowerCase();
        els.emailInput.value = email;

        if (!email) {
            if (els.emailError) {
                els.emailError.textContent = 'Please enter an email address.';
                els.emailError.classList.remove('hidden');
            }
            if (els.emailSuccess) {
                els.emailSuccess.classList.add('hidden');
            }
            return false;
        }
        if (!emailRegex.test(email)) {
            if (els.emailError) {
                els.emailError.textContent = 'Please enter a valid email address.';
                els.emailError.classList.remove('hidden');
            }
            if (els.emailSuccess) {
                els.emailSuccess.classList.add('hidden');
            }
            return false;
        }

        if (els.emailError) {
            els.emailError.classList.add('hidden');
        }
        if (els.emailSuccess) {
            els.emailSuccess.classList.add('hidden');
        }
        submitting = true;
        els.submitBtn.disabled = true;
        els.submitBtn.textContent = 'Subscribing...';

        postNewsletter(email)
            .then(function (data) {
                showNewsletterSuccess(els, data);
                els.form.reset();
            })
            .catch(function (error) {
                alert(error.message || 'There was an error submitting the form. Please try again.');
                if (window.console && console.error) {
                    console.error('[newsletter] error:', error, 'proxy:', newsletterProxyUrl());
                }
            })
            .finally(function () {
                submitting = false;
                els.submitBtn.disabled = false;
                els.submitBtn.textContent = buttonLabel;
            });

        return false;
    }

    window.cmsSubmitNewsletter = handleNewsletterSubmit;

    function bindNewsletterForm() {
        var els = getNewsletterEls();
        if (!els) {
            return;
        }
        if (els.form.dataset.newsletterBound === '1') {
            return;
        }
        els.form.dataset.newsletterBound = '1';

        els.emailInput.addEventListener('input', function () {
            this.value = this.value.toLowerCase().trim();
            if (!els.emailError) {
                return;
            }
            if (!this.value) {
                els.emailError.classList.add('hidden');
            } else {
                els.emailError.classList.toggle('hidden', emailRegex.test(this.value));
            }
        });

        els.form.addEventListener('submit', handleNewsletterSubmit);
        els.submitBtn.addEventListener('click', handleNewsletterSubmit);
    }

    function initNewsletter() {
        bindNewsletterForm();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNewsletter, { once: true });
    } else {
        initNewsletter();
    }
    window.addEventListener('load', initNewsletter, { once: true });
})();
</script>
        <?php
    }
}
