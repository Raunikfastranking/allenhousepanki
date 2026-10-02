<?php
require_once 'api.php';
$addressdata = fetchApiData('all-address-details/' . BRANCH_ID);
$contactsdata = fetchApiData('header-contact/' . BRANCH_ID);
$erpsdata = fetchApiData('erp-login/' . BRANCH_ID);
$thmdata = fetchApiData('top-header-marquee-text/' . BRANCH_ID);
$alupdata = fetchApiData('alumni-portal/' . BRANCH_ID);
?>
<style>
    @keyframes slideInfinite {
        0% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    .animate-slide {
        animation: slideInfinite 20s linear infinite;
    }

    .animate-slide:hover {
        animation-play-state: paused;
    }
</style>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PQB9HHQM" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div id="esuccessPopup" class="relative fixed  bg-green-500 text-white px-4 py-2 rounded mb-5 hidden" style="z-index:9999;
    position: fixed;
    right: 0;
    top: 20%;">
    Form submitted successfully!
</div>
<div
    class="fixed right-0 top-1/2 -translate-y-1/2 pointer-events-none z-[9999] transform rotate-90 right-[-50px] xl:hidden">
    <a href="#" id="openPopup"
        class="text-gray-500 border-[1px] border-white bg-blue-main px-5 py-2 text-lg origin-right pointer-events-auto no-underline text-white transition">
        Enquire Now
    </a>
</div>

<div id="popupForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-[9998]">
    <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative">
        <!-- Close Button -->
        <button id="closePopup" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl font-bold">
            &times;
        </button>

        <h2 class="text-2xl font-semibold mb-4">Enquiry Form</h2>
        <form class="space-y-4" id="enquiryForm" action="" method="POST">
            <div>
                <select name="class-selection" required id="egrade"
                    class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                    <option value="" disabled selected>Select Grade</option>
                    <?php include 'grade-api.php'; ?>
                </select>
                <span id="classError" class="text-red-500 text-sm hidden">Please select a class.</span>
            </div>

            <div>
                <input type="text" name="student-name" id="estudent_name" placeholder="Student Name"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="student-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces
                    allowed.</span>
            </div>

            <div>
                <input type="text" name="parent-name" id="eparent_name" placeholder="Parents Name"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="parent-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces allowed.</span>
            </div>

            <div>
                <input type="text" name="mobile" id="emobile" placeholder="Mobile No."
                    class="w-full border border-gray-300 p-2 rounded-md" required maxlenth="10">
                <span id="mobile-error" class="text-red-500 text-sm mt-1 hidden">Please enter valid phone number</span>
            </div>

            <div>
                <input type="text" name="email" id="eemail" placeholder="E-mail"
                    class="w-full border border-gray-300 p-2 rounded-md" required>
                <span id="email-error" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email
                    address.</span>
            </div>

            <!-- City -->
            <!-- <div>
                <select name="class-selection" id="ecity" required
                    class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                    <option value="" disabled selected>Select City</option>
                    <option value="Delhi">Delhi</option>
                </select>
            </div> -->

            <div class="mt-4 relative customSelect">
                <select id="ecity" name="city" class="hidden">
                    <option value="">Select City</option>
                    <?php include 'get-city.php'; ?>
                </select>

                <!-- Fake dropdown display -->
                <div class="border border-gray-300 p-[11px] rounded-md bg-white cursor-pointer flex justify-between items-center">
                    <span class="selected-text text-[#808080cc]">Select City</span>
                    <span>▼</span>
                </div>

                <!-- Dropdown options -->
                <div class="absolute mt-1 border border-gray-300 rounded-md bg-white shadow-md hidden z-50 w-full">
                    <input type="text" placeholder="Search..."
                        class="w-full p-2 border-b border-gray-300 outline-none">
                    <ul class="max-h-48 overflow-y-auto"></ul>
                </div>
            </div>

            <!-- Pincode -->
            <div>
                <input type="text" name="pincode" id="epincode" placeholder="Enter your Pincode"
                    class="w-full border border-gray-300 p-2 rounded-md" maxlength="6"
                    oninput="this.value=this.value.replace(/\D/g,'')" required>
                <span id="pincode-error" class="text-red-500 text-sm hidden">Please enter a valid Pincode.</span>
            </div>

            <div class="flex items-start gap-2">
                <input type="checkbox" id="popupCheckbox" required />
                <label for="popupCheckbox" class="text-sm">I agree to <a href="termsandconditions"
                        class="text-blue-600 underline">Terms
                        and Conditions</a>.</label>
                <span id="checkboxError" class="text-red-500 text-sm hidden">You must agree to the terms and
                    conditions.</span>
            </div>
            <input type="hidden" name="source" id="source">
            <button type="submit" id="submitBtn"
                class="w-full py-2 bg-blue-main text-white rounded-md hover:bg-red-500 transition">Submit</button>
        </form>
    </div>
</div>

<div class="bg-blue-main p-2 px-10 sm:flex hidden justify-between items-center text-[12px]">


    <div class="sm:w-[10%]">
        <a href="tel:<?php echo $contactsdata['data'][0]['contact1'] ?>" class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">
            <svg width="22" height="23" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17.7875 19.5793C15.8778 19.5793 13.991 19.1631 12.1271 18.3308C10.2632 17.4985 8.56736 16.3181 7.03958 14.7897C5.51181 13.2613 4.33175 11.5655 3.49942 9.7022C2.66708 7.83892 2.25061 5.95212 2.25 4.04178C2.25 3.76678 2.34167 3.53762 2.525 3.35428C2.70833 3.17095 2.9375 3.07928 3.2125 3.07928H6.925C7.13889 3.07928 7.32986 3.15201 7.49792 3.29745C7.66597 3.4429 7.76528 3.61462 7.79583 3.81262L8.39167 7.02095C8.42222 7.2654 8.41458 7.47165 8.36875 7.6397C8.32292 7.80776 8.23889 7.9529 8.11667 8.07512L5.89375 10.321C6.19931 10.8862 6.562 11.4323 6.98183 11.959C7.40167 12.4858 7.86397 12.994 8.36875 13.4835C8.84236 13.9571 9.33889 14.3965 9.85833 14.8016C10.3778 15.2068 10.9278 15.5771 11.5083 15.9126L13.6625 13.7585C13.8 13.621 13.9797 13.518 14.2015 13.4495C14.4233 13.3811 14.6409 13.3618 14.8542 13.3918L18.0167 14.0335C18.2306 14.0946 18.4062 14.2055 18.5438 14.3662C18.6812 14.5269 18.75 14.7063 18.75 14.9043V18.6168C18.75 18.8918 18.6583 19.121 18.475 19.3043C18.2917 19.4876 18.0625 19.5793 17.7875 19.5793Z"
                    class="fill-white transition-all group-hover:fill-red-500" fill="white" />
            </svg>
            +91-<?php echo $contactsdata['data'][0]['contact1'] ?>
        </a>
    </div>


    <div class=" overflow-hidden sm:w-[60%] ">
        <div>
            <p class="flex  items-center justify-between animate-slide sm:gap-0 gap-10 text-white">
                <?php echo $thmdata['data'][0]['contant'] ?>
            </p>
        </div>
    </div>



    <div class="hidden sm:block">
        <ul class="flex  items-center gap-4 sm:w-[30%]">
            <li>
                <a href="<?php echo $erpsdata['data'][0]['url'] ?>"
                    class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M15.5 12.3293L11.5 8.32928M15.5 12.3293L11.5 16.3293M15.5 12.3293H5.5M10.5 21.3293C11.6819 21.3293 12.8522 21.0965 13.9442 20.6442C15.0361 20.1919 16.0282 19.529 16.864 18.6932C17.6997 17.8575 18.3626 16.8654 18.8149 15.7734C19.2672 14.6815 19.5 13.5112 19.5 12.3293C19.5 11.1474 19.2672 9.97706 18.8149 8.88513C18.3626 7.7932 17.6997 6.80105 16.864 5.96532C16.0282 5.1296 15.0361 4.46666 13.9442 4.01437C12.8522 3.56208 11.6819 3.32928 10.5 3.32928"
                            class=" transition-all group-hover:stroke-red-500" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    ERP Login
                </a>
            </li>
            <li>
                <a href="<?php echo $alupdata['data'][0]['url'] ?>"
                    class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18.5 19.7543V20.9793C18.5 21.2626 18.404 21.5003 18.212 21.6923C18.02 21.8843 17.7827 21.98 17.5 21.9793C17.2173 21.9786 16.98 21.883 16.788 21.6923C16.596 21.5016 16.5 21.264 16.5 20.9793V17.8293C16.5 17.4126 16.646 17.0586 16.938 16.7673C17.23 16.476 17.584 16.33 18 16.3293H21.15C21.4333 16.3293 21.671 16.4253 21.863 16.6173C22.055 16.8093 22.1507 17.0466 22.15 17.3293C22.1493 17.612 22.0537 17.8496 21.863 18.0423C21.6723 18.235 21.4347 18.3306 21.15 18.3293H19.9L22.15 20.5793C22.3333 20.7626 22.425 20.992 22.425 21.2673C22.425 21.5426 22.3333 21.78 22.15 21.9793C21.95 22.1793 21.7127 22.2793 21.438 22.2793C21.1633 22.2793 20.9257 22.1793 20.725 21.9793L18.5 19.7543ZM12.5 22.3293C11.1167 22.3293 9.81667 22.0666 8.6 21.5413C7.38334 21.016 6.325 20.3036 5.425 19.4043C4.525 18.505 3.81267 17.4466 3.288 16.2293C2.76333 15.012 2.50067 13.712 2.5 12.3293C2.49933 10.9466 2.762 9.64662 3.288 8.42929C3.814 7.21195 4.52633 6.15362 5.425 5.25428C6.32367 4.35495 7.382 3.64262 8.6 3.11728C9.818 2.59195 11.118 2.32928 12.5 2.32928C13.882 2.32928 15.182 2.59195 16.4 3.11728C17.618 3.64262 18.6763 4.35495 19.575 5.25428C20.4737 6.15362 21.1863 7.21195 21.713 8.42929C22.2397 9.64662 22.502 10.9466 22.5 12.3293C22.5 12.496 22.496 12.6793 22.488 12.8793C22.48 13.0793 22.4673 13.2626 22.45 13.4293C22.4167 13.7126 22.3 13.9336 22.1 14.0923C21.9 14.251 21.65 14.33 21.35 14.3293C21.0833 14.3293 20.8583 14.2126 20.675 13.9793C20.4917 13.746 20.4167 13.496 20.45 13.2293C20.4833 13.0626 20.5 12.9126 20.5 12.7793V12.3293C20.5 11.996 20.479 11.6626 20.437 11.3293C20.395 10.996 20.3327 10.6626 20.25 10.3293H16.85C16.9 10.6626 16.9377 10.996 16.963 11.3293C16.9883 11.6626 17.0007 11.996 17 12.3293V12.8673C17 13.0586 16.9917 13.2376 16.975 13.4043C16.9417 13.6876 16.825 13.9126 16.625 14.0793C16.425 14.246 16.1833 14.3293 15.9 14.3293C15.6333 14.3293 15.4043 14.221 15.213 14.0043C15.0217 13.7876 14.9423 13.546 14.975 13.2793C14.9917 13.1126 15 12.9543 15 12.8043V12.3293C15 11.996 14.9877 11.6626 14.963 11.3293C14.9383 10.996 14.9007 10.6626 14.85 10.3293H10.15C10.1 10.6626 10.0627 10.996 10.038 11.3293C10.0133 11.6626 10.0007 11.996 10 12.3293C9.99933 12.6626 10.012 12.996 10.038 13.3293C10.064 13.6626 10.1013 13.996 10.15 14.3293H12.5C12.7833 14.3293 13.021 14.4253 13.213 14.6173C13.405 14.8093 13.5007 15.0466 13.5 15.3293C13.4993 15.612 13.4033 15.8496 13.212 16.0423C13.0207 16.235 12.7833 16.3306 12.5 16.3293H10.6C10.8 17.046 11.0583 17.7336 11.375 18.3923C11.6917 19.051 12.0667 19.68 12.5 20.2793C12.6667 20.2793 12.8333 20.2836 13 20.2923C13.1667 20.301 13.3333 20.2966 13.5 20.2793C13.7833 20.246 14.0167 20.317 14.2 20.4923C14.3833 20.6676 14.475 20.8966 14.475 21.1793C14.475 21.4793 14.4 21.7293 14.25 21.9293C14.1 22.1293 13.8833 22.246 13.6 22.2793C13.4333 22.296 13.25 22.3086 13.05 22.3173C12.85 22.326 12.6667 22.33 12.5 22.3293ZM4.75 14.3293H8.15C8.1 13.996 8.06267 13.6626 8.038 13.3293C8.01333 12.996 8.00067 12.6626 8 12.3293C7.99933 11.996 8.012 11.6626 8.038 11.3293C8.064 10.996 8.10134 10.6626 8.15 10.3293H4.75C4.66667 10.6626 4.60433 10.996 4.563 11.3293C4.52167 11.6626 4.50067 11.996 4.5 12.3293C4.49933 12.6626 4.52033 12.996 4.563 13.3293C4.60567 13.6626 4.668 13.996 4.75 14.3293ZM9.9 19.8793C9.6 19.3126 9.33734 18.7336 9.112 18.1423C8.88667 17.551 8.69934 16.9466 8.55 16.3293H5.6C6.08333 17.1793 6.69167 17.9086 7.425 18.5173C8.15833 19.126 8.98333 19.58 9.9 19.8793ZM5.6 8.32928H8.55C8.7 7.71262 8.88767 7.10862 9.113 6.51728C9.33833 5.92595 9.60067 5.34662 9.9 4.77928C8.98333 5.07928 8.15833 5.53362 7.425 6.14228C6.69167 6.75095 6.08333 7.47995 5.6 8.32928ZM10.6 8.32928H14.4C14.2 7.61262 13.9417 6.92528 13.625 6.26728C13.3083 5.60928 12.9333 4.97995 12.5 4.37928C12.0667 4.97928 11.6917 5.60862 11.375 6.26728C11.0583 6.92595 10.8 7.61328 10.6 8.32928ZM16.45 8.32928H19.4C18.9167 7.47928 18.3083 6.75028 17.575 6.14228C16.8417 5.53428 16.0167 5.07995 15.1 4.77928C15.4 5.34595 15.6627 5.92528 15.888 6.51728C16.1133 7.10928 16.3007 7.71328 16.45 8.32928Z"
                            class="fill-white transition-all group-hover:fill-red-500" fill="white" stroke="#053B7A"
                            stroke-width="0.5" />
                    </svg>

                    Alumni Portal
                </a>
            </li>
            <li>
                <a href="contact-us" class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.5 2.32928V4.32928M7.5 22.3293V20.3293C7.5 19.7989 7.71071 19.2901 8.08579 18.9151C8.46086 18.54 8.96957 18.3293 9.5 18.3293H15.5C16.0304 18.3293 16.5391 18.54 16.9142 18.9151C17.2893 19.2901 17.5 19.7989 17.5 20.3293V22.3293M8.5 2.32928V4.32928"
                            class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M12.5 14.3293C14.1569 14.3293 15.5 12.9861 15.5 11.3293C15.5 9.67243 14.1569 8.32928 12.5 8.32928C10.8431 8.32928 9.5 9.67243 9.5 11.3293C9.5 12.9861 10.8431 14.3293 12.5 14.3293Z"
                            class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M19.5 4.32928H5.5C4.39543 4.32928 3.5 5.22472 3.5 6.32928V20.3293C3.5 21.4339 4.39543 22.3293 5.5 22.3293H19.5C20.6046 22.3293 21.5 21.4339 21.5 20.3293V6.32928C21.5 5.22472 20.6046 4.32928 19.5 4.32928Z"
                            class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    Contact Us
                </a>
            </li>

        </ul>
    </div>
</div>
</div>
</div>

<div class="bg-blue-main sm:hidden text-[10px] p-2">


    <div class="flex justify-between items-center">
        <div>
            <a href="tel:919956292768" class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">
                <svg width="12" height="12" viewBox="0 0 22 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.7875 19.5793C15.8778 19.5793 13.991 19.1631 12.1271 18.3308C10.2632 17.4985 8.56736 16.3181 7.03958 14.7897C5.51181 13.2613 4.33175 11.5655 3.49942 9.7022C2.66708 7.83892 2.25061 5.95212 2.25 4.04178C2.25 3.76678 2.34167 3.53762 2.525 3.35428C2.70833 3.17095 2.9375 3.07928 3.2125 3.07928H6.925C7.13889 3.07928 7.32986 3.15201 7.49792 3.29745C7.66597 3.4429 7.76528 3.61462 7.79583 3.81262L8.39167 7.02095C8.42222 7.2654 8.41458 7.47165 8.36875 7.6397C8.32292 7.80776 8.23889 7.9529 8.11667 8.07512L5.89375 10.321C6.19931 10.8862 6.562 11.4323 6.98183 11.959C7.40167 12.4858 7.86397 12.994 8.36875 13.4835C8.84236 13.9571 9.33889 14.3965 9.85833 14.8016C10.3778 15.2068 10.9278 15.5771 11.5083 15.9126L13.6625 13.7585C13.8 13.621 13.9797 13.518 14.2015 13.4495C14.4233 13.3811 14.6409 13.3618 14.8542 13.3918L18.0167 14.0335C18.2306 14.0946 18.4062 14.2055 18.5438 14.3662C18.6812 14.5269 18.75 14.7063 18.75 14.9043V18.6168C18.75 18.8918 18.6583 19.121 18.475 19.3043C18.2917 19.4876 18.0625 19.5793 17.7875 19.5793Z"
                        class="fill-white transition-all group-hover:fill-red-500" fill="white" />
                </svg>
                +91-9956292768

            </a>
        </div>

        <div>
            <ul class="flex  items-center gap-2">
                <li>
                    <a href="https://allenv.superhouseerp.com/"
                        class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                        <svg width="14" height="14" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.5 12.3293L11.5 8.32928M15.5 12.3293L11.5 16.3293M15.5 12.3293H5.5M10.5 21.3293C11.6819 21.3293 12.8522 21.0965 13.9442 20.6442C15.0361 20.1919 16.0282 19.529 16.864 18.6932C17.6997 17.8575 18.3626 16.8654 18.8149 15.7734C19.2672 14.6815 19.5 13.5112 19.5 12.3293C19.5 11.1474 19.2672 9.97706 18.8149 8.88513C18.3626 7.7932 17.6997 6.80105 16.864 5.96532C16.0282 5.1296 15.0361 4.46666 13.9442 4.01437C12.8522 3.56208 11.6819 3.32928 10.5 3.32928"
                                class=" transition-all group-hover:stroke-red-500" stroke="white" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        ERP Login
                    </a>
                </li>
                <li>
                    <a href="https://alumni.superhouseeducation.com/group/100110367.dz"
                        class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                        <svg width="14" height="14" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.5 19.7543V20.9793C18.5 21.2626 18.404 21.5003 18.212 21.6923C18.02 21.8843 17.7827 21.98 17.5 21.9793C17.2173 21.9786 16.98 21.883 16.788 21.6923C16.596 21.5016 16.5 21.264 16.5 20.9793V17.8293C16.5 17.4126 16.646 17.0586 16.938 16.7673C17.23 16.476 17.584 16.33 18 16.3293H21.15C21.4333 16.3293 21.671 16.4253 21.863 16.6173C22.055 16.8093 22.1507 17.0466 22.15 17.3293C22.1493 17.612 22.0537 17.8496 21.863 18.0423C21.6723 18.235 21.4347 18.3306 21.15 18.3293H19.9L22.15 20.5793C22.3333 20.7626 22.425 20.992 22.425 21.2673C22.425 21.5426 22.3333 21.78 22.15 21.9793C21.95 22.1793 21.7127 22.2793 21.438 22.2793C21.1633 22.2793 20.9257 22.1793 20.725 21.9793L18.5 19.7543ZM12.5 22.3293C11.1167 22.3293 9.81667 22.0666 8.6 21.5413C7.38334 21.016 6.325 20.3036 5.425 19.4043C4.525 18.505 3.81267 17.4466 3.288 16.2293C2.76333 15.012 2.50067 13.712 2.5 12.3293C2.49933 10.9466 2.762 9.64662 3.288 8.42929C3.814 7.21195 4.52633 6.15362 5.425 5.25428C6.32367 4.35495 7.382 3.64262 8.6 3.11728C9.818 2.59195 11.118 2.32928 12.5 2.32928C13.882 2.32928 15.182 2.59195 16.4 3.11728C17.618 3.64262 18.6763 4.35495 19.575 5.25428C20.4737 6.15362 21.1863 7.21195 21.713 8.42929C22.2397 9.64662 22.502 10.9466 22.5 12.3293C22.5 12.496 22.496 12.6793 22.488 12.8793C22.48 13.0793 22.4673 13.2626 22.45 13.4293C22.4167 13.7126 22.3 13.9336 22.1 14.0923C21.9 14.251 21.65 14.33 21.35 14.3293C21.0833 14.3293 20.8583 14.2126 20.675 13.9793C20.4917 13.746 20.4167 13.496 20.45 13.2293C20.4833 13.0626 20.5 12.9126 20.5 12.7793V12.3293C20.5 11.996 20.479 11.6626 20.437 11.3293C20.395 10.996 20.3327 10.6626 20.25 10.3293H16.85C16.9 10.6626 16.9377 10.996 16.963 11.3293C16.9883 11.6626 17.0007 11.996 17 12.3293V12.8673C17 13.0586 16.9917 13.2376 16.975 13.4043C16.9417 13.6876 16.825 13.9126 16.625 14.0793C16.425 14.246 16.1833 14.3293 15.9 14.3293C15.6333 14.3293 15.4043 14.221 15.213 14.0043C15.0217 13.7876 14.9423 13.546 14.975 13.2793C14.9917 13.1126 15 12.9543 15 12.8043V12.3293C15 11.996 14.9877 11.6626 14.963 11.3293C14.9383 10.996 14.9007 10.6626 14.85 10.3293H10.15C10.1 10.6626 10.0627 10.996 10.038 11.3293C10.0133 11.6626 10.0007 11.996 10 12.3293C9.99933 12.6626 10.012 12.996 10.038 13.3293C10.064 13.6626 10.1013 13.996 10.15 14.3293H12.5C12.7833 14.3293 13.021 14.4253 13.213 14.6173C13.405 14.8093 13.5007 15.0466 13.5 15.3293C13.4993 15.612 13.4033 15.8496 13.212 16.0423C13.0207 16.235 12.7833 16.3306 12.5 16.3293H10.6C10.8 17.046 11.0583 17.7336 11.375 18.3923C11.6917 19.051 12.0667 19.68 12.5 20.2793C12.6667 20.2793 12.8333 20.2836 13 20.2923C13.1667 20.301 13.3333 20.2966 13.5 20.2793C13.7833 20.246 14.0167 20.317 14.2 20.4923C14.3833 20.6676 14.475 20.8966 14.475 21.1793C14.475 21.4793 14.4 21.7293 14.25 21.9293C14.1 22.1293 13.8833 22.246 13.6 22.2793C13.4333 22.296 13.25 22.3086 13.05 22.3173C12.85 22.326 12.6667 22.33 12.5 22.3293ZM4.75 14.3293H8.15C8.1 13.996 8.06267 13.6626 8.038 13.3293C8.01333 12.996 8.00067 12.6626 8 12.3293C7.99933 11.996 8.012 11.6626 8.038 11.3293C8.064 10.996 8.10134 10.6626 8.15 10.3293H4.75C4.66667 10.6626 4.60433 10.996 4.563 11.3293C4.52167 11.6626 4.50067 11.996 4.5 12.3293C4.49933 12.6626 4.52033 12.996 4.563 13.3293C4.60567 13.6626 4.668 13.996 4.75 14.3293ZM9.9 19.8793C9.6 19.3126 9.33734 18.7336 9.112 18.1423C8.88667 17.551 8.69934 16.9466 8.55 16.3293H5.6C6.08333 17.1793 6.69167 17.9086 7.425 18.5173C8.15833 19.126 8.98333 19.58 9.9 19.8793ZM5.6 8.32928H8.55C8.7 7.71262 8.88767 7.10862 9.113 6.51728C9.33833 5.92595 9.60067 5.34662 9.9 4.77928C8.98333 5.07928 8.15833 5.53362 7.425 6.14228C6.69167 6.75095 6.08333 7.47995 5.6 8.32928ZM10.6 8.32928H14.4C14.2 7.61262 13.9417 6.92528 13.625 6.26728C13.3083 5.60928 12.9333 4.97995 12.5 4.37928C12.0667 4.97928 11.6917 5.60862 11.375 6.26728C11.0583 6.92595 10.8 7.61328 10.6 8.32928ZM16.45 8.32928H19.4C18.9167 7.47928 18.3083 6.75028 17.575 6.14228C16.8417 5.53428 16.0167 5.07995 15.1 4.77928C15.4 5.34595 15.6627 5.92528 15.888 6.51728C16.1133 7.10928 16.3007 7.71328 16.45 8.32928Z"
                                class="fill-white transition-all group-hover:fill-red-500" fill="white" stroke="#053B7A"
                                stroke-width="0.5" />
                        </svg>

                        Alumni Portal
                    </a>
                </li>
                <li>
                    <a href="contact-us" class="gap-1 flex items-center text-white transition-all  whitespace-nowrap">

                        <svg width="14" height="14" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16.5 2.32928V4.32928M7.5 22.3293V20.3293C7.5 19.7989 7.71071 19.2901 8.08579 18.9151C8.46086 18.54 8.96957 18.3293 9.5 18.3293H15.5C16.0304 18.3293 16.5391 18.54 16.9142 18.9151C17.2893 19.2901 17.5 19.7989 17.5 20.3293V22.3293M8.5 2.32928V4.32928"
                                class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M12.5 14.3293C14.1569 14.3293 15.5 12.9861 15.5 11.3293C15.5 9.67243 14.1569 8.32928 12.5 8.32928C10.8431 8.32928 9.5 9.67243 9.5 11.3293C9.5 12.9861 10.8431 14.3293 12.5 14.3293Z"
                                class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M19.5 4.32928H5.5C4.39543 4.32928 3.5 5.22472 3.5 6.32928V20.3293C3.5 21.4339 4.39543 22.3293 5.5 22.3293H19.5C20.6046 22.3293 21.5 21.4339 21.5 20.3293V6.32928C21.5 5.22472 20.6046 4.32928 19.5 4.32928Z"
                                class="stroke-white transition-all group-hover:stroke-red-500" stroke="white"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        Contact Us
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class=" overflow-hidden text-[10px] mt-1">
        <div class="w-full">
            <p class="animate-slide  text-white">

                We don’t just teach, we care, guide, and prepare them for life
            </p>
        </div>
    </div>

</div>

<header>
    <nav>
        <div class="logo">
            <a href="index"><img src="assets\images\logo.jpg" class="w-[200px]" alt=""></a>
        </div>
        <label for="menubrop" class="bartoggle">≡</label>
        <input type="checkbox" id="menubrop">

        <ul class="NavMenu MobileNav" id="NavMenu">
            <div class="md:hidden bg-white flex py-5 justify-between px-6">
                <a href="index"><img src="assets\images\logo.jpg" class="w-[200px]" alt=""></a>
                <button id="closeMenu"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                        viewBox="0 0 32 32">
                        <path fill="#f11e1e"
                            d="M16 2C8.2 2 2 8.2 2 16s6.2 14 14 14s14-6.2 14-14S23.8 2 16 2m5.4 21L16 17.6L10.6 23L9 21.4l5.4-5.4L9 10.6L10.6 9l5.4 5.4L21.4 9l1.6 1.6l-5.4 5.4l5.4 5.4z" />
                    </svg></button>
            </div>
            <li><a href="index" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Home</a></li>

            <li><a href="about-us" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">About Us</a></li>

            <li>
                <a href="#" class="group hover:text-white hover:bg-[#002a5b] p-2 transition-all block">
                    <label for="admission" class="flex items-center gap-1 cursor-pointer">
                        Admission
                        <svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg"
                            class="transition fill-white sm:fill-black group-hover:fill-white">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z" />
                        </svg>
                    </label>
                </a>
                <input type="checkbox" id="admission" class="hidden">
                <ul>
                    <li><a href="admission-procedure"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Admission
                            Procedure</a></li>
                    <li><a href="https://allenp.superhouseerp.com/RegistrationTransaction/OnlineRegistration"
                            target="_blank" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Online
                            Registration</a></li>
                    <li><a href="school-brochure" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">School
                            Brochure</a></li>
                    <li><a href="bus-route" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Bus
                            Route</a></li>
                    <li><a href="fee-structure" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Fee
                            Structure</a></li>
                    <li><a href="academic-calendar"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Academic
                            Calendar</a></li>
                    <li><a href="book-info" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Book List
                            Info</a></li>
                    <li><a href="transfer-policy"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Transfer
                            Policy</a></li>
                    <li><a href="transfer-certificate"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Transfer
                            Certificate</a></li>
                </ul>
            </li>



            <li>
                <a href="#" class="group hover:text-white hover:bg-[#002a5b] p-2 transition-all block">
                    <label for="academics" class="toggle flex items-center gap-1">
                        Academics
                        <svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg"
                            class="transition fill-white sm:fill-black group-hover:fill-white">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z" />
                        </svg>
                    </label>
                </a>
                <input type="checkbox" id="academics" class="hidden">
                <ul>
                    <li>
                        <a href="pedagogy" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Pedagogy</a>
                    </li>
                    <li>
                        <a href="facilities"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Facilities</a>
                    </li>
                    <li>
                        <a href="cambridge" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Cambridge
                            Curriculum</a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#" class="group hover:text-white hover:bg-[#002a5b] p-2 transition-all block">
                    <label for="beyond-academics" class="toggle flex items-center gap-1">
                        Beyond Academics
                        <svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg"
                            class="transition fill-white sm:fill-black group-hover:fill-white">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z" />
                        </svg>
                    </label>
                </a>
                <input type="checkbox" id="beyond-academics" class="hidden">
                <ul>
                    <li><a href="coding" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Coding
                            Academy</a></li>
                    <li><a href="animation-master-class"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Animation Academy</a></li>
                    <li><a href="robotics" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Robotics
                            Academy</a></li>
                    <li><a href="sports" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Sports
                            Academy</a></li>
                    <li><a href="clubs" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Clubs</a>
                    </li>
                    <li><a href="entrepreneur-dream-hub"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Entrepreneur's Dream Hub</a>
                    </li>
                    <li><a href="oluxi-smart-class" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Oluxi
                            Smart skills</a></li>
                </ul>
            </li>


            <li>
                <a href="#" class="group hover:text-white hover:bg-[#002a5b] p-2 transition-all block">
                    <label for="achievements" class="toggle flex items-center gap-1">
                        Achievements
                        <svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg"
                            class="transition fill-white sm:fill-black group-hover:fill-white">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z" />
                        </svg>
                    </label>
                </a>
                <input type="checkbox" id="achievements" class="hidden">
                <ul>
                    <li><a href="achievements"
                            class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Achievements</a>
                    </li>
                    <li><a href="awards" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Awards</a>
                    </li>
                    <li id="child-menu-li" class="relative">
                        <input type="checkbox" id="" class="peer hidden text-[#000]" />
                        <label for=""
                            class="block p-2 text-[14px] hover:text-white hover:bg-[#002a5b] transition-all cursor-pointer text-[#000] md:pl-[15px] pl-[20px]">
                            Board Toppers
                        </label>
                        <ul id="child-menu-ul" class="hidden peer-checked:block">
                            <li>
                                <a href="x-toppers"
                                    class="block p-2 hover:text-white hover:bg-[#002a5b] transition-all">X Toppers</a>
                            </li>
                            <li>
                                <a href="xii-toppers"
                                    class="block p-2 hover:text-white hover:bg-[#002a5b] transition-all">XII Toppers</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <script>
                var childUl = document.getElementById("child-menu-ul")
                document.getElementById("child-menu-li").addEventListener('click', function() {
                    childUl.style.display = "flex"
                })
            </script>

            <li>
                <a href="#" class="group hover:text-white hover:bg-[#002a5b] p-2 transition-all block">
                    <label for="academicsddd" class="toggle flex items-center gap-1">
                        Media & Events

                        <svg width="8" height="5" viewBox="0 0 8 5" xmlns="http://www.w3.org/2000/svg"
                            class="transition fill-white sm:fill-black group-hover:fill-white">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.699203 0.281451C0.825931 0.154761 0.997788 0.0835904 1.17698 0.0835904C1.35617 0.0835904 1.52803 0.154761 1.65476 0.281451L3.88011 2.5068L6.10546 0.281451C6.1678 0.216906 6.24237 0.165424 6.32482 0.130007C6.40727 0.0945895 6.49594 0.0759472 6.58567 0.0751674C6.6754 0.0743877 6.76439 0.091486 6.84744 0.125465C6.93049 0.159444 7.00595 0.209623 7.0694 0.273074C7.13285 0.336525 7.18303 0.411978 7.21701 0.495029C7.25099 0.57808 7.26808 0.667067 7.2673 0.756797C7.26652 0.846527 7.24788 0.935203 7.21246 1.01765C7.17705 1.1001 7.12557 1.17467 7.06102 1.23701L4.35789 3.94014C4.23116 4.06683 4.0593 4.138 3.88011 4.138C3.70092 4.138 3.52906 4.06683 3.40233 3.94014L0.699203 1.23701C0.572513 1.11028 0.501343 0.938422 0.501343 0.759229C0.501343 0.580035 0.572513 0.408179 0.699203 0.281451Z" />
                        </svg>
                    </label>
                </a>
                <input type="checkbox" id="academicsddd" class="hidden">
                <ul>
                    <li><a href="photo-gallery" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Photo
                            Gallery</a></li>
                    <li><a href="video-gallery" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Video
                            Gallery</a></li>
                    <li><a href="print-media" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Print
                            Media</a></li>
                    <li><a href="event" class="hover:text-white hover:bg-[#002a5b] p-2 transition-all">Events</a>
                    </li>
                </ul>
            </li>





            <div class="sm:ml-12 sm:mt-[-1px] mt-5 ml-5 flex sm:block hidden">
                <button onclick="openPopup()" class="px-4 py-2 bg-blue-main text-white ">Enquiry Form</button>
            </div>

            <!-- Popup Form -->
            <div id="popupForm"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-[9998]">
                <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative">
                    <!-- Close Button -->
                    <button id="closePopup" onclick="closePopup()"
                        class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl font-bold">
                        &times;
                    </button>

                    <h2 class="text-2xl font-semibold mb-4">Enquiry Form</h2>
                    <form class="space-y-4" id="enquiryPopupForm" onsubmit="return validateForm()">
                        <!-- Select Class -->
                        <div>
                            <select name="class-selection" id="classSelect" required
                                class="w-full border border-gray-300 p-2 rounded-md text-gray-500">
                                <option value="" disabled selected>Select Class</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                                <option value="VI">VI</option>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                            <span id="classError" class="text-red-500 text-sm hidden">Please select a class.</span>
                        </div>

                        <!-- Student Name -->
                        <div>
                            <input type="text" name="student-name" id="studentName" placeholder="Student Name"
                                class="w-full border border-gray-300 p-2 rounded-md" required>
                            <span id="studentNameError" class="text-red-500 text-sm hidden">Please enter the student's
                                name.</span>
                        </div>

                        <!-- Parent Name -->
                        <div>
                            <input type="text" name="parent-name" id="parentName" placeholder="Parents Name"
                                class="w-full border border-gray-300 p-2 rounded-md" required>
                            <span id="parentNameError" class="text-red-500 text-sm hidden">Please enter the parent's
                                name.</span>
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <input type="tel" name="mobile" id="mobile" placeholder="Mobile No."
                                class="w-full border border-gray-300 p-2 rounded-md" required
                                oninput="validateMobile()">
                            <span id="mobileError" class="text-red-500 text-sm hidden">Please enter a valid 10-digit
                                mobile number (no letters allowed).</span>
                        </div>

                        <!-- Email -->
                        <div>
                            <input type="email" name="email" id="email" placeholder="E-mail"
                                class="w-full border border-gray-300 p-2 rounded-md" required>
                            <span id="emailError" class="text-red-500 text-sm hidden">Please enter a valid email
                                address.</span>
                        </div>

                        <!-- Address -->
                        <div>
                            <textarea name="address" id="address" placeholder="Address" rows="3"
                                class="w-full border border-gray-300 p-2 rounded-md" required></textarea>
                            <span id="addressError" class="text-red-500 text-sm hidden">Please enter an address.</span>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="popupCheckbox" required />
                            <label for="popupCheckbox" class="text-sm">I agree to <a href="termsandconditions"
                                    class="text-blue-600 underline">Terms and Conditions</a>.</label>
                            <span id="checkboxError" class="text-red-500 text-sm hidden">You must agree to the terms and
                                conditions.</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-red-500 transition">Submit</button>
                    </form>
                </div>
            </div>
        </ul>
        </div>
    </nav>
</header>

<script>
    function openPopup() {
        document.getElementById("popupForm").classList.remove("hidden");
    }

    function closePopup() {
        document.getElementById("popupForm").classList.add("hidden");
    }
</script>