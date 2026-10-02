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
                <form id="AdmissionForm" method="post" class="mt-8 space-y-4">
                    <div>
                        <select name="session" required id="asession"
                            class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                            <?php
                            $sessionFile = $includesDir . '/session-api.php';
                            if (is_file($sessionFile)) {
                                $sessions = include $sessionFile;
                                if (is_array($sessions) && $sessions !== []) {
                                    echo '<option value="" disabled selected>Enquiry For Session</option>';
                                    foreach ($sessions as $item) {
                                        if (!is_array($item)) {
                                            continue;
                                        }
                                        $sess = htmlspecialchars((string) ($item['session'] ?? ''), ENT_QUOTES, 'UTF-8');
                                        if ($sess === '') {
                                            continue;
                                        }
                                        echo "<option value=\"{$sess}\">{$sess}</option>";
                                    }
                                } else {
                                    echo '<option value="" disabled selected>Sessions unavailable</option>';
                                }
                            } else {
                                echo '<option value="" disabled selected>Sessions unavailable</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php if ($includeBoard): ?>
                    <div>
                        <select name="board" id="aboard" required
                            class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                            <?php
                            $boardFile = $includesDir . '/board-api.php';
                            if (is_file($boardFile)) {
                                include $boardFile;
                            } else {
                                echo '<option value="" disabled selected>Boards unavailable</option>';
                            }
                            ?>
                        </select>
                        <span id="aboard-error" class="text-red-500 text-sm mt-1 hidden">Please select a board.</span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <select name="grade" id="agrade" required
                            class="w-full border border-gray-300 p-[11px] rounded-md text-[#808080cc]">
                            <?php
                            $gradeFile = $includesDir . '/grade-api.php';
                            if (is_file($gradeFile)) {
                                include $gradeFile;
                            } else {
                                echo '<option value="" disabled selected>Grades unavailable</option>';
                            }
                            ?>
                        </select>
                        <span id="aclassError" class="text-red-500 text-sm hidden">Please select a class.</span>
                    </div>
                    <div>
                        <input type="text" name="student-name" id="astudent_name" placeholder="Student Name"
                            class="w-full border border-gray-300 p-2 rounded-md" required>
                        <span id="astudent-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces allowed.</span>
                    </div>
                    <div>
                        <input type="text" name="parent-name" id="aparent_name" placeholder="Parents Name"
                            class="w-full border border-gray-300 p-2 rounded-md">
                        <span id="aparent-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces allowed.</span>
                    </div>
                    <div>
                        <input type="text" name="mobile" id="amobile" placeholder="Mobile No."
                            class="w-full border border-gray-300 p-2 rounded-md" required maxlength="10">
                        <span id="amobile-error" class="text-red-500 text-sm mt-1 hidden">Please enter valid phone number</span>
                    </div>
                    <div>
                        <input type="email" name="email" id="aemail" placeholder="E-mail"
                            class="w-full border border-gray-300 p-2 rounded-md">
                        <span id="aemail-error" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address.</span>
                    </div>
                    <div id="admissionCityWrapper" class="relative customSelect overflow-visible" data-cms-city-dropdown="1">
                        <select id="acity" name="city" class="hidden" required>
                            <option value="">Select City</option>
                            <?php
                            $cityFile = $includesDir . '/get-city.php';
                            $cityNames = [];
                            if (is_file($cityFile)) {
                                $cmsGetCityMode = 'array';
                                $cityNames = include $cityFile;
                                if (!is_array($cityNames)) {
                                    $cityNames = [];
                                }
                            }
                            foreach ($cityNames as $cityName) {
                                $safe = htmlspecialchars((string) $cityName, ENT_QUOTES, 'UTF-8');
                                echo "<option value=\"{$safe}\">{$safe}</option>";
                            }
                            if ($cityNames === []) {
                                echo "<option value=''>Cities unavailable</option>";
                            }
                            ?>
                        </select>
                        <div id="admissionCityTrigger" class="border border-gray-300 p-[11px] rounded-md bg-white cursor-pointer flex justify-between items-center">
                            <span class="selected-text text-[#808080cc]">Select City</span>
                            <span>&#9660;</span>
                        </div>
                        <div id="admissionCityDropdown" class="absolute mt-1 border border-gray-300 rounded-md bg-white shadow-md hidden z-50 w-full">
                            <input type="text" id="admissionCitySearch" placeholder="Search..."
                                class="w-full p-2 border-b border-gray-300 outline-none">
                            <ul id="admissionCityList" class="max-h-48 overflow-y-auto"></ul>
                        </div>
                    </div>
                    <span id="acity-error" class="text-red-500 text-sm hidden">Please select a city.</span>
                    <div>
                        <input type="text" name="pincode" id="apincode" placeholder="Enter your Pincode"
                            class="w-full border border-gray-300 p-2 rounded-md" maxlength="6"
                            oninput="this.value=this.value.replace(/\D/g,'')" required>
                        <span id="apincode-error" class="text-red-500 text-sm hidden">Please enter a valid Pincode.</span>
                    </div>
                    <input type="hidden" id="source" name="source" value="">
                    <div class="flex items-start gap-2">
                        <input type="checkbox" id="admissionTerms" required />
                        <label for="admissionTerms" class="text-sm">I agree to <a href="termsandconditions"
                                class="text-blue-600 underline">Terms and Conditions</a>.</label>
                        <span id="admissionTermsError" class="text-red-500 text-sm hidden">You must agree to the terms and conditions.</span>
                    </div>
                    <button type="submit" id="AsubmitBtn"
                        class="w-full py-3 bg-blue-main text-white rounded-md hover:bg-red-500 transition font-semibold">
                        Submit
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}
