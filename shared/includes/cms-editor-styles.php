<style>
    /*
     * CMS rich text — match Quill/TinyMCE preview (same spacing & inline styles as CMS).
     * Block margins default to 0 like .ql-editor; spacing comes from CMS HTML + inline style attributes.
     */
    .ql-editor {
        height: auto !important;
        padding: 0 !important;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        overflow: visible !important;
        overflow-y: visible !important;
        font-size: inherit;
        line-height: inherit;
        color: inherit;
        box-sizing: border-box;
    }

    .ql-editor .ql-ui {
        display: none !important;
    }

    .ql-editor p,
    .ql-editor ol,
    .ql-editor ul,
    .ql-editor pre,
    .ql-editor blockquote,
    .ql-editor h1,
    .ql-editor h2,
    .ql-editor h3,
    .ql-editor h4,
    .ql-editor h5,
    .ql-editor h6 {
        margin: 0;
        padding: 0;
        counter-reset: list-1 list-2 list-3 list-4 list-5 list-6 list-7 list-8 list-9;
    }

    /* Blank lines from extra Enter in Quill — visible like CMS */
    .ql-editor p:empty,
    .ql-editor p:has(> br:only-child) {
        min-height: 1em;
        line-height: inherit;
        height: auto;
        overflow: visible;
    }

    .cms-page-content {
        margin-top: 1.5rem;
        margin-bottom: 2.5rem;
    }

    /* No Quill chrome box on the live site */
    .cms-page-content .ql-snow,
    .main .ql-snow,
    .cms-section-body .ql-snow,
    .cms-rich-text .ql-snow,
    .prose .ql-snow {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Do not squeeze CMS sections — stack as authored */
    .cms-page-content .cms-section + .cms-section,
    .cms-page-content .ql-snow + .ql-snow,
    .cms-page-content .cms-section-after-columns,
    .cms-page-content .cms-section-after-media {
        margin-top: 0;
    }

    .cms-hero-inner,
    .cms-section-body,
    .cms-rich-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }

    .main h1,
    .main h2,
    .main h3,
    .main h4,
    .main h5,
    .main h6 {
        white-space: normal;
    }

    /* CMS inline style="" on elements is never stripped in cmsPrepareCmsHtml — wins over rules below. */

    /* CMS editor tables: inner cell borders only (no outer wrapper box) */
    .cms-table-host {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }

    .cms-page-content table,
    .main .ql-editor table,
    .cms-section-body table,
    .cms-rich-text table {
        border-collapse: collapse;
        width: 100%;
        max-width: 100%;
        border: none;
    }

    .cms-page-content table th:not([style]),
    .cms-page-content table td:not([style]),
    .main .ql-editor th:not([style]),
    .main .ql-editor td:not([style]),
    .cms-section-body th:not([style]),
    .cms-section-body td:not([style]),
    .cms-rich-text th:not([style]),
    .cms-rich-text td:not([style]) {
        border: 1px solid #cbd5e1;
        padding: 0.625rem 0.75rem;
        vertical-align: top;
    }

    .cms-page-content table[style] th,
    .cms-page-content table[style] td,
    .main .ql-editor table[style] th,
    .main .ql-editor table[style] td {
        vertical-align: top;
    }

    /* Quill wrapper cell: holds only a nested table — no outer box border */
    .cms-page-content table > tbody > tr > td:has(> table:only-child),
    .cms-page-content table > tbody > tr > th:has(> table:only-child),
    .main .ql-editor table > tbody > tr > td:has(> table:only-child),
    .main .ql-editor table > tbody > tr > th:has(> table:only-child),
    .cms-section-body table > tbody > tr > td:has(> table:only-child),
    .cms-section-body table > tbody > tr > th:has(> table:only-child),
    .cms-rich-text table > tbody > tr > td:has(> table:only-child),
    .cms-rich-text table > tbody > tr > th:has(> table:only-child) {
        border: none !important;
        padding: 0 !important;
    }

    .cms-page-content table th,
    .main .ql-editor th,
    .cms-section-body th,
    .cms-rich-text th {
        background: #f8fafc;
        font-weight: 600;
    }

    /* Wide CMS tables: horizontal scroll on mobile */
    @media (max-width: 767px) {
        .cms-page-content .ql-snow,
        .main .ql-snow:not(.ql-toolbar),
        .cms-section-body,
        .cms-rich-text,
        .cms-bus-routes {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        .cms-page-content table,
        .main .ql-editor table,
        .cms-section-body table,
        .cms-rich-text table,
        .cms-bus-routes table {
            width: max-content;
            max-width: 100%;
        }
    }

    /*
     * CMS lists: respect editor list-style (decimal, upper/lower-alpha, roman, etc.).
     * Do NOT force decimal on ol[style*="list-style-type"] — that broke A,B,C lists in CMS.
     */
    .ql-editor ol,
    .ql-editor ul,
    .main .ql-editor ol,
    .main .ql-editor ul,
    .cms-section-body ol,
    .cms-section-body ul,
    .cms-rich-text ol,
    .cms-rich-text ul,
    .cms-hero-inner ol,
    .cms-hero-inner ul,
    .cms-page-content .ql-editor ol,
    .cms-page-content .ql-editor ul {
        list-style-position: outside;
        margin: 0 0 1em;
        padding-left: 1.5em;
    }

  /* Quill default numeric / bullet lists (no custom list-style from CMS) */
    .ql-editor ol:has(> li[data-list="ordered"]):not([style*="list-style"]):not([type]),
    .main .ql-editor ol:has(> li[data-list="ordered"]):not([style*="list-style"]):not([type]),
    .cms-page-content .ql-editor ol:has(> li[data-list="ordered"]):not([style*="list-style"]):not([type]) {
        list-style: decimal outside;
        list-style-type: decimal;
    }

    .ql-editor ul:has(> li[data-list="bullet"]):not([style*="list-style"]),
    .main .ql-editor ul:has(> li[data-list="bullet"]):not([style*="list-style"]),
    .cms-page-content .ql-editor ul:has(> li[data-list="bullet"]):not([style*="list-style"]) {
        list-style: disc outside;
        list-style-type: disc;
    }

    /* Plain ol/ul without CMS inline list-style — safe defaults */
    .ql-editor ol:not([style*="list-style"]):not([type]):not(:has(> li[data-list])),
    .main .ql-editor ol:not([style*="list-style"]):not([type]):not(:has(> li[data-list])),
    .cms-section-body ol:not([style*="list-style"]):not([type]),
    .cms-rich-text ol:not([style*="list-style"]):not([type]),
    .cms-hero-inner ol:not([style*="list-style"]):not([type]) {
        list-style: decimal outside;
        list-style-type: decimal;
    }

    .ql-editor ul:not([style*="list-style"]):not(:has(> li[data-list])),
    .main .ql-editor ul:not([style*="list-style"]):not(:has(> li[data-list])),
    .cms-section-body ul:not([style*="list-style"]),
    .cms-rich-text ul:not([style*="list-style"]),
    .cms-hero-inner ul:not([style*="list-style"]) {
        list-style: disc outside;
        list-style-type: disc;
    }

    /* CMS TinyMCE/Quill: upper-alpha, lower-alpha, roman — inline style on <ol> wins */
    .ql-editor ol[style*="list-style"],
    .ql-editor ol[type],
    .main .ql-editor ol[style*="list-style"],
    .main .ql-editor ol[type],
    .cms-section-body ol[style*="list-style"],
    .cms-section-body ol[type],
    .cms-rich-text ol[style*="list-style"],
    .cms-rich-text ol[type] {
        list-style-position: outside;
    }

    .ql-editor ul[style*="list-style"],
    .main .ql-editor ul[style*="list-style"],
    .cms-section-body ul[style*="list-style"],
    .cms-rich-text ul[style*="list-style"] {
        list-style-position: outside;
    }

    .ql-editor ol > li,
    .ql-editor ul > li,
    .main .ql-editor ol > li,
    .main .ql-editor ul > li,
    .cms-section-body ol > li,
    .cms-section-body ul > li,
    .cms-rich-text ol > li,
    .cms-rich-text ul > li,
    .cms-hero-inner ol > li,
    .cms-hero-inner ul > li {
        display: list-item !important;
        list-style: inherit;
        list-style-type: inherit;
    }

    .cms-page-content .ql-editor li[data-list]::before,
    .cms-section-body li[data-list]::before,
    .cms-rich-text li[data-list]::before,
    .cms-hero-inner li[data-list]::before,
    .prose .ql-editor li[data-list]::before {
        content: none !important;
        display: none !important;
    }

    .ql-editor ol > li::marker,
    .ql-editor ul > li::marker,
    .main .ql-editor ol > li::marker,
    .main .ql-editor ul > li::marker {
        content: normal !important;
    }

    /* Quill list on wrong parent tag */
    ol:has(> li[data-list="bullet"]):not([style*="list-style"]) {
        list-style: disc outside;
        list-style-type: disc;
        margin: 0 0 1em;
        padding-left: 1.5em;
    }

    ul:has(> li[data-list="ordered"]):not([style*="list-style"]) {
        list-style: decimal outside;
        list-style-type: decimal;
        margin: 0 0 1em;
        padding-left: 1.5em;
    }

    .cms-facilities-page .cms-facilities-row--white h3.cms-facilities-card-title,
    .cms-facilities-page .cms-facilities-row--white .cms-facilities-card-title {
        color: #053B7A !important;
    }

    .bg-blue-main .ql-editor,
    .ab-cr-bg .ql-editor,
    .facilities-feature-band .ql-editor,
    .pedagogy-feature-band .ql-editor,
    .cms-facilities-row--blue .ql-editor,
    .facilities-feature-copy .ql-editor,
    .pedagogy-feature-copy .ql-editor {
        color: inherit;
    }

    /* Headings without CMS inline size — browser defaults (Tailwind reset undo) */
    .ql-editor h1:not([style]) { font-size: 2em; font-weight: 700; }
    .ql-editor h2:not([style]) { font-size: 1.5em; font-weight: 700; }
    .ql-editor h3:not([style]) { font-size: 1.17em; font-weight: 700; }
    .ql-editor h4:not([style]) { font-size: 1em; font-weight: 700; }
    .ql-editor h5:not([style]) { font-size: 0.83em; font-weight: 700; }
    .ql-editor h6:not([style]) { font-size: 0.67em; font-weight: 700; }

    /* Unstyled links inside CMS content should look like links (editor default). */
    .ql-editor a:not([style*="color"]),
    .cms-rich-text a:not([style*="color"]) {
        color: #1155cc;
        text-decoration: underline;
    }
</style>
