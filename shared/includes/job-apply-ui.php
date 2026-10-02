<?php

declare(strict_types=1);

require_once __DIR__ . '/careers-jobs-ui.php';

if (!function_exists('cmsJobApplyResolveRequestId')) {
    function cmsJobApplyResolveRequestId(): ?string
    {
        foreach (['job_id', 'id'] as $key) {
            $value = trim((string) ($_GET[$key] ?? $_POST['job_id'] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}

if (!function_exists('cmsJobApplyPageUrl')) {
    function cmsJobApplyPageUrl(int|string $jobId): string
    {
        $base = function_exists('cmsBreadcrumbHref') ? cmsBreadcrumbHref('job-apply') : 'job-apply';

        return $base . '?job_id=' . rawurlencode((string) $jobId);
    }
}

if (!function_exists('cmsJobPdfUrl')) {
    function cmsJobPdfUrl(?array $job): string
    {
        if (!is_array($job)) {
            return '';
        }

        $jd = $job['jd'] ?? '';
        if (is_string($jd) && trim($jd) !== '') {
            return trim($jd);
        }
        if (is_array($jd)) {
            if (isset($jd[0]) && is_array($jd[0]) && !empty($jd[0]['jd'])) {
                return trim((string) $jd[0]['jd']);
            }
            if (!empty($jd['jd'])) {
                return trim((string) $jd['jd']);
            }
        }

        return trim((string) ($job['job_description_url'] ?? ''));
    }
}

if (!function_exists('cmsJobField')) {
    function cmsJobField(?array $job, string $key, string $fallback = ''): string
    {
        if (!is_array($job)) {
            return $fallback;
        }

        $value = trim((string) ($job[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }

        $aliases = [
            'min_exp' => ['from_exp'],
            'max_exp' => ['to_exp'],
            'job_title' => ['title'],
            'job_address' => ['branch.address', 'address'],
        ];
        foreach ($aliases[$key] ?? [] as $alias) {
            if (str_contains($alias, '.')) {
                [$parent, $child] = explode('.', $alias, 2);
                if (is_array($job[$parent] ?? null)) {
                    $nested = trim((string) ($job[$parent][$child] ?? ''));
                    if ($nested !== '') {
                        return $nested;
                    }
                }
                continue;
            }
            $alt = trim((string) ($job[$alias] ?? ''));
            if ($alt !== '') {
                return $alt;
            }
        }

        return $fallback;
    }
}

if (!function_exists('cmsValidateJobApplicationPdf')) {
    function cmsValidateJobApplicationPdf(array $file, bool $required = true): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? 'File is required.' : null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return 'Unable to upload file. Please try again.';
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return 'File must be ≤ 2MB.';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, (string) ($file['tmp_name'] ?? '')) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($mime !== 'application/pdf') {
            return 'Only PDF files are allowed.';
        }

        return null;
    }
}

if (!function_exists('cmsPostJobApplication')) {
    /**
     * @param array<string, mixed> $fields
     * @return array{ok: bool, http: int, error: string, body: string}
     */
    function cmsPostJobApplication(array $fields): array
    {
        if (!defined('CMS_API_URL')) {
            return ['ok' => false, 'http' => 0, 'error' => 'CMS API is not configured.', 'body' => ''];
        }

        $ch = curl_init(rtrim(CMS_API_URL, '/') . '/job-applications');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => api_auth_headers(),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ]);
        if (function_exists('apply_curl_ssl_options')) {
            apply_curl_ssl_options($ch);
        }

        $body = curl_exec($ch);
        $curlError = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError !== '') {
            return ['ok' => false, 'http' => $http, 'error' => $curlError, 'body' => (string) $body];
        }

        return [
            'ok' => $http >= 200 && $http < 300,
            'http' => $http,
            'error' => '',
            'body' => is_string($body) ? $body : '',
        ];
    }
}

if (!function_exists('cmsHandleJobApplicationPost')) {
    /**
     * @return array{type: string, message: string}|null
     */
    function cmsHandleJobApplicationPost(?string $jobId): ?array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return null;
        }

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['mobile_no'] ?? ''));
        $jobId = trim((string) ($_POST['job_id'] ?? $jobId ?? ''));

        $errors = [];
        if ($fullName === '' || !preg_match('/^[A-Za-z\s]+$/', $fullName)) {
            $errors[] = 'Please enter a valid full name (letters and spaces only).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($phone === '' || !preg_match('/^[6-9]\d{9}$/', $phone)) {
            $errors[] = 'Please enter a valid 10-digit mobile number.';
        }
        if ($jobId === '') {
            $errors[] = 'Job reference is missing.';
        }

        $resumeError = cmsValidateJobApplicationPdf($_FILES['cv'] ?? [], true);
        if ($resumeError !== null) {
            $errors[] = 'Resume: ' . $resumeError;
        }

        $coverError = cmsValidateJobApplicationPdf($_FILES['cover_letter_file'] ?? [], false);
        if ($coverError !== null) {
            $errors[] = 'Cover letter: ' . $coverError;
        }

        if ($errors !== []) {
            return ['type' => 'error', 'message' => implode(' ', $errors)];
        }

        $postData = [
            'full_name' => $fullName,
            'email' => $email,
            'mobile_no' => $phone,
            'job_id' => $jobId,
            'cv' => new CURLFile(
                (string) $_FILES['cv']['tmp_name'],
                'application/pdf',
                (string) $_FILES['cv']['name']
            ),
        ];

        if (isset($_FILES['cover_letter_file']) && ($_FILES['cover_letter_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $postData['cover_letter_file'] = new CURLFile(
                (string) $_FILES['cover_letter_file']['tmp_name'],
                'application/pdf',
                (string) $_FILES['cover_letter_file']['name']
            );
        }

        $result = cmsPostJobApplication($postData);
        if (!$result['ok']) {
            $apiMessage = '';
            $decoded = json_decode($result['body'], true);
            if (is_array($decoded)) {
                $apiMessage = trim((string) ($decoded['message'] ?? $decoded['error'] ?? ''));
            }
            $message = $apiMessage !== ''
                ? $apiMessage
                : 'Unable to submit your application. Please try again later.';
            if ($result['error'] !== '') {
                $message .= ' (' . $result['error'] . ')';
            }

            return ['type' => 'error', 'message' => $message];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['job_apply_success'] = true;

        $redirect = strtok((string) ($_SERVER['REQUEST_URI'] ?? 'job-apply'), '?') ?: 'job-apply';
        $redirect .= '?job_id=' . rawurlencode($jobId);
        header('Location: ' . $redirect);
        exit;
    }
}

