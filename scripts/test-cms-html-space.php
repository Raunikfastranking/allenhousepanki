<?php
require_once dirname(__DIR__) . '/shared/includes/encoding.php';

$cases = [
    '<p><a href="#">campuses</a> <strong>combine</strong></p>',
    '<h2><strong><span>5.</span> <span>How</span></strong></h2>',
    '<h2><strong>5. How does</strong></h2>',
    '<p>word</a> </span> next</p>',
];

foreach ($cases as $html) {
    $out = cmsPrepareCmsHtml($html);
    echo "IN:  $html\nOUT: $out\n\n";
}