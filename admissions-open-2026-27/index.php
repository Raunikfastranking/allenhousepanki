<?php
include "apis.php";
$sessions = include "session-api.php";
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once dirname(__DIR__) . '/shared/includes/cms-page-seo.php';
    cmsLandingSeoRender($landing_data, [
    ]);
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon"
        href="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ZORSdJXNGlZAVQJ4ZNWZsMEVZ1thgvBkL8JeOSXH.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="robots" content="noindex, nofollow" />
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5DCR9LHW');
    </script>
</head>
<style>
      .hidden { display: none; }
    #student-error, #parent-error, #mobile-error, #email-error, #pincode-error, #classError, #checkboxError {
        min-height: 1.25rem;
        transition: opacity 0.3s ease;
    }
    #successMessage {
        transition: opacity 0.3s ease;
    }

    #scroll {
        position: fixed;
        right: 10px;
        bottom: 30px;
        cursor: pointer;
        width: 50px;
        height: 50px;
        background: #fff;
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        text-indent: -9999px;
        display: none;
        -webkit-border-radius: 60px;
        -moz-border-radius: 60px;
        border-radius: 60px;
        cursor: pointer;
        z-index: 99999;
    }

    #scroll span {
        position: absolute;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -12px;
        height: 0;
        width: 0;
        border: 8px solid transparent;
        border-bottom-color: #000;
    }

    .topper-card::after {
        content: '';
        height: 70%;
        width: 100%;
        background-color: #053B7A;
        opacity: 1;
        position: absolute;
        bottom: 0;
    }

    @keyframes blinkShadow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(227, 30, 36, 0.5);
        }

        50% {
            box-shadow: 0 0 12px 6px rgba(227, 30, 36, 0.8);
        }
    }

    .blink-button {
        animation: blinkShadow 1s infinite;
    }

    h2 span {
        font-size: 36px;
        font-weight: 600;
    }
</style>
<!-- <div id="esuccessPopup" class="fixed relative hidden px-4 py-2 mb-5 text-white bg-green-500 rounded"
    style="z-index:9999; position: fixed; right: 0; top: 20%;">
    Form submitted successfully!
</div> -->

<body style="overflow-x: hidden">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DCR9LHW"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <div class="main" style="overflow-x: hidden;">
        <header class="sticky top-0 z-90 lg:px-5 w-[100%] bg-white lg:h-[100px]" style="z-index: 9999;">
            <div class="main-header flex lg:justify-center justify-between px-3 items-center p-[8px]">
                <div class="lg:flex lg:w-[50%]">
                    <img src="assets/images/logo.jpg" class="lg:w-[200px] w-[150px]" alt="logo">
                </div>
                <div class="lg:w-[50%] flex justify-end">
                    <a href="<?= htmlspecialchars($brochure['data'][0]['url'] ?? '#switchForm') ?>"
                        class="transition-all hover:bg-[#F4131B] bg-[#053B7A] text-[#fff] lg:p-3 p-2  lg:px-5 px-3 font-[500] lg:text-[16px] text-[13px] rounded-[25px]"
                        style="white-space: nowrap;"><?= htmlspecialchars($brochure['data'][0]['label'] ?? $header_meta['primary_cta_text'] ?? 'Download E-Brochure') ?></a>
                </div>
            </div>
        </header>
        <div class="main-content">
            <!--==== START ====-->
               <section class="relative hero-bg" id="switchForm">
    <div class="lg:flex">
        <div class="lg:w-[50%] lg:p-10 p-5 lg:pt-[110px] relative z-10">
            <div class="text-center" data-aos="fade-right">
                <?= $landing_data['data']['sections'][0]['content_heading'] ?? '' ?>
                <?= $landing_data['data']['sections'][0]['content'] ?? '' ?>
            </div>
        </div>

        <div class="lg:w-[50%] py-1 relative z-10">
            <div class="bg-[#E31E24] border-[1px] border-white text-white rounded-[8px] lg:m-10 lg:pb-6 p-6 lg:w-[400px] lg:mx-auto mx-[10px] slideLeft">
               <form class="space-y-4" id="enquiryForm" action="" method="POST">
                    <!-- Session Selection -->
                    <div>
                        <label for="session" class="font-[500] text-[14px]">Enquiry For Session</label>
                        <select name="session" id="session" required class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]">
                            <option value="" disabled selected>Enquiry For Session</option>
                            <?php foreach ($sessions as $item): ?>
                                <option value="<?= htmlspecialchars($item['session']) ?>">
                                    <?= htmlspecialchars($item['session']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Grade Selection -->
                    <div>
                        <label for="egrade" class="font-[500] text-[14px]">Select Grade</label>
                        <select name="class-selection" id="egrade" required class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]">
                            <option value="" disabled selected>Select Grade</option>
                            <?php include 'grade-api.php'; ?>
                        </select>
                        <span id="classError" class="hidden mt-2 text-sm text-black">Please select a class.</span>
                    </div>

                    <!-- Student Name -->
                    <div>
                        <label for="estudent_name" class="font-[500] text-[14px]">Student Name</label>
                        <input type="text" name="student-name" id="estudent_name"
                               placeholder="Enter Student Name"
                               class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]"
                               required maxlength="50">
                        <span id="student-error" class="hidden mt-2 text-sm text-black"></span>
                    </div>

                    <!-- Parent Name -->
                    <div>
                        <label for="eparent_name" class="font-[500] text-[14px]">Parent Name</label>
                        <input type="text" name="parent-name" id="eparent_name"
                               placeholder="Enter Parent Name"
                               class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]"
                               required maxlength="50">
                        <span id="parent-error" class="hidden mt-2 text-sm text-black"></span>
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label for="emobile" class="font-[500] text-[14px]">Mobile Number</label>
                        <input type="text" name="mobile" id="emobile" placeholder="Enter Mobile Number"
                               class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]"
                               required maxlength="10">
                        <span id="mobile-error" class="hidden mt-2 text-sm text-black">Please enter a valid phone number</span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="eemail" class="font-[500] text-[14px]">Email</label>
                        <input type="text" name="email" id="eemail" placeholder="Enter Email"
                               class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]"
                               required>
                        <span id="email-error" class="hidden mt-2 text-sm text-black">Please enter a valid email address.</span>
                    </div>

                    <!-- City Selection -->
                    <div class="relative mt-2 customSelect">
                        <label class="font-[500] text-[14px]">City</label>
                        <select id="ecity" name="city" class="hidden" required>
                            <option value="">Select City</option>
                            <?php include 'get-city.php'; ?>
                        </select>
                        <div class="flex items-center justify-between p-3 mt-1 text-gray-300 bg-white border border-black rounded-md cursor-pointer">
                            <span class="selected-text text-[#808080cc]">Select City</span>
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#808080cc" d="m17 10l-5 6l-5-6z"/></svg></span>
                        </div>
                        <div class="absolute z-50 hidden w-full mt-1 text-gray-300 bg-white border border-black rounded-md shadow-md">
                            <input type="text" placeholder="Search..." class="w-full p-2 text-gray-700 bg-white border-b border-black outline-none">
                            <ul class="overflow-y-auto text-gray-700 max-h-48"></ul>
                        </div>
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="epincode" class="font-[500] text-[14px]">Pincode</label>
                        <input type="text" name="pincode" id="epincode" placeholder="Enter Pincode"
                               class="border-[1px] p-2 w-[100%] bg-[#fff] text-gray-500 outline-none mt-1 rounded-[5px]"
                               maxlength="6" required>
                        <span id="pincode-error" class="hidden mt-2 text-sm text-black">Please enter a valid Pincode.</span>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="flex items-start gap-2">
                        <input type="checkbox" id="popupCheckbox" required class="mt-1">
                        <label for="popupCheckbox" class="text-sm text-white">
                            I agree to <a href="https://allenhouserooma.com/termsandconditions" class="text-blue-300 underline">Terms and Conditions</a>.
                        </label>
                        <span id="checkboxError" class="hidden mt-2 text-sm text-black">You must agree to the terms.</span>
                    </div>

                    <input type="hidden" name="source" id="source">

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                            class="bg-[#053B7A] p-2 w-[100%] text-white rounded-[50px] enquiry-btn transition-all border-[1px] border-[#fff] cursor-pointer">
                        Enquire Now
                    </button>
                </form>

                <div id="successMessage"
                     class="fixed z-50 hidden px-4 py-2 font-medium text-white transition-opacity duration-300 transform -translate-x-1/2 bg-green-500 rounded shadow-md top-4 left-1/2">
                    Form submitted successfully!
                </div>
            </div>
        </div>
    </div>
