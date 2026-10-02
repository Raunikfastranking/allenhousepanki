<?php

declare(strict_types=1);

if (!function_exists('cmsEnsureUtf8Response')) {
    function cmsEnsureUtf8Response(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        if (function_exists('ini_set')) {
            @ini_set('default_charset', 'UTF-8');
        }
        if (function_exists('mb_internal_encoding')) {
            @mb_internal_encoding('UTF-8');
        }

        if (!headers_sent()) {
            $headers = headers_list();
            $hasCharset = false;
            foreach ($headers as $header) {
                if (stripos($header, 'Content-Type:') === 0 && stripos($header, 'charset=') !== false) {
                    $hasCharset = true;
                    break;
                }
            }
            if (!$hasCharset) {
                header('Content-Type: text/html; charset=UTF-8');
            }
        }
    }
}

if (!function_exists('cmsFixUtf8Mojibake')) {
    /**
     * Repair UTF-8 text that was mis-decoded as Windows-1252 (e.g. 2026â€“2027 → 2026–2027).
     */
    function cmsFixUtf8Mojibake(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = (string) $text;
        if (!preg_match('/(?:â€|â–|Ã.|Â[\x80-\xBF])/u', $text)) {
            return $text;
        }

        static $map = null;
        if ($map === null) {
            $map = [
                'â€"' => '–',
                'â€"' => '—',
                'â€“' => '–',
                'â€”' => '—',
                'â€™' => "'",
                'â€˜' => "'",
                'â€œ' => '"',
                'â€' => '"',
                'â€¦' => '…',
                'â–º' => '▼',
                'â–¼' => '▼',
                'â–¶' => '▶',
                'Â©' => '©',
                'Â®' => '®',
                'Â°' => '°',
                'Â·' => '·',
                'Â ' => ' ',
            ];
            if (function_exists('mb_convert_encoding')) {
                $dash = mb_convert_encoding("\xE2\x80\x93", 'UTF-8', 'Windows-1252');
                if (is_string($dash) && $dash !== '') {
                    $map[$dash] = '–';
                }
            }
        }

        return strtr($text, $map);
    }
}

if (!function_exists('cmsFixUtf8Deep')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function cmsFixUtf8Deep(mixed $value): mixed
    {
        if (is_string($value)) {
            return cmsFixUtf8Mojibake($value);
        }
        if (!is_array($value)) {
            return $value;
        }
        foreach ($value as $key => $item) {
            $value[$key] = cmsFixUtf8Deep($item);
        }

        return $value;
    }
}

if (!function_exists('cmsPreserveHtmlTextSpaces')) {
    /**
     * Turn runs of 2+ spaces in HTML text nodes into nbsp so they survive white-space: normal.
     */
    function cmsPreserveHtmlTextSpaces(string $html): string
    {
        return (string) preg_replace_callback(
            '/>([^<]+)</u',
            static function (array $matches): string {
                $text = $matches[1];
                if (!str_contains($text, '  ')) {
                    return '>' . $text . '<';
                }

                $preserved = preg_replace_callback(
                    '/ {2,}/',
                    static function (array $spaceMatch): string {
                        return ' ' . str_repeat("\xC2\xA0", strlen($spaceMatch[0]) - 1);
                    },
                    $text
                );

                return '>' . ($preserved ?? $text) . '<';
            },
            $html
        );
    }
}

if (!function_exists('cmsEscPlain')) {
    function cmsEscPlain(?string $text): string
    {
        return htmlspecialchars(cmsPrepareCmsPlainText($text), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cmsDomMeaningfulChildren')) {
    /**
     * @return DOMNode[]
     */
    function cmsDomMeaningfulChildren(DOMNode $parent): array
    {
        $nodes = [];
        foreach ($parent->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE && trim($child->textContent ?? '') === '') {
                continue;
            }
            if ($child->nodeType === XML_COMMENT_NODE) {
                continue;
            }
            $nodes[] = $child;
        }

        return $nodes;
    }
}

if (!function_exists('cmsDomNodeTextIsEmpty')) {
    function cmsDomNodeTextIsEmpty(DOMNode $node): bool
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return trim($node->textContent ?? '') === '';
        }
        if ($node->nodeType === XML_ELEMENT_NODE) {
            foreach ($node->childNodes as $child) {
                if (!cmsDomNodeTextIsEmpty($child)) {
                    return false;
                }
            }

            return true;
        }

        return trim($node->textContent ?? '') === '';
    }
}

if (!function_exists('cmsDomInnerWrappedTable')) {
    function cmsDomInnerWrappedTable(DOMElement $table): ?DOMElement
    {
        if (strtolower($table->tagName) !== 'table') {
            return null;
        }

        $tbody = null;
        foreach (cmsDomMeaningfulChildren($table) as $child) {
            if (!($child instanceof DOMElement)) {
                return null;
            }
            $tag = strtolower($child->tagName);
            if ($tag === 'colgroup' || $tag === 'thead' || $tag === 'tfoot') {
                continue;
            }
            if ($tag === 'tbody') {
                if ($tbody !== null) {
                    return null;
                }
                $tbody = $child;
                continue;
            }

            return null;
        }

        if (!$tbody instanceof DOMElement) {
            return null;
        }

        $rows = cmsDomMeaningfulChildren($tbody);
        if (count($rows) !== 1 || !($rows[0] instanceof DOMElement) || strtolower($rows[0]->tagName) !== 'tr') {
            return null;
        }

        $cells = cmsDomMeaningfulChildren($rows[0]);
        if (count($cells) !== 1 || !($cells[0] instanceof DOMElement)) {
            return null;
        }

        $cellTag = strtolower($cells[0]->tagName);
        if ($cellTag !== 'td' && $cellTag !== 'th') {
            return null;
        }

        $cellChildren = cmsDomMeaningfulChildren($cells[0]);
        if (count($cellChildren) !== 1
            || !($cellChildren[0] instanceof DOMElement)
            || strtolower($cellChildren[0]->tagName) !== 'table') {
            return null;
        }

        return $cellChildren[0];
    }
}

