<?php
require_once __DIR__ . '/environment.php';
require_once __DIR__ . '/image-alt-text.php';
require_once __DIR__ . '/api-adapters.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/cms-page-helpers.php';
require_once __DIR__ . '/cms-prefetch.php';
if (!isset($GLOBALS['api_cache'])) {
    require_once __DIR__ . '/apis.php';
}
cmsPrefetchPankiGlobal();

require_once dirname(__DIR__) . '/shared/includes/cms-header-bulk.php';

$marqueeText = cmsPankiScrollingText();
$headerMeta = cmsPankiHeaderMeta();
$layoutBulk = $GLOBALS['header_footer_data'] ?? $GLOBALS['api_cache']['header_footer_data'] ?? [];
if (!isset($addressdata) || !is_array($addressdata)) {
    $addressdata = cmsAddressLegacy($layoutBulk);
}
if (!isset($contactsdata) || !is_array($contactsdata)) {
    $contactsdata = cmsContactHeaderLegacy($layoutBulk);
}
$contactRow = $contactsdata['data'][0] ?? [];
$erpRow = [];
$headerPhone = trim((string) ($headerMeta['phones'] ?? ($contactRow['contact1'] ?? '')));
$erpUrl = cmsMenuUrl($headerMeta['primary_cta_url'] ?? ($erpRow['url'] ?? ''));
$erpLabel = trim((string) ($headerMeta['primary_cta_text'] ?? ''));
$alumniUrl = cmsMenuUrl($headerMeta['secondary_cta_url'] ?? '');
$alumniLabel = trim((string) ($headerMeta['secondary_cta_text'] ?? ''));
$headerLogoUrl = cmsAssetUrl($headerMeta['logo_url'] ?? '');

require_once dirname(__DIR__) . '/shared/includes/cms-image-alt.php';
$headerLogoAlt = cms_image_alt($headerMeta, 'Allenhouse Panki');
$allenNavItems = cmsNavItems();
?>
<style>
@keyframes slideInfinite {
    0% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(-100%);
    }
}

.animate-slide {
    animation: slideInfinite 20s linear infinite;
}

.animate-slide:hover {
    animation-play-state: paused;
}

@media (prefers-reduced-motion: reduce) {
    .animate-slide {
        animation: none !important;
        transform: none !important;
    }
}

.allen-header-marquee {
    flex: 1 1 auto;
    min-width: 0;
}
</style>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PMKBJSX5" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div id="esuccessPopup" class="relative fixed  bg-green-500 text-white px-4 py-2 rounded mb-5 hidden" style="z-index:9999;
    position: fixed;
    right: 0;
    top: 20%;">
    Form submitted successfully!
</div>

<div
    class="allen-mobile-enquire-tab fixed right-[-50px] top-1/2 -translate-y-1/2 pointer-events-none z-[99] rotate-90 xl:hidden">
    <a href="#" id="openPopup"
        class="text-gray-500 border-[1px] border-white bg-blue-main px-5 py-2 text-lg origin-right pointer-events-auto no-underline text-white transition">
        Enquire Now
    </a>
</div>

