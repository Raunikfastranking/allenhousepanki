<?php

declare(strict_types=1);

/**
 * Self-hosted Poppins — no render-blocking Google Fonts request; preload critical weights.
 */
$fontBase = site_asset_url('assets/fonts/poppins/');
$fontVer = '1';
?>
<link rel="preload" href="<?= htmlspecialchars($fontBase . 'poppins-400-latin.woff2', ENT_QUOTES, 'UTF-8') ?>?v=<?= $fontVer ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= htmlspecialchars($fontBase . 'poppins-600-latin.woff2', ENT_QUOTES, 'UTF-8') ?>?v=<?= $fontVer ?>" as="font" type="font/woff2" crossorigin>
<style>
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 300;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-300-latin.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-400-devanagari.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0900-097F, U+1CD0-1CF9, U+200C-200D, U+20A8, U+20B9, U+20F0, U+25CC, U+A830-A839, U+A8E0-A8FF, U+11B00-11B09;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-400-latin.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-500-latin.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-600-devanagari.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0900-097F, U+1CD0-1CF9, U+200C-200D, U+20A8, U+20B9, U+20F0, U+25CC, U+A830-A839, U+A8E0-A8FF, U+11B00-11B09;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-600-latin.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('<?= $fontBase ?>poppins-700-latin.woff2?v=<?= $fontVer ?>') format('woff2');
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
body {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
</style>
