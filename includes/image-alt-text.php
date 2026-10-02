<?php

/**
 * CMS image alt text (same API host / branch id as includes/api.php).
 * Endpoint: image_alt_text/{BRANCH_ID}
 *
 * Expected payload (webpage linkage matches meta-data):
 * { "status":"success", "data": [
 *   { "webpage": { "name": "Home Page" }, "image_url": "https://...", "alt_text": "..." }
 * ]}
 * Also accepted: url/src, media.urls; alt/alternative_text/image_alt.
 * Rows with webpage.name restrict alts to that page; rows without apply for any page when the URL matches.
 */

if (!function_exists('fetchApiData')) {
    require_once __DIR__ . '/api.php';
}

require_once dirname(__DIR__) . '/proxy/config.php';
require_once dirname(__DIR__) . '/shared/includes/cms-image-alt.php';
require_once dirname(__DIR__) . '/shared/includes/cms-header-bulk.php';
const CMS_IMAGE_ALT_API_PATH = 'image_alt_text/';

/**
 * @param string $src
 * @param string $siteOrigin e.g. https://allenhousepanki.com
 */
function cms_normalize_image_src_url($src, $siteOrigin = 'https://allenhousepanki.com')
{
    $src = trim((string) $src);
    if ($src === '') {
        return '';
    }
    if (strpos($src, '//') === 0) {
        $src = 'https:' . $src;
    }
    if (!preg_match('#^https?://#i', $src)) {
        $path = str_replace('\\', '/', $src);
        $path = '/' . ltrim($path, '/');
        $src = rtrim($siteOrigin, '/') . $path;
    }
    $parts = parse_url($src);
    if (!$parts || empty($parts['host'])) {
        return rtrim($src, '/');
    }
    $scheme = strtolower($parts['scheme'] ?? 'https');
    $host = strtolower($parts['host']);
    $path = $parts['path'] ?? '/';
    $path = str_replace('\\', '/', $path);
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    return rtrim($scheme . '://' . $host . $path . $query, '/');
}

function cms_current_webpage_name_for_alt()
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $pageMap = include __DIR__ . '/cms-webpage-map.php';
    $base = basename($_SERVER['PHP_SELF'] ?? '', '.php');
    $cached = $pageMap[$base] ?? null;
    return $cached;
}

function cms_image_alt_row_image_url(array $row)
{
    if (!empty($row['image_url'])) {
        return $row['image_url'];
    }
    if (!empty($row['url'])) {
        return $row['url'];
    }
    if (!empty($row['src'])) {
        return $row['src'];
    }
    if (!empty($row['media']['urls'])) {
        return $row['media']['urls'];
    }
    return '';
}

function cms_image_alt_row_alt_text(array $row)
{
    if (function_exists('cms_media_alt_plain')) {
        $alt = cms_media_alt_plain($row, '');
        if ($alt !== '') {
            return $alt;
        }
    }

    foreach (['alt_text', 'alt', 'alternative_text', 'image_alt', 'image_alt_text', 'media_alt_text'] as $k) {
        if (!empty($row[$k]) && is_string($row[$k])) {
            return trim(strip_tags($row[$k]));
        }
    }

    return '';
}

/**
 * Build normalized URL => alt for the current request (filtered by CMS webpage name when set on rows).
 *
 * @return array<string, string>
 */
function cms_image_alt_client_map()
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = function_exists('cms_collect_image_alt_map_from_api_cache')
        ? cms_collect_image_alt_map_from_api_cache()
        : [];

    $resp = cmsBulkApiCacheGet('image_alt_data');
    if ($resp === null) {
        $resp = fetchApiData(CMS_IMAGE_ALT_API_PATH . BRANCH_ID);
    }
    if (
        $resp
        && ($resp['status'] ?? '') === 'success'
        && !empty($resp['data'])
        && is_array($resp['data'])
    ) {
        $pageName = cms_current_webpage_name_for_alt();
        foreach ($resp['data'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $wname = isset($row['webpage']['name']) ? trim((string) $row['webpage']['name']) : '';
            if ($wname !== '') {
                if ($pageName === null || strcasecmp($wname, $pageName) !== 0) {
                    continue;
                }
            }
            $urlRaw = cms_image_alt_row_image_url($row);
            $alt = cms_image_alt_row_alt_text($row);
            if ($urlRaw === '' || $alt === '') {
                continue;
            }
            if (function_exists('cms_image_alt_map_register_entry')) {
                cms_image_alt_map_register_entry($map, $urlRaw, $alt);
            } else {
                $norm = cms_normalize_image_src_url($urlRaw);
                if ($norm !== '') {
                    $map[$norm] = $alt;
                }
            }
        }
    }

    return $map;

}

/**
 * Echo one inline script; safe to call once per page. Uses DOMContentLoaded, load, and a debounced
 * MutationObserver so late / AJAX-injected <img> nodes still get alts when the CMS returns a match.
 */
function cms_print_image_alt_script()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $map = cms_image_alt_client_map();
    if ($map === []) {
        return;
    }

    $json = json_encode(
        $map,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    if ($json === false) {
        return;
    }

    echo '<script>(function(){var M=' . $json . ';function n(u){try{var a=new URL(u,window.location.href).href;return a.replace(/\\/$/,"");}catch(e){return"";}}function pk(u){try{return"path:"+new URL(u,window.location.href).pathname.replace(/\\/$/,"");}catch(e){return"";}}function setAlt(img){if(!img||!img.getAttribute)return;var s=img.getAttribute("src")||"";if(!s)return;var a=M[n(s)]||M[pk(s)];if(a&&!img.getAttribute("alt"))img.setAttribute("alt",a);}function applyAll(){document.querySelectorAll("img[src]").forEach(setAlt);}function applyAdded(nodes){nodes.forEach(function(node){if(!node||node.nodeType!==1)return;if(node.tagName==="IMG")setAlt(node);if(node.querySelectorAll)node.querySelectorAll("img[src]").forEach(setAlt);});}function go(){applyAll();if(!document.body)return;var t=null,observedMs=0,start=Date.now();var mo=new MutationObserver(function(muts){if(t)clearTimeout(t);t=setTimeout(function(){var adds=[];muts.forEach(function(m){if(m.addedNodes&&m.addedNodes.length)adds=adds.concat(Array.from(m.addedNodes));});if(adds.length)applyAdded(adds);observedMs=Date.now()-start;if(observedMs>12000){mo.disconnect();}},150);});mo.observe(document.body,{childList:true,subtree:true});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",go,{once:true});}else{go();}window.addEventListener("load",applyAll,{once:true});})();</script>' . "\n";
}