<div id="popupForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-[10050]" aria-hidden="true">
    <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative mx-1" role="dialog" aria-modal="true" aria-labelledby="enquiryFormTitle">
        <!-- Close Button -->
        <button type="button" id="closePopup" aria-label="Close enquiry form" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl font-bold z-30 cursor-pointer">
            &times;
        </button>

        <h2 id="enquiryFormTitle" class="text-2xl font-semibold mb-4">Enquiry Form</h2>
        <script>
        window.closePopup = window.closePopup || function () {
            var el = document.getElementById('popupForm');
            if (!el) return;
            el.classList.add('hidden');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };
        window.openPopup = window.openPopup || function () {
            var el = document.getElementById('popupForm');
            if (!el) return;
            el.classList.remove('hidden');
            el.style.removeProperty('display');
            el.setAttribute('aria-hidden', 'false');
        };
        </script>
         <form class="space-y-4" id="enquiryForm" method="POST">
            <div>
                <select name="class-selection" required="" id="esession" class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                    <!-- <option value="" selected>Enquiry For Session</option> -->
                    <?php
                    $sessions = include __DIR__ . "/session-api.php";
                    if (is_array($sessions)) {
                         echo "<option value='' disabled selected>Select Session</option>";
                        foreach ($sessions as $item):
                            $sess = htmlspecialchars($item['session'] ?? '');
                            echo "<option value='$sess'>$sess</option>";
                        endforeach;
                    }
                    ?>
                </select>
            </div>
            <div>
                <select name="class-selection" id="egrade" required
                    class="w-full border border-gray-300 p-[11px] rounded-md text-[#808080cc]">
                    <!-- <option value="" selected>Select Grade</option> -->
                    <?php include __DIR__ . "/grade-api.php"; ?>
                </select>
                <span id="classError" class="text-red-500 text-sm hidden">Please select a class.</span>
            </div>

            <div>
                <input type="text" name="student-name" id="estudent_name" placeholder="Student Name"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="student-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces
                    allowed.</span>
            </div>

            <div>
                <input type="text" name="parent-name" id="eparent_name" placeholder="Parents Name"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="parent-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces allowed.</span>
            </div>

            <div>
                <input type="text" name="mobile" id="emobile" placeholder="Mobile No."
                    class="w-full border border-gray-300 p-2 rounded-md" required maxlenth="10">
                <span id="mobile-error" class="text-red-500 text-sm mt-1 hidden">Please enter valid phone number</span>
            </div>

            <div>
                <input type="text" name="email" id="eemail" placeholder="E-mail"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="email-error" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email
                    address.</span>
            </div>

            <div class="mt-4 relative customSelect">
                <select id="ecity" name="city" class="hidden">
                    <option value="">Select City</option>
                    <?php include __DIR__ . "/get-city.php"; ?>
                </select>

                <!-- Fake dropdown display -->
                <div class="border border-gray-300 p-[11px] rounded-md bg-white cursor-pointer flex justify-between items-center">
                    <span class="selected-text text-[#808080cc]">Select City</span>
                    <span>▼</span>
                </div>

                <!-- Dropdown options -->
                <div class="absolute mt-1 border border-gray-300 rounded-md bg-white shadow-md hidden z-50 w-full">
                    <input type="text" placeholder="Search..."
                        class="w-full p-2 border-b border-gray-300 outline-none">
                    <ul class="max-h-48 overflow-y-auto"></ul>
                </div>
            </div>

            <!-- Pincode -->
            <div>
                <input type="text" name="pincode" id="epincode" placeholder="Enter your Pincode"
                    class="w-full border border-gray-300 p-2 rounded-md" maxlength="6"
                    oninput="this.value=this.value.replace(/\D/g,'')" required>
                <span id="pincode-error" class="text-red-500 text-sm hidden">Please enter a valid Pincode.</span>
            </div>

            <div class="flex items-start gap-2">
                <input type="checkbox" id="popupCheckbox" required />
                <label for="popupCheckbox" class="text-sm">I agree to <a href="termsandconditions"
                        class="text-blue-600 underline">Terms
                        and Conditions</a>.</label>
                <span id="checkboxError" class="text-red-500 text-sm hidden">You must agree to the terms and
                    conditions.</span>
            </div>
            <input type="hidden" name="source" id="source">
            <button type="submit" id="submitBtn" class="w-full py-2 bg-blue-main text-white rounded-md hover:bg-red-500 transition">Submit</button>
        </form>
    </div>
</div>

