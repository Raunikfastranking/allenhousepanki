<?php

declare(strict_types=1);

if (!function_exists('site_asset_url')) {
    require_once dirname(__DIR__, 2) . '/includes/environment.php';
}

$tcProxyUrl = function_exists('site_asset_url')
    ? site_asset_url('proxy/tc-proxy.php')
    : '/proxy/tc-proxy.php';
?>
<script>
(function () {
    var proxyUrl = <?= json_encode($tcProxyUrl, JSON_UNESCAPED_SLASHES) ?>;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function parseJsonResponse(response, body) {
        var contentType = (response.headers.get('content-type') || '').toLowerCase();
        if (contentType.indexOf('application/json') === -1 && body.trim().charAt(0) !== '{') {
            return null;
        }
        try {
            return JSON.parse(body);
        } catch (error) {
            return null;
        }
    }

    function initTcSearch() {
        var form = document.getElementById('transfer-Form');
        if (!form || form.dataset.bound === '1') {
            return;
        }
        form.dataset.bound = '1';

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            var admissionNo = (document.getElementById('admissionNo') || {}).value;
            admissionNo = admissionNo ? admissionNo.trim() : '';
            var dobInput = document.querySelector('input[name="dob"]');
            var dob = dobInput && dobInput.value ? dobInput.value.trim() : '';
            var resultContainer = document.getElementById('resultContainer');
            var noResult = document.getElementById('noResult');
            var resultTableBody = document.getElementById('resultTableBody');
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalBtnText = submitBtn ? submitBtn.textContent : 'Search';

            if (!resultContainer || !noResult || !resultTableBody) {
                return;
            }

            resultContainer.classList.add('hidden');
            noResult.classList.add('hidden');
            noResult.textContent = 'No data found.';
            resultTableBody.innerHTML = '';

            if (!admissionNo || !dob) {
                noResult.textContent = 'Please enter admission number and date of birth.';
                noResult.classList.remove('hidden');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Searching...';
            }

            try {
                var response = await fetch(proxyUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ admission_no: admissionNo, dob: dob }),
                    credentials: 'same-origin'
                });
                var body = await response.text();
                var resData = parseJsonResponse(response, body);

                if (!resData) {
                    noResult.textContent = 'Unable to reach the transfer certificate service. Please try again.';
                    noResult.classList.remove('hidden');
                    return;
                }

                if (!response.ok || !resData.success || !resData.data) {
                    noResult.textContent = resData.message || 'No data found.';
                    noResult.classList.remove('hidden');
                    return;
                }

                var matched = resData.data;
                var downloadUrl = matched.url ? escapeHtml(matched.url) : '#';
                var row = '<tr class="odd:bg-white even:bg-gray-200 text-gray-700 border-b text-[16px] text-center">' +
                    '<td class="px-6 py-2">' + escapeHtml(matched.student_name) + '</td>' +
                    '<td class="px-6 py-2">' + escapeHtml(matched.admission_no) + '</td>' +
                    '<td class="px-6 py-2">' + escapeHtml(matched.class) + '</td>' +
                    '<td class="px-6 py-2">' + escapeHtml(matched.parent_name) + '</td>' +
                    '<td class="px-6 py-2">' + escapeHtml(matched.dob) + '</td>' +
                    '<td class="px-6 py-2"><a href="' + downloadUrl + '" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Download</a></td>' +
                    '</tr>';
                resultTableBody.insertAdjacentHTML('beforeend', row);
                resultContainer.classList.remove('hidden');
            } catch (error) {
                console.error('Error fetching TC data:', error);
                noResult.textContent = 'Something went wrong. Please try again.';
                noResult.classList.remove('hidden');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTcSearch, { once: true });
    } else {
        initTcSearch();
    }
})();
</script>
