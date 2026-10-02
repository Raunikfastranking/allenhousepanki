<?php
require_once dirname(__DIR__) . '/proxy/config.php';
require_once dirname(__DIR__) . '/shared/includes/grade-api-helpers.php';

$boardId = isset($_GET['board_id']) ? trim((string) $_GET['board_id']) : '';
$labels = cms_fetch_sorted_grade_labels($boardId !== '' ? $boardId : null);
$placeholder = cms_grades_require_board() && $boardId === '' ? 'Select board first' : 'Grades unavailable';

cms_render_grade_select_options($labels, $placeholder);
