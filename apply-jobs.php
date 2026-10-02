<?php
session_start();
require_once __DIR__ . '/includes/environment.php';
require_once __DIR__ . '/proxy/config.php';
$GLOBALS['cms_api_load_flags'] = ['careers' => true];
$GLOBALS['cms_page_slug'] = 'careers-page-panki';
require_once __DIR__ . '/includes/apis.php';
require_once __DIR__ . '/includes/api-adapters.php';
require_once __DIR__ . '/includes/cms-bootstrap.php';

$GLOBALS['cms_skip_auto_sections'] = true;
require_once __DIR__ . '/includes/cms-page-helpers.php';
$cms_apply_jobs_page = cmsFetchPankiPage($GLOBALS['cms_page_slug']);
if (($cms_apply_jobs_page['data'] ?? []) === []) {
    $cms_apply_jobs_page = [
        'data' => [
            'page_heading' => 'Apply for a Job',
            'title' => 'Apply for a Job',
        ],
    ];
}

$jobid = $_GET['id'] ?? null;
$j_data = cmsFindJobInResponse($jobs_data, $jobid);

$fullName = '';
$email = '';
$phone = '';
$applied_for = cmsJobDisplayTitle($j_data);
if ($applied_for === '') {
    $applied_for = trim((string) ($_GET['applied_for'] ?? $_GET['title'] ?? ''));
}

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName     = trim($_POST['full_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $jobId        = $_POST['jobid'] ?? $jobid;
    $applied_for  = trim($_POST['applied_for'] ?? '');

    $errors = [];

    if (empty($fullName) || !preg_match('/^[A-Za-z\s]+$/', $fullName)) {
        $errors[] = "Please enter a valid full name (letters and spaces only).";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = "Please enter a valid 10-digit mobile number starting with 6-9.";
    }
    if (empty($applied_for)) {
        $errors[] = "Applied For field is required.";
    }

    // Resume (required)
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Resume file is required.";
    } else {
        $resume = $_FILES['resume'];
        if ($resume['size'] > 2 * 1024 * 1024) $errors[] = "Resume must be ≤ 2MB.";
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $resume['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') $errors[] = "Resume must be a PDF file only.";
    }

    // Cover letter (optional)
    if (isset($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
        $cover = $_FILES['cover_letter'];
        if ($cover['size'] > 2 * 1024 * 1024) $errors[] = "Cover letter must be ≤ 2MB.";
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $cover['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') $errors[] = "Cover letter must be a PDF file only.";
    }

    if (!empty($errors)) {
        $successMessage = '<div class="inline-block ml-6 px-6 py-3 bg-red-100 border border-red-400 text-red-800 font-medium rounded-md shadow-md">' . implode('<br>', $errors) . '</div>';
    } else {
        $resumeFile = new CURLFile($_FILES['resume']['tmp_name'], 'application/pdf', $_FILES['resume']['name']);

        $postData = [
            'name'        => $fullName,
            'email'       => $email,
            'mobile'      => $phone,
            'jobid'       => $jobId,
            'applied_for' => $applied_for,
            'resume'      => $resumeFile
        ];

        if (isset($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
            $coverFile = new CURLFile($_FILES['cover_letter']['tmp_name'], 'application/pdf', $_FILES['cover_letter']['name']);
            $postData['cover_letter'] = $coverFile;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => rtrim(CMS_API_URL, '/') . '/save-external-job-application/' . SCHOOL_ID . '/' . BRANCH_ID,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => api_auth_headers(),
        ]);
        apply_curl_ssl_options($ch);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            $successMessage = '<div class="inline-block ml-6 px-6 py-3 bg-red-100 border border-red-400 text-red-800 font-medium rounded-md shadow-md">cURL Error: ' . htmlspecialchars($curlError) . '</div>';
        } elseif ($httpCode !== 200 && $httpCode !== 201) {
            $apiResp = json_decode($response, true);
            $detail = $apiResp['message'] ?? 'Unknown error';
            $successMessage = '<div class="inline-block ml-6 px-6 py-3 bg-red-100 border border-red-400 text-red-800 font-medium rounded-md shadow-md">
                Submission failed (HTTP ' . $httpCode . ').<br>Server message: ' . htmlspecialchars($detail) . '
            </div>';
        } else {
            // Success: Show success message directly without redirect
            $successMessage = '
            <div id="successMessage" class="inline-flex items-center ml-6 px-6 py-3 bg-green-100 border border-green-400 text-green-800 font-medium rounded-md shadow-lg gap-3 opacity-100 transition-opacity duration-500">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Job application submitted successfully!
            </div>

            <script>
                setTimeout(function() {
                    var msg = document.getElementById("successMessage");
                    if (msg) {
                        msg.style.opacity = "0";
                        setTimeout(() => msg.remove(), 600);
                    }
                }, 8000);
            </script>';

            // Reset fields
            $fullName = $email = $phone = $applied_for = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/shared/includes/cms-page-seo.php';
    cmsResolveAndRenderPageSeo($cms_apply_jobs_page, [
        'includes_dir' => __DIR__ . '/includes',
    ]);
    ?>
    <?php include "includes/head.php"; ?>

    <style>
        #submitOverlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        #submitOverlay .loader-box {
            background: white;
            padding: 40px 60px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 90%;
        }
        #submitOverlay .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #dc2626;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #submitOverlay p {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        #submitOverlay small {
            display: block;
            margin-top: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <div id="submitOverlay">
        <div class="loader-box">
            <div class="spinner"></div>
            <p>Uploading your application...</p>
            <small>Please wait, this may take a few seconds depending on file size.</small>
        </div>
    </div>

    <div class="main relative mb-0">
        <?php cmsRenderInnerPageHeader($cms_apply_jobs_page, 'Apply for a Job', []); ?>
        <div class="<?= htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="text-gray-800">
            <div class="max-w-5xl mx-auto p-6 space-y-8 bg-white shadow-xl mt-10 rounded-xl">
                <form id="applicationForm" method="post" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="jobid" value="<?php echo htmlspecialchars($jobid ?? ''); ?>">

                    <h2 class="text-xl font-semibold">Application Form</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-medium">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required maxlength="150" class="w-full border border-gray-300 p-3 rounded-md" />
                            <p id="nameError" class="text-sm text-red-500 hidden mt-1">Only letters and spaces allowed.</p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full border border-gray-300 p-3 rounded-md" />
                            <p id="emailError" class="text-sm text-red-500 hidden mt-1">Enter a valid email.</p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Mobile No <span class="text-red-500">*</span></label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required maxlength="10" class="w-full border border-gray-300 p-3 rounded-md" />
                            <p id="phoneError" class="text-sm text-red-500 hidden mt-1">Enter a valid 10-digit Indian mobile number.</p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Applied For <span class="text-red-500">*</span></label>
                            <input type="text" name="applied_for" value="<?php echo htmlspecialchars($applied_for); ?>" required maxlength="150" class="w-full border border-gray-300 p-3 rounded-md" />
                            <p id="appliedErr" class="text-sm text-red-500 hidden mt-1">This field is required.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Upload Resume (PDF only) <span class="text-red-500">*</span></label>
                        <input type="file" required name="resume" id="resume" accept="application/pdf" class="w-full p-3 border border-dashed border-gray-400 rounded-md bg-gray-50" />
                        <p id="resumeError" class="text-sm text-red-500 hidden mt-1">Only PDF files ≤ 2MB are allowed.</p>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Upload Cover Letter (PDF only, optional)</label>
                        <input type="file" name="cover_letter" id="cover_letter" accept="application/pdf" class="w-full p-3 border border-dashed border-gray-400 rounded-md bg-gray-50" />
                        <p id="coverError" class="text-sm text-red-500 hidden mt-1">Only PDF files ≤ 2MB are allowed.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 mt-8">
                        <button type="submit" id="submitBtn" class="bg-blue-main text-white px-8 py-3 rounded-md hover:bg-red-500 transition font-medium text-lg flex items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span id="btnText">Submit Application</span>
                            <svg id="btnSpinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>

                        <?php echo $successMessage; ?>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

    <script>
        const form = document.getElementById("applicationForm");
        const submitBtn = document.getElementById("submitBtn");
        const btnText = document.getElementById("btnText");
        const btnSpinner = document.getElementById("btnSpinner");
        const overlay = document.getElementById("submitOverlay");

        const fullName = document.getElementById("full_name");
        const email = document.getElementById("email");
        const phone = document.getElementById("phone");
        const applied_for = document.querySelector("input[name='applied_for']");
        const resume = document.getElementById("resume");
        const cover = document.getElementById("cover_letter");

        const nameRegex = /^[A-Za-z\s]+$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^[6-9]\d{9}$/;

        // Real-time validation
        fullName.addEventListener("input", () => document.getElementById("nameError").classList.toggle("hidden", nameRegex.test(fullName.value.trim())));
        email.addEventListener("input", () => document.getElementById("emailError").classList.toggle("hidden", emailRegex.test(email.value.trim())));
        phone.addEventListener("input", () => document.getElementById("phoneError").classList.toggle("hidden", phoneRegex.test(phone.value.trim())));

        function validateFile(input, errorId, required = true) {
            return () => {
                const file = input.files[0];
                const errorEl = document.getElementById(errorId);
                if (!file) {
                    if (required) errorEl.classList.remove("hidden");
                    else errorEl.classList.add("hidden");
                    return;
                }
                const valid = file.type === 'application/pdf' && file.size <= 2 * 1024 * 1024;
                errorEl.classList.toggle("hidden", valid);
            };
        }

        resume.addEventListener("change", validateFile(resume, "resumeError", true));
        cover.addEventListener("change", validateFile(cover, "coverError", false));

        form.addEventListener("submit", function(e) {
            let isValid = true;
            document.querySelectorAll('.text-red-500').forEach(el => el.classList.add('hidden'));

            if (!nameRegex.test(fullName.value.trim())) {
                document.getElementById("nameError").classList.remove("hidden");
                isValid = false;
            }
            if (!emailRegex.test(email.value.trim())) {
                document.getElementById("emailError").classList.remove("hidden");
                isValid = false;
            }
            if (!phoneRegex.test(phone.value.trim())) {
                document.getElementById("phoneError").classList.remove("hidden");
                isValid = false;
            }
            if (!applied_for.value.trim()) {
                document.getElementById("appliedErr").classList.remove("hidden");
                isValid = false;
            }

            const resumeFile = resume.files[0];
            if (!resumeFile || resumeFile.type !== 'application/pdf' || resumeFile.size > 2*1024*1024) {
                document.getElementById("resumeError").classList.remove("hidden");
                isValid = false;
            }

            const coverFile = cover.files[0];
            if (coverFile && (coverFile.type !== 'application/pdf' || coverFile.size > 2*1024*1024)) {
                document.getElementById("coverError").classList.remove("hidden");
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            // Show loading overlay & button spinner
            overlay.style.display = "flex";
            submitBtn.disabled = true;
            btnText.textContent = "Submitting...";
            btnSpinner.classList.remove("hidden");
        });
    </script>

    <?php include "includes/footer.php"; ?>
    <?php include "includes/foot.php"; ?>
</body>
</html>