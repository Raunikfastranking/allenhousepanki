<?php

declare(strict_types=1);

/**
 * Home page flyer popup — layout matches dpsamrapali/index.php.
 * Expects $showPopup, $image, $url, and optional $text to be set by the caller.
 */
if (empty($showPopup)) {
    return;
}

$flyerUrl = htmlspecialchars((string) ($url ?? '#'), ENT_QUOTES, 'UTF-8');
$flyerImage = htmlspecialchars((string) ($image ?? ''), ENT_QUOTES, 'UTF-8');

if (function_exists('getApiAltText')) {
    $flyerAlt = htmlspecialchars(
        getApiAltText(['image' => $image ?? '', 'text' => $text ?? ''], 'School announcement'),
        ENT_QUOTES,
        'UTF-8'
    );
} elseif (function_exists('cms_image_alt')) {
    $flyerAlt = htmlspecialchars(
        cms_image_alt(['image' => $image ?? '', 'text' => $text ?? ''] ?? null, 'School announcement'),
        ENT_QUOTES,
        'UTF-8'
    );
} else {
    $flyerAlt = htmlspecialchars((string) ($text ?? 'School announcement'), ENT_QUOTES, 'UTF-8');
}
?>
<div id="formOverlay"
    class="fixed inset-0 bg-gray-800 bg-opacity-60 flex items-center justify-center z-[99999] hidden">

    <div class="bg-white rounded-xl shadow-2xl p-6 w-full lg:max-w-xl md:max-w-lg sm:max-w-md relative">
        <button id="dismissPopup" type="button" aria-label="Close popup"
            class="absolute top-4 right-4 text-gray-400 hover:text-red-400 text-2xl font-bold">&times;</button>

        <a href="<?= $flyerUrl ?>">
            <img src="<?= $flyerImage ?>" alt="<?= $flyerAlt ?>">
        </a>
    </div>

</div>
<script>
window.addEventListener('load', function () {
    var popup = document.getElementById('formOverlay');
    if (popup) {
        popup.classList.remove('hidden');
    }
});

var dismiss = document.getElementById('dismissPopup');
if (dismiss) {
    dismiss.addEventListener('click', function () {
        var popup = document.getElementById('formOverlay');
        if (popup) {
            popup.classList.add('hidden');
        }
    });
}
</script>
