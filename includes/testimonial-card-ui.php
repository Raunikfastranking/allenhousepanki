<?php
declare(strict_types=1);

if (!function_exists('testimonialDescriptionSummary')) {
    /**
     * @return array{short: string, needs_more: bool}
     */
    function testimonialDescriptionSummary(string $html, int $wordLimit = 40): array
    {
        if (function_exists('cmsPlainText')) {
            $plain = cmsPlainText($html);
        } elseif (function_exists('cmsPrepareCmsPlainText')) {
            $plain = cmsPrepareCmsPlainText($html);
        } else {
            $plain = trim(preg_replace(
                '/\s+/u',
                ' ',
                strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ) ?? '');
        }
        if ($plain === '') {
            return ['short' => '', 'needs_more' => false];
        }

        $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($words)) {
            $words = [];
        }

        $needsMore = count($words) > $wordLimit;
        $short = $needsMore
            ? implode(' ', array_slice($words, 0, $wordLimit)) . '...'
            : $plain;

        return ['short' => $short, 'needs_more' => $needsMore];
    }
}

function testimonialCardStyles(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
        #tab-1.Testimonials,
        .tab-content.Testimonials:not(.rooma-written-testimonials) {
            width: 100%;
        }

        #tab-1.Testimonials [data-glide-el="track"],
        .tab-content.Testimonials:not(.rooma-written-testimonials) [data-glide-el="track"] {
            overflow: hidden;
            width: 100%;
        }

        #tab-1.Testimonials .glide__slides,
        .tab-content.Testimonials:not(.rooma-written-testimonials) .glide__slides {
            align-items: stretch;
        }

        #tab-1.Testimonials .glide__slide,
        .tab-content.Testimonials:not(.rooma-written-testimonials) .glide__slide {
            height: auto;
            align-self: stretch;
        }

        .home-testimonial-card {
            box-sizing: border-box;
        }

        .home-testimonial-card .testimonial-text,
        .home-testimonial-card .testimonial-text p,
        .home-testimonial-card .testimonial-text span,
        .home-testimonial-card .testimonial-text li,
        .home-testimonial-card .testimonial-text .full-text,
        .home-testimonial-card .testimonial-text .full-text * {
            color: #e5e7eb !important;
        }

        .home-testimonial-card .testimonial-text a {
            color: #ffffff !important;
            text-decoration: underline;
        }

        .home-testimonial-card .testimonial-text .full-text.hidden {
            display: none !important;
        }

        .home-testimonial-card .short-text,
        .home-testimonial-card .full-text:not(.hidden) {
            display: block;
            max-height: 150px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 10px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.65) rgba(255, 255, 255, 0.15);
        }

        .home-testimonial-card .short-text::-webkit-scrollbar,
        .home-testimonial-card .full-text:not(.hidden)::-webkit-scrollbar {
            width: 6px;
        }

        .home-testimonial-card .short-text::-webkit-scrollbar-track,
        .home-testimonial-card .full-text:not(.hidden)::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .home-testimonial-card .short-text::-webkit-scrollbar-thumb,
        .home-testimonial-card .full-text:not(.hidden)::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 4px;
        }

        .home-testimonial-card .testimonial-text .short-text.hidden {
            display: none !important;
        }

        .home-testimonial-card .moreless-button {
            display: block;
            width: fit-content;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        @media (min-width: 640px) {
            .home-testimonial-card .moreless-button {
                margin-left: 0;
                margin-right: 0;
                text-align: left;
                align-self: flex-start;
            }

        }

        .moreless-button.is-hidden {
            display: none;
        }


        .testimonial-card {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            width: 100%;
            height: auto;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #053b7a;
            box-sizing: border-box;
        }

        .testimonial-card__media {
            flex: 0 0 30%;
            max-width: 30%;
            text-align: center;
        }

        .testimonial-card__content {
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 767px) {
            #tab-1.Testimonials .glide__slide,
            .tab-content.Testimonials:not(.rooma-written-testimonials) .glide__slide {
                width: 100% !important;
            }

        }
    </style>
    <?php
}

