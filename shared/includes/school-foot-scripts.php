<?php

declare(strict_types=1);

/**
 * Optional post-load hook after Glide/jQuery (Tailwind loads deferred from head).
 * Re-scan Tailwind when CMS injects late markup (rare).
 */
require_once __DIR__ . '/cms-nav-nested-flyout.php';
cmsNavNestedFlyoutScript();
?>
<script>
(function () {
    function refreshTailwind() {
        if (typeof tailwind !== 'undefined' && typeof tailwind.refresh === 'function') {
            tailwind.refresh();
        }
        window.dispatchEvent(new Event('ah:tailwind-ready'));
    }
    window.addEventListener('load', refreshTailwind, { once: true });
})();
</script>
<?php
// Precompiled Tailwind — page loader disabled (P10).
?>