<div id="allen-site-header" class="allen-site-header">
<div class="allen-top-header-bar allen-top-header-bar--desktop bg-blue-main py-2 px-2 sm:px-4 lg:px-6 justify-between items-center text-[12px] w-full max-w-full overflow-hidden">
    <?php if ($headerPhone !== ''): ?>
    <div>
        <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $headerPhone)) ?>" class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">
            <svg width="22" height="23" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17.7875 19.5793C15.8778 19.5793 13.991 19.1631 12.1271 18.3308C10.2632 17.4985 8.56736 16.3181 7.03958 14.7897C5.51181 13.2613 4.33175 11.5655 3.49942 9.7022C2.66708 7.83892 2.25061 5.95212 2.25 4.04178C2.25 3.76678 2.34167 3.53762 2.525 3.35428C2.70833 3.17095 2.9375 3.07928 3.2125 3.07928H6.925C7.13889 3.07928 7.32986 3.15201 7.49792 3.29745C7.66597 3.4429 7.76528 3.61462 7.79583 3.81262L8.39167 7.02095C8.42222 7.2654 8.41458 7.47165 8.36875 7.6397C8.32292 7.80776 8.23889 7.9529 8.11667 8.07512L5.89375 10.321C6.19931 10.8862 6.562 11.4323 6.98183 11.959C7.40167 12.4858 7.86397 12.994 8.36875 13.4835C8.84236 13.9571 9.33889 14.3965 9.85833 14.8016C10.3778 15.2068 10.9278 15.5771 11.5083 15.9126L13.6625 13.7585C13.8 13.621 13.9797 13.518 14.2015 13.4495C14.4233 13.3811 14.6409 13.3618 14.8542 13.3918L18.0167 14.0335C18.2306 14.0946 18.4062 14.2055 18.5438 14.3662C18.6812 14.5269 18.75 14.7063 18.75 14.9043V18.6168C18.75 18.8918 18.6583 19.121 18.475 19.3043C18.2917 19.4876 18.0625 19.5793 17.7875 19.5793Z"
                    class="fill-white transition-all group-hover:fill-red-500" fill="white" />
            </svg>
            <?php echo htmlspecialchars($headerPhone) ?>
        </a>
    </div>
    <?php endif; ?>

    <?php if ($marqueeText !== ''): ?>
    <div class="allen-header-marquee overflow-hidden md:w-[60%] md:min-w-[200px]">
        <p class="flex items-center animate-slide text-white whitespace-nowrap m-0">
            <?php echo htmlspecialchars($marqueeText) ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="shrink-0">
        <ul class="flex  items-center gap-4 md:w-[30%]">
            <?php if ($erpUrl !== '' && $erpUrl !== '#'): ?>
            <li>
                <a href="<?php echo htmlspecialchars($erpUrl) ?>" target="_blank"
                    class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15.5 12.3293L11.5 8.32928M15.5 12.3293L11.5 16.3293M15.5 12.3293H5.5M10.5 21.3293C11.6819 21.3293 12.8522 21.0965 13.9442 20.6442C15.0361 20.1919 16.0282 19.529 16.864 18.6932C17.6997 17.8575 18.3626 16.8654 18.8149 15.7734C19.2672 14.6815 19.5 13.5112 19.5 12.3293C19.5 11.1474 19.2672 9.97706 18.8149 8.88513C18.3626 7.7932 17.6997 6.80105 16.864 5.96532C16.0282 5.1296 15.0361 4.46666 13.9442 4.01437C12.8522 3.56208 11.6819 3.32928 10.5 3.32928"
                            class=" transition-all group-hover:stroke-red-500" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <?php echo htmlspecialchars($erpLabel) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($alumniUrl !== '' && $alumniUrl !== '#'): ?>
            <li>
                <a href="<?php echo htmlspecialchars($alumniUrl) ?>" target="_blank"
                    class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18.5 19.7543V20.9793C18.5 21.2626 18.404 21.5003 18.212 21.6923C18.02 21.8843 17.7827 21.98 17.5 21.9793C17.2173 21.9786 16.98 21.883 16.788 21.6923C16.596 21.5016 16.5 21.264 16.5 20.9793V17.8293C16.5 17.4126 16.646 17.0586 16.938 16.7673C17.23 16.476 17.584 16.33 18 16.3293H21.15C21.4333 16.3293 21.671 16.4253 21.863 16.6173C22.055 16.8093 22.1507 17.0466 22.15 17.3293C22.1493 17.612 22.0537 17.8496 21.863 18.0423C21.6723 18.235 21.4347 18.3306 21.15 18.3293H19.9L22.15 20.5793C22.3333 20.7626 22.425 20.992 22.425 21.2673C22.425 21.5426 22.3333 21.78 22.15 21.9793C21.95 22.1793 21.7127 22.2793 21.438 22.2793C21.1633 22.2793 20.9257 22.1793 20.725 21.9793L18.5 19.7543ZM12.5 22.3293C11.1167 22.3293 9.81667 22.0666 8.6 21.5413C7.38334 21.016 6.325 20.3036 5.425 19.4043C4.525 18.505 3.81267 17.4466 3.288 16.2293C2.76333 15.012 2.50067 13.712 2.5 12.3293C2.49933 10.9466 2.762 9.64662 3.288 8.42929C3.814 7.21195 4.52633 6.15362 5.425 5.25428C6.32367 4.35495 7.382 3.64262 8.6 3.11728C9.818 2.59195 11.118 2.32928 12.5 2.32928C13.882 2.32928 15.182 2.59195 16.4 3.11728C17.618 3.64262 18.6763 4.35495 19.575 5.25428C20.4737 6.15362 21.1863 7.21195 21.713 8.42929C22.2397 9.64662 22.502 10.9466 22.5 12.3293C22.5 12.496 22.496 12.6793 22.488 12.8793C22.48 13.0793 22.4673 13.2626 22.45 13.4293C22.4167 13.7126 22.3 13.9336 22.1 14.0923C21.9 14.251 21.65 14.33 21.35 14.3293C21.0833 14.3293 20.8583 14.2126 20.675 13.9793C20.4917 13.746 20.4167 13.496 20.45 13.2293C20.4833 13.0626 20.5 12.9126 20.5 12.7793V12.3293C20.5 11.996 20.479 11.6626 20.437 11.3293C20.395 10.996 20.3327 10.6626 20.25 10.3293H16.85C16.9 10.6626 16.9377 10.996 16.963 11.3293C16.9883 11.6626 17.0007 11.996 17 12.3293V12.8673C17 13.0586 16.9917 13.2376 16.975 13.4043C16.9417 13.6876 16.825 13.9126 16.625 14.0793C16.425 14.246 16.1833 14.3293 15.9 14.3293C15.6333 14.3293 15.4043 14.221 15.213 14.0043C15.0217 13.7876 14.9423 13.546 14.975 13.2793C14.9917 13.1126 15 12.9543 15 12.8043V12.3293C15 11.996 14.9877 11.6626 14.963 11.3293C14.9383 10.996 14.9007 10.6626 14.85 10.3293H10.15C10.1 10.6626 10.0627 10.996 10.038 11.3293C10.0133 11.6626 10.0007 11.996 10 12.3293C9.99933 12.6626 10.012 12.996 10.038 13.3293C10.064 13.6626 10.1013 13.996 10.15 14.3293H12.5C12.7833 14.3293 13.021 14.4253 13.213 14.6173C13.405 14.8093 13.5007 15.0466 13.5 15.3293C13.4993 15.612 13.4033 15.8496 13.212 16.0423C13.0207 16.235 12.7833 16.3306 12.5 16.3293H10.6C10.8 17.046 11.0583 17.7336 11.375 18.3923C11.6917 19.051 12.0667 19.68 12.5 20.2793C12.6667 20.2793 12.8333 20.2836 13 20.2923C13.1667 20.301 13.3333 20.2966 13.5 20.2793C13.7833 20.246 14.0167 20.317 14.2 20.4923C14.3833 20.6676 14.475 20.8966 14.475 21.1793C14.475 21.4793 14.4 21.7293 14.25 21.9293C14.1 22.1293 13.8833 22.246 13.6 22.2793C13.4333 22.296 13.25 22.3086 13.05 22.3173C12.85 22.326 12.6667 22.33 12.5 22.3293ZM4.75 14.3293H8.15C8.1 13.996 8.06267 13.6626 8.038 13.3293C8.01333 12.996 8.00067 12.6626 8 12.3293C7.99933 11.996 8.012 11.6626 8.038 11.3293C8.064 10.996 8.10134 10.6626 8.15 10.3293H4.75C4.66667 10.6626 4.60433 10.996 4.563 11.3293C4.52167 11.6626 4.50067 11.996 4.5 12.3293C4.49933 12.6626 4.52033 12.996 4.563 13.3293C4.60567 13.6626 4.668 13.996 4.75 14.3293ZM9.9 19.8793C9.6 19.3126 9.33734 18.7336 9.112 18.1423C8.88667 17.551 8.69934 16.9466 8.55 16.3293H5.6C6.08333 17.1793 6.69167 17.9086 7.425 18.5173C8.15833 19.126 8.98333 19.58 9.9 19.8793ZM5.6 8.32928H8.55C8.7 7.71262 8.88767 7.10862 9.113 6.51728C9.33833 5.92595 9.60067 5.34662 9.9 4.77928C8.98333 5.07928 8.15833 5.53362 7.425 6.14228C6.69167 6.75095 6.08333 7.47995 5.6 8.32928ZM10.6 8.32928H14.4C14.2 7.61262 13.9417 6.92528 13.625 6.26728C13.3083 5.60928 12.9333 4.97995 12.5 4.37928C12.0667 4.97928 11.6917 5.60862 11.375 6.26728C11.0583 6.92595 10.8 7.61328 10.6 8.32928ZM16.45 8.32928H19.4C18.9167 7.47928 18.3083 6.75028 17.575 6.14228C16.8417 5.53428 16.0167 5.07995 15.1 4.77928C15.4 5.34595 15.6627 5.92528 15.888 6.51728C16.1133 7.10928 16.3007 7.71328 16.45 8.32928Z"
                            class="fill-white transition-all group-hover:fill-red-500" fill="white" stroke="#053B7A"
                            stroke-width="0.5" />
                    </svg>
                    <?php echo htmlspecialchars($alumniLabel) ?>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="careers" class="gap-1 flex items-center text-white transition-all whitespace-nowrap group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="#fff" stroke-width="1.5"><path stroke-linecap="round" d="M11.007 21H9.605c-3.585 0-5.377 0-6.491-1.135S2 16.904 2 13.25c0-3.653 0-5.48 1.114-6.615S6.02 5.5 9.605 5.5h3.803c3.585 0 5.378 0 6.492 1.135c.857.873 1.054 2.156 1.1 4.365"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.017 20.023L22 22m-.947-4.474a3.527 3.527 0 1 0-7.053 0a3.527 3.527 0 0 0 7.053 0"/><path d="m16 5.5l-.1-.31c-.495-1.54-.742-2.31-1.331-2.75C13.979 2 13.197 2 11.63 2h-.263c-1.565 0-2.348 0-2.937.44c-.59.44-.837 1.21-1.332 2.75L7 5.5"/></g></svg>
                    Career
                </a>
            </li>
            <li>
                <a href="contact-us" class="gap-1 flex items-center text-white transition-all whitespace-nowrap group">
                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M16.5 2.32928V4.32928M7.5 22.3293V20.3293C7.5 19.7989 7.71071 19.2901 8.08579 18.9151C8.46086 18.54 8.96957 18.3293 9.5 18.3293H15.5C16.0304 18.3293 16.5391 18.54 16.9142 18.9151C17.2893 19.2901 17.5 19.7989 17.5 20.3293V22.3293M8.5 2.32928V4.32928" class="stroke-white transition-all group-hover:stroke-red-500" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5 14.3293C14.1569 14.3293 15.5 12.9861 15.5 11.3293C15.5 9.67243 14.1569 8.32928 12.5 8.32928C10.8431 8.32928 9.5 9.67243 9.5 11.3293C9.5 12.9861 10.8431 14.3293 12.5 14.3293Z" class="stroke-white transition-all group-hover:stroke-red-500" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.5 4.32928H5.5C4.39543 4.32928 3.5 5.22472 3.5 6.32928V20.3293C3.5 21.4339 4.39543 22.3293 5.5 22.3293H19.5C20.6046 22.3293 21.5 21.4339 21.5 20.3293V6.32928C21.5 5.22472 20.6046 4.32928 19.5 4.32928Z" class="stroke-white transition-all group-hover:stroke-red-500" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Contact Us
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="allen-top-header-bar allen-top-header-bar--mobile bg-blue-main text-[10px] py-2 px-2 w-full">
    <div class="allen-top-header-cta-row flex flex-row flex-nowrap items-center justify-between w-full min-w-0 gap-1">
                <?php if ($headerPhone !== ''): ?>
        <div class="allen-top-header-phone min-w-0">
            <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $headerPhone)) ?>" class="gap-1 inline-flex items-center text-white transition-all max-w-full truncate">
                <svg width="12" height="12" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path
                        d="M17.7875 19.5793C15.8778 19.5793 13.991 19.1631 12.1271 18.3308C10.2632 17.4985 8.56736 16.3181 7.03958 14.7897C5.51181 13.2613 4.33175 11.5655 3.49942 9.7022C2.66708 7.83892 2.25061 5.95212 2.25 4.04178C2.25 3.76678 2.34167 3.53762 2.525 3.35428C2.70833 3.17095 2.9375 3.07928 3.2125 3.07928H6.925C7.13889 3.07928 7.32986 3.15201 7.49792 3.29745C7.66597 3.4429 7.76528 3.61462 7.79583 3.81262L8.39167 7.02095C8.42222 7.2654 8.41458 7.47165 8.36875 7.6397C8.32292 7.80776 8.23889 7.9529 8.11667 8.07512L5.89375 10.321C6.19931 10.8862 6.562 11.4323 6.98183 11.959C7.40167 12.4858 7.86397 12.994 8.36875 13.4835C8.84236 13.9571 9.33889 14.3965 9.85833 14.8016C10.3778 15.2068 10.9278 15.5771 11.5083 15.9126L13.6625 13.7585C13.8 13.621 13.9797 13.518 14.2015 13.4495C14.4233 13.3811 14.6409 13.3618 14.8542 13.3918L18.0167 14.0335C18.2306 14.0946 18.4062 14.2055 18.5438 14.3662C18.6812 14.5269 18.75 14.7063 18.75 14.9043V18.6168C18.75 18.8918 18.6583 19.121 18.475 19.3043C18.2917 19.4876 18.0625 19.5793 17.7875 19.5793Z"
                        class="fill-white transition-all group-hover:fill-red-500" fill="white" />
                </svg>
                <span class="truncate"><?php echo htmlspecialchars($headerPhone) ?></span>
            </a>
        </div>
        <?php endif; ?>
        <ul class="allen-top-header-cta-links flex flex-row flex-nowrap items-center justify-end gap-x-1.5 flex-1 min-w-0">
               <?php if ($erpUrl !== '' && $erpUrl !== '#'): ?>
               <li>
                <a href="<?php echo htmlspecialchars($erpUrl) ?>" target="_blank"
                    class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15.5 12.3293L11.5 8.32928M15.5 12.3293L11.5 16.3293M15.5 12.3293H5.5M10.5 21.3293C11.6819 21.3293 12.8522 21.0965 13.9442 20.6442C15.0361 20.1919 16.0282 19.529 16.864 18.6932C17.6997 17.8575 18.3626 16.8654 18.8149 15.7734C19.2672 14.6815 19.5 13.5112 19.5 12.3293C19.5 11.1474 19.2672 9.97706 18.8149 8.88513C18.3626 7.7932 17.6997 6.80105 16.864 5.96532C16.0282 5.1296 15.0361 4.46666 13.9442 4.01437C12.8522 3.56208 11.6819 3.32928 10.5 3.32928"
                            class=" transition-all group-hover:stroke-red-500" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <?php echo htmlspecialchars($erpLabel) ?>
                </a>
            </li>
               <?php endif; ?>
                <?php if ($alumniUrl !== '' && $alumniUrl !== '#'): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($alumniUrl) ?>"
                        class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                        <svg width="14" height="14" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.5 19.7543V20.9793C18.5 21.2626 18.404 21.5003 18.212 21.6923C18.02 21.8843 17.7827 21.98 17.5 21.9793C17.2173 21.9786 16.98 21.883 16.788 21.6923C16.596 21.5016 16.5 21.264 16.5 20.9793V17.8293C16.5 17.4126 16.646 17.0586 16.938 16.7673C17.23 16.476 17.584 16.33 18 16.3293H21.15C21.4333 16.3293 21.671 16.4253 21.863 16.6173C22.055 16.8093 22.1507 17.0466 22.15 17.3293C22.1493 17.612 22.0537 17.8496 21.863 18.0423C21.6723 18.235 21.4347 18.3306 21.15 18.3293H19.9L22.15 20.5793C22.3333 20.7626 22.425 20.992 22.425 21.2673C22.425 21.5426 22.3333 21.78 22.15 21.9793C21.95 22.1793 21.7127 22.2793 21.438 22.2793C21.1633 22.2793 20.9257 22.1793 20.725 21.9793L18.5 19.7543ZM12.5 22.3293C11.1167 22.3293 9.81667 22.0666 8.6 21.5413C7.38334 21.016 6.325 20.3036 5.425 19.4043C4.525 18.505 3.81267 17.4466 3.288 16.2293C2.76333 15.012 2.50067 13.712 2.5 12.3293C2.49933 10.9466 2.762 9.64662 3.288 8.42929C3.814 7.21195 4.52633 6.15362 5.425 5.25428C6.32367 4.35495 7.382 3.64262 8.6 3.11728C9.818 2.59195 11.118 2.32928 12.5 2.32928C13.882 2.32928 15.182 2.59195 16.4 3.11728C17.618 3.64262 18.6763 4.35495 19.575 5.25428C20.4737 6.15362 21.1863 7.21195 21.713 8.42929C22.2397 9.64662 22.502 10.9466 22.5 12.3293C22.5 12.496 22.496 12.6793 22.488 12.8793C22.48 13.0793 22.4673 13.2626 22.45 13.4293C22.4167 13.7126 22.3 13.9336 22.1 14.0923C21.9 14.251 21.65 14.33 21.35 14.3293C21.0833 14.3293 20.8583 14.2126 20.675 13.9793C20.4917 13.746 20.4167 13.496 20.45 13.2293C20.4833 13.0626 20.5 12.9126 20.5 12.7793V12.3293C20.5 11.996 20.479 11.6626 20.437 11.3293C20.395 10.996 20.3327 10.6626 20.25 10.3293H16.85C16.9 10.6626 16.9377 10.996 16.963 11.3293C16.9883 11.6626 17.0007 11.996 17 12.3293V12.8673C17 13.0586 16.9917 13.2376 16.975 13.4043C16.9417 13.6876 16.825 13.9126 16.625 14.0793C16.425 14.246 16.1833 14.3293 15.9 14.3293C15.6333 14.3293 15.4043 14.221 15.213 14.0043C15.0217 13.7876 14.9423 13.546 14.975 13.2793C14.9917 13.1126 15 12.9543 15 12.8043V12.3293C15 11.996 14.9877 11.6626 14.963 11.3293C14.9383 10.996 14.9007 10.6626 14.85 10.3293H10.15C10.1 10.6626 10.0627 10.996 10.038 11.3293C10.0133 11.6626 10.0007 11.996 10 12.3293C9.99933 12.6626 10.012 12.996 10.038 13.3293C10.064 13.6626 10.1013 13.996 10.15 14.3293H12.5C12.7833 14.3293 13.021 14.4253 13.213 14.6173C13.405 14.8093 13.5007 15.0466 13.5 15.3293C13.4993 15.612 13.4033 15.8496 13.212 16.0423C13.0207 16.235 12.7833 16.3306 12.5 16.3293H10.6C10.8 17.046 11.0583 17.7336 11.375 18.3923C11.6917 19.051 12.0667 19.68 12.5 20.2793C12.6667 20.2793 12.8333 20.2836 13 20.2923C13.1667 20.301 13.3333 20.2966 13.5 20.2793C13.7833 20.246 14.0167 20.317 14.2 20.4923C14.3833 20.6676 14.475 20.8966 14.475 21.1793C14.475 21.4793 14.4 21.7293 14.25 21.9293C14.1 22.1293 13.8833 22.246 13.6 22.2793C13.4333 22.296 13.25 22.3086 13.05 22.3173C12.85 22.326 12.6667 22.33 12.5 22.3293ZM4.75 14.3293H8.15C8.1 13.996 8.06267 13.6626 8.038 13.3293C8.01333 12.996 8.00067 12.6626 8 12.3293C7.99933 11.996 8.012 11.6626 8.038 11.3293C8.064 10.996 8.10134 10.6626 8.15 10.3293H4.75C4.66667 10.6626 4.60433 10.996 4.563 11.3293C4.52167 11.6626 4.50067 11.996 4.5 12.3293C4.49933 12.6626 4.52033 12.996 4.563 13.3293C4.60567 13.6626 4.668 13.996 4.75 14.3293ZM9.9 19.8793C9.6 19.3126 9.33734 18.7336 9.112 18.1423C8.88667 17.551 8.69934 16.9466 8.55 16.3293H5.6C6.08333 17.1793 6.69167 17.9086 7.425 18.5173C8.15833 19.126 8.98333 19.58 9.9 19.8793ZM5.6 8.32928H8.55C8.7 7.71262 8.88767 7.10862 9.113 6.51728C9.33833 5.92595 9.60067 5.34662 9.9 4.77928C8.98333 5.07928 8.15833 5.53362 7.425 6.14228C6.69167 6.75095 6.08333 7.47995 5.6 8.32928ZM10.6 8.32928H14.4C14.2 7.61262 13.9417 6.92528 13.625 6.26728C13.3083 5.60928 12.9333 4.97995 12.5 4.37928C12.0667 4.97928 11.6917 5.60862 11.375 6.26728C11.0583 6.92595 10.8 7.61328 10.6 8.32928ZM16.45 8.32928H19.4C18.9167 7.47928 18.3083 6.75028 17.575 6.14228C16.8417 5.53428 16.0167 5.07995 15.1 4.77928C15.4 5.34595 15.6627 5.92528 15.888 6.51728C16.1133 7.10928 16.3007 7.71328 16.45 8.32928Z"
                                class="fill-white transition-all group-hover:fill-red-500" fill="white" stroke="#053B7A"
                                stroke-width="0.5" />
                        </svg>

                        <?php echo htmlspecialchars($alumniLabel) ?>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="careers" class="gap-1 flex items-center text-white transition-all whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="#fff" stroke-width="1.5"><path stroke-linecap="round" d="M11.007 21H9.605c-3.585 0-5.377 0-6.491-1.135S2 16.904 2 13.25c0-3.653 0-5.48 1.114-6.615S6.02 5.5 9.605 5.5h3.803c3.585 0 5.378 0 6.492 1.135c.857.873 1.054 2.156 1.1 4.365"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.017 20.023L22 22m-.947-4.474a3.527 3.527 0 1 0-7.053 0a3.527 3.527 0 0 0 7.053 0"/><path d="m16 5.5l-.1-.31c-.495-1.54-.742-2.31-1.331-2.75C13.979 2 13.197 2 11.63 2h-.263c-1.565 0-2.348 0-2.937.44c-.59.44-.837 1.21-1.332 2.75L7 5.5"/></g></svg>
                        Career
                    </a>
                </li>
                <li>
                    <a href="contact-us" class="gap-1 flex items-center text-white transition-all whitespace-nowrap">
                        <svg width="14" height="14" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M16.5 2.32928V4.32928M7.5 22.3293V20.3293C7.5 19.7989 7.71071 19.2901 8.08579 18.9151C8.46086 18.54 8.96957 18.3293 9.5 18.3293H15.5C16.0304 18.3293 16.5391 18.54 16.9142 18.9151C17.2893 19.2901 17.5 19.7989 17.5 20.3293V22.3293M8.5 2.32928V4.32928" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.5 14.3293C14.1569 14.3293 15.5 12.9861 15.5 11.3293C15.5 9.67243 14.1569 8.32928 12.5 8.32928C10.8431 8.32928 9.5 9.67243 9.5 11.3293C9.5 12.9861 10.8431 14.3293 12.5 14.3293Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19.5 4.32928H5.5C4.39543 4.32928 3.5 5.22472 3.5 6.32928V20.3293C3.5 21.4339 4.39543 22.3293 5.5 22.3293H19.5C20.6046 22.3293 21.5 21.4339 21.5 20.3293V6.32928C21.5 5.22472 20.6046 4.32928 19.5 4.32928Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Contact Us
                    </a>
                </li>
            </ul>
    </div>

    <?php if ($marqueeText !== ''): ?>
    <div class="allen-top-header-marquee text-[10px]">
        <div class="w-full">
            <p class="animate-slide text-white whitespace-nowrap"><?php echo htmlspecialchars($marqueeText) ?></p>
        </div>
    </div>
    <?php endif; ?>

