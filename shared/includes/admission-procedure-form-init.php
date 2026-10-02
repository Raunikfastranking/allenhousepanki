<?php

if (!function_exists('admissionProcedureFormInitScript')) {
    function admissionProcedureFormInitScript(?string $siteBaseUrl = null): void
    {
        if ($siteBaseUrl === null || $siteBaseUrl === '') {
            if (function_exists('site_origin_url')) {
                $siteBaseUrl = site_origin_url();
            } else {
                $siteBaseUrl = '';
            }
        }
        $siteBaseJson = json_encode($siteBaseUrl, JSON_UNESCAPED_SLASHES);
        ?>
<script>
(function () {
    var SITE_BASE_URL = <?= $siteBaseJson ?>;
    function byId(id) { return document.getElementById(id); }

    function initAdmissionCitySelect() {
        var root = byId("admissionProcedureEnquiry");
        if (!root) return;
        var wrapper = byId("admissionCityWrapper")
            || root.querySelector("[data-cms-city-dropdown]")
            || root.querySelector(".customSelect");
        if (!wrapper || wrapper.dataset.bound === "1") return;

        var realSelect = wrapper.querySelector("select") || byId("acity");
        var trigger = byId("admissionCityTrigger")
            || wrapper.querySelector(".cursor-pointer.flex")
            || wrapper.querySelector(".border.border-gray-300");
        var dropdown = byId("admissionCityDropdown") || wrapper.querySelector("div.absolute");
        var searchInput = byId("admissionCitySearch") || (dropdown ? dropdown.querySelector("input") : null);
        var optionsList = byId("admissionCityList") || (dropdown ? dropdown.querySelector("ul") : null);
        var display = trigger ? trigger.querySelector(".selected-text") : wrapper.querySelector(".selected-text");
        if (!realSelect || !display || !dropdown || !searchInput || !optionsList || !trigger) return;

        wrapper.dataset.bound = "1";

        function loadOptions(filter) {
            optionsList.innerHTML = "";
            var hasMatches = false;
            Array.from(realSelect.options).forEach(function (opt) {
                if (!opt.value) return;
                if (opt.text.toLowerCase().indexOf((filter || "").toLowerCase()) === -1) return;
                hasMatches = true;
                var li = document.createElement("li");
                li.textContent = opt.text;
                li.className = "p-2 hover:bg-gray-100 cursor-pointer";
                li.dataset.value = opt.value;
                optionsList.appendChild(li);
            });
            if (!hasMatches) {
                var empty = document.createElement("li");
                empty.className = "p-2 text-gray-400 text-sm";
                empty.textContent = "No cities available";
                optionsList.appendChild(empty);
            }
        }

        function bindCityDropdown() {
            trigger.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle("hidden");
                searchInput.value = "";
                loadOptions("");
                if (!dropdown.classList.contains("hidden")) {
                    searchInput.focus();
                }
            });
            searchInput.addEventListener("input", function () {
                loadOptions(searchInput.value);
            });
            optionsList.addEventListener("click", function (e) {
                if (e.target.tagName !== "LI" || !e.target.dataset.value) return;
                realSelect.value = e.target.dataset.value;
                display.textContent = e.target.textContent;
                display.classList.remove("text-[#808080cc]");
                dropdown.classList.add("hidden");
                realSelect.dispatchEvent(new Event("change", { bubbles: true }));
            });
            document.addEventListener("click", function (e) {
                if (!wrapper.contains(e.target)) dropdown.classList.add("hidden");
            });
            loadOptions("");
        }

        function ensureCityOptions(done) {
            if (realSelect.options.length > 1) {
                done();
                return;
            }
            var siteBase = SITE_BASE_URL || "";
            fetch(siteBase + "/includes/get-city.php", { credentials: "same-origin" })
                .then(function (response) {
                    if (!response.ok) throw new Error("City API failed");
                    return response.text();
                })
                .then(function (html) {
                    if (html && html.indexOf("<option") !== -1) {
                        realSelect.innerHTML = '<option value="">Select City</option>' + html;
                    }
                })
                .catch(function (err) {
                    console.error("Admission city API load failed:", err);
                })
                .finally(done);
        }

        ensureCityOptions(bindCityDropdown);
    }

    function initAdmissionLeadSource() {
        var sourceInput = byId("source");
        if (!sourceInput) {
            return;
        }
        var params = new URLSearchParams(window.location.search);
        var source = params.get("utm_source") || document.referrer || "Website";
        var srcLower = String(source).toLowerCase();
        if (srcLower.includes("google")) {
            source = "Google-Ads by Agency";
        } else if (srcLower.includes("facebook") || srcLower.includes("meta")) {
            source = "Facebook by Agency";
        } else if (srcLower.includes("instagram") || srcLower.includes("ig")) {
            source = "Instagram by Agency";
        } else if (source && source !== "Website") {
            source = "Others";
        }
        if (!sessionStorage.getItem("leadSource")) {
            sessionStorage.setItem("leadSource", source);
        }
        sourceInput.value = sessionStorage.getItem("leadSource") || "Website";
    }

    function initAdmissionProcedureForm() {
        initAdmissionLeadSource();
        initAdmissionCitySelect();
        var form = byId("AdmissionForm");
        if (!form || form.dataset.bound === "1") return;
        form.dataset.bound = "1";

        var submitBtn = byId("AsubmitBtn");
        var regex = {
            name: /^[A-Za-z\s]+$/,
            mobile: /^[6-9]\d{9}$/,
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            pincode: /^[1-9][0-9]{5}$/
        };

        var session = byId("asession");
        var board = byId("aboard");
        var grade = byId("agrade");
        var student = byId("astudent_name");
        var parent = byId("aparent_name");
        var mobile = byId("amobile");
        var email = byId("aemail");
        var city = byId("acity");
        var pincode = byId("apincode");

        function bindInputValidation(input, test, errorId, normalizer) {
            var errorEl = byId(errorId);
            if (!input || !errorEl) return;
            input.addEventListener("input", function () {
                if (typeof normalizer === "function") normalizer(input);
                errorEl.classList.toggle("hidden", !input.value || test(input.value));
            });
        }

        bindInputValidation(student, function (v) { return regex.name.test(v); }, "astudent-error");
        bindInputValidation(parent, function (v) { return regex.name.test(v); }, "aparent-error");
        bindInputValidation(mobile, function (v) { return regex.mobile.test(v); }, "amobile-error", function (el) { el.value = el.value.replace(/\D/g, "").slice(0, 10); });
        bindInputValidation(email, function (v) { return regex.email.test(v); }, "aemail-error", function (el) { el.value = el.value.toLowerCase(); });
        bindInputValidation(pincode, function (v) { return regex.pincode.test(v); }, "apincode-error", function (el) { el.value = el.value.replace(/\D/g, "").slice(0, 6); });

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var payloadFields = {
                session: (session && session.value || "").trim(),
                grade: (grade && grade.value || "").trim(),
                student_name: (student && student.value || "").trim(),
                parent_name: (parent && parent.value || "").trim(),
                email: (email && email.value || "").trim(),
                phone: (mobile && mobile.value || "").trim(),
                city: (city && city.value || "").trim(),
                pincode: (pincode && pincode.value || "").trim(),
                source: sessionStorage.getItem("leadSource") || "website",
                source_type: "organic"
            };
            if (board) {
                payloadFields.board = (board.value || "").trim();
            }
            var payload = window.cmsBuildEnquiryPayload(payloadFields);

            var terms = byId("admissionTerms");
            var termsError = byId("admissionTermsError");
            if (terms && !terms.checked) {
                if (termsError) termsError.classList.remove("hidden");
                return;
            }
            if (termsError) termsError.classList.add("hidden");

            var classError = byId("aclassError");
            var boardError = byId("aboard-error");
            var cityError = byId("acity-error");
            var valid = regex.name.test(payload.studentName)
                && (payload.parentName === "" || regex.name.test(payload.parentName))
                && regex.mobile.test(payload.mobile)
                && (payload.email === "" || regex.email.test(payload.email))
                && regex.pincode.test(payload.pincode)
                && (!board || (payload.board || "") !== "")
                && payload.grade !== ""
                && payload.session !== ""
                && payload.city !== "";

            if (!valid) {
                if (boardError) boardError.classList.toggle("hidden", !board || (payload.board || "") !== "");
                if (classError) classError.classList.toggle("hidden", payload.grade !== "");
                if (cityError) cityError.classList.toggle("hidden", payload.city !== "");
                return;
            }
            if (boardError) boardError.classList.add("hidden");
            if (classError) classError.classList.add("hidden");
            if (cityError) cityError.classList.add("hidden");

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = "Submitting...";
            }

            var siteBase = SITE_BASE_URL || "";
            fetch((window.CMS_FORM_PROXIES && window.CMS_FORM_PROXIES.admission) || (siteBase + "/proxy/admission-proxy.php"), {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(function (response) {
                return window.cmsParseProxyJson(response);
            })
            .then(function () {
                var popup = byId("AdmissionFormPopup");
                if (popup) {
                    popup.classList.remove("hidden");
                    setTimeout(function () { popup.classList.add("hidden"); }, 5000);
                }
                form.reset();
                var cityDisplay = document.querySelector("#admissionProcedureEnquiry .customSelect .selected-text");
                if (cityDisplay) {
                    cityDisplay.textContent = "Select City";
                    cityDisplay.classList.add("text-[#808080cc]");
                }
                sessionStorage.removeItem("leadSource");
            })
            .catch(function (err) {
                console.error("Admission procedure form submit failed:", err);
                alert(err.message || "Unable to submit enquiry. Please try again.");
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Submit";
                }
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initAdmissionProcedureForm, { once: true });
    } else {
        initAdmissionProcedureForm();
    }
})();
</script>
        <?php
            require_once __DIR__ . '/grade-api-helpers.php';
        cms_grade_board_select_init_script();
    }
}