if (!function_exists('cmsDomTableIsEmpty')) {
    function cmsDomTableIsEmpty(DOMElement $table): bool
    {
        foreach ($table->getElementsByTagName('td') as $td) {
            if ($td instanceof DOMElement && !cmsDomNodeTextIsEmpty($td)) {
                return false;
            }
        }
        foreach ($table->getElementsByTagName('th') as $th) {
            if ($th instanceof DOMElement && !cmsDomNodeTextIsEmpty($th)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('cmsDomWrapPercentWidthTables')) {
    function cmsDomWrapPercentWidthTables(DOMElement $root, DOMDocument $doc): void
    {
        $tables = [];
        foreach ($root->getElementsByTagName('table') as $table) {
            if ($table instanceof DOMElement) {
                $tables[] = $table;
            }
        }

        foreach ($tables as $table) {
            $parent = $table->parentNode;
            if (!$parent instanceof DOMElement) {
                continue;
            }
            if ($parent->getAttribute('class') === 'cms-table-host') {
                continue;
            }

            $style = strtolower($table->getAttribute('style'));
            if ($style === '' || !preg_match('/(?:^|;)\s*width\s*:\s*[\d.]+%/', $style)) {
                continue;
            }

            $host = $doc->createElement('div');
            $host->setAttribute('class', 'cms-table-host');
            $parent->replaceChild($host, $table);
            $host->appendChild($table);
        }
    }
}

if (!function_exists('cmsCleanupEditorTables')) {
    /**
     * Remove empty Quill tables and unwrap single-cell wrapper tables around real tables.
     */
    function cmsCleanupEditorTables(string $html): string
    {
        if (!str_contains($html, '<table')) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $wrapped = '<div id="cms-table-root">' . $html . '</div>';
        $loaded = @$doc->loadHTML(
            '<?xml encoding="UTF-8">' . $wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return $html;
        }

        $root = $doc->getElementById('cms-table-root');
        if (!$root instanceof DOMElement) {
            return $html;
        }

        $changed = true;
        while ($changed) {
            $changed = false;

            $tables = [];
            foreach ($root->getElementsByTagName('table') as $table) {
                if ($table instanceof DOMElement) {
                    $tables[] = $table;
                }
            }

            foreach ($tables as $table) {
                $parent = $table->parentNode;
                if (!$parent instanceof DOMNode) {
                    continue;
                }

                if (cmsDomTableIsEmpty($table)) {
                    $parent->removeChild($table);
                    $changed = true;
                    break;
                }

                $inner = cmsDomInnerWrappedTable($table);
                if ($inner instanceof DOMElement) {
                    $host = $doc->createElement('div');
                    $host->setAttribute('class', 'cms-table-host');
                    $replacement = $inner->cloneNode(true);
                    if ($replacement instanceof DOMNode) {
                        $host->appendChild($replacement);
                        $parent->replaceChild($host, $table);
                        $changed = true;
                        break;
                    }
                }
            }
        }

        cmsDomWrapPercentWidthTables($root, $doc);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }

        return $output !== '' ? $output : $html;
    }
}

if (!function_exists('cmsRewriteEditorInlineImages')) {
    function cmsRewriteEditorInlineImages(string $html): string
    {
        if ($html === '' || stripos($html, '<img') === false) {
            return $html;
        }

        if (!function_exists('cmsPrepareEditorInlineImageSrc')) {
            $mediaUrlFile = __DIR__ . '/cms-media-url.php';
            if (is_file($mediaUrlFile)) {
                require_once $mediaUrlFile;
            }
        }
        if (!function_exists('cmsPrepareEditorInlineImageSrc')) {
            return $html;
        }

        return preg_replace_callback(
            '/<img\b([^>]*?\bsrc=)(["\'])(.*?)\2/i',
            static function (array $match): string {
                $raw = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $prepared = cmsPrepareEditorInlineImageSrc($raw);
                if ($prepared === null || $prepared === '') {
                    return $match[0];
                }
                if ($prepared === $raw) {
                    return $match[0];
                }

                return '<img' . $match[1] . $match[2] . htmlspecialchars($prepared, ENT_QUOTES, 'UTF-8') . $match[2];
            },
            $html
        ) ?? $html;
    }
}

if (!function_exists('cmsPrepareCmsHtml')) {
    function cmsPrepareCmsHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $html = cmsFixUtf8Mojibake((string) $html);

        // Preserve Quill/editor spacing (&nbsp;) before entity decode.
        $html = preg_replace('/&(?:nbsp|#160|#x0?A0);/i', "\xC2\xA0", $html) ?? $html;

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse insignificant whitespace between tags only (preserve blank <p> lines from the editor).
        $html = preg_replace('/>\s+</u', '><', $html) ?? $html;

        $html = cmsCleanupEditorTables($html);
        $html = cmsRewriteEditorInlineImages($html);

        return cmsPreserveHtmlTextSpaces($html);
    }
}

if (!function_exists('cmsPrepareCmsPlainText')) {
    function cmsPrepareCmsPlainText(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = cmsPrepareCmsHtml($text);
        $text = strip_tags($text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}

cmsEnsureUtf8Response();
