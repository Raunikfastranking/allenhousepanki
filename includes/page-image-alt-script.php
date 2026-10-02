<?php

if (!empty($GLOBALS['__page_image_alt_script_included'])) {
    return;
}
$GLOBALS['__page_image_alt_script_included'] = true;

require_once __DIR__ . '/image-alt-text.php';
$pageImageAltMapJson = json_encode(
    cms_image_alt_client_map(),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
);
if ($pageImageAltMapJson === false) {
    $pageImageAltMapJson = '{}';
}
?>
<script>
(function () {
    var map = <?php echo $pageImageAltMapJson; ?>;

    function norm(u) {
        if (!u) return "";
        u = String(u).trim();
        try {
            return new URL(u, window.location.href).href;
        } catch (e) {
            return u.replace(/\\/g, "/");
        }
    }

    function lookupAlt(src) {
        var n = norm(src);
        if (map[n]) return map[n];
        if (map[src]) return map[src];
        for (var k in map) {
            if (!Object.prototype.hasOwnProperty.call(map, k)) continue;
            if (n === k || n.indexOf(k) !== -1 || k.indexOf(n) !== -1) return map[k];
        }
        return "";
    }

    function applyAlts() {
        document.querySelectorAll("img[src]").forEach(function (img) {
            var v = lookupAlt(img.getAttribute("src"));
            if (v) img.setAttribute("alt", v);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", applyAlts);
    } else {
        applyAlts();
    }

    if (typeof MutationObserver !== "undefined") {
        var mo = new MutationObserver(function () { applyAlts(); });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    }
})();
</script>
