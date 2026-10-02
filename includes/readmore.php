<?php
if (!function_exists('renderReadMore')) {
    function renderReadMore($content = '', $maxHeight = 150, $wrapperClass = '')
    {
        static $stylesPrinted = false;
        $uniqueId = uniqid('readmore_');
        $wrapperClass = trim((string) $wrapperClass);
        $wrapperAttr = $wrapperClass !== '' ? ' ' . htmlspecialchars($wrapperClass, ENT_QUOTES, 'UTF-8') : '';
?>
        <?php if (!$stylesPrinted): $stylesPrinted = true; ?>
        <style>
            .read-more-wrapper {
                position: relative;
                margin-bottom: 1rem;
            }

            .read-more-content {
                overflow: hidden;
                position: relative;
                max-height: <?= (int)$maxHeight ?>px;
                transition: max-height 0.3s ease;
            }

            .read-more-btn {
                color: #053B7A;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                display: inline-block;
                margin-top: 10px;
            }

            .read-more-content.expanded {
                max-height: none;
            }

            .read-more-overlay {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 60px;
                background: linear-gradient(to top, #fff, transparent);
                display: none;
            }

            .read-more-content:not(.expanded) .read-more-overlay {
                display: block;
            }

            .read-more-wrapper--light .read-more-btn {
                color: #fff !important;
            }

            .read-more-wrapper--light .read-more-overlay {
                background: linear-gradient(to top, rgba(0, 42, 91, 0.95), transparent);
            }
        </style>
        <?php endif; ?>

        <div class="read-more-wrapper<?= $wrapperAttr ?>">
            <div class="read-more-content" id="<?= $uniqueId ?>_content">
                <?= $content ?>
                <div class="read-more-overlay"></div>
            </div>
            <span class="read-more-btn" id="<?= $uniqueId ?>_btn">Read More</span>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const content = document.getElementById("<?= $uniqueId ?>_content");
                const btn = document.getElementById("<?= $uniqueId ?>_btn");
                if (!content || !btn) {
                    return;
                }

                if (content.scrollHeight <= <?= (int)$maxHeight ?>) {
                    btn.style.display = "none";
                    content.classList.add("expanded");
                    const overlay = content.querySelector(".read-more-overlay");
                    if (overlay) {
                        overlay.style.display = "none";
                    }
                }

                btn.addEventListener("click", function() {
                    content.classList.toggle("expanded");
                    btn.textContent = content.classList.contains("expanded") ? "Read Less" : "Read More";
                });
            });
        </script>
<?php
    }
}
?>