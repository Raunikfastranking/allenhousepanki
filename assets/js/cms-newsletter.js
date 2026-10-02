(function () {
    var boot = window.CMS_NEWSLETTER_BOOT || {};
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var buttonLabel = boot.buttonLabel || 'Subscribe';
    var submitting = false;

    function resolveProxyUrl() {
        if (window.CMS_FORM_PROXIES && window.CMS_FORM_PROXIES.newsletter) {
            return window.CMS_FORM_PROXIES.newsletter;
        }
        if (boot.proxyUrl) {
            return boot.proxyUrl;
        }
        var path = window.location.pathname || '/';
        var match = path.match(/^(\/[^/]+)\//);
        var prefix = match ? match[1] : '';
        return window.location.origin + prefix + '/proxy/newsletter-proxy.php';
    }

    function isNewsletterSuccess(status, data) {
        if (status < 200 || status >= 300) {
            return false;
        }
        if (!data || typeof data !== 'object') {
            return true;
        }
        if (data.success === true) {
            return true;
        }
        if (data.success === false) {
            return false;
        }
        return typeof data.message === 'string' && /subscribed/i.test(data.message);
    }

    function postNewsletter(email) {
        var proxyUrl = resolveProxyUrl();
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', proxyUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) {
                    return;
                }
                var data = {};
                if (xhr.responseText) {
                    try {
                        data = JSON.parse(xhr.responseText);
                    } catch (e) {
                        reject(new Error('Invalid server response. Please try again.'));
                        return;
                    }
                }
                if (!isNewsletterSuccess(xhr.status, data)) {
                    var msg = data.message || ('Request failed (' + xhr.status + ')');
                    if (!data.message && data.errors && typeof data.errors === 'object') {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            msg = data.errors[keys[0]][0];
                        }
                    }
                    reject(new Error(msg));
                    return;
                }
                resolve(data);
            };
            xhr.onerror = function () {
                reject(new Error('Network error. Please try again.'));
            };
            xhr.send(JSON.stringify({
                email: email,
                subscription_source: 'website_footer'
            }));
        });
    }

    function getNewsletterEls() {
        var form = document.getElementById('newsForm');
        if (!form) {
            return null;
        }
        return {
            form: form,
            emailInput: form.querySelector('#news-email'),
            emailError: document.getElementById('news-email-error'),
            emailSuccess: document.getElementById('news-email-success'),
            submitBtn: form.querySelector('#newsSubmit'),
            popup: document.getElementById('NewsPopup')
        };
    }

    function showNewsletterSuccess(els, data) {
        if (els.emailSuccess) {
            els.emailSuccess.classList.remove('hidden');
            setTimeout(function () { els.emailSuccess.classList.add('hidden'); }, 8000);
        }
        if (els.popup) {
            els.popup.classList.remove('hidden');
            setTimeout(function () { els.popup.classList.add('hidden'); }, 5000);
        }
        if (window.console && console.log) {
            var savedId = data && data.data && data.data.id ? data.data.id : null;
            console.log('[newsletter] saved', { email: els.emailInput ? els.emailInput.value : '', id: savedId, proxy: resolveProxyUrl() });
        }
    }

    function handleNewsletterSubmit(evt) {
        if (evt && evt.preventDefault) {
            evt.preventDefault();
        }
        if (submitting) {
            return false;
        }

        var els = getNewsletterEls();
        if (!els || !els.emailInput || !els.emailError || !els.submitBtn) {
            return false;
        }

        var email = els.emailInput.value.trim().toLowerCase();
        els.emailInput.value = email;

        if (!email) {
            els.emailError.textContent = 'Please enter an email address.';
            els.emailError.classList.remove('hidden');
            if (els.emailSuccess) {
                els.emailSuccess.classList.add('hidden');
            }
            return false;
        }
        if (!emailRegex.test(email)) {
            els.emailError.textContent = 'Please enter a valid email address.';
            els.emailError.classList.remove('hidden');
            if (els.emailSuccess) {
                els.emailSuccess.classList.add('hidden');
            }
            return false;
        }

        els.emailError.classList.add('hidden');
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
                var msg = error.message || 'There was an error submitting the form. Please try again.';
                if (els.emailError) {
                    els.emailError.textContent = msg;
                    els.emailError.classList.remove('hidden');
                }
                alert(msg);
                if (window.console && console.error) {
                    console.error('[newsletter] error:', error, 'proxy:', resolveProxyUrl());
                }
            })
            .finally(function () {
                submitting = false;
                els.submitBtn.disabled = false;
                els.submitBtn.textContent = buttonLabel;
            });

        return false;
    }

    window.__cmsNewsletterSubmit = handleNewsletterSubmit;
    window.cmsSubmitNewsletter = handleNewsletterSubmit;

    function bindNewsletterForm() {
        var els = getNewsletterEls();
        if (!els || !els.emailInput || !els.emailError || !els.submitBtn) {
            return;
        }
        if (els.form.dataset.newsletterBound === '1') {
            return;
        }
        els.form.dataset.newsletterBound = '1';

        els.emailInput.addEventListener('input', function () {
            this.value = this.value.toLowerCase().trim();
            if (!this.value) {
                els.emailError.classList.add('hidden');
            } else {
                els.emailError.classList.toggle('hidden', emailRegex.test(this.value));
            }
        });

        els.submitBtn.addEventListener('click', handleNewsletterSubmit);
        els.form.addEventListener('submit', handleNewsletterSubmit);
    }

    function initNewsletter() {
        bindNewsletterForm();
        return document.getElementById('newsForm') !== null
            && document.getElementById('newsForm').dataset.newsletterBound === '1';
    }

    function ensureNewsletterInit() {
        if (initNewsletter()) {
            return;
        }
        var attempts = 0;
        var timer = setInterval(function () {
            attempts += 1;
            if (initNewsletter() || attempts >= 40) {
                clearInterval(timer);
            }
        }, 50);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureNewsletterInit, { once: true });
    } else {
        ensureNewsletterInit();
    }
    window.addEventListener('load', ensureNewsletterInit, { once: true });
})();
