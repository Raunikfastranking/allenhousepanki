<?php
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/environment.php';
require_once __DIR__ . '/cms-bootstrap.php';
if (empty($GLOBALS['cms_skip_auto_sections'])) {
    cmsMaybeRenderExtraSections();
}

if (!function_exists('cmsLayoutPart')) {
    function cmsLayoutItems($layoutData): array
    {
        if (!is_array($layoutData)) {
            return [];
        }

        // Common API shape: ['data' => [...]]
        if (isset($layoutData['data']) && is_array($layoutData['data'])) {
            return $layoutData['data'];
        }

        // Already a list of layout items.
        if (isset($layoutData[0]) && is_array($layoutData[0])) {
            return $layoutData;
        }

        return [];
    }

    function cmsLayoutPart($layoutData, string $slug)
    {
        if ($slug === '') {
            return null;
        }

        foreach (cmsLayoutItems($layoutData) as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['slug'] ?? null) === $slug || ($item['layout_slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('cmsLayoutPartByType')) {
    function cmsLayoutPartByType($layoutData, string $type)
    {
        if ($type === '') {
            return null;
        }

        foreach (cmsLayoutItems($layoutData) as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['type'] ?? null) === $type || ($item['layout_type'] ?? null) === $type) {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('cmsLayoutMeta')) {
    function cmsLayoutMeta($layoutData, string $slug): array
    {
        $part = cmsLayoutPart($layoutData, $slug);
        return is_array($part['meta_data'] ?? null) ? $part['meta_data'] : [];
    }
}

require_once __DIR__ . '/layout-helpers.php';

if (!function_exists('cmsFooterLinkGroup')) {
    function cmsFooterLinkGroup($linkData, string $groupSlug): array
    {
        if ($groupSlug === '') {
            return [];
        }

        $groups = [];
        if (is_array($linkData)) {
            $groups = isset($linkData['data']) && is_array($linkData['data']) ? $linkData['data'] : $linkData;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            if (($group['slug'] ?? null) === $groupSlug || ($group['group_slug'] ?? null) === $groupSlug) {
                return $group;
            }
        }

        return [];
    }
}

if (!function_exists('cmsLoadPankiFooterData')) {
    function cmsLoadPankiFooterData(): array
    {
        static $loaded = false;
        static $layoutData = [];
        static $linkData = [];

        if ($loaded) {
            return ['layout' => $layoutData, 'links' => $linkData];
        }

        $loaded = true;
        if (!defined('BRANCH_ID') || !function_exists('cms_curl_get_json') || !defined('CMS_API_URL')) {
            return ['layout' => [], 'links' => []];
        }

        require_once dirname(__DIR__) . '/shared/includes/cms-layout-bootstrap.php';
        $boot = cmsLoadSiteLayoutBootstrap();
        $layoutData = $boot['layout'];
        $linkData = $boot['links'];

        return ['layout' => $layoutData, 'links' => $linkData];
    }
}

$header_footer_data = isset($header_footer_data) && is_array($header_footer_data) ? $header_footer_data : [];
$footer_link_data = isset($footer_link_data) && is_array($footer_link_data) ? $footer_link_data : [];
if ($header_footer_data === [] && isset($GLOBALS['header_footer_data']) && is_array($GLOBALS['header_footer_data'])) {
    $header_footer_data = $GLOBALS['header_footer_data'];
}
if (
    ($footer_link_data === [] || (cmsFooterLinkGroup($footer_link_data, 'important-links') === [] && cmsFooterLinkGroup($footer_link_data, 'quick-links') === []))
    && isset($GLOBALS['footer_link_data'])
    && is_array($GLOBALS['footer_link_data'])
) {
    $footer_link_data = $GLOBALS['footer_link_data'];
}

if (cmsLayoutItems($header_footer_data) === []) {
    $footerBootstrap = cmsLoadPankiFooterData();
    $header_footer_data = $footerBootstrap['layout'];
}

if (
    cmsFooterLinkGroup($footer_link_data, 'important-links') === []
    || cmsFooterLinkGroup($footer_link_data, 'quick-links') === []
) {
    $footerBootstrap = cmsLoadPankiFooterData();
    $footer_link_data = $footerBootstrap['links'];
}

$layoutItems = cmsLayoutItems($header_footer_data);
$importantGroup = cmsFooterLinkGroup($footer_link_data, 'important-links');
$quickGroup = cmsFooterLinkGroup($footer_link_data, 'quick-links');

// Extract header and footer parts
$headerPart = cmsLayoutPart($header_footer_data, 'header-aps-panki')
    ?? cmsLayoutPartByType($header_footer_data, 'header')
    ?? ($layoutItems[0] ?? []);
$headerMeta = is_array($headerPart['meta_data'] ?? null) ? $headerPart['meta_data'] : cmsLayoutMeta($header_footer_data, 'header-aps-panki');

$footerPart = cmsLayoutPart($header_footer_data, 'footer-aps-panki')
    ?? cmsLayoutPartByType($header_footer_data, 'footer')
    ?? ($layoutItems[1] ?? []);
$footerMeta = is_array($footerPart['meta_data'] ?? null) ? $footerPart['meta_data'] : cmsLayoutMeta($header_footer_data, 'footer-aps-panki');

// Footer logo (fallback to header logo if footer logo missing)
$footerLogo = cmsAssetUrl($footerMeta['footer_logo_url'] ?? ($headerMeta['logo_url'] ?? ''));

// Address (from footer_about)
$footerAbout = trim((string) ($footerMeta['footer_about'] ?? ''));
$footerPhone = trim((string) ($footerMeta['footer_phones'] ?? ($headerMeta['phones'] ?? '')));
$footerEmail = trim((string) ($footerMeta['footer_email'] ?? ''));
$copyrightText = trim((string) ($footerMeta['copyright_text'] ?? ''));

// App badges
$androidAppLink = $footerMeta['android_app_link'] ?? '';
$iosAppLink     = $footerMeta['ios_app_link'] ?? '';
$androidBadge   = cmsAssetUrl($footerMeta['android_badge_url'] ?? '');
$iosBadge       = cmsAssetUrl($footerMeta['ios_badge_url'] ?? '');


require_once dirname(__DIR__) . '/shared/includes/cms-image-alt.php';
$footerLogoAlt = cms_image_alt(
    is_array($footerMeta)
        ? array_merge($footerMeta, ['logo_alt' => $footerMeta['footer_logo_alt'] ?? $footerMeta['logo_alt'] ?? ($headerMeta['logo_alt'] ?? null)])
        : null,
    'Allenhouse Panki'
);
$androidBadgeAlt = cms_image_alt(['image_alt' => $footerMeta['android_badge_alt'] ?? null], 'Get it on Google Play');
$iosBadgeAlt = cms_image_alt(['image_alt' => $footerMeta['ios_badge_alt'] ?? null], 'Download on the App Store');// Social media links (from footer meta)
$socialLinks = [
    ['name' => 'Youtube',   'url' => $footerMeta['footer_youtube'] ?? ''],
    ['name' => 'Facebook',  'url' => $footerMeta['footer_facebook'] ?? ''],
    ['name' => 'Instagram', 'url' => $footerMeta['footer_instagram'] ?? ''],
    ['name' => 'LinkedIn',  'url' => $footerMeta['footer_linkedin'] ?? ''],
];
$socialLinks = array_filter($socialLinks, fn($item) => !empty($item['url']));
require_once dirname(__DIR__) . '/shared/includes/footer-social-media.php';

// WhatsApp number
$whatsappNumber = trim((string) ($footerMeta['whatsapp_number'] ?? ''));

// Important Links & Quick Links (from CMS link groups – if available)
$importantLinks = $importantGroup['links'] ?? [];
require_once dirname(__DIR__) . '/shared/includes/cms-footer-portal-links.php';
$quickLinks = cmsFooterQuickLinksForDisplay(is_array($quickGroup['links'] ?? null) ? $quickGroup['links'] : []);

$linkClass = 'agra-footer-link text-white transition hover:text-red-500';
$newsletterPlaceholder = $footerMeta['newsletter_placeholder'] ?? 'Type Email';
$newsletterButton = $footerMeta['newsletter_button'] ?? 'Subscribe';
?>
<?php include __DIR__ . '/form-proxy-client.php'; ?>
<style>
    .real-is-hide { display: none; }
    .real-should-display:hover .real-is-hide { display: block; }
    .real-should-display:hover .not-real-hide { display: none; }
    .whatsapp-button { position: fixed; right: 15px; bottom: 15px; z-index: 999; transition: transform 0.3s ease; }
    .whatsapp-button img { width: 60px; height: 60px; border-radius: 50%; }
    .whatsapp-button:hover img { box-shadow: 0 0 20px #25D366, 0 0 40px #25D366; transform: scale(1.1); }

    .agra-footer-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        max-width: 1280px;
        margin-left: auto;
        margin-right: auto;
        align-items: start;
    }
    @media (min-width: 640px) {
        .agra-footer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1300px) {
        .agra-footer-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2.5rem; }
    }
    .footer-links-col h2 {
        display: block;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        font-size: 18px;
        font-weight: 600;
    }
    @media (min-width: 768px) {
        .footer-links-col h2 { font-size: 22px; }
    }
    .agra-footer-link,
    .footer-links-col .toggle-submenu {
        font-size: 12px;
        font-weight: 400;
        line-height: 1.5;
    }
    @media (min-width: 768px) {
        .agra-footer-link,
        .footer-links-col .toggle-submenu {
            font-size: 16px;
        }
    }
    @media (min-width: 1024px) {
        .agra-footer-link,
        .footer-links-col .toggle-submenu {
            font-size: 17px;
        }
    }
    .footer-links-col .submenu a {
        font-size: 14px;
    }
    @media (min-width: 768px) {
        .footer-links-col .submenu a { font-size: 15px; }
    }
    .footer-contact-row {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        text-align: left;
        gap: 0.75rem;
    }
    .footer-contact-icon {
        width: 22px;
        height: 22px;
        min-width: 22px;
        min-height: 22px;
        flex-shrink: 0;
        margin-top: 0.15rem;
    }
    .footer-contact-text {
        text-align: left;
    }
</style>

<div id="NewsPopup" class="fixed bg-green-500 text-white px-4 py-2 rounded hidden" style="z-index:9999; right: 0; bottom: 20%;">
    Newsletter Subscribed.
</div>

<footer class="py-5 px-4 md:px-8 pmt-20 bg-blue-main">
<div class="agra-footer-grid">

    <!-- Left column: Logo & contact info -->
    <div class="mt-3 text-left">
        <div>
            <a href="index">
                <img src="<?= htmlspecialchars($footerLogo) ?>" alt="<?= $footerLogoAlt ?>" class="w-[236px] md:w-[200px] h-auto object-contain" width="236" height="102" style="aspect-ratio: 236 / 102" loading="lazy" decoding="async">
            </a>
        </div>
        <div class="mt-5">
            <div class="mt-4 md:mt-6">
                <div class="flex gap-2 mb-3">
                    <p class="text-white font-[400] 2xl:text-[17px] xl:text-[14px] lg:text-[13px] md:text-[12px]">
                        <?= nl2br(htmlspecialchars($footerAbout)) ?>
                    </p>
                </div>
                <div class="footer-contact-row mb-3">
                    <svg class="footer-contact-icon" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <g clip-path="url(#clip0_105_2550)">
                            <path d="M11.388 12.4369C9.20445 11.5124 7.46809 9.772 6.54862 7.58633L9.09841 5.03204L4.4038 0.33368L2.02575 2.71098C1.61328 3.12581 1.28717 3.6183 1.06628 4.15998C0.845385 4.70166 0.734098 5.28176 0.738855 5.86672C0.738855 11.3023 7.67728 18.2407 13.1128 18.2407C13.6977 18.2458 14.2778 18.1347 14.8194 17.9138C15.361 17.6929 15.8534 17.3666 16.2678 16.9538L18.6459 14.5758L13.9475 9.87739L11.388 12.4369ZM15.2067 15.8934C14.9313 16.1664 14.6043 16.3819 14.2449 16.5274C13.8854 16.6729 13.5006 16.7454 13.1128 16.7408C8.43847 16.7408 2.23873 10.5411 2.23873 5.86672C2.23431 5.47888 2.30695 5.09402 2.45242 4.73446C2.59789 4.3749 2.8133 4.04781 3.08616 3.77215L4.4038 2.4545L6.98134 5.03204L4.78027 7.23311L4.964 7.69357C5.5049 9.14043 6.35046 10.4541 7.44341 11.5457C8.53636 12.6372 9.85116 13.481 11.2987 14.02L11.7532 14.1933L13.9475 11.9982L16.525 14.5758L15.2067 15.8934ZM11.238 1.74206V0.242188C13.2263 0.244371 15.1325 1.03518 16.5384 2.44112C17.9444 3.84705 18.7352 5.75328 18.7374 7.74157H17.2375C17.2357 6.15095 16.603 4.62599 15.4783 3.50125C14.3536 2.37651 12.8286 1.74385 11.238 1.74206ZM11.238 4.74182V3.24194C12.431 3.24313 13.5748 3.71758 14.4184 4.56117C15.262 5.40475 15.7364 6.54856 15.7376 7.74157H14.2377C14.2377 6.94598 13.9217 6.18298 13.3591 5.62042C12.7966 5.05786 12.0336 4.74182 11.238 4.74182Z" fill="#FBFBFB" />
                        </g>
                        <defs><clipPath id="clip0_105_2550"><rect width="17.9985" height="17.9985" fill="white" transform="translate(0.738953 0.242188)"/></clipPath></defs>
                    </svg>
                    <p class="footer-contact-text text-white font-[400] 2xl:text-[17px] xl:text-[14px] lg:text-[13px] md:text-[12px]">
                        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $footerPhone)) ?>"><?= htmlspecialchars($footerPhone) ?></a>
                    </p>
                </div>
                <div class="footer-contact-row mb-3">
                    <svg class="footer-contact-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" aria-hidden="true"><path fill="#fff" d="M28.516 7.167H3.482L16 14.275zM16.74 17.303a1.5 1.5 0 0 1-1.48 0L2.5 10.06v14.773h27V10.06z"/></svg>
                    <a class="footer-contact-text text-white font-[400] 2xl:text-[17px] xl:text-[14px] lg:text-[13px] md:text-[12px]" href="mailto:<?= htmlspecialchars($footerEmail) ?>"><?= htmlspecialchars($footerEmail) ?></a>
                </div>
                <?php cmsRenderFooterSocialMedia($socialLinks, ['wrapper_class' => 'mt-4', 'hover_swap' => false, 'list_class' => 'flex flex-wrap gap-4 mt-3']); ?>
            </div>
        </div>
    </div>
    <!-- Column 2: Important Links -->
    <div class="mt-3 footer-links-col min-w-[9rem]">
        <h2 class="block mb-2" style="color:#B4D7FF">Important Links</h2>
        <ul class="grid grid-cols-1 gap-2">
                <?php foreach ($importantLinks as $link):
                    $url = cmsMenuUrl($link['url'] ?? '#');
                    $title = $link['title'] ?? $link['name'] ?? 'Link';
                    $children = $link['children'] ?? [];
                ?>
                <li>
                    <?php if (empty($children)): ?>
                        <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" class="<?= $linkClass ?>"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <button type="button" class="toggle-submenu <?= $linkClass ?> transition text-left inline-flex items-center relative my-1 gap-1 group" aria-expanded="false">
                            <?= htmlspecialchars($title) ?>
                            <i class="fa-solid fa-chevron-down footer-submenu-caret text-[10px] leading-none" aria-hidden="true"></i>
                        </button>
                        <div class="submenu hidden z-10 mt-1 bg-white w-44 rounded shadow-md">
                            <ul class="text-sm">
                                <?php foreach ($children as $child): ?>
                                <li class="border-b border-b-[#e5e7eb] last:border-b-0 hover:border-l-[#002A5B] hover:border-l-2">
                                    <a href="<?= htmlspecialchars(cmsMenuUrl($child['url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" class="block transition py-2 px-2 text-blue-main hover:text-red-500"><?= htmlspecialchars($child['title'] ?? $child['name'] ?? 'Link', ENT_QUOTES, 'UTF-8') ?></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
    </div>

    <!-- Column 3: Quick Links -->
    <div class="mt-3 sm:mt-3 footer-links-col min-w-[9rem]">
        <h2 class="block mb-2" style="color:#B4D7FF">Quick links</h2>
        <ul class="grid grid-cols-1 md:gap-4 gap-2">
                <?php foreach ($quickLinks as $link):
                    $url = cmsMenuUrl($link['url'] ?? '#');
                    $title = $link['title'] ?? $link['name'] ?? 'Link';
                ?>
                <li><a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" class="<?= $linkClass ?>"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
    </div>

    <!-- Column 4: App badges, Newsletter -->
    <div class="mt-3">
        <!-- Allen Care App badges -->
        <div>
            <h2 class="sm:text-[22px] text-[18px] font-[600] mt-3 sm:mt-5" style="color:#B4D7FF">Allen Care App</h2>
            <div class="flex items-center mt-3 gap-1">
                <?php if ($androidAppLink && $androidBadge): ?>
                <a href="<?= htmlspecialchars($androidAppLink) ?>" download><img class="w-[140px]" src="<?= htmlspecialchars($androidBadge) ?>" alt="<?= $androidBadgeAlt ?>"></a>
                <?php endif; ?>
                <?php if ($iosAppLink && $iosBadge): ?>
                <a href="<?= htmlspecialchars($iosAppLink) ?>" target="_blank"><img class="w-[140px]" src="<?= htmlspecialchars($iosBadge) ?>" alt="<?= $iosBadgeAlt ?>"></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="md:mt-5 mt-3">
            <h2 class="md:text-[22px] text-[18px] font-[600]" style="color:#B4D7FF">Newsletter</h2>
            <div class="md:mt-5 mt-2">
                <form id="newsForm" action="#" method="post" novalidate>
                    <div>
                        <div class="flex items-stretch gap-2 w-full">
                            <input type="email" class="sm:p-3 p-2 px-3 border-[1px] outline-none rounded-[10px] flex-1 min-w-0 text-[12px] sm:text-[16px]" style="flex: 1 1 0%; min-width: 0;" placeholder="<?= htmlspecialchars($newsletterPlaceholder) ?>" id="news-email" autocomplete="email" required />
                            <button type="submit" id="newsSubmit" class="bg-red-500 text-white p-2 px-3 sm:p-3 sm:px-4 rounded-[10px] transition-all hover:bg-red-600 text-[11px] sm:text-[16px] flex-none whitespace-nowrap" style="background-color:#ef4444; flex: 0 0 auto; white-space: nowrap;"><?= htmlspecialchars($newsletterButton) ?></button>
                        </div>
                        <span id="news-email-error" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address.</span>
                        <span id="news-email-success" class="text-white text-sm mt-1 hidden">Thank you for subscribing!</span>
                    </div>
                </form>
            </div>
        </div>
        <?php include __DIR__ . '/newsletter-script.php'; ?>
    </div>
</div>
</footer>

<!-- Copyright -->
<?php if (!empty($copyrightText)): ?>
<div class="bg-gray-200 p-3 text-center">
    <p class="text-gray-600 sm:text-[14px] text-[12px]"><?= htmlspecialchars($copyrightText) ?></p>
</div>
<?php endif; ?>

<!-- WhatsApp floating button -->
<?php if (!empty($whatsappNumber)): ?>
    <a href="https://api.whatsapp.com/send?phone=<?= htmlspecialchars(preg_replace('/\D+/', '', $whatsappNumber)) ?>" class="whatsapp-button" target="_blank" rel="noopener">
        <img src="https://i.ibb.co/VgSspjY/whatsapp-button.png" alt="WhatsApp">
    </a>
<?php endif; ?>

<!-- Footer submenu toggle (Agra pattern) -->
<script>
document.querySelectorAll('footer .toggle-submenu').forEach(function (button) {
    button.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var parentLi = button.closest('li');
        var submenu = parentLi ? parentLi.querySelector('.submenu') : null;
        if (!submenu) {
            return;
        }
        var wasOpen = !submenu.classList.contains('hidden');

        document.querySelectorAll('footer .submenu').forEach(function (other) {
            other.classList.add('hidden');
        });
        document.querySelectorAll('footer .toggle-submenu').forEach(function (otherBtn) {
            otherBtn.setAttribute('aria-expanded', 'false');
            var otherCaret = otherBtn.querySelector('.footer-submenu-caret');
            if (otherCaret) {
                otherCaret.classList.add('fa-chevron-down');
                otherCaret.classList.remove('fa-chevron-up');
            }
        });

        if (!wasOpen) {
            submenu.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');
            var caret = button.querySelector('.footer-submenu-caret');
            if (caret) {
                caret.classList.remove('fa-chevron-down');
                caret.classList.add('fa-chevron-up');
            }
        }
    });
});
</script>