</div>

  <div class="stick-header">
    <header class="desktop-header">
        <div class="logo">
            <a href="<?= site_base_url() ?>"><?php if ($headerLogoUrl !== ''): ?><img src="<?= htmlspecialchars($headerLogoUrl) ?>" class="w-[200px]" alt="<?= $headerLogoAlt ?>"><?php endif; ?></a>
        </div>
        <div class="navbar">
            <ul>
                <?php include __DIR__ . '/cms-nav-desktop.php'; ?>
                  <!-- <li><a href="careers" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Careers </a></li> -->
                 <li class="sm:ml-12 sm:mt-[-1px] mt-5 ml-5 desktopEnquiry list-none">
                    <button type="button" onclick="openPopup()" class="px-4 py-2 bg-blue-main text-white">Enquiry Form</button>
                </li>
            </ul>
        </div>
    </header>
    <!-- Mobile -->
    <div class="mobile-header sdsaf">
        <div class="mobile-header-menu mhm">
            <div>
                <a href="<?= site_base_url() ?>"><?php if ($headerLogoUrl !== ''): ?><img src="<?= htmlspecialchars($headerLogoUrl) ?>" class="w-[150px]" alt="<?= $headerLogoAlt ?>"><?php endif; ?></a>
            </div>
            <div>
                <a class="cursor-pointer" id="menubrop">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24">
                        <path fill="#000" d="M3 18h13v-2H3zm0-5h10v-2H3zm0-7v2h13V6zm18 9.59L17.42 12L21 8.41L19.59 7l-5 5l5 5z" />
                    </svg>
                </a>
            </div>
        </div>
        <!-- Mobile Nav -->
        <div>
            <div class="navbar dfsd" id="NavMenu">
                <div class="bg-white flex items-center justify-between p-5 border-b-[1px]">
                    <div>
                        <a href="<?= site_base_url() ?>"><?php if ($headerLogoUrl !== ''): ?><img src="<?= htmlspecialchars($headerLogoUrl) ?>" class="w-[120px]" alt="<?= $headerLogoAlt ?>"><?php endif; ?></a>
                    </div>
                    <div>
                        <a class="cursor-pointer" id="closeMenu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 64 64">
                                <path fill="#ec1c24" d="M50.592 2.291L32 20.884C25.803 14.689 19.604 8.488 13.406 2.291c-7.17-7.17-18.284 3.948-11.12 11.12c6.199 6.193 12.4 12.395 18.592 18.592A32589 32589 0 0 1 2.286 50.595c-7.164 7.168 3.951 18.283 11.12 11.12q9.297-9.3 18.593-18.594l18.592 18.594c7.17 7.168 18.287-3.951 11.12-11.12q-9.297-9.298-18.597-18.594q9.298-9.299 18.597-18.596c7.168-7.166-3.949-18.284-11.12-11.11" />
                            </svg>
                        </a>
                    </div>
                </div>
                <ul class="mobile-nav">
                    <?php include __DIR__ . '/cms-nav-mobile.php'; ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- End -->