</section>

            <!--==== START ====-->
            <section class="py-10 mt-8 ">
                <div>
                    <div class="text-center">
                        <h2 class="lg:text-3xl text-2xl font-[600] text-[#053B7A]"><?= $advance_data['data'][0]['title'] ?? "" ?></h2>
                    </div>
                    <div class="relative mt-10 text-center">
                        <h3
                            class="bg-[#E31E24] inline font-[600] lg:text-[30px] text-[11px] text-white rounded-[50px] lg:pl-5 pl-2 p-3 pr-0 relative whitespace-nowrap">
                            ACHIEVER <span
                                class="ml-[10px] rounded-[50px] lg:p-[13px] relative lg:bottom-1 lg:right-[5px] right-[2px] p-[10px]  text-[#154173] lg:text-[20px] text-[10px]"
                                style="background: #FFFFFF;">8
                                An 8-Dimensional Approach for Holistic Education</span>
                            <div class="absolute right-10 lg:bottom-[35px] bottom-[25px]">
                                <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/Q7XYOna6eZGW6ADf0fZVKFDQpIGLv2VRbt0Lpp8T.png"
                                    class="lg:w-[70px] w-[45px] animate-bounce" alt="">
                            </div>
                        </h3>

                    </div>
                </div>
                <div class="grid justify-center grid-cols-2 p-4 mt-10 lg:grid-cols-4 md:grid-cols-3 lg:p-10">
                    <?php
                    $items = $advance_data['data'][0]['details'];
                    $total = count($items);
                    $skipFrom = $total - 4; // last 4 items
                    ?>

                    <?php foreach ($items as $index => $data) { ?>
                        <div data-aos="zoom-out-left"
                            class="text-center <?= $index < $skipFrom ? 'lg:border-b-[1px]' : '' ?> lg:border-r-[1px] border-[#D3D3D3] pb-5 md:pt-5 cursor-pointer academic-animation">
                            <div class="lg:h-[90px] lg:w-[90px] h-[75px] w-[75px] rounded-[50%] bg-[#053B7A] flex justify-center items-center mx-auto">
                                <span><img src="<?= $data['media']['urls'] ?? "" ?>" class="aca-img" alt=""></span>
                            </div>
                            <h2 class="lg:text-[18px] text-[16px] mt-3 font-[500]"><?= strip_tags($data['description']) ?? "" ?></h2>
                        </div>
                    <?php } ?>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== START ====-->
            <section class="lg:mt-[60px] mt-8 ">
                <div class="lg:flex lg:mx-auto mx-[20px] gap-10 xl:w-[1280px] lg:w-[1080px] mx-2">
                    <h2 class="text-[36px] font-[600] text-[#053B7A] lg:hidden block lg:mb-0 mb-5 text-center">About Us
                    </h2>

                    <div class="relative lg:w-[50%]" data-aos="fade-right" data-aos-offset="300"
                        data-aos-easing="ease-in-sine">
                        <div id="circleContainer"
                            class="lg:h-[500px] lg:w-[500px] md:w-[600px] md:h-[600px] w-[300px] h-[300px] rounded-full flex items-center justify-center mx-auto bg-cover"
                            style="background-image:url('<?= htmlspecialchars(landingAboutThumbnailUrl($landing_data['data']['sections'][2] ?? null)) ?>')">
                            <button id="openModalBtn"
                                class="blink-button cursor-pointer w-[70px] h-[70px] flex items-center justify-center rounded-full bg-[#fff] border-[5px] border-[#E31E24]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="28" viewBox="0 0 9 16">
                                    <path fill="#053B7A"
                                        d="M7.62 7.18L2.79 3.03c-.7-.6-1.79-.1-1.79.82v8.29c0 .93 1.09 1.42 1.79.82l4.83-4.14c.5-.43.5-1.21 0-1.64" />
                                </svg>
                            </button>
                        </div>

                        <!-- <div class="hidden mx-auto o-video" id="videoContainer">
                            <div class="w-full aspect-video">
                                <iframe class="w-full h-full"
                                    src="https://www.youtube.com/embed/1Vzi5P3yE7c?autoplay=1&mute=1&loop=1&playlist=1Vzi5P3yE7c"
                                    frameborder="0" allow="autoplay;" allowfullscreen>
                                </iframe>
                            </div>
                        </div> -->

                    </div>

                    <div class="lg:w-[50%] lg:mt-5 mt-7" data-aos="fade-left" data-aos-anchor="#example-anchor"
                        data-aos-offset="500" data-aos-duration="500">
                        <h2 class="text-[42px] font-[600] text-[#053B7A] hidden lg:block"><?= $about_us['data'][0]['title'] ?? "" ?></h2>
                        <?= $about_us['data'][0]['desctiption'] ?? "" ?>
                    </div>
                </div>
            </section>
            <div id="videoModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-opacity-70">
                <div class="bg-white rounded-xl w-[90%] max-w-3xl relative shadow-lg">
                    <!-- Close Button -->
                    <button id="closeModalBtn"
                        class="absolute z-50 flex items-center justify-center w-8 h-8 text-white bg-red-600 rounded-full top-2 right-2 hover:bg-red-700">
                        &times;
                    </button>
                    <?php
                    $youtubeUrl = $about_us['data'][0]['url'] ?? "";

                    // Extract video ID (works for youtu.be and youtube.com/watch?v=)
                    function getYouTubeEmbedUrl($url)
                    {
                        $videoId = '';

                        // Case 1: youtu.be short link
                        if (preg_match("/youtu\.be\/([^\?&]+)/", $url, $matches)) {
                            $videoId = $matches[1];
                        }
                        // Case 2: youtube.com/watch?v= link
                        elseif (preg_match("/v=([^\?&]+)/", $url, $matches)) {
                            $videoId = $matches[1];
                        }

                        if ($videoId) {
                            return "https://www.youtube.com/embed/$videoId?autoplay=1&mute=1&loop=1&playlist=$videoId";
                        }

                        return '';
                    }

                    $embedUrl = getYouTubeEmbedUrl($youtubeUrl);
                    ?>
                    <!-- https://www.youtube.com/embed/Vb-_k6SIV34?autoplay=1&mute=1&loop=1&playlist=Vb-_k6SIV34 -->
                    <!-- Video Container -->
                    <div class="w-full aspect-video">
                        <iframe id="videoIframe" class="w-full h-full rounded-b-xl" class="w-[100%] lg:h-[100%]"
                            src="<?= $embedUrl ?? "" ?>"
                            frameborder="0" allow="autoplay;" allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
            <!--==== END ====-->

            <!--==== START ====-->
            <section class="mt-10 lg:mt-20 future-skill-bg">
                <div class="mx-4 lg:mx-10">
                    <div class="relative z-10 pt-10 text-center text-white">
                        <h2 class="text-[36px] leading-11 mt-4  text-[#fff] font-[600]"><?= $shaping_data['data'][0]['title'] ?? "" ?></h2>
                        <p class="text-gray-500 mt-3 text-[18px] text-white">
                            <?= $shaping_data['data'][0]['description'] ?? "" ?>
                        </p>
                    </div>
                    <div class="mt-10 swiper carousel2">
                        <div class="swiper-wrapper">
                            <?php foreach ($shaping_data['data'][0]['details'] as $data) { ?>
                                <div class="swiper-slide p-4 mb-2 bg-white transition-all cursor-pointer hover:translate-y-[-5px]"
                                    style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                                    <div data-aos="fade-down" data-aos-easing="linear" data-aos-duration="300">
                                        <img src="<?= $data['media']['urls'] ?? "" ?>"
                                            class="w-[100%]" alt="<?php echo $data['mediaid'] ?>" />
                                        <div class="mt-2 bg-white">
                                            <!-- <h2 class="text-[22px] font-[600] text-[#2C4073]">Robotics Academy </h2>
                                        <p class="text-[16px] text-[#70747F] mt-1">Experiential learning bridges theory
                                            and real-world application.</p> -->
                                            <?= $data['description'] ?? "" ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="lg:swiper-button-next"></div>
                        <div class="lg:swiper-button-prev"></div>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Start ====-->
            <section class="lg:mt-[90px] mt-10">
                <div class="bg-[#053B7A] relative">
                    <div data-aos="fade-right"
                        class="items-center justify-around py-4 py-10 pt-5 mx-2 text-center lg:flex lg:mx-2 lg:text-start lg:py-0 lg:pt-0">
                        <div class="mt-[-35px] relative bottom-[-7px]">
                            <img src="assets/images/announce.webp" class="lg:w-[450px] mx-auto  text-center"
                                alt="Announce">
                        </div>
                        <div class="text-white">
                            <?= $landing_data['data']['sections'][5]['content_heading'] ?? '' ?>
                            <?= $landing_data['data']['sections'][5]['content'] ?? '' ?>
                        </div>
                        <div data-aos="fade-left" class="pb-5 mt-[25px] md:flex justify-center block lg:block">
                            <a href="#switchForm"
                                class="transition-all hover:bg-[#F4131B] bg-white p-3 px-5 font-[500] lg:text-[16px] justify-center items-center text-[15px] rounded-[30px] hover:bg-red-main flex gap-2 relative z-90"
                                style="white-space: nowrap;">Download Fee Structure<svg
                                    xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 24 24">
                                    <g fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2">
                                        <path stroke-dasharray="20" stroke-dashoffset="20" d="M3 12h17.5">
                                            <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s"
                                                values="20;0" />
                                        </path>
                                        <path stroke-dasharray="12" stroke-dashoffset="12" d="M21 12l-7 7M21 12l-7 -7">
                                            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s"
                                                dur="0.2s" values="12;0" />
                                        </path>
                                    </g>
                                </svg></a>
                        </div>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Start ====-->
            <section class="">
                <div class="lg:mx-[100px] mx-[10px] rounded-[20px] lg:p-10 p-3 lg:py-15 py-8">
                    <div class="text-center">
                        <!-- <h2 class="lg:text-4xl text-[24px] font-[600] text-[#053B7A]">
                            Why Parents Trust <br>
                            Allenhouse Public School, Khalasi Lines
                        </h2>
                        <p class="text-[#424242] mt-3 font-[400] text-[16px]">Recognised as a leading CBSE school in Kanpur, Allenhouse Public School, Khalasi Lines brings together academic excellence with values. Our environment nurtures students into confident and compassionate individuals–ready to bring about a change.</p> -->
                        <?= $why_data['data'][0]['heading'] ?? "" ?>
                        <?= $why_data['data'][0]['intro'] ?? "" ?>
                    </div>
                    <div class="lg:flex flex-wrap grid grid-cols-2 grid-cols-3 items-center justify-center gap-10 mx-auto mt-10 lg:w-[1100px]">

                        <?php foreach ($why_data['data'][0]['details'] as $data) { ?>
                            <div data-aos="fade-right"
                                class="group rounded-[10px] lg:w-[245px] w-[150px] h-[150px] lg:h-[202px] border-[1px] border-blue-700 bg-[#053B7A] hover:bg-[#0E65CC] transition-all duration-500 p-3 lg:py-10 text-center cursor-pointer aos-init aos-animate">
                                <div
                                    class="flex items-center justify-center h-[60px] w-[60px] mx-auto bg-white rounded-full mb-3 transform transition-transform duration-500">
                                    <img src="<?= $data['media']['urls'] ?? "" ?>" alt="">
                                </div>
                                <h2 class="text-white  font-[600] transition-colors duration-500">
                                    <?= strip_tags($data['description']) ?? "" ?>
                                </h2>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Achievement Start ====-->
            <section class="bg-cover bg-center relative mt-[60px] allen-achievements overflow-hidden "
                style="background-image: url(assets/images/achievement-bg.png)">
                <div class="absolute animate-scale-loop">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ibXzaME0Gg0WHvJXH0ndRZ5ZVTZ49sAViQQIeaHC.png"
                        class="lg:w-[100%] w-[200px]" alt="">
                </div>
                <div class="absolute right-0 animate-scale-loop">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/AIfngM5Cv0X679VMEwLn1cdQy0cgLA8iZbFAJXyL.png"
                        alt="" class="lg:w-[100%] w-[200px]">
                </div>
                <div class="absolute lg:left-[5%] left-[50%]  ">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/jt3XMtSTZ5kroJxzns1VBIyrHJBnkX7mmWrGAGoa.png"
                        class="lg:w-[100px] w-[60px]" alt="">
                </div>
                <div class="lg:w-[1280px] mx-auto p-10 py-20 relative z-90">
                    <div class="justify-between lg:flex">
                        <h2 class="text-3xl font-[600] text-white relative z-10"><?= $achievement_data['data'][0]['title'] ?? "" ?></h2>
                        <p class="text-white">
                            <?= !empty($achievement_data['data'][0]['description'])
                                ? implode("<br>", array_map(fn($c) => implode(" ", $c), array_chunk(explode(" ", strip_tags($achievement_data['data'][0]['description'])), 10)))
                                : "" ?>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-5 mt-10 lg:gap-20">
                        <?php
                        foreach ($achievement_data['data'][0]['details'] as $data) {
                        ?>
                            <div data-aos="flip-left"
                                class="text-white cursor-pointer transition-all hover:translate-y-[-5px] [6px] border-[1px] border-white p-5 w-[346px]">
                                <div class="flex justify-center items-center w-[80px] h-[80px] bg-red-main rounded-[50%]  mx-auto">
                                    <span><img src="<?= $data["media"]["urls"] ?? "" ?>"
                                            alt=""></span>
                                </div>
                                <h2 class="mt-2 text-[18px] font-[500] text-center lg:text-left"><?= strip_tags($data["description"]) ?? "" ?></h2>
                            </div>
                        <?php } ?>
                        <!-- <div data-aos="flip-left"
                            class="transition-all hover:translate-y-[-5px] rounded-[6px] border-[1px] border-white p-5 w-[346px]">
                            <div class="flex justify-center items-center w-[80px] h-[80px] bg-red-main rounded-[50%] lg:m-0 mx-auto">
                                <span><img
                                        src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/W40UqpRS5tM9qvdocEGuZvB0a18WP1W0U1IVYt9J.png"
                                        alt=""></span>
                            </div>
                            <h2 class="mt-2 text-white text-[18px] font-[500] text-center lg:text-left ">Skating Winners of FRS <br>
                                National Sports Championship</h2>
                        </div>
                        <div data-aos="flip-left"
                            class="transition-all hover:translate-y-[-5px] rounded-[6px] border-[1px] border-white p-5 w-[346px]">
                            <div class="flex justify-center items-center w-[80px] h-[80px] bg-red-main rounded-[50%] lg:m-0 mx-auto">
                                <span><img
                                        src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/W40UqpRS5tM9qvdocEGuZvB0a18WP1W0U1IVYt9J.png"
                                        alt=""></span>
                            </div>
                            <h2 class="mt-2 text-white text-[18px] font-[500] text-center lg:text-left">Taekwondo State <br>
                                Championship Medalists</h2>
                        </div>
                        <div data-aos="flip-left"
                            class="transition-all hover:translate-y-[-5px] rounded-[6px] border-[1px] border-white p-5 w-[346px]">
                            <div class="flex justify-center items-center w-[80px] h-[80px] bg-red-main rounded-[50%] lg:m-0 mx-auto">
                                <span><img
                                        src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/W40UqpRS5tM9qvdocEGuZvB0a18WP1W0U1IVYt9J.png"
                                        alt=""></span>
                            </div>
                            <h2 class="mt-2 text-white text-[18px] font-[500] text-center lg:text-left">Awardees of Symphony of <br>
                                Splendours Contest</h2>
                        </div>
                        <div data-aos="flip-left"
                            class="transition-all hover:translate-y-[-5px] rounded-[6px] border-[1px] border-white p-5 w-[346px]">
                            <div class="flex justify-center items-center w-[80px] h-[80px] bg-red-main rounded-[50%] lg:m-0 mx-auto">
                                <span><img
                                        src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/W40UqpRS5tM9qvdocEGuZvB0a18WP1W0U1IVYt9J.png"
                                        alt=""></span>
                            </div>
                            <h2 class="mt-2 text-white text-[18px] font-[500] text-center lg:text-left">KSS English Debate <br>
                                Competition Winner</h2>
                        </div> -->
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Class 10th Topper Start ====-->
            <section class="lg:mt-5 ">
                <div class="mx-4 lg:mx-10 ">
                    <!--=== NO USE ===-->
                    <div class="hidden mt-10 swiper our-toppers" style="display: none;">
                        <div class="swiper-wrapper" data-aos="zoom-in">
                            <div class="swiper-slide cursor-pointer transition-all hover:traslate-y-[-5px] topper-card relative"
                                style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
                                <div class="p-4">
                                    <img src="assets/images/afra.webp"
                                        class="w-[200px] h-[200px] rounded-full mx-auto relative z-50 border-[4px] border-[#053B7A]"
                                        alt="Afra Ali">
                                    <div class="relative z-10 p-3 text-center text-white">
                                        <div>
                                            <p class="border-[1px] rounded-[20px] p-[6px] px-3 text-[14px] hidden">
                                                12/04/24</p>
                                        </div>
                                        <h2 class="text-[22px] font-[600] text-[#fff] mt-2">Afra Ali</h2>
                                        <div class="flex justify-center gap-6">
                                            <h3 class="text-[15px] font-[500] text-gray-300">10th Class</h3>
                                            <h3 class="text-[15px] font-[500] text-gray-300">99.2%</h3>
                                        </div>
                                        <p class="text-[16px] text-[#fff] mt-2">We practice an integrated thematic
                                            study which aims at enhancing the development and education of children
                                            through play.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide cursor-pointer transition-all hover:traslate-y-[-5px] topper-card relative"
                                style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
                                <div class="p-4">
                                    <img src="assets/images/prabhav.webp"
                                        class="w-[200px] h-[200px] rounded-full mx-auto relative z-50 border-[4px] border-[#053B7A]"
                                        alt="Prabhav Agarwal">
                                    <div class="relative z-10 p-3 text-center text-white">
                                        <div>
                                            <p class="border-[1px] rounded-[20px] p-[6px] px-3 text-[14px]  hidden">
                                                12/04/24</p>
                                        </div>
                                        <h2 class="text-[22px] font-[600] text-[#fff] mt-2">Prabhav Agarwal</h2>
                                        <div class="flex justify-center gap-6">
                                            <h3 class="text-[15px] font-[500] text-gray-300">10th Class</h3>
                                            <h3 class="text-[15px] font-[500] text-gray-300">98.2%</h3>
                                        </div>
                                        <p class="text-[16px] text-[#fff] mt-2">We practice an integrated thematic
                                            study which aims at enhancing the development and education of children
                                            through play.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide cursor-pointer transition-all hover:traslate-y-[-5px] topper-card relative"
                                style="box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;">
                                <div class="p-4">
                                    <img src="assets/images/shaurya.webp"
                                        class="w-[200px] h-[200px] rounded-full mx-auto relative z-50 border-[4px] border-[#053B7A]"
                                        alt="Jasreet Kaur">
                                    <div class="relative z-10 p-3 text-center text-white">
                                        <div>
                                            <p class="border-[1px] rounded-[20px] p-[6px] px-3 text-[14px] hidden">
                                                12/04/24</p>
                                        </div>
                                        <h2 class="text-[22px] font-[600] text-[#fff] mt-2">Shaurya Khandelwal</h2>
                                        <div class="flex justify-center gap-6">
                                            <h3 class="text-[15px] font-[500] text-gray-300">10th Class</h3>
                                            <h3 class="text-[15px] font-[500] text-gray-300">98%</h3>
                                        </div>
                                        <p class="text-[16px] text-[#fff] mt-2">We practice an integrated thematic
                                            study which aims at enhancing the development and education of children
                                            through play.</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="lg:swiper-button-next"></div>
                        <div class="lg:swiper-button-prev"></div>
                    </div>
                    <!--=== END ===-->
                </div>

                <!--==== New Topper Section ====-->
                <!-- <div class="lg:mx-[100px] mx-[20px] lg:mt-8 mt-4">
                    <div class="pt-10 text-center text-white">
                        <h2 class="lg:text-[36px] text-[24px] leading-11 mt-4 text-[#053B7A] font-[600]">Our Achievers
                        </h2>
                    </div>
                    <div class="gap-10 lg:flex">
                        <div>
                            <div class="mt-5">
                                <img src="assets/images/10th-topper.webp" alt="10th Class Topper">
                            </div>
                        </div>
                        <div>

                            <div class="mt-5">
                                <img src="assets/images/12th-topper.webp" alt="12th Class Topper">
                            </div>
                        </div>
                    </div>
                </div> -->
            </section>
            <!--==== END ====-->

            <!--==== Class 12th Topper Start ====-->
            <!-- <section class="hidden lg:mt-5">
                <div class="mx-4 lg:mx-10">
                    <div class="pt-10 text-center text-white">
                        <h2 class="lg:text-[36px] text-[24px] leading-11 mt-4 text-[#053B7A] font-[600]">Class 12
                            Toppers</h2>
                    </div>
                    <div class="lg:w-[767] mx-auto lg:mt-8 mt-4">
                        <div>
                            <img src="assets/images/12th-topper.webp" alt="10th Class Topper">
                        </div>
                    </div>
                </div>
            </section> -->
            <!--==== END ====-->



                <section class="lg:mt-[60px] mt-8 world-class-campus">
                <div class="lg:flex flex-col lg:mx-auto mx-[20px] gap-10 xl:w-[1044px] lg:w-[1080px] mx-2 items-center justify-center">
                    <?= $landing_data['data']['sections'][8]['content_heading'] ?? '' ?>
                    <?= $landing_data['data']['sections'][8]['content'] ?? '' ?>
                </div>
                </section>

            <!--==== Start ====-->
            <section class="mt-[60px] century-skills-section">
                <div class="lg:mx-[100px] mx-[20px] gap-5">
                    <div>
                        <?= $landing_data['data']['sections'][9]['content_heading'] ?? '' ?>
                    </div>
                    <div class="century-skills flex flex-wrap justify-center items-start mt-10 mx-auto max-w-7xl">
                        <?php foreach ($century_data['data'][0]['details'] as $data) { ?>
                            <div data-aos="zoom-out"
                                class="century-skills__item text-center cursor-pointer hover:translate-y-[-4px] transition-all">
                                <img src="<?= $data['media']['urls'] ?? "" ?>" class="mx-auto" alt="<?= strip_tags($data['description']) ?? ""  ?>">
                                <h3 class="text-[18px] font-[500] mt-2 transition-all hover:text-[#053B7A] uppercase"><?= strip_tags($data['description']) ?? ""  ?></h3>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Start ====-->
            <section class="lg:mt-[90px] lg:mt-0 mt-8">
                <div class="bg-[#053B7A] relative">
                    <div data-aos="fade-right"
                        class="items-center justify-around py-4 py-10 pt-5 mx-2 text-center lg:flex lg:mx-2 lg:text-start lg:py-0 lg:pt-0">
                        <div class="mt-[-35px] relative bottom-[-7px]">
                            <img src="assets/images/announce.webp" class="lg:w-[450px] mx-auto  text-center"
                                alt="Announce">
                        </div>
                        <div class="text-white">
                            <?= $landing_data['data']['sections'][10]['content_heading'] ?? '' ?>
                            <?= $landing_data['data']['sections'][10]['content'] ?? '' ?>
                        </div>
                        <div data-aos="fade-left" class="pb-5 mt-[25px] md:flex justify-center block lg:block">
                            <a href="#switchForm"
                                class="transition-all hover:bg-[#F4131B] bg-white p-3 px-5 font-[500] lg:text-[16px] justify-center items-center text-[15px] rounded-[30px] hover:bg-red-main flex gap-2 relative z-90"
                                style="white-space: nowrap;">Book a School Tour Now<svg xmlns="http://www.w3.org/2000/svg"
                                    width="20" height="18" viewBox="0 0 24 24">
                                    <g fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2">
                                        <path stroke-dasharray="20" stroke-dashoffset="20" d="M3 12h17.5">
                                            <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s"
                                                values="20;0" />
                                        </path>
                                        <path stroke-dasharray="12" stroke-dashoffset="12" d="M21 12l-7 7M21 12l-7 -7">
                                            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s"
                                                dur="0.2s" values="12;0" />
                                        </path>
                                    </g>
                                </svg></a>
                        </div>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Start ====-->
            <section class="mt-12 ">
                <div class="lg:p-10 p-5 lg:w-[1280px] mx-auto">
                    <div class="text-center">
                        <?= $legacy_data['data'][0]['title'] ?? $landing_data['data']['sections'][11]['content_heading'] ?? '' ?>
                    </div>
                    <div class="grid grid-cols-2 gap-5 mt-5 lg:mt-10 lg:grid-cols-5">
                        <?php
                        foreach ($legacy_data['data'][0]['details'] as $index => $data) {
                        ?>
                            <div data-aos="zoom-out-right" class="rounded-[6px] border-white bg-[#053B7A] p-5 text-center">
                                <div
                                    class="flex justify-center items-center lg:w-[80px] lg:h-[80px] w-[80px] h-[80px]  rounded-[50%] bg-[#E31E24] mx-auto">
                                    <span><?= landingLegacyIconSvg((int) $index) ?></span>
                                </div>
                                <!-- <h2 class="mt-2 text-white text-[18px] font-[700]">27+ </h2>
                                <span class="text-[13px] text-white">Years of Experience</span> -->
                                <div class="mt-3">
                                    <?= $data['description'] ?? "" ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== LIFE Start ====-->
            <section class="">
                <div class="mx-4 lg:mx-10">
                    <div class="pt-10 text-center">
                        <h2 class="lg:text-[36px] text-[24px] leading-11 mt-4 text-[#053B7A] font-[600]"><?= htmlspecialchars($life_data['data'][0]['title'] ?? '') ?></h2>
                    </div>
                    <div class="mt-10 swiper carousel3">
                        <div class="swiper-wrapper">
                            <?php foreach ($life_data['data'][0]['details'] ?? [] as $data) { ?>
                            <div class="swiper-slide">
                                <div data-aos="fade-down" data-aos-easing="linear" data-aos-duration="300">
                                    <img src="<?= $data['media']['urls'] ?? "" ?>"
                                        class="w-[100%] rounded-[12px]" alt="" />
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="lg:swiper-button-next"></div>
                        <div class="lg:swiper-button-prev"></div>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== TESTIMONIAL Start ====-->
            <section class="">
                <div class="mx-4 lg:mx-10">
                    <div class="pt-10 text-center">
                        <h2 class="lg:text-[36px] text-[24px] leading-11 mt-4 text-[#053B7A] font-[600]"><?= strip_tags($testimonial_data['data'][0]['heading'] ?? '') ?></h2>
                    </div>
                    <div class="xl:w-[1280px] lg:w-[1080px] lg:w-[768px] lg:mx-auto mx-3 px-2">
                        <div class="mt-10 swiper carousel4">
                            <div class="swiper-wrapper">

                                <?php foreach ($testimonial_data['data'][0]['details'] as $data) { ?>
                                    <div class="swiper-slide" data-aos="zoom-out-right">
                                        <div class="relative py-5 p-5 gap-5 rounded-[15px] shadow items-center "
                                            style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                                            <div class="">
                                                <img src="<?= $data['media']['urls'] ?? "" ?>"
                                                    class="w-[150px] h-[150px] cover mx-auto rounded-[50%]" alt="" />
                                            </div>
                                            <div class="mt-1 text-center">
                                                <?php if (!empty($data['title'])): ?>
                                                <h3 class="mt-3 font-[600] text-[20px] text-black"><?= htmlspecialchars($data['title']) ?></h3>
                                                <?php endif; ?>
                                                <p
                                                    class="lg:text-[15px] lg:text-[14px] xl:text-[13px] 2xl:text-[14px] text-gray-500 relative z-90">
                                                    <?= $data['description'] ?? "" ?>
                                                </p>
                                            </div>
                                            <div class="absolute right-3 top-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44"
                                                    viewBox="0 0 16 16">
                                                    <path fill="#E31E24"
                                                        d="M12.5 10A3.5 3.5 0 1 1 16 6.5l.016.5a7 7 0 0 1-7 7v-2a4.97 4.97 0 0 0 3.536-1.464a5 5 0 0 0 .497-.578a3.6 3.6 0 0 1-.549.043zm-9 0A3.5 3.5 0 1 1 7 6.5l.016.5a7 7 0 0 1-7 7v-2a4.97 4.97 0 0 0 3.536-1.464a5 5 0 0 0 .497-.578a3.6 3.6 0 0 1-.549.043z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                            <!-- <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div> -->
                        </div>
                    </div>
                </div>
            </section>
            <!--==== END ====-->

            <!--==== Start ====-->
            <footer class="mt-10">
                <div>
                    <div class="lg:flex">
                        <div class="lg:w-[50%]">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3571.7376136641456!2d80.25274111120626!3d26.46418547912532!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399c37e01176e6f5%3A0xa354b4d3e53a02ed!2sAllenhouse%20Public%20School%2C%20Panki%2C%20Kanpur!5e0!3m2!1sen!2sin!4v1758034385766!5m2!1sen!2sin"
                                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="lg:w-[50%] bg-blue-main">
                            <div class="p-10">
                                <h2 class="font-[600] text-[40px] text-white">Contact Us</h2>
                                <ul class="mt-4">
                                    <li class="flex items-center gap-3 mb-4 text-white"><span><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <path fill="#fff"
                                                    d="M19.95 21q-3.125 0-6.175-1.362t-5.55-3.863t-3.862-5.55T3 4.05q0-.45.3-.75t.75-.3H8.1q.35 0 .625.238t.325.562l.65 3.5q.05.4-.025.675T9.4 8.45L6.975 10.9q.5.925 1.187 1.787t1.513 1.663q.775.775 1.625 1.438T13.1 17l2.35-2.35q.225-.225.588-.337t.712-.063l3.45.7q.35.1.575.363T21 15.9v4.05q0 .45-.3.75t-.75.3" />
                                            </svg></span><a href="tel:<?= $address_data['data'][0]['contact1'] ?? "" ?>"><?= $address_data['data'][0]['contact1'] ?? "" ?></a></li>
                                    <li class="flex items-center gap-3 mb-4 text-white"><span><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <path fill="#fff"
                                                    d="M4 20q-.825 0-1.412-.587T2 18V6q0-.825.588-1.412T4 4h16q.825 0 1.413.588T22 6v12q0 .825-.587 1.413T20 20zm8-7l8-5V6l-8 5l-8-5v2z" />
                                            </svg></span>
                                        <?= $address_data['data'][0]['email'] ?? "" ?>
                                    </li>
                                    <li class="flex items-center gap-3 text-white"><span><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <path fill="#fff"
                                                    d="M12 11.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7" />
                                            </svg></span><?= $address_data['data'][0]['addressline1'] ?? "" ?>, <?= $address_data['data'][0]['pincode'] ?? "" ?></li>
                                </ul>
                                <div class="flex gap-4 mt-5">
                                    <h5 class="text-white text-[20px]">Follow us on :</h5>
                                    <ul class="flex items-center gap-4">
                                        <li>
                                            <a href="<?= $social_data['data'][0]['url'] ?? "" ?>">
                                                <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/9d0DF8djfjoxGImlA20yu8I0QNhnrwI8a89WS6D7.png" alt="" class="w-[12px]">
                                            </a>
                                        </li>
                                        <li><a href="<?= $social_data['data'][1]['url'] ?? "" ?>"><img
                                                    src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/r3CZWLa921XyxMbb1bki2tYIA2oAnOvlMDFWBVIw.png"
                                                    alt="" class="w-[22px]"></a></li>
                                        <li>
                                            <a href="<?= $social_data['data'][2]['url'] ?? "" ?>">
                                                <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ORGYw8Xo1qQ0LS8LVEaTGU3khQ7r2HkkrFZ0pH50.png" alt="" class="w-[25px]">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!--==== END ====-->

        </div>
    </div>
    <div>
        <a id="scroll" style="display: block;"><span></span></a>
    </div>

    <div id="videoModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-opacity-70">
        <div class="bg-white rounded-xl w-[90%] max-w-3xl relative shadow-lg">
            <!-- Close Button -->
            <button id="closeModalBtn"
                class="absolute z-50 flex items-center justify-center w-8 h-8 text-white bg-red-600 rounded-full top-2 right-2 hover:bg-red-700">
                &times;
            </button>
            <!-- Video Container -->
            <div class="w-full aspect-video">
                <iframe id="videoIframe" class="w-full h-full rounded-b-xl" class="w-[100%] lg:h-[100%]"
                    src="https://www.youtube.com/embed/LfBDFMjfSew?autoplay=1&mute=1&loop=1&playlist=LfBDFMjfSew"
                    frameborder="0" allow="autoplay;" allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
    <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const videoModal = document.getElementById('videoModal');
        const videoIframe = document.getElementById('videoIframe');

        openModalBtn.addEventListener('click', () => {
            videoModal.classList.remove('hidden');
            videoIframe.src += ''; // Reload video to autoplay
        });

        closeModalBtn.addEventListener('click', () => {
            videoModal.classList.add('hidden');
            videoIframe.src = videoIframe.src; // Reset to stop the video
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script>
        const swiper2 = new Swiper('.carousel2', {
            loop: true,
            spaceBetween: 20,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                },
                1220: {
                    slidesPerView: 4
                },
                1320: {
                    slidesPerView: 5
                }
            }
        });
        const swiper3 = new Swiper('.carousel3', {
            loop: true,
            spaceBetween: 15,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                }
            }
        });
        const swiper4 = new Swiper('.carousel4', {
            loop: true,
            spaceBetween: 20,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 1
                },
                1024: {
                    slidesPerView: 2
                }
            }
        });


        const ourToppers = new Swiper('.our-toppers', {
            loop: true,
            spaceBetween: 15,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                },
                1220: {
                    slidesPerView: 4
                },
                1320: {
                    slidesPerView: 5
                }
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.addEventListener("scroll", function() {
                if (window.scrollY > 100) {
                    document.getElementById("scroll").style.display = "block";
                } else {
                    document.getElementById("scroll").style.display = "none";
                }
            });

            document.getElementById("scroll").addEventListener("click", function() {
                setTimeout(function() {
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth"
                    });
                }, 500); // 500ms delay
            });
        });
    </script>
  <?php include __DIR__ . '/../includes/form-proxy-client.php'; ?>
  <script>
    /* ==============================================================
       LEAD SOURCE TRACKING (unchanged)
       ============================================================== */
    (function() {
        function getParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }
        var source = getParam("utm_source") || document.referrer || "";
        console.log("Initial Source:", source);

        if (!source) {
            source = "Landing Page";
        } else if (source.includes("google.")) {
            source = "Google-Ads by Agency";
            console.log("Referrer is Google, setting source to 'Google-Ads by Agency'");
        } else if (source.includes("facebook.")) {
            source = "Facebook by Agency";
        } else if (source.includes("instagram.")) {
            source = "Instagram by Agency";
        }

        if (!sessionStorage.getItem("leadSource")) {
            sessionStorage.setItem("leadSource", source);
        }

        var finalSource = sessionStorage.getItem("leadSource");
        var sourceInput = document.getElementById("source");
        if (sourceInput) sourceInput.value = finalSource;
        console.log("Captured Source:", finalSource);
    })();

    const baseUrl = "save-enqury-form/6";

    // Validation regex
    const nameRegex = /^[A-Za-z\s]+$/;
    const mobileRegex = /^[6-9]\d{9}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const pincodeRegex = /^[1-9][0-9]{5}$/;

    // Error elements
    const studentError = document.getElementById("student-error");
    const parentError  = document.getElementById("parent-error");
    const mobileError  = document.getElementById("mobile-error");
    const emailError   = document.getElementById("email-error");
    const pincodeError = document.getElementById("pincode-error");
    const classError   = document.getElementById("classError");
    const checkboxError= document.getElementById("checkboxError");

    const MAX_NAME = 50;

    /* ==============================================================
       NAME FIELD: 50 CHARS + LETTERS ONLY → BOTH ERRORS WORK
       ============================================================== */
    function setupNameField(inputId, errorId) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        if (!input || !error) return;

        let lastValidValue = input.value;

        const flashError = (msg) => {
            error.textContent = msg;
            error.classList.remove("hidden");
            clearTimeout(error._timer);
            error._timer = setTimeout(() => error.classList.add("hidden"), 3000);
        };

        const sanitizeAndValidate = () => {
            let value = input.value;

            // Remove invalid characters
            const cleanValue = value.replace(/[^A-Za-z\s]/g, '');

            // Enforce max length
            const limitedValue = cleanValue.slice(0, MAX_NAME);

            // Update input
            if (input.value !== limitedValue) {
                input.value = limitedValue;
            }

            // Show correct error
            if (value.length > MAX_NAME) {
                flashError("Only 50 characters are allowed.");
            } else if (value !== cleanValue) {
                flashError("Only letters and spaces allowed.");
            }

            lastValidValue = limitedValue;
        };

        // Real-time input
        input.addEventListener("input", sanitizeAndValidate);
        input.addEventListener("paste", () => setTimeout(sanitizeAndValidate, 0));

        // Block 51st character
        input.addEventListener("keydown", (e) => {
            if (input.value.length >= MAX_NAME && 
                e.key.length === 1 && 
                !e.ctrlKey && !e.metaKey && 
                ![8, 46].includes(e.keyCode)) {
                e.preventDefault();
                flashError("Only 50 characters are allowed.");
            }
        });

        // Expose clean value for form
        input.dataset.getCleanValue = () => input.value;
    }

    // Apply to both fields
    setupNameField("estudent_name", "student-error");
    setupNameField("eparent_name",  "parent-error");

    /* ==============================================================
       OTHER INPUTS
       ============================================================== */
    document.getElementById("emobile").addEventListener("input", function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
        mobileError.classList.toggle("hidden", mobileRegex.test(this.value));
    });

    document.getElementById("eemail").addEventListener("input", function() {
        this.value = this.value.toLowerCase();
        emailError.classList.toggle("hidden", emailRegex.test(this.value));
    });

    document.getElementById("epincode").addEventListener("input", function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        pincodeError.classList.toggle("hidden", pincodeRegex.test(this.value));
    });

    /* ==============================================================
       FORM SUBMISSION
       ============================================================== */
    document.getElementById("enquiryForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById("submitBtn");
        submitBtn.disabled = true;
        submitBtn.textContent = "Submitting...";

        const grade      = document.getElementById("egrade").value.trim();
        const student    = document.getElementById("estudent_name").value.trim();
        const parent     = document.getElementById("eparent_name").value.trim();
        const mobile     = document.getElementById("emobile").value.trim();
        const email      = document.getElementById("eemail").value.trim();
        const city       = document.getElementById("ecity").value.trim();
        const pincode    = document.getElementById("epincode").value.trim();
        const checkbox   = document.getElementById("popupCheckbox").checked;
        const source     = sessionStorage.getItem("leadSource") || "Landing Page";
        const session    = document.getElementById("session")?.value.trim() || "";

        let ok = true;

        // Reset errors
        [classError, studentError, parentError, mobileError, emailError, pincodeError, checkboxError]
            .forEach(el => el && el.classList.add("hidden"));

        // Grade
        if (!grade) { classError.classList.remove("hidden"); ok = false; }

        // Student Name
        if (!student || !nameRegex.test(student) || student.length > MAX_NAME) {
            studentError.textContent = student.length > MAX_NAME 
                ? "Only 50 characters are allowed." 
                : "Only letters and spaces allowed.";
            studentError.classList.remove("hidden"); 
            ok = false;
        }

        // Parent Name
        if (!parent || !nameRegex.test(parent) || parent.length > MAX_NAME) {
            parentError.textContent = parent.length > MAX_NAME 
                ? "Only 50 characters are allowed." 
                : "Only letters and spaces allowed.";
            parentError.classList.remove("hidden"); 
            ok = false;
        }

        // Mobile
        if (!mobileRegex.test(mobile)) { mobileError.classList.remove("hidden"); ok = false; }

        // Email
        if (!emailRegex.test(email)) { emailError.classList.remove("hidden"); ok = false; }

        // Pincode
        if (!pincodeRegex.test(pincode)) { pincodeError.classList.remove("hidden"); ok = false; }

        // Checkbox
        if (!checkbox) { checkboxError.classList.remove("hidden"); ok = false; }

        if (!ok) {
            submitBtn.disabled = false;
            submitBtn.textContent = "Enquire Now";
            return;
        }

                const payload = window.cmsBuildEnquiryPayload({
            session: session,
            grade: grade,
            student_name: student,
            parent_name: parent,
            phone: mobile,
            email: email,
            city: city,
            pincode: pincode,
            source: source,
            source_type: "organic"
        });

        fetch("admission-proxy.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json", "Cache-Control": "no-cache" },
            body: JSON.stringify(payload)
        })
        .then(function (response) {
            return window.cmsParseProxyJson(response);
        })
        .then(function () {
            document.getElementById("enquiryForm").reset();
            const sel = document.querySelector(".customSelect .selected-text");
            if (sel) sel.textContent = "Select City";
            document.getElementById("ecity").value = "";

            const msg = document.getElementById("successMessage");
            msg.classList.remove("hidden");
            setTimeout(() => msg.style.opacity = "1", 10);
            setTimeout(() => {
                msg.style.opacity = "0";
                setTimeout(() => { msg.classList.add("hidden"); window.location.href = "thankyou"; }, 300);
            }, 3000);
        })
        .catch(err => {
            console.error(err);
            alert("Submission error: " + err.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = "Enquire Now";
        });
    });
