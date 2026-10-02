<?php

declare(strict_types=1);

/**
 * Static Teacher Portal app store section (footer right column, after Allen Care App).
 */

if (!function_exists('cmsStaticFooterTeacherPortalAppLinks')) {
    /** @return array{android: string, ios: string} */
    function cmsStaticFooterTeacherPortalAppLinks(): array
    {
        return [
            'android' => 'https://play.google.com/store/apps/details?id=com.edunext.teacherApp&hl=en_IN',
            'ios' => 'https://apps.apple.com/in/app/edunext-teacher/id6443792390',
        ];
    }
}

if (!function_exists('cmsRenderFooterTeacherPortalSection')) {
    /**
     * @param string $androidBadge Resolved badge image URL (reuse Allen Care footer badges).
     * @param string $iosBadge Resolved badge image URL.
     * @param string $headingColor Inline heading color (e.g. #B4D7FF).
     * @param string $bp Tailwind breakpoint prefix for heading size/margin: sm or md.
     */
    function cmsRenderFooterTeacherPortalSection(
        string $androidBadge,
        string $iosBadge,
        string $headingColor = '#B4D7FF',
        string $bp = 'sm'
    ): void {
        $androidBadge = trim($androidBadge);
        $iosBadge = trim($iosBadge);
        if ($androidBadge === '' && $iosBadge === '') {
            return;
        }

        $bp = $bp === 'md' ? 'md' : 'sm';
        $links = cmsStaticFooterTeacherPortalAppLinks();
        $androidLink = $links['android'];
        $iosLink = $links['ios'];
        ?>
        <!-- Teacher Portal app badges (static) -->
        <div>
            <h2 class="<?= $bp ?>:text-[22px] text-[18px] font-[600] mt-3 <?= $bp ?>:mt-5" style="color:<?= htmlspecialchars($headingColor, ENT_QUOTES, 'UTF-8') ?>">Teacher Portal</h2>
            <div class="flex flex-wrap items-center mt-3 gap-1">
                <?php if ($androidBadge !== ''): ?>
                <a href="<?= htmlspecialchars($androidLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><img class="w-[140px]" src="<?= htmlspecialchars($androidBadge, ENT_QUOTES, 'UTF-8') ?>" alt="Download Teacher Portal on Google Play"></a>
                <?php endif; ?>
                <?php if ($iosBadge !== ''): ?>
                <a href="<?= htmlspecialchars($iosLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><img class="w-[140px]" src="<?= htmlspecialchars($iosBadge, ENT_QUOTES, 'UTF-8') ?>" alt="Download Teacher Portal on the App Store"></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
