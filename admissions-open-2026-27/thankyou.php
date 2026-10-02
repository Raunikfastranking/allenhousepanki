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
        'title_suffix' => 'Thankyou',
    ]);
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon"
    href="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ZORSdJXNGlZAVQJ4ZNWZsMEVZ1thgvBkL8JeOSXH.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="robots" content="noindex, nofollow" />
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TB7XFRX4');</script>
    <style>
        :root {
            --circle-width: 150px;
            --circle-height: 150px;
            --tick-width: calc(var(--circle-width) * 0.56);
            --tick-height: calc(var(--circle-height) * 0.24);
            --tick-thickness: calc(var(--tick-width) * 0.1);
            --circle-color: #fff;
            --tick-final-color: #28a745;
        }

        body {
            margin: 0;
            background: #fff;
            overflow-x: hidden;
            text-align: center;

        }

        .circle {
            background-color: var(--circle-color);
            width: var(--circle-width);
            height: var(--circle-height);
            display: inline-block;
            position: relative;
            border-radius: 50%;
            margin: 80px auto 20px;
            border: var(--tick-thickness) solid #28a745;
        }

        .tick {
            display: inline-block;
            width: 56%;
            height: 24%;
            border-left: var(--tick-thickness) solid var(--tick-final-color);
            border-bottom: var(--tick-thickness) solid var(--tick-final-color);
            position: absolute;
            left: calc(var(--circle-width) * 0.15);
            top: calc(var(--circle-height) / 2 - var(--tick-height));
            transform: rotate(-40deg);
            transform-origin: calc(var(--tick-width) * 0.57) calc(var(--tick-height) / 2);
            animation: tick-animation 1s ease forwards;
        }

        @keyframes tick-animation {
            0% {
                height: 0;
                width: 0;
                border-left-color: transparent;
                border-bottom-color: transparent;
            }

            50% {
                width: 0;
                height: var(--tick-height);
                border-left-color: var(--tick-final-color);
            }

            100% {
                width: var(--tick-width);
                border-color: var(--tick-final-color);
            }
        }
    </style>
</head>

<div id="esuccessPopup" class="relative fixed  bg-green-500 text-white px-4 py-2 rounded mb-5 hidden"
    style="z-index:9999; position: fixed; right: 0; top: 20%;">
    Form submitted successfully!
</div>

