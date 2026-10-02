<?php

declare(strict_types=1);

/** @var array|null $s_intro */
/** @var array|null $s_plc_intro */
/** @var array|null $s_plc_columns */
/** @var array|null $s_cards_heading */
/** @var list<array<string, mixed>> $s_card_sections */

if (!function_exists('cmsFacilitiesRenderIntroCarousel')) {
    function cmsFacilitiesRenderIntroCarousel(array $medias): void
    {
        if ($medias === []) {
            return;
        }
        ?>
        <div class="glide relative cms-facilities-glide cms-facilities-intro-carousel opne-hide-circle mt-5" data-cms-dedicated-glide="1">
            <div class="glide__track overflow-hidden" data-glide-el="track">
                <ul class="glide__slides">
                    <?php foreach ($medias as $imgData):
                        $src = (string) ($imgData['urls'] ?? $imgData['image_url'] ?? '');
                        if ($src === '') {
                            continue;
                        }
                        $alt = (string) ($imgData['image_alt'] ?? $imgData['title'] ?? 'Facilities');
                        ?>
                    <li class="glide__slide">
                        <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                             class="w-full max-w-full m-auto"
                             loading="lazy">
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="absolute left-0 xl:flex hidden items-center justify-between w-full h-0 px-4 top-1/2 hide-circle" data-glide-el="controls">
                <button class="inline-flex items-center relative sm:right-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir="<" aria-label="prev slide">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button class="inline-flex items-center relative sm:left-[66px] justify-center hover:bg-red-500 hover:text-white w-8 h-8 transition duration-300 border rounded-full lg:w-10 lg:h-10 text-slate-700 border-slate-700 hover:text-slate-900 hover:border-slate-900 focus-visible:outline-none bg-white/20" data-glide-dir=">" aria-label="next slide">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('cmsFacilitiesRenderReadMoreBlock')) {
    function cmsFacilitiesRenderReadMoreBlock(string $html, int $maxHeight = 220): void
    {
        if (trim(strip_tags($html)) === '') {
            return;
        }

        if (function_exists('cmsPedagogyRenderReadMoreBlock')) {
            cmsPedagogyRenderReadMoreBlock($html, $maxHeight);
            return;
        }

        echo '<div class="mt-3 facilities-readmore-block">';
        pedagogyRenderReadMoreHtml($html, $maxHeight);
        echo '</div>';
    }
}
?>

<?php if (is_array($s_intro)):
    $introHeading = cmsFacilitiesPrepareHtml(trim((string) ($s_intro['content_heading'] ?? '')));
    $introBody = cmsFacilitiesPrepareHtml(trim((string) ($s_intro['content'] ?? '')));
    $introMedias = cmsFacilitiesCarouselMedias($s_intro);
?>
<div class="<?= cmsFacilitiesWidthWrapClass() ?> cms-facilities-intro">
    <?php if ($introHeading !== ''): ?>
    <div class="text-left sm:text-center">
        <div class="ql-snow"><div class="ql-editor facilities-section-heading" style="padding:0;"><?= $introHeading ?></div></div>
    </div>
    <?php endif; ?>
    <?php cmsFacilitiesRenderIntroCarousel($introMedias); ?>
    <?php cmsFacilitiesRenderReadMoreBlock($introBody, 220); ?>
</div>
<?php endif; ?>

<?php if (is_array($s_plc_columns)):
    $plcIntroHeading = is_array($s_plc_intro)
        ? cmsFacilitiesPrepareHtml(trim((string) ($s_plc_intro['content_heading'] ?? '')))
        : '';
    $plcIntroBody = is_array($s_plc_intro)
        ? cmsFacilitiesPrepareHtml(trim((string) ($s_plc_intro['content'] ?? '')))
        : '';

    $columns = $s_plc_columns['columns'] ?? [];
    $imageCol = null;
    $textCol = null;
    if (is_array($columns)) {
        foreach ($columns as $column) {
            if (!is_array($column)) {
                continue;
            }
            $type = strtolower((string) ($column['content_type'] ?? ''));
            if ($type === 'image' && $imageCol === null) {
                $imageCol = $column;
            } elseif ($textCol === null) {
                $textCol = $column;
            }
        }
    }

    $plcImageUrl = is_array($imageCol) ? cmsFacilitiesColumnImageUrl($imageCol) : '';
    $plcTextHtml = '';
    if (is_array($textCol)) {
        $plcTextHtml = cmsFacilitiesPrepareHtml(trim((string) ($textCol['content'] ?? '')));
        if ($plcTextHtml === '') {
            $plcTextHtml = cmsFacilitiesPrepareHtml(trim((string) ($textCol['content_heading'] ?? '')));
        }
    }
?>
<div class="mt-8 sm:mt-10 cms-facilities-plc">
    <div class="text-center mx-3 <?= cmsFacilitiesWidthWrapClass() ?>">
        <?php if ($plcIntroHeading !== ''): ?>
        <div class="ql-snow"><div class="ql-editor facilities-section-heading" style="padding:0;"><?= $plcIntroHeading ?></div></div>
        <?php endif; ?>
        <?php if ($plcIntroBody !== ''): ?>
        <div class="mt-3 facilities-plc-intro">
            <?php pedagogyRenderReadMoreHtml($plcIntroBody, 120); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($plcImageUrl !== '' || $plcTextHtml !== ''): ?>
    <div class="mt-5 relative facilities-feature-band ab-cr-bg w-full py-8 sm:py-10">
        <div class="relative <?= cmsFacilitiesWidthWrapClass() ?> facilities-feature-band__inner sm:flex gap-4 sm:gap-8 items-center justify-center">
            <?php if ($plcImageUrl !== ''): ?>
            <div class="facilities-feature-band__media sm:w-1/2 w-full flex items-center justify-center">
                <img src="<?= htmlspecialchars($plcImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Personalised Learning Centre" class="w-full h-auto object-contain" loading="lazy">
            </div>
            <?php endif; ?>
            <?php if ($plcTextHtml !== ''): ?>
            <div class="facilities-feature-band__copy sm:w-1/2 w-full flex items-center">
                <div class="py-2 sm:py-4 facilities-feature-copy w-full">
                    <div class="ql-snow"><div class="ql-editor text-[16px]" style="padding:0;"><?= $plcTextHtml ?></div></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$facilityCards = cmsFacilitiesCollectCards(is_array($s_card_sections) ? $s_card_sections : []);
if (is_array($s_cards_heading) && $facilityCards !== []):
    $cardsHeading = cmsFacilitiesPrepareHtml(trim((string) ($s_cards_heading['content_heading'] ?? '')));
    $cardsIntro = cmsFacilitiesPrepareHtml(trim((string) ($s_cards_heading['content'] ?? '')));
?>
<div class="mt-10 sm:mt-14 cms-facilities-grid-section">
    <div class="text-center <?= cmsFacilitiesWidthWrapClass() ?>">
        <?php if ($cardsHeading !== ''): ?>
        <div class="ql-snow"><div class="ql-editor facilities-section-heading" style="padding:0;"><?= $cardsHeading ?></div></div>
        <?php endif; ?>
        <?php if ($cardsIntro !== ''): ?>
        <div class="mt-3 facilities-grid-intro">
            <div class="ql-snow"><div class="ql-editor" style="padding:0;"><?= $cardsIntro ?></div></div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    $facilityRows = array_chunk($facilityCards, 4);
    foreach ($facilityRows as $rowIndex => $rowCards):
        $isBlueRow = ($rowIndex % 2) === 1;
        $rowClass = $isBlueRow
            ? 'cms-facilities-row cms-facilities-row--blue ab-cr-bg w-full py-8 sm:py-10'
            : 'cms-facilities-row cms-facilities-row--white w-full py-8 sm:py-10';
    ?>
    <div class="<?= $rowClass ?>">
        <div class="<?= cmsFacilitiesWidthWrapClass() ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 cms-facilities-grid">
            <?php foreach ($rowCards as $card):
                if (!is_array($card)) {
                    continue;
                }
                [$cardTitle, $cardBody] = cmsFacilitiesCardParts($card);
                $imgUrl = function_exists('cmsAssetUrl')
                    ? cmsAssetUrl((string) ($card['media_url'] ?? $card['media_file'] ?? ''))
                    : (string) ($card['media_url'] ?? '');
                if ($imgUrl === '' && $cardTitle === '' && trim(strip_tags($cardBody)) === '') {
                    continue;
                }
            ?>
            <article class="cms-facilities-card overflow-hidden flex flex-col h-full">
                <?php if ($imgUrl !== ''): ?>
                <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($cardTitle !== '' ? $cardTitle : 'Facility', ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-[180px] object-cover"
                     loading="lazy">
                <?php endif; ?>
                <div class="p-4 flex flex-col flex-grow">
                    <?php if ($cardTitle !== ''): ?>
                    <h3 class="font-[700] text-[18px] leading-6 mb-2 text-blue-main cms-facilities-card-title"><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php endif; ?>
                    <?php if (trim(strip_tags($cardBody)) !== ''): ?>
                    <div class="cms-facilities-card-body flex-grow">
                        <?php pedagogyRenderReadMoreHtml($cardBody, 72, $isBlueRow); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