</div>
</div>
<script>
(function () {
    function initAllenMainHeaderSticky() {
        var header = document.getElementById('allen-site-header');
        if (!header) return;

        function getVisibleTopBar() {
            var topBars = header.querySelectorAll(':scope > .bg-blue-main');
            for (var i = 0; i < topBars.length; i++) {
                if (topBars[i].offsetHeight > 0) {
                    return topBars[i];
                }
            }
            return topBars.length ? topBars[0] : null;
        }
        var stickHeader = header.querySelector('.stick-header');
        if (!stickHeader) return;

        function updateSticky() {
            var topBar = getVisibleTopBar();
            var topBarHeight = topBar ? topBar.offsetHeight : 0;
            var scrollTop = (window.scrollY || window.pageYOffset || 0);
            var shouldFix = topBarHeight > 0 ? scrollTop >= topBarHeight : scrollTop > 0;
            stickHeader.classList.toggle('ah-main-fixed', shouldFix);
            document.body.classList.toggle('allen-main-fixed', shouldFix);
            document.body.style.setProperty('--allen-main-header-height', stickHeader.offsetHeight + 'px');
        }

        updateSticky();
        window.addEventListener('scroll', updateSticky, { passive: true });
        window.addEventListener('resize', updateSticky);
        window.addEventListener('load', updateSticky);
        window.addEventListener('ah:tailwind-ready', updateSticky);
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(updateSticky).observe(stickHeader);
            var topBars = header.querySelectorAll(':scope > .bg-blue-main');
            for (var i = 0; i < topBars.length; i++) {
                new ResizeObserver(updateSticky).observe(topBars[i]);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllenMainHeaderSticky);
    } else {
        initAllenMainHeaderSticky();
    }
})();
</script>
