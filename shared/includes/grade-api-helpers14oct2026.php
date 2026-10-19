<?php
declare(strict_types=1);

if (!function_exists('cms_grade_label_for_sort')) {
    function cms_grade_label_for_sort(string $label): string
    {
        $key = strtoupper(trim(preg_replace('/\s+/', ' ', $label) ?? $label));
        $aliases = [
            'P.G' => 'P.G',
            'P.G.' => 'P.G',
            'PG' => 'P.G',
            'P.G. I' => 'P.G-I',
            'P.G I' => 'P.G-I',
            'PG-I' => 'P.G-I',
            'PG I' => 'P.G-I',
            'P.G. II' => 'P.G-II',
            'P.G II' => 'P.G-II',
            'PG-II' => 'P.G-II',
            'PG II' => 'P.G-II',
        ];

        return $aliases[$key] ?? trim($label);
    }
}

if (!function_exists('cms_sort_grade_labels')) {
    /**
     * @param array<int, mixed> $labels
     * @return list<string>
     */
    function cms_sort_grade_labels(array $labels): array
    {
        $gradeOrder = [
            'P.G', 'P.G-I', 'P.G-II', 'Nursery', 'Prep',
            'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII',
        ];
        $unique = [];
        foreach ($labels as $label) {
            $label = trim((string) $label);
            if ($label !== '' && !in_array($label, $unique, true)) {
                $unique[] = $label;
            }
        }
        usort($unique, static function (string $a, string $b) use ($gradeOrder): int {
            $posA = array_search(cms_grade_label_for_sort($a), $gradeOrder, true);
            $posB = array_search(cms_grade_label_for_sort($b), $gradeOrder, true);
            if ($posA === false && $posB === false) {
                return strnatcasecmp($a, $b);
            }
            if ($posA === false) {
                return 1;
            }
            if ($posB === false) {
                return -1;
            }

            return $posA <=> $posB;
        });

        return $unique;
    }
}

if (!function_exists('cms_grades_require_board')) {
    function cms_grades_require_board(): bool
    {
        return defined('CMS_GRADES_REQUIRE_BOARD') && CMS_GRADES_REQUIRE_BOARD;
    }
}

if (!function_exists('cms_fetch_sorted_grade_labels')) {
    /**
     * Grades for a branch come from grades/{branch_id}; when a board is chosen, filter by board_id.
     *
     * @return list<string>
     */
    function cms_fetch_sorted_grade_labels(?string $boardId = null): array
    {
        $boardId = $boardId !== null ? trim($boardId) : '';

        if (cms_grades_require_board() && $boardId === '') {
            return [];
        }

        $response = null;
        if (function_exists('cms_curl_get_json') && defined('CMS_API_URL') && defined('BRANCH_ID')) {
            $response = cms_curl_get_json(
                rtrim(CMS_API_URL, '/') . '/grades/' . BRANCH_ID,
                6,
                true
            );
        }
        if (!is_array($response)) {
            $response = cms_fetch_json_endpoint('grades/' . BRANCH_ID);
        }
        if (!is_array($response)) {
            return [];
        }

        $labels = [];
        foreach (($response['data'] ?? []) as $grade) {
            if (!is_array($grade)) {
                continue;
            }
            if ($boardId !== '' && (string) ($grade['board_id'] ?? '') !== $boardId) {
                continue;
            }
            $label = trim((string) ($grade['grades'] ?? $grade['name'] ?? $grade['grade_name'] ?? ''));
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return cms_sort_grade_labels($labels);
    }
}

if (!function_exists('cms_render_grade_select_options')) {
    /**
     * @param list<string> $labels
     */
    function cms_render_grade_select_options(array $labels, string $placeholder = 'Select Grade'): void
    {
        $placeholder = trim($placeholder);
        if ($labels === []) {
            echo '<option value="" disabled selected>'
                . htmlspecialchars($placeholder !== '' ? $placeholder : 'Select Grade', ENT_QUOTES, 'UTF-8')
                . '</option>';

            return;
        }

        echo '<option value="" disabled selected>Select Grade</option>';
        foreach ($labels as $label) {
            if ($label === '') {
                continue;
            }
            echo '<option value="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</option>';
        }
    }
}

if (!function_exists('cms_grade_board_select_init_script')) {
    function cms_grade_board_select_init_script(): void
    {
        if (!cms_grades_require_board()) {
            return;
        }

        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
        <script>
        (function () {
            function gradeApiUrl() {
                if (window.CMS_GRADE_API_URL) {
                    return window.CMS_GRADE_API_URL;
                }
                var path = window.location.pathname || "";
                if (path.indexOf("admissions-open") !== -1) {
                    return "grade-api.php";
                }
                var base = window.SITE_BASE_URL || "";
                return base + "/includes/grade-api.php";
            }

            function bindBoardGradeLoader(boardEl, gradeEl) {
                if (!boardEl || !gradeEl || boardEl.dataset.gradeLoaderBound === "1") {
                    return;
                }
                boardEl.dataset.gradeLoaderBound = "1";

                function resetGrades(message) {
                    gradeEl.innerHTML = '<option value="" disabled selected>' + message + "</option>";
                }

                function loadGrades() {
                    var boardId = (boardEl.value || "").trim();
                    if (!boardId) {
                        resetGrades("Select board first");
                        return;
                    }
                    resetGrades("Loading grades...");
                    var url = gradeApiUrl() + "?board_id=" + encodeURIComponent(boardId);
                    fetch(url, { headers: { Accept: "text/html" }, credentials: "same-origin" })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error("grades unavailable");
                            }
                            return response.text();
                        })
                        .then(function (html) {
                            gradeEl.innerHTML = html;
                        })
                        .catch(function () {
                            resetGrades("Grades unavailable");
                        });
                }

                boardEl.addEventListener("change", loadGrades);
                if (!(boardEl.value || "").trim()) {
                    resetGrades("Select board first");
                }
            }

            function initAll() {
                bindBoardGradeLoader(document.getElementById("eboard"), document.getElementById("egrade"));
                bindBoardGradeLoader(document.getElementById("aboard"), document.getElementById("agrade"));
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initAll, { once: true });
            } else {
                initAll();
            }
        })();
        </script>
        <?php
    }
}