function testimonialCardScripts(): void
{
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
        (function () {
            function testimonialCardRoot(button) {
                return button.closest('.home-testimonial-card');
            }

            function collapseOtherTestimonialCards(activeCard) {
                document.querySelectorAll('.home-testimonial-card').forEach(function (other) {
                    if (other === activeCard) {
                        return;
                    }
                    var shortText = other.querySelector('.short-text');
                    var fullText = other.querySelector('.full-text');
                    var btn = other.querySelector('.moreless-button');
                    if (shortText && fullText && !fullText.classList.contains('hidden')) {
                        shortText.classList.remove('hidden');
                        fullText.classList.add('hidden');
                        if (btn) {
                            btn.textContent = 'Read more...';
                        }
                    }
                    var expandedPara = other.querySelector('.testimonial-text.expanded');
                    if (expandedPara) {
                        expandedPara.classList.remove('expanded');
                        expandedPara.classList.add('clamp-2');
                        if (btn) {
                            btn.textContent = 'Read more...';
                        }
                    }
                });
            }

            function bindHomeTestimonialToggles(scope) {
                (scope || document).querySelectorAll('.home-testimonial-card .moreless-button').forEach(function (button) {
                    if (button.dataset.bound === '1') {
                        return;
                    }
                    button.dataset.bound = '1';
                    button.addEventListener('click', function () {
                        var para = button.previousElementSibling;
                        if (!para || !para.classList.contains('testimonial-text')) {
                            var card = testimonialCardRoot(button);
                            if (!card) {
                                return;
                            }
                            var shortText = card.querySelector('.short-text');
                            var fullText = card.querySelector('.full-text');
                            if (!shortText || !fullText) {
                                return;
                            }
                            var showingFull = !fullText.classList.contains('hidden');
                            if (showingFull) {
                                shortText.classList.remove('hidden');
                                fullText.classList.add('hidden');
                                button.textContent = 'Read more...';
                            } else {
                                collapseOtherTestimonialCards(card);
                                shortText.classList.add('hidden');
                                fullText.classList.remove('hidden');
                                button.textContent = 'Read less';
                            }
                            return;
                        }
                        para.classList.toggle('expanded');
                        para.classList.toggle('clamp-2');
                        if (para.classList.contains('expanded')) {
                            collapseOtherTestimonialCards(testimonialCardRoot(button));
                            button.textContent = 'Read less';
                        } else {
                            button.textContent = 'Read more...';
                        }
                    });
                });
            }

            window.initTestimonialCards = function () {
                bindHomeTestimonialToggles();
            };

            window.bootTestimonialCarouselHeights = function () {
                bindHomeTestimonialToggles();
            };

            function scheduleInit() {
                requestAnimationFrame(function () {
                    window.initTestimonialCards();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleInit, { once: true });
            } else {
                scheduleInit();
            }

            window.addEventListener('ah:home-glides-ready', scheduleInit);
        })();
    </script>
    <?php
}

function renderTestimonialCard(array $row, bool $forPageCarousel = false): void
{
    $rawHeading = (string) ($row['heading'] ?? $row['name'] ?? '');
    if (function_exists('cmsPlainText')) {
        $heading = htmlspecialchars(cmsPlainText($rawHeading), ENT_QUOTES, 'UTF-8');
        $plainText = cmsPlainText((string) ($row['description'] ?? ''));
    } elseif (function_exists('cmsPrepareCmsPlainText')) {
        $heading = htmlspecialchars(cmsPrepareCmsPlainText($rawHeading), ENT_QUOTES, 'UTF-8');
        $plainText = cmsPrepareCmsPlainText((string) ($row['description'] ?? ''));
    } else {
        $heading = htmlspecialchars(
            trim(strip_tags(html_entity_decode($rawHeading, ENT_QUOTES | ENT_HTML5, 'UTF-8'))),
            ENT_QUOTES,
            'UTF-8'
        );
        $plainText = trim(preg_replace(
            '/\s+/u',
            ' ',
            strip_tags(html_entity_decode((string) ($row['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        ) ?? '');
    }
    $image = htmlspecialchars((string) ($row['media']['urls'] ?? $row['image'] ?? ''), ENT_QUOTES, 'UTF-8');
    $summary = testimonialDescriptionSummary($plainText, 40);
    $shortText = htmlspecialchars($summary['short'], ENT_QUOTES, 'UTF-8');
    $fullText = htmlspecialchars($plainText, ENT_QUOTES, 'UTF-8');
    $needsMore = $summary['needs_more'];
    $mbClass = $forPageCarousel ? 'mb-0' : 'mb-4';
    $pageClass = $forPageCarousel ? 'ah-testimonials-item ah-page-testimonial-card ' : '';
    $flexClass = $forPageCarousel ? '' : 'sm:flex items-start ';
    $textAlign = $forPageCarousel ? ' text-center' : ' text-center sm:text-left';
    ?>
    <li class="<?= $pageClass ?>home-testimonial-card <?= $flexClass ?>gap-2 <?= $mbClass ?> border-[1px] border-gray-300 p-3 rounded-[8px] bg-blue-main">
        <div class="text-center<?= $forPageCarousel ? '' : ' sm:w-[30%]' ?> w-[90%] mx-auto">
            <?php if ($image !== ''): ?>
                <img src="<?= $image ?>" alt="<?= $heading ?>" class="mb-2 mx-auto rounded-[10px] w-[130px] h-[130px] object-cover" loading="lazy" decoding="async">
            <?php endif; ?>
            <?php if ($heading !== ''): ?>
                <h2 class="font-[700] text-[16px] text-gray-100"><?= $heading ?></h2>
            <?php endif; ?>
        </div>
        <div class="<?= $forPageCarousel ? '' : 'sm:w-[50%] ' ?>w-[90%] mx-auto sm:mt-0 mt-3 sm:text-left text-center">
            <?php if ($needsMore): ?>
                <p class="short-text text-[16px] text-gray-200<?= $textAlign ?>"><?= $shortText ?></p>
                <p class="full-text hidden text-[16px] text-gray-200<?= $textAlign ?>"><?= $fullText ?></p>
                <button type="button" class="moreless-button font-[600] text-white text-sm mt-1">Read more...</button>
            <?php else: ?>
                <p class="text-[16px] text-gray-200<?= $textAlign ?>"><?= $fullText ?></p>
            <?php endif; ?>
        </div>
    </li>
    <?php
}
