<?php

declare(strict_types=1);

if (!function_exists('cmsEnquiryFormIncludeBoard')) {
    function cmsEnquiryFormIncludeBoard(): bool
    {
        if (function_exists('cmsCoreGet')) {
            return (bool) cmsCoreGet('enquiry_form_include_board', false);
        }

        return false;
    }
}

if (!function_exists('cmsAdmissionProcedureSessionLabel')) {
    function cmsAdmissionProcedureSessionLabel(string $includesDir): string
    {
        $sessionFile = rtrim($includesDir, '/\\') . '/session-api.php';
        if (!is_file($sessionFile)) {
            return 'Enquiry Form';
        }

        $sessions = include $sessionFile;
        if (!is_array($sessions) || $sessions === []) {
            return 'Enquiry Form';
        }

        $first = trim((string) ($sessions[0]['session'] ?? ''));
        if ($first === '') {
            return 'Enquiry Form';
        }

        return 'Enquiry Form | Session ' . $first;
    }
}

if (!function_exists('cmsRenderAdmissionProcedureEnquiryForm')) {
    /** Render inline admission enquiry form (session, optional board, grade, contact fields). */
    function cmsRenderAdmissionProcedureEnquiryForm(string $includesDir): void
    {
        $includesDir = rtrim($includesDir, '/\\');
        $includeBoard = cmsEnquiryFormIncludeBoard();
        $wrapClass = function_exists('cmsPageContentWrapClass')
            ? cmsPageContentWrapClass('sm:px-5 px-4')
            : 'container mx-auto px-4';
        $heading = cmsAdmissionProcedureSessionLabel($includesDir);
        ?>
        <div class="<?= htmlspecialchars($wrapClass, ENT_QUOTES, 'UTF-8') ?>">
            <div id="admissionProcedureEnquiry" class="cms-admission-procedure-enquiry sm:mt-16 mt-10 mb-10 w-full">
                <h2 class="text-center sm:text-[32px] text-[28px] font-[700] text-blue-main leading-9">
                    <?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <div id="AdmissionFormPopup"
                    class="relative mt-5 bg-green-500 text-white px-4 py-2 rounded mb-5 hidden"
                    style="z-index:999">
                    Form submitted successfully!
                </div>
                <div id="ee-form-6-inline" data-ee-form-id="form-6" class="mt-8"></div>
            </div>
        </div>
        <?php
    }
}