</script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const select = document.getElementById("ecity");
            const fakeDropdown = select.nextElementSibling; // div showing selected text
            const optionsContainer = fakeDropdown.nextElementSibling; // ul container
            const ul = optionsContainer.querySelector("ul");
            const searchInput = optionsContainer.querySelector("input");

            // Populate the UL from hidden select
            Array.from(select.options).forEach(opt => {
                if (opt.value === "") return;
                const li = document.createElement("li");
                li.textContent = opt.textContent;
                li.setAttribute("data-value", opt.value);
                li.classList.add("cursor-pointer", "p-2", "hover:bg-gray-100");
                ul.appendChild(li);

                // Click event
                li.addEventListener("click", function() {
                    select.value = this.getAttribute("data-value"); // update hidden select
                    fakeDropdown.querySelector(".selected-text").textContent = this.textContent; // update display
                    optionsContainer.classList.add("hidden"); // close dropdown
                });
            });

            // Show/hide dropdown
            fakeDropdown.addEventListener("click", () => {
                optionsContainer.classList.toggle("hidden");
                searchInput.value = "";
                Array.from(ul.children).forEach(li => li.classList.remove("hidden"));
            });

            // Search filter
            searchInput.addEventListener("input", function() {
                const term = this.value.toLowerCase();
                Array.from(ul.children).forEach(li => {
                    li.classList.toggle("hidden", !li.textContent.toLowerCase().includes(term));
                });
            });

            // Close dropdown if click outside
            document.addEventListener("click", function(e) {
                if (!fakeDropdown.contains(e.target) && !optionsContainer.contains(e.target)) {
                    optionsContainer.classList.add("hidden");
                }
            });
        });
    </script>
    <?php
    $page = 'admissions-open-2026-27';
    include __DIR__ . '/../includes/page-image-alt-script.php';
    ?>
</body>

</html>