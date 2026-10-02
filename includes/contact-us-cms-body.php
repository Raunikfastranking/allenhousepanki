<?php

declare(strict_types=1);

/** @var array<string, string> $contact */
/** @var string $introHtml */

$mapEmbed = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3571.737613661177!2d80.25274647596038!3d26.464185479220998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399c37e01176e6f5%3A0xa354b4d3e53a02ed!2sAllenhouse%20Public%20School%20Panki%2C%20Kanpur!5e0!3m2!1sen!2sin!4v1781687008433!5m2!1sen!2sin';
?>
<div class="<?= cmsContactWidthWrapClass() ?> mb-10 relative mt-10">
    <div class="sm:flex sm:mx-2">
        <div class="sm:w-[50%] sm:mt-4 mt-10">
            <?php if ($introHtml !== ''): ?>
            <div class="sm:text-left ql-snow mb-4">
                <div class="ql-editor cms-contact-intro"><?= $introHtml ?></div>
            </div>
            <?php else: ?>
            <div class="sm:text-left">
                <h2 class="sm:text-[32px] text-[28px] font-[700] text-blue-main uppercase leading-9">Let's talk with us</h2>
                <p class="sm:text-[20px] text-[18px] text-gray-500 font-[400] sm:mt-3 mt-2">Fill out the form &amp; we'll be in touch soon!</p>
            </div>
            <?php endif; ?>
            <div class="mt-4 sm:mt-6">
                <div class="flex gap-2 mb-3">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ZmL7hSpo40CpVDMhScotlXkFAz0XlzJyoGhO2Pq4.png" alt="" class="w-[28px] block h-[30px] mt-[3px]" loading="lazy">
                    <p class="text-gray-700 font-[400] sm:text-[18px] text-[16px]">
                        <?= htmlspecialchars($contact['addressline1'], ENT_QUOTES, 'UTF-8') ?><br>
                        <?= htmlspecialchars($contact['addressline2'], ENT_QUOTES, 'UTF-8') ?><?php if ($contact['pincode'] !== ''): ?> - <?= htmlspecialchars($contact['pincode'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                    </p>
                </div>
                <?php if ($contact['contact1'] !== ''): ?>
                <div class="flex gap-2 mb-3">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/oJ6mhPYvNt2N9dgrQ8zY1IeW1ds6f8l9E9yvAiDS.png" alt="" class="w-[23px] block h-[23px]" loading="lazy">
                    <p class="text-gray-700 font-[400] sm:text-[18px] text-[16px]">
                        <?php $contact1Digits = preg_replace('/\D+/', '', $contact['contact1']); ?>
                        <a href="tel:+91<?= htmlspecialchars(preg_replace('/\D+/', '', $contact['contact1']), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(str_starts_with($contact1Digits, '91') ? $contact['contact1'] : '+91-' . $contact['contact1'], ENT_QUOTES, 'UTF-8') ?><?php if ($contact['contact2'] !== ''): ?>, <?= htmlspecialchars($contact['contact2'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                <?php if ($contact['email'] !== ''): ?>
                <div class="flex gap-2">
                    <img src="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/Uk9fc8u92eMqkV3meFQNiUoRYB2DxzNi4d5T17Id.png" alt="" class="w-[23px] block h-[23px]" loading="lazy">
                    <p class="text-gray-700 font-[400] sm:text-[18px] text-[16px]">
                        <a href="mailto:<?= htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8') ?></a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sm:w-[50%]">
            <div class="mt-5">
                <form id="contactForm" novalidate>
                    <div>
                        <input type="text" id="cname" placeholder="Name" maxlength="50"
                            class="w-full border-[1px] p-[11px] rounded-[5px] outline-none" required>
                        <span id="cname-error" class="text-red-500 text-sm mt-1 hidden">Only letters and spaces allowed.</span>
                        <span id="cname-length-error" class="text-red-500 text-sm mt-1 hidden">Only 50 characters allowed.</span>
                    </div>

                    <div class="mt-4">
                        <input type="text" placeholder="Mobile" id="cmobile"
                            class="w-full border-[1px] p-[11px] rounded-[5px] outline-none" required maxlength="10">
                        <div id="mobileErrorss" class="text-red-500 text-sm mt-1 hidden">Please enter a valid 10-digit Indian mobile number.</div>
                    </div>

                    <div class="mt-4">
                        <input type="email" placeholder="E-mail" id="cemail"
                            class="w-full border-[1px] p-[11px] rounded-[5px] outline-none" required>
                        <span id="emailErrorss" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address.</span>
                    </div>

                    <div class="mt-4">
                        <select id="cquery" required
                            class="w-full border border-gray-300 p-2 rounded-md text-gray-500 text-[#808080d4]">
                            <option value="" disabled selected>Select Query</option>
                            <option value="General">General</option>
                            <option value="Admission Related">Admission Related</option>
                            <option value="Job Related">Job Related</option>
                            <option value="Suggestion/Feedback">Suggestion/Feedback</option>
                        </select>
                        <span id="cquery-error" class="text-red-500 text-sm mt-1 hidden">Please select a query type.</span>
                    </div>

                    <div class="mt-4">
                        <textarea id="cmessage" class="w-full border-[1px] p-[11px] rounded-[5px] outline-none" placeholder="Message" required rows="4"></textarea>
                    </div>

                    <div id="successPopup" class="relative bg-green-500 text-white px-4 py-2 rounded mb-5 hidden" style="z-index:999">
                        Form submitted successfully!
                    </div>

                    <div class="mt-4">
                        <button type="submit" id="contactSubmitBtn"
                            class="uppercase p-3 bg-blue-main w-full text-white font-[600] text-[18px] transition hover:bg-red-500">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-10">
    <iframe class="w-full" src="<?= htmlspecialchars($mapEmbed, ENT_QUOTES, 'UTF-8') ?>" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Allenhouse Public School Panki location"></iframe>
</div>