if (!function_exists('cmsJobApplyFlashMessage')) {
    function cmsJobApplyFlashMessage(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['job_apply_success'])) {
            return '';
        }
        unset($_SESSION['job_apply_success']);

        return '<div id="successMessage" class="mt-3 block max-w-md rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">'
            . '<p class="m-0 leading-5">Job application submitted successfully.</p></div>'
            . '<script>setTimeout(function(){var p=document.getElementById("successMessage");if(p){p.style.display="none";}},8000);</script>';
    }
}

if (!function_exists('cmsRenderJobApplyPage')) {
    /** @param array<string, mixed>|null $job */
    function cmsRenderJobApplyPage(?array $job, ?string $jobId, ?array $formError = null): void
    {
        $title = cmsJobField($job, 'job_title', 'Job Application');
        $status = cmsJobField($job, 'status', 'active');
        $jobType = cmsJobField($job, 'job_type', 'Full Time');
        $minExp = cmsJobField($job, 'min_exp');
        $maxExp = cmsJobField($job, 'max_exp');
        $openings = cmsJobField($job, 'openings');
        $minSalary = cmsJobField($job, 'min_salary');
        $maxSalary = cmsJobField($job, 'max_salary');
        $address = cmsJobField($job, 'job_address');
        $qualification = cmsJobField($job, 'req_qualification');
        $pdfUrl = cmsJobPdfUrl($job);
        $flash = cmsJobApplyFlashMessage();
        $errorHtml = '';
        if (is_array($formError) && ($formError['message'] ?? '') !== '') {
            $errorHtml = '<div class="bg-red-100 border border-red-300 text-red-800 p-3 rounded-md">' . htmlspecialchars((string) $formError['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        }
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/cms-page-seo.php';
    $seoPage = is_array($GLOBALS['cms_careers_seo_page'] ?? null) ? $GLOBALS['cms_careers_seo_page'] : ['data' => []];
    $jobTitle = trim($title);
    if ($jobTitle !== '' && $jobTitle !== 'Job Application') {
        $seoPage = ['data' => array_merge(is_array($seoPage['data'] ?? null) ? $seoPage['data'] : [], [
            'title' => $jobTitle . ' | Job Application',
        ])];
    }
    cmsResolveAndRenderPageSeo($seoPage, [
        'includes_dir' => __DIR__ . '/../../includes',
    ]);
    ?>
    <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <div class="main relative mb-[120px]">
        <div class="text-gray-800">
            <div class="max-w-5xl mx-auto p-6 space-y-8 bg-white shadow-xl mt-10 rounded-xl">
                <?php if ($job === null): ?>
                    <h1 class="text-2xl font-bold text-blue-main">Job not found</h1>
                    <p class="text-gray-600">This opening may have been filled or removed.</p>
                    <a href="<?= htmlspecialchars(function_exists('cmsBreadcrumbHref') ? cmsBreadcrumbHref('careers') : 'careers', ENT_QUOTES, 'UTF-8') ?>" class="inline-block mt-4 text-blue-main font-semibold hover:underline">Back to careers</a>
                <?php else: ?>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="text-[#12B76A] text-sm flex items-center border border-[#12B76A] bg-[#ECFDF3] rounded-full p-1 px-2">
                            <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                    <p class="text-sm"><?= htmlspecialchars($jobType, ENT_QUOTES, 'UTF-8') ?> | Expected Experience: <?= htmlspecialchars($minExp, ENT_QUOTES, 'UTF-8') ?>- <?= htmlspecialchars($maxExp, ENT_QUOTES, 'UTF-8') ?> years</p>
                    <?php if ($openings !== ''): ?>
                    <p class="text-sm">Openings: <?= htmlspecialchars($openings, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if ($minSalary !== '' || $maxSalary !== ''): ?>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars(trim($minSalary . ' - ' . $maxSalary, ' -'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="border border-blue-300 p-6 rounded-xl space-y-4">
                    <h2 class="text-xl font-semibold">Job Description</h2>
                    <div class="grid sm:grid-cols-2 gap-4 text-sm">
                        <div><strong>Title:</strong> <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
                        <div><strong>Job Type:</strong> <?= htmlspecialchars($jobType, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($qualification !== ''): ?>
                        <div><strong>Education:</strong> <?= htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php if ($pdfUrl !== ''): ?>
                        <div>
                            <a href="<?= htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="font-[600] flex gap-1 items-center text-blue-main">
                                View Job Description (PDF)
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?= $errorHtml ?>

                <form id="applicationForm" method="post" action="" class="space-y-6" enctype="multipart/form-data" novalidate>
                    <h2 class="text-xl font-semibold">Application Form</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-medium" for="full_name">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" required class="w-full border border-gray-300 p-2 rounded-md" />
                            <p id="nameError" class="text-sm text-red-500 hidden mt-1">Only letters and spaces allowed.</p>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="email">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required class="w-full border border-gray-300 p-2 rounded-md" />
                            <p id="emailError" class="text-sm text-red-500 hidden mt-1">Enter a valid email.</p>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium" for="phone">Mobile No <span class="text-red-500">*</span></label>
                            <input type="text" id="phone" name="mobile_no" required maxlength="10" class="w-full border border-gray-300 p-2 rounded-md" />
                            <p id="phoneError" class="text-sm text-red-500 hidden mt-1">Enter a valid 10-digit number.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium" for="resume">Upload Resume <span class="text-red-500">*</span></label>
                        <input type="file" required name="cv" id="resume" accept="application/pdf" class="w-full p-2 border border-dashed border-gray-400 rounded-md" />
                        <p id="resumeError" class="text-sm text-red-500 hidden mt-1">Only PDF files ≤ 2MB are allowed.</p>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium" for="cover_latter">Upload Cover Letter (optional)</label>
                        <input type="file" name="cover_letter_file" id="cover_latter" accept="application/pdf" class="w-full p-2 border border-dashed border-gray-400 rounded-md" />
                        <p id="coverError" class="text-sm text-red-500 hidden mt-1">Only PDF files ≤ 2MB are allowed.</p>
                    </div>

                    <input type="hidden" name="job_id" value="<?= htmlspecialchars((string) ($jobId ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit" class="bg-blue-main text-white px-6 py-2 rounded-md hover:bg-red-500">Submit</button>
                    <?= $flash ?>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($job !== null): ?>
    <script>
    (function () {
        var form = document.getElementById('applicationForm');
        if (!form) return;

        var fullName = document.getElementById('full_name');
        var email = document.getElementById('email');
        var phone = document.getElementById('phone');
        var resume = document.getElementById('resume');
        var cover = document.getElementById('cover_latter');
        var nameRegex = /^[A-Za-z\s]+$/;
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var phoneRegex = /^[6-9]\d{9}$/;
        var allowedType = 'application/pdf';

        function validatePdf(input, errorId, required) {
            var file = input.files[0];
            var errorEl = document.getElementById(errorId);
            if (!file) {
                errorEl.classList.toggle('hidden', !required);
                return !required;
            }
            var valid = file.type === allowedType && file.size <= 2 * 1024 * 1024;
            errorEl.classList.toggle('hidden', valid);
            return valid;
        }

        fullName.addEventListener('input', function () {
            document.getElementById('nameError').classList.toggle('hidden', nameRegex.test(fullName.value.trim()));
        });
        email.addEventListener('input', function () {
            document.getElementById('emailError').classList.toggle('hidden', emailRegex.test(email.value.trim()));
        });
        phone.addEventListener('input', function () {
            document.getElementById('phoneError').classList.toggle('hidden', phoneRegex.test(phone.value.trim()));
        });
        resume.addEventListener('change', function () { validatePdf(resume, 'resumeError', true); });
        cover.addEventListener('change', function () { validatePdf(cover, 'coverError', false); });

        form.addEventListener('submit', function (e) {
            var isValid = true;
            if (!nameRegex.test(fullName.value.trim())) {
                document.getElementById('nameError').classList.remove('hidden');
                isValid = false;
            }
            if (!emailRegex.test(email.value.trim())) {
                document.getElementById('emailError').classList.remove('hidden');
                isValid = false;
            }
            if (!phoneRegex.test(phone.value.trim())) {
                document.getElementById('phoneError').classList.remove('hidden');
                isValid = false;
            }
            if (!validatePdf(resume, 'resumeError', true)) isValid = false;
            if (cover.files[0] && !validatePdf(cover, 'coverError', false)) isValid = false;
            if (!isValid) e.preventDefault();
        });
    })();
    </script>
    <?php endif; ?>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <?php include __DIR__ . '/../../includes/foot.php'; ?>
</body>
</html>
        <?php
    }
}

if (!function_exists('cmsRunJobApplyPage')) {
  /**
   * @param array<string, mixed>|null $jobsResponse
   */
    function cmsRunJobApplyPage(?array $jobsResponse): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $jobId = cmsJobApplyResolveRequestId();
        if ($jobsResponse === null || cmsJobsListFromResponse($jobsResponse) === []) {
            $fresh = cmsFetchJobsForBranch();
            if ($fresh !== []) {
                $jobsResponse = ['data' => $fresh];
            }
        }
        $job = cmsFindJobInResponse($jobsResponse, $jobId);
        $formError = cmsHandleJobApplicationPost($jobId);
        cmsRenderJobApplyPage($job, $jobId, $formError);
    }
}