<body style="overflow-x: hidden">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TB7XFRX4"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <div class="main" style="overflow-x: hidden;">
        <header class="sticky top-0 z-90 md:px-5 w-[100%] bg-white md:h-[100px] border-b border-gray-300" style="z-index: 9999;">
            <div class="main-header flex md:justify-center justify-between px-3 items-center p-[8px]">
                <div class="md:flex md:w-[50%]">
                    <img src="assets/images/logo.jpg" class="md:w-[200px] w-[150px]" alt="logo">
                </div>
                <div class="md:w-[50%] flex justify-end">
                    <a href="<?= $brochure['data'][0]['url'] ?? "" ?>"
                        class="transition-all hover:bg-[#F4131B] bg-[#053B7A] text-[#fff] md:p-3 p-2  md:px-5 px-3 font-[500] md:text-[16px] text-[13px] rounded-[25px]"
                        style="white-space: nowrap;">Download E-Brochure</a>
                </div>
            </div>
        </header>

        <div>
            <div class="relative">
                <!-- Tick -->
                <div class="circle">
                    <div class="tick"></div>
                </div>


                 <div>
                    <h1 class="text-[#053B7A] lg:text-[100px] md:text-[80px] text-[50px]">Thank You</h1>
                    <p class="text-[#053B7A] lg:text-[35px] md:text-[26px] text-[20px] ">
                        Your enquiry has been successfully submitted.<br />
                        Our admissions team will get in touch with you shortly.
                    </p>
                    <!-- <img class="absolute left-1/2 " src="./assets/images/Vector.png" alt=""> -->
                </div>

                <div class="mt-10">
                    <button class="rounded-full md:p-4 md:px-10 p-2 px-6 bg-[#053B7A]">
                        <a href="/" class="text-white hover:text-red-500">
                            Visit Our Website
                        </a>
                    </button>
                </div>

                <div class="my-[100px] flex md:flex-row flex-col items-center justify-center lg:gap-5 gap-2">

                    <div >
                       
                            <button>
                                <a href="https://api.whatsapp.com/send?phone=<?= $whatsapp['data'][0]['number'] ?? "" ?>" class="flex items-center lg:gap-5 gap-2">
                                    <div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M0.852344 19.7608C0.851406 23.1216 1.73641 26.4031 3.41922 29.2955L0.691406 39.178L10.8839 36.5262C13.703 38.049 16.8616 38.8469 20.0714 38.8472H20.0798C30.6759 38.8472 39.3014 30.2917 39.3059 19.7759C39.308 14.6802 37.3098 9.88857 33.6795 6.28361C30.0498 2.67896 25.2225 0.692755 20.0791 0.69043C9.48172 0.69043 0.856875 9.24547 0.8525 19.7608"
                                            fill="url(#paint0_linear_1794_8113)" />
                                        <path
                                            d="M0.167188 19.7544C0.166094 23.2361 1.08281 26.635 2.82562 29.631L0 39.8678L10.558 37.1209C13.467 38.6947 16.7423 39.5245 20.0752 39.5257H20.0837C31.06 39.5257 39.9953 30.6625 40 19.7702C40.0019 14.4915 37.9319 9.5276 34.1719 5.79349C30.4114 2.05984 25.4114 0.00217054 20.0837 0C9.10562 0 0.171563 8.86202 0.167188 19.7544ZM6.45484 29.115L6.06062 28.4941C4.40344 25.8795 3.52875 22.8581 3.53 19.7557C3.53344 10.7022 10.9591 3.33643 20.09 3.33643C24.5119 3.33829 28.6675 5.04868 31.7931 8.15194C34.9186 11.2555 36.6384 15.3811 36.6373 19.769C36.6333 28.8225 29.2075 36.1891 20.0837 36.1891H20.0772C17.1064 36.1876 14.1928 35.396 11.6519 33.9L11.0472 33.5442L4.78188 35.1741L6.45484 29.115Z"
                                            fill="url(#paint1_linear_1794_8113)" />
                                        <path
                                            d="M15.107 11.4955C14.7342 10.6734 14.3419 10.6568 13.9873 10.6423C13.697 10.6299 13.3652 10.6309 13.0336 10.6309C12.7017 10.6309 12.1625 10.7548 11.7067 11.2485C11.2505 11.7428 9.96484 12.9372 9.96484 15.3665C9.96484 17.7958 11.7481 20.1437 11.9967 20.4735C12.2456 20.8027 15.4394 25.9475 20.4975 27.9267C24.7012 29.5715 25.5567 29.2444 26.4691 29.1619C27.3816 29.0797 29.4134 27.9678 29.828 26.8148C30.2428 25.6619 30.2428 24.6737 30.1184 24.4672C29.9941 24.2614 29.6622 24.1379 29.1645 23.891C28.6669 23.6442 26.2202 22.4495 25.7641 22.2847C25.3078 22.12 24.9761 22.0379 24.6442 22.5323C24.3123 23.0259 23.3594 24.1379 23.0689 24.4672C22.7787 24.7972 22.4883 24.8383 21.9908 24.5913C21.4928 24.3436 19.8902 23.8228 17.9888 22.1408C16.5094 20.832 15.5106 19.2157 15.2203 18.7213C14.93 18.2276 15.1892 17.96 15.4388 17.714C15.6623 17.4927 15.9366 17.1374 16.1856 16.8492C16.4338 16.5608 16.5166 16.3551 16.6825 16.0258C16.8486 15.6961 16.7655 15.4078 16.6413 15.1608C16.5166 14.9138 15.5495 12.4718 15.107 11.4955Z"
                                            fill="white" />
                                        <defs>
                                            <linearGradient id="paint0_linear_1794_8113" x1="1931.42" y1="3849.45"
                                                x2="1931.42" y2="0.69043" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#1FAF38" />
                                                <stop offset="1" stop-color="#60D669" />
                                            </linearGradient>
                                            <linearGradient id="paint1_linear_1794_8113" x1="2000" y1="3986.78"
                                                x2="2000" y2="0" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#F9F9F9" />
                                                <stop offset="1" stop-color="white" />
                                            </linearGradient>
                                        </defs>
                                    </svg>


                        </div>
                        <div>
                            <p class="text-[053B7A] lg:text-[20px]">Connect on Whatsapp!</p>
                        </div>
                        <div class="md:block hidden">
                            <img src="./assets/images/line.png" alt="">
                        </div>
                        </a>
                        </button>
                    </div>
                    <div >
                        <button>
                            <a href="<?= $brochure['data'][0]['url'] ?? "" ?>" class="flex items-center lg:gap-5 gap-2">
                                <div>


                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20.0013 25.9587C19.7791 25.9587 19.5707 25.9242 19.3763 25.8553C19.1819 25.7864 19.0013 25.6681 18.8346 25.5003L12.8346 19.5003C12.5013 19.167 12.3413 18.7781 12.3546 18.3337C12.368 17.8892 12.528 17.5003 12.8346 17.167C13.168 16.8337 13.5641 16.6603 14.023 16.647C14.4819 16.6337 14.8774 16.7931 15.2096 17.1253L18.3346 20.2503V8.33366C18.3346 7.86144 18.4946 7.46589 18.8146 7.147C19.1346 6.82811 19.5302 6.66811 20.0013 6.667C20.4724 6.66589 20.8685 6.82589 21.1896 7.147C21.5107 7.46811 21.6702 7.86366 21.668 8.33366V20.2503L24.793 17.1253C25.1263 16.792 25.5224 16.632 25.9813 16.6453C26.4402 16.6587 26.8357 16.8326 27.168 17.167C27.4735 17.5003 27.6335 17.8892 27.648 18.3337C27.6624 18.7781 27.5024 19.167 27.168 19.5003L21.168 25.5003C21.0013 25.667 20.8207 25.7853 20.6263 25.8553C20.4319 25.9253 20.2235 25.9598 20.0013 25.9587ZM10.0013 33.3337C9.08464 33.3337 8.30019 33.0076 7.64797 32.3553C6.99575 31.7031 6.66908 30.9181 6.66797 30.0003V26.667C6.66797 26.1948 6.82797 25.7992 7.14797 25.4803C7.46797 25.1614 7.86352 25.0014 8.33464 25.0003C8.80575 24.9992 9.20186 25.1592 9.52297 25.4803C9.84408 25.8014 10.0035 26.197 10.0013 26.667V30.0003H30.0013V26.667C30.0013 26.1948 30.1613 25.7992 30.4813 25.4803C30.8013 25.1614 31.1969 25.0014 31.668 25.0003C32.1391 24.9992 32.5352 25.1592 32.8563 25.4803C33.1774 25.8014 33.3369 26.197 33.3346 26.667V30.0003C33.3346 30.917 33.0085 31.702 32.3563 32.3553C31.7041 33.0087 30.9191 33.3348 30.0013 33.3337H10.0013Z"
                                            fill="black" />
                                    </svg>



                                </div>
                                <div>
                                    <p class="text-[053B7A] lg:text-[20px]">Download E-Brochure</p>
                                </div>
                                <div class="md:block hidden">
                                    <img src="./assets/images/line.png" alt="">
                                </div>
                            </a>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button>
                            <a href="<?= $fee['data'][0]['url'] ?? "" ?>" class="flex items-center lg:gap-5 gap-2 ">
                                <div >


                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20.0013 25.9587C19.7791 25.9587 19.5707 25.9242 19.3763 25.8553C19.1819 25.7864 19.0013 25.6681 18.8346 25.5003L12.8346 19.5003C12.5013 19.167 12.3413 18.7781 12.3546 18.3337C12.368 17.8892 12.528 17.5003 12.8346 17.167C13.168 16.8337 13.5641 16.6603 14.023 16.647C14.4819 16.6337 14.8774 16.7931 15.2096 17.1253L18.3346 20.2503V8.33366C18.3346 7.86144 18.4946 7.46589 18.8146 7.147C19.1346 6.82811 19.5302 6.66811 20.0013 6.667C20.4724 6.66589 20.8685 6.82589 21.1896 7.147C21.5107 7.46811 21.6702 7.86366 21.668 8.33366V20.2503L24.793 17.1253C25.1263 16.792 25.5224 16.632 25.9813 16.6453C26.4402 16.6587 26.8357 16.8326 27.168 17.167C27.4735 17.5003 27.6335 17.8892 27.648 18.3337C27.6624 18.7781 27.5024 19.167 27.168 19.5003L21.168 25.5003C21.0013 25.667 20.8207 25.7853 20.6263 25.8553C20.4319 25.9253 20.2235 25.9598 20.0013 25.9587ZM10.0013 33.3337C9.08464 33.3337 8.30019 33.0076 7.64797 32.3553C6.99575 31.7031 6.66908 30.9181 6.66797 30.0003V26.667C6.66797 26.1948 6.82797 25.7992 7.14797 25.4803C7.46797 25.1614 7.86352 25.0014 8.33464 25.0003C8.80575 24.9992 9.20186 25.1592 9.52297 25.4803C9.84408 25.8014 10.0035 26.197 10.0013 26.667V30.0003H30.0013V26.667C30.0013 26.1948 30.1613 25.7992 30.4813 25.4803C30.8013 25.1614 31.1969 25.0014 31.668 25.0003C32.1391 24.9992 32.5352 25.1592 32.8563 25.4803C33.1774 25.8014 33.3369 26.197 33.3346 26.667V30.0003C33.3346 30.917 33.0085 31.702 32.3563 32.3553C31.7041 33.0087 30.9191 33.3348 30.0013 33.3337H10.0013Z"
                                            fill="black" />
                                    </svg>



                                </div>
                                <div>
                                    <p class="text-[053B7A] lg:text-[20px]">Download Fee Structure</p>
                                </div>
                            </a>
                        </button>
                    </div>

                </div>
                <div class="absolute left-[0px] top-[0px]">
                    <img src="./assets/images/left.png" alt="" />
                </div>

                <div class="absolute right-[0px] bottom-[-100px]">
                    <img src="./assets/images/right.png" alt="" />
                </div>
            </div>

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

            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

            <script>
                const tick = document.querySelector(".tick");

                tick.addEventListener("animationend", () => {
                    confetti({
                        particleCount: 250, // fewer confetti
                        startVelocity: 35, // softer speed
                        spread: 150, // tighter focus
                        origin: { x: 0.5, y: 0.4 }, // center above Thank You
                    });
                });
            </script>
    <?php
    $page = 'admissions-open-2026-27-thankyou';
    include __DIR__ . '/../includes/page-image-alt-script.php';
    ?>
</body>

</html>