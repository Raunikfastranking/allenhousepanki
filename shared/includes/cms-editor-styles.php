<style>
    /* Quill / CMS rich text — normal block flow (pre-wrap doubles line gaps in <p> HTML) */
    .ql-editor {
        height: auto !important;
        padding: 0 !important;
        white-space: normal !important;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.45;
        overflow: visible !important;
        overflow-y: visible !important;
    }

    .ql-editor .ql-ui {
        display: none !important;
    }

    .ql-editor p {
        margin: 0 0 1.4em;
        white-space: normal;
        line-height: 1.45;
    }

    .ql-editor p:last-child {
        margin-bottom: 0;
    }

    /* Blank lines created in Quill (extra Enter) — keep visible on the frontend */
    .ql-editor p:empty,
    .ql-editor p:has(> br:only-child) {
        margin: 0 0 1.4em;
        min-height: 1.4em;
        line-height: 1.45 !important;
        height: auto;
        overflow: visible;
    }

    .cms-page-content {
        margin-top: 1.5rem;
        margin-bottom: 2.5rem;
    }

    /* Inner CMS pages + blog detail: no Quill container box */
    .cms-page-content .ql-snow,
    .main .ql-snow,
    .cms-section-body .ql-snow,
    .cms-rich-text .ql-snow,
    .prose .ql-snow {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .cms-page-content .cms-section + .cms-section {
        margin-top: 0.5rem;
    }

    .cms-page-content .ql-editor h1,
    .cms-page-content .ql-editor h2,
    .cms-page-content .ql-editor h3,
    .cms-page-content .ql-editor h4 {
        margin: 0.4em 0 0.2em;
        line-height: 1.3;
    }

    .cms-page-content .ql-editor > :first-child {
        margin-top: 0;
    }

    .cms-page-content .ql-snow + .ql-snow {
        margin-top: 0.25rem !important;
    }

    .cms-page-content .cms-section-after-columns,
    .cms-page-content .cms-section-after-media {
        margin-top: 0.25rem !important;
    }

    .cms-page-content .cms-column.mb-4 {
        margin-bottom: 0.5rem;
    }

    .cms-hero-inner,
    .cms-section-body,
    .cms-rich-text {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.45;
    }

    .cms-hero-inner p,
    .cms-section-body p,
    .cms-rich-text p {
        margin: 0 0 1.4em;
        line-height: 1.45;
    }

    .cms-hero-inner p:last-child,
    .cms-section-body p:last-child,
    .cms-rich-text p:last-child {
        margin-bottom: 0;
    }

    .cms-page-content .ql-editor p {
        margin: 0 0 1.4em;
    }

    .cms-page-content .ql-editor p:last-child {
        margin-bottom: 0;
    }

    .main h1,
    .main h2,
    .main h3,
    .main h4,
    .main h5,
    .main h6 {
        white-space: normal;
    }

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

    .cms-page-content table th,
    .cms-page-content table td,
    .main .ql-editor th,
    .main .ql-editor td,
    .cms-section-body th,
    .cms-section-body td,
    .cms-rich-text th,
    .cms-rich-text td {
        border: 1px solid #cbd5e1;
        padding: 0.625rem 0.75rem;
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

    /* CMS editor lists only — Quill bullet/ordered lists or explicit list-style */
    .cms-page-content .ql-editor ul:has(> li[data-list="bullet"]),
    .cms-page-content .ql-editor ul[style*="list-style"],
    .cms-section-body ul:has(> li[data-list="bullet"]),
    .cms-section-body ul[style*="list-style"],
    .cms-rich-text ul:has(> li[data-list="bullet"]),
    .cms-rich-text ul[style*="list-style"],
    .cms-hero-inner ul:has(> li[data-list="bullet"]),
    .cms-hero-inner ul[style*="list-style"],
    .prose .ql-editor ul:has(> li[data-list="bullet"]),
    .prose .ql-editor ul[style*="list-style"] {
        list-style: disc outside !important;
        list-style-type: disc !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    .cms-page-content .ql-editor ol:has(> li[data-list="ordered"]),
    .cms-page-content .ql-editor ol[style*="list-style"],
    .cms-section-body ol:has(> li[data-list="ordered"]),
    .cms-section-body ol[style*="list-style"],
    .cms-rich-text ol:has(> li[data-list="ordered"]),
    .cms-rich-text ol[style*="list-style"],
    .cms-hero-inner ol:has(> li[data-list="ordered"]),
    .cms-hero-inner ol[style*="list-style"],
    .prose .ql-editor ol:has(> li[data-list="ordered"]),
    .prose .ql-editor ol[style*="list-style"] {
        list-style: decimal outside !important;
        list-style-type: decimal !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    .cms-page-content .ql-editor ul:has(> li[data-list="bullet"]) > li,
    .cms-page-content .ql-editor ul[style*="list-style"] > li,
    .cms-page-content .ql-editor ol:has(> li[data-list="ordered"]) > li,
    .cms-page-content .ql-editor ol[style*="list-style"] > li,
    .cms-section-body ul:has(> li[data-list="bullet"]) > li,
    .cms-section-body ul[style*="list-style"] > li,
    .cms-section-body ol:has(> li[data-list="ordered"]) > li,
    .cms-section-body ol[style*="list-style"] > li,
    .cms-rich-text ul:has(> li[data-list="bullet"]) > li,
    .cms-rich-text ul[style*="list-style"] > li,
    .cms-rich-text ol:has(> li[data-list="ordered"]) > li,
    .cms-rich-text ol[style*="list-style"] > li,
    .cms-hero-inner ul:has(> li[data-list="bullet"]) > li,
    .cms-hero-inner ul[style*="list-style"] > li,
    .cms-hero-inner ol:has(> li[data-list="ordered"]) > li,
    .cms-hero-inner ol[style*="list-style"] > li,
    .prose .ql-editor ul:has(> li[data-list="bullet"]) > li,
    .prose .ql-editor ul[style*="list-style"] > li,
    .prose .ql-editor ol:has(> li[data-list="ordered"]) > li,
    .prose .ql-editor ol[style*="list-style"] > li {
        list-style: inherit;
        list-style-type: inherit;
        display: list-item;
    }

    .cms-page-content .ql-editor li[data-list]::before,
    .cms-section-body li[data-list]::before,
    .cms-rich-text li[data-list]::before,
    .cms-hero-inner li[data-list]::before,
    .prose .ql-editor li[data-list]::before {
        content: none !important;
        display: none !important;
    }

    .cms-facilities-page .cms-facilities-row--white h3.cms-facilities-card-title,
    .cms-facilities-page .cms-facilities-row--white .cms-facilities-card-title {
        color: #053B7A !important;
    }

    /* Quill list fallback for the opposite parent element */
    ol:has(> li[data-list="bullet"]) {
        list-style: disc outside !important;
        list-style-type: disc !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    ul:has(> li[data-list="ordered"]) {
        list-style: decimal outside !important;
        list-style-type: decimal !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    /* Fallback: show bullets/numbers for any Quill list even without data-list */
    .ql-editor ol,
    .main .ql-editor ol,
    .cms-section-body ol,
    .cms-rich-text ol,
    .cms-hero-inner ol {
        list-style: decimal outside !important;
        list-style-type: decimal !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    .ql-editor ul,
    .main .ql-editor ul,
    .cms-section-body ul,
    .cms-rich-text ul,
    .cms-hero-inner ul {
        list-style: disc outside !important;
        list-style-type: disc !important;
        margin: 0 0 1em !important;
        padding-left: 1.5em !important;
    }

    /* Override styles.css that hides markers on Quill list items */
    .ql-editor ol > li,
    .main .ql-editor ol > li,
    .ql-editor ul > li,
    .main .ql-editor ul > li {
        list-style: inherit !important;
        list-style-type: inherit !important;
        display: list-item !important;
    }

    .ql-editor ol > li::marker,
    .main .ql-editor ol > li::marker,
    .ql-editor ul > li::marker,
    .main .ql-editor ul > li::marker {
        content: normal !important;
    }
</style>
