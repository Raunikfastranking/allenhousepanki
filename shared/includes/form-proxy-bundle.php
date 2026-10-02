<?php
/**
 * CMS form proxy JSON parser + enquiry payload builder for header / admission forms.
 */
require_once __DIR__ . '/enquiry-form-client.php';
if (!function_exists('site_origin_url')) {
    require_once dirname(__DIR__, 2) . '/includes/environment.php';
}
require_once __DIR__ . '/cms-form-client-init.php';
?>
<script>
window.cmsParseProxyJson = function (response) {
    return response.text().then(function (text) {
        var data = {};
        if (text) {
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error("Invalid server response. Please try again.");
            }
        }
        var failed = !response.ok;
        if (!failed && data.success === false) {
            failed = true;
        }
        if (!failed && data.status === "error") {
            failed = true;
        }
        if (!failed && data.status === false) {
            failed = true;
        }
        if (failed && response.ok && typeof data.message === "string" && /subscribed|subscription successful/i.test(data.message)) {
            failed = false;
        }
        if (failed && response.ok && data.data && data.data.id) {
            failed = false;
        }
        if (failed) {
            var msg = data.message || "Submission failed. Please try again.";
            if (!data.message && data.errors && typeof data.errors === "object") {
                var keys = Object.keys(data.errors);
                if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                    msg = data.errors[keys[0]][0];
                }
            }
            throw new Error(msg);
        }
        return data;
    });
};
</script>
