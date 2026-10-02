<?php

declare(strict_types=1);

/**
 * CMS gallery sub-type IDs (global across branches; filter by branch_id in API responses).
 *
 * @return array<string, int>
 */
function cmsGallerySubtypeIds(): array
{
    return [
        'achievements' => 1,
        'awards' => 2,
        'x_toppers' => 3,
        'xii_toppers' => 4,
        'photo' => 5,
        'photo_gallery' => 5,
        'video' => 6,
        'video_gallery' => 6,
        'print' => 7,
        'print_media' => 7,
        'events' => 8,
    ];
}

/** Inline JS object for head.php */
function cmsGallerySubtypeJsObject(): string
{
    $map = cmsGallerySubtypeIds();
    $pairs = [];
    foreach ($map as $key => $id) {
        $pairs[] = $key . ': ' . $id;
    }
    return '{ ' . implode(', ', $pairs) . ' }';
}
