<?php

declare(strict_types=1);

/**
 * Renders a CMS gallery listing block (year filter + grid) for dynamic cms-page routes.
 *
 * @param array<string, mixed> $page
 * @param array<string, mixed> $galleryMeta from cmsPageGalleryMeta()
 */
function cmsRenderDynamicGalleryPage(array $page, array $galleryMeta): void
{
    $config = [
        'mode' => (string) ($galleryMeta['mode'] ?? 'events'),
        'subType' => $galleryMeta['subType'] ?? null,
        'detailBase' => (string) ($galleryMeta['detailBase'] ?? 'event-gallery'),
    ];
    ?>
    <?php
    if (function_exists('cmsRenderPageBanner')) {
        cmsRenderPageBanner($page, []);
    }
    if (function_exists('cmsRenderPageBreadcrumbs')) {
        cmsRenderPageBreadcrumbs($page, []);
    }
    ?>
    <div class="<?= htmlspecialchars(cmsPageContentWrapClass(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:justify-end border-b pb-4 mb-6">
            <select id="cmsDynamicYearFilter" class="bg-gray-100 w-full sm:w-auto border-none px-5 py-3 outline-none rounded-xl text-sm"><option value="">All Years</option></select>
            <input type="text" id="cmsDynamicGallerySearch" class="bg-gray-100 w-full sm:w-[320px] border-none px-5 py-3 outline-none rounded-xl text-sm" placeholder="Search..." />
        </div>
        <div id="cmsDynamicGalleryGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <p class="col-span-full text-center text-gray-500 py-20">Loading gallery...</p>
        </div>
        <nav id="cmsDynamicGalleryPagination" class="hidden flex flex-wrap items-center justify-center gap-2 mt-10" aria-label="Gallery pagination"></nav>
    </div>

    <div id="cmsVideoGalleryModal" class="cms-video-gallery-modal hidden fixed inset-0 items-center justify-center p-4 sm:p-6" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cmsVideoGalleryModalTitle">
        <div class="cms-video-gallery-modal__backdrop absolute inset-0" data-close-video-modal></div>
        <div class="cms-video-gallery-modal__dialog relative w-full max-w-4xl bg-black rounded-xl shadow-2xl">
            <button type="button" class="cms-video-gallery-modal__close absolute top-3 right-3 z-20 w-10 h-10 rounded-full bg-white/90 text-gray-800 text-2xl leading-none flex items-center justify-center hover:bg-white" data-close-video-modal aria-label="Close video">&times;</button>
            <div class="cms-video-gallery-modal__player w-full bg-black">
                <iframe class="w-full h-full" src="" title="YouTube video" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>
            </div>
            <p id="cmsVideoGalleryModalTitle" class="cms-video-gallery-modal__title px-4 py-3 text-white text-sm font-semibold"></p>
        </div>
    </div>

    <style>
        .cms-video-gallery-modal {
            z-index: 100020;
        }
        .cms-video-gallery-modal:not(.hidden) {
            display: flex;
        }
        .cms-video-gallery-modal__backdrop {
            background: rgba(0, 0, 0, 0.8);
        }
        .cms-video-gallery-modal__dialog {
            z-index: 1;
            /* Do not use overflow:hidden here — it traps YouTube iframe fullscreen. */
            overflow: visible;
        }
        .cms-video-gallery-modal__player {
            aspect-ratio: 16 / 9;
            width: 100%;
            max-height: min(80vh, 720px);
            border-radius: 0.75rem 0.75rem 0 0;
            overflow: hidden;
            background: #000;
        }
        .cms-video-gallery-modal__player iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .cms-video-gallery-modal__title {
            border-radius: 0 0 0.75rem 0.75rem;
        }
        /* Keep site chrome below the video modal / YouTube fullscreen layer. */
        body.cms-video-modal-open .allen-site-header .stick-header.ah-main-fixed,
        body.cms-video-modal-open .allen-site-header,
        body.cms-video-modal-open .allen-mobile-enquire-tab {
            z-index: 1 !important;
        }
        body.cms-video-modal-open {
            overflow: hidden;
        }
        .cms-video-play-trigger { border: 0; padding: 0; background: transparent; }
        .cms-video-play-trigger:focus-visible { outline: 2px solid #053B7A; outline-offset: 2px; }
        .cms-gallery-card-thumb {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background: #f3f4f6;
        }
        .cms-gallery-card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cms-gallery-pdf-preview {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: background 0.2s;
        }
        .cms-gallery-card-thumb:hover .cms-gallery-pdf-preview {
            background: #e9ecef;
        }
        .cms-gallery-pdf-icon {
            width: 64px;
            height: 80px;
            background: #dc3545;
            color: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        .cms-gallery-pdf-label {
            font-size: 0.85rem;
            color: #495057;
            text-align: center;
            padding: 0 12px;
        }
    </style>

    <script>
    (function() {
        var cfg = <?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        var subTypeMap = typeof CMS_GALLERY_SUBTYPE !== 'undefined' ? CMS_GALLERY_SUBTYPE : { achievements: 1, awards: 2, x_toppers: 3, xii_toppers: 4, photo: 5, video: 6, print: 7, events: 8 };
        var items = [];
        var currentYear = '';
        var searchTerm = '';
        var currentPage = 1;
        var perPage = 9;
        var filteredList = [];

        function subTypeIdFor(key) {
            if (!key) return null;
            return subTypeMap[key] || subTypeMap[key + '_gallery'] || null;
        }

        function filterItems(all) {
            var id = subTypeIdFor(cfg.subType);
            if (!id) {
                return Array.isArray(all) ? all.slice() : [];
            }
            return (all || []).filter(function (item) {
                if (typeof galleryItemMatchesSubTypeId === 'function') {
                    return galleryItemMatchesSubTypeId(item, id);
                }
                return Number(item.subTypeId) === Number(id);
            });
        }

        function loadGalleryItems(subId) {
            if (subId && typeof fetchCmsGalleriesSubType === 'function') {
                return fetchCmsGalleriesSubType(subId, { forceLive: true });
            }
            if (typeof fetchAllCmsGalleriesByBranch === 'function') {
                return fetchAllCmsGalleriesByBranch(true);
            }
            return Promise.resolve([]);
        }

        function applyFilters() {
            var list = typeof filterGalleryItemsByYear === 'function'
                ? filterGalleryItemsByYear(items, currentYear)
                : items;
            if (searchTerm.trim()) {
                var term = searchTerm.toLowerCase().trim();
                list = list.filter(function(item) {
                    var title = (item.title || '').toLowerCase();
                    var year = String(typeof galleryItemYear === 'function' ? galleryItemYear(item) : '');
                    return title.includes(term) || year.includes(term);
                });
            }
            filteredList = Array.isArray(list) ? list.slice() : [];
            currentPage = 1;
            renderCurrentPage();
        }

        function decodeHtmlEntities(text) {
            if (typeof window.decodeHtmlEntities === 'function') {
                return window.decodeHtmlEntities(text);
            }
            if (text == null || text === '') {
                return '';
            }
            var el = document.createElement('textarea');
            el.innerHTML = String(text);
            return el.value;
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = decodeHtmlEntities(text);
            return div.innerHTML;
        }

        function isPdfUrl(url) {
            if (!url) return false;
            if (typeof galleryIsPdfUrl === 'function') return galleryIsPdfUrl(url);
            return /\.pdf$/i.test(String(url).split('?')[0]);
        }

        function defaultGalleryPlaceholder() {
            return 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-family="sans-serif" font-size="20">Gallery</text></svg>');
        }

        function buildPdfPreviewCardHtml() {
            return '<div class="cms-gallery-pdf-preview">' +
                '<span class="cms-gallery-pdf-icon">PDF</span>' +
                '<span class="cms-gallery-pdf-label">PDF Document</span>' +
                '</div>';
        }

        function resolveCardCoverUrl(item) {
            if (item.coverUrl && !isPdfUrl(item.coverUrl)) return item.coverUrl;
            if (typeof galleryResolveCoverUrl === 'function') {
                var resolved = galleryResolveCoverUrl(item);
                if (resolved && !isPdfUrl(resolved)) return resolved;
            }
            var medias = item.medias || [];
            var coverMedia = medias.find(function(m) { return m.pivot && m.pivot.is_cover === '1'; }) || medias[0] || {};
            var url = coverMedia.urls || '';
            if (url && isPdfUrl(url)) return '';
            if (url && typeof galleryExtractYoutubeId === 'function') {
                var ytId = galleryExtractYoutubeId(url);
                if (ytId) return 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg';
            }
            if (url && !isPdfUrl(url)) return url;
            return '';
        }

        function cardShowsPdfPreview(item) {
            if (resolveCardCoverUrl(item)) return false;
            var medias = item.medias || [];
            return medias.some(function(m) { return isPdfUrl(m.urls || ''); });
        }

        function resolveYoutubeId(item) {
            var url = item.videoUrl || '';
            if (!url && typeof galleryResolveYoutubeUrl === 'function') {
                url = galleryResolveYoutubeUrl(item);
            }
            if (!url) {
                var medias = item.medias || [];
                for (var i = 0; i < medias.length; i++) {
                    url = medias[i].urls || '';
                    if (url) break;
                }
            }
            if (typeof galleryExtractYoutubeId === 'function') {
                return galleryExtractYoutubeId(url);
            }
            var match = String(url).match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|shorts\/|watch\?v=))([A-Za-z0-9_-]{11})/i);
            return match ? match[1] : '';
        }

        function openCmsVideoPopup(ytId, title) {
            var modal = document.getElementById('cmsVideoGalleryModal');
            if (!modal || !ytId) return;
            var iframe = modal.querySelector('iframe');
            var titleEl = document.getElementById('cmsVideoGalleryModalTitle');
            if (iframe) {
                iframe.referrerPolicy = 'strict-origin-when-cross-origin';
                iframe.src = (typeof galleryYoutubeEmbedUrl === 'function'
                    ? galleryYoutubeEmbedUrl(ytId, { autoplay: 1, rel: 0 })
                    : 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(ytId) + '?autoplay=1&rel=0');
            }
            if (titleEl) titleEl.textContent = title || '';
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('cms-video-modal-open');
            document.body.style.overflow = 'hidden';
        }

        function closeCmsVideoPopup() {
            var modal = document.getElementById('cmsVideoGalleryModal');
            if (!modal) return;
            var iframe = modal.querySelector('iframe');
            if (iframe) iframe.src = '';
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('cms-video-modal-open');
            document.body.style.overflow = '';
        }

        var videoModalBound = false;
        function bindVideoGalleryModal() {
            if (videoModalBound) return;
            videoModalBound = true;
            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('.cms-video-play-trigger');
                if (trigger) {
                    e.preventDefault();
                    var ytId = trigger.getAttribute('data-youtube-id');
                    if (ytId) {
                        openCmsVideoPopup(ytId, trigger.getAttribute('data-video-title') || '');
                    }
                    return;
                }
                if (e.target.closest('[data-close-video-modal]')) {
                    closeCmsVideoPopup();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeCmsVideoPopup();
            });
        }

        function mediaKindFromItem(media) {
            if (!media) return '';
            var explicit = String(media.media_type || media.type || media.file_type || media.mime_type || '').toLowerCase();
            if (explicit.indexOf('video') !== -1) return 'video';
            if (explicit.indexOf('pdf') !== -1 || explicit.indexOf('application/pdf') !== -1) return 'pdf';
            if (explicit.indexOf('image') !== -1 || explicit.indexOf('photo') !== -1) return 'photo';

            var url = String(media.urls || media.url || media.path || '').toLowerCase().split('?')[0];
            if (!url) return '';
            if (/\.(mp4|mov|webm|m3u8|avi|mkv)$/.test(url)) return 'video';
            if (/\.pdf$/.test(url)) return 'pdf';
            if (/\.(jpg|jpeg|png|webp|gif|bmp|svg)$/.test(url)) return 'photo';
            return '';
        }

        function mediaTotalsLabel(item, isVideoCard) {
            var medias = Array.isArray(item && item.medias) ? item.medias : [];
            var counts = { photo: 0, video: 0, pdf: 0, other: 0 };

            medias.forEach(function (media) {
                var kind = mediaKindFromItem(media);
                if (kind === 'photo' || kind === 'video' || kind === 'pdf') {
                    counts[kind]++;
                } else {
                    counts.other++;
                }
            });

            if (medias.length === 0 && isVideoCard) {
                counts.video = 1;
            }

            var nonZeroKinds = ['photo', 'video', 'pdf'].filter(function (k) { return counts[k] > 0; });
            var total = counts.photo + counts.video + counts.pdf + counts.other;

            if (total <= 0) return '';
            if (nonZeroKinds.length === 1 && counts.other === 0) {
                if (nonZeroKinds[0] === 'photo') return total + ' PHOTO' + (total === 1 ? '' : 'S');
                if (nonZeroKinds[0] === 'video') return total + ' VIDEO' + (total === 1 ? '' : 'S');
                if (nonZeroKinds[0] === 'pdf') return total + ' PDF' + (total === 1 ? '' : 'S');
            }
            return total + ' ITEMS';
        }

        function buildCardHtml(item) {
            var coverUrl = resolveCardCoverUrl(item);
            var showPdfPreview = cardShowsPdfPreview(item);
            var date = item.date ? new Date(item.date) : null;
            var day = date ? date.getDate() : '';
            var month = date ? date.toLocaleString('default', { month: 'short' }) : '';
            var year = date ? date.getFullYear() : '';
            var cleanDesc = item.description ? decodeHtmlEntities(String(item.description).replace(/<[^>]*>?/gm, '')) : '';
            var isVideoCard = item.isVideo || cfg.subType === 'video';
            var ytId = isVideoCard ? resolveYoutubeId(item) : '';
            var totalsLabel = mediaTotalsLabel(item, isVideoCard);
            var detailUrl = cfg.detailBase + '?id=' + encodeURIComponent(item.id);
            var thumbInner = showPdfPreview
                ? buildPdfPreviewCardHtml()
                : '<img src="' + escapeHtml(coverUrl || defaultGalleryPlaceholder()) + '" alt="" loading="lazy" />';
            var playOverlay = '<span class="absolute inset-0 flex items-center justify-center bg-black/25 group-hover/video:bg-black/40 transition-colors pointer-events-none">' +
                '<span class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center text-blue-main text-2xl pl-1">&#9654;</span></span>';
            var thumbBlock;
            if (isVideoCard && ytId) {
                thumbBlock = '<button type="button" class="cms-video-play-trigger cms-gallery-card-thumb block w-full cursor-pointer group/video text-left" data-youtube-id="' + escapeHtml(ytId) + '" data-video-title="' + escapeHtml(item.title || '') + '" aria-label="Play video">' +
                    thumbInner + playOverlay + '</button>';
            } else if (isVideoCard) {
                thumbBlock = '<div class="cms-gallery-card-thumb">' + thumbInner + playOverlay + '</div>';
            } else {
                thumbBlock = '<div class="cms-gallery-card-thumb">' + thumbInner + '</div>';
            }
            var ctaHtml = isVideoCard
                ? ''
                : '<a href="' + escapeHtml(detailUrl) + '" class="mt-auto inline-block px-5 py-2 rounded-lg bg-blue-main text-white text-xs font-bold">View more</a>';
            return '<div class="flex flex-col bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden group">' +
                thumbBlock +
                '<div class="p-6 flex-1 flex flex-col">' +
                '<h3 class="text-blue-main font-bold text-md mb-2 uppercase">' + escapeHtml(item.title || '') + '</h3>' +
                (cleanDesc ? '<p class="text-gray-500 text-xs mb-3">' + escapeHtml(cleanDesc.substring(0, 120)) + '</p>' : '') +
                ((year || totalsLabel) ? '<div class="mb-3 flex items-center justify-between gap-3">' +
                    (year ? '<p class="text-xs text-gray-400">' + escapeHtml(day + ' ' + month + ' ' + year) + '</p>' : '<span></span>') +
                    (totalsLabel ? '<p class="text-xs text-gray-500 uppercase tracking-wide text-right">' + escapeHtml(totalsLabel) + '</p>' : '') +
                '</div>' : '') +
                ctaHtml +
                '</div></div>';
        }

        function updatePaginationUi() {
            var pager = document.getElementById('cmsDynamicGalleryPagination');
            if (!pager) return;

            var totalItems = filteredList.length;
            var totalPages = Math.max(1, Math.ceil(totalItems / perPage));
            if (totalItems <= perPage) {
                pager.innerHTML = '';
                pager.classList.add('hidden');
                return;
            }

            pager.classList.remove('hidden');
            var html = '';

            html += '<button type="button" data-page="prev" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-blue-main blue-hover disabled:opacity-40 disabled:pointer-events-none"' +
                (currentPage <= 1 ? ' disabled' : '') + '>Previous</button>';

            var windowSize = 5;
            var startPage = Math.max(1, currentPage - Math.floor(windowSize / 2));
            var endPage = Math.min(totalPages, startPage + windowSize - 1);
            startPage = Math.max(1, endPage - windowSize + 1);

            for (var p = startPage; p <= endPage; p++) {
                var active = p === currentPage;
                html += '<button type="button" data-page="' + p + '" class="min-w-[40px] px-3 py-2 rounded-lg text-sm font-semibold ' +
                    (active ? 'bg-blue-main text-white' : 'border border-gray-200 text-blue-main blue-hover') +
                    '" aria-current="' + (active ? 'page' : 'false') + '">' + p + '</button>';
            }

            html += '<button type="button" data-page="next" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-blue-main blue-hover disabled:opacity-40 disabled:pointer-events-none"' +
                (currentPage >= totalPages ? ' disabled' : '') + '>Next</button>';

            pager.innerHTML = html;
        }

        function bindPaginationControls() {
            var pager = document.getElementById('cmsDynamicGalleryPagination');
            if (!pager || pager.dataset.cmsBound === '1') return;
            pager.dataset.cmsBound = '1';
            pager.addEventListener('click', function(e) {
                var btn = e.target.closest('button[data-page]');
                if (!btn || btn.disabled) return;
                e.preventDefault();

                var action = btn.getAttribute('data-page');
                var totalPages = Math.max(1, Math.ceil(filteredList.length / perPage));
                if (action === 'prev' && currentPage > 1) {
                    currentPage--;
                } else if (action === 'next' && currentPage < totalPages) {
                    currentPage++;
                } else if (action !== 'prev' && action !== 'next') {
                    var pageNum = parseInt(action, 10);
                    if (!isNaN(pageNum) && pageNum >= 1) currentPage = pageNum;
                }

                renderCurrentPage();
                var gridTop = document.getElementById('cmsDynamicGalleryGrid');
                if (gridTop) {
                    gridTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        function renderCurrentPage() {
            var container = document.getElementById('cmsDynamicGalleryGrid');
            if (!container) return;

            container.innerHTML = '';

            if (!filteredList.length) {
                container.innerHTML = '<p class="text-center text-gray-500 col-span-full py-20">No items found.</p>';
                updatePaginationUi();
                return;
            }

            var totalPages = Math.max(1, Math.ceil(filteredList.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            var start = (currentPage - 1) * perPage;
            var pageItems = filteredList.slice(start, start + perPage);
            if (!pageItems.length && filteredList.length) {
                currentPage = 1;
                pageItems = filteredList.slice(0, perPage);
            }

            pageItems.forEach(function(item) {
                container.insertAdjacentHTML('beforeend', buildCardHtml(item));
            });

            updatePaginationUi();
        }

        async function load() {
            var grid = document.getElementById('cmsDynamicGalleryGrid');
            if (grid && grid.dataset.cmsLoaded !== '1') {
                grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-20">Loading gallery...</p>';
            }
            try {
                var subId = cfg.subType ? subTypeIdFor(cfg.subType) : null;
                var all = await loadGalleryItems(subId);
                items = filterItems(all || []);
                if (typeof populateGalleryYearSelect === 'function') {
                    populateGalleryYearSelect(document.getElementById('cmsDynamicYearFilter'), items);
                }
                bindPaginationControls();
                applyFilters();
                if (grid) grid.dataset.cmsLoaded = '1';
            } catch (e) {
                console.error('Gallery load failed', e);
                if (grid) {
                    grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-20">Unable to load gallery. Please refresh the page.</p>';
                }
            }
        }

        var searchEl = document.getElementById('cmsDynamicGallerySearch');
        if (searchEl) {
            searchEl.addEventListener('input', function() {
                searchTerm = this.value;
                applyFilters();
            });
        }
        var yearEl = document.getElementById('cmsDynamicYearFilter');
        if (yearEl) {
            yearEl.addEventListener('change', function() {
                currentYear = this.value;
                applyFilters();
            });
        }
        bindPaginationControls();
        bindVideoGalleryModal();
        window.addEventListener('cms:galleries-refreshed', function(e) {
            var subId = subTypeIdFor(cfg.subType);
            if (!subId || !e || !e.detail || Number(e.detail.subTypeId) !== Number(subId)) return;
            load();
        });
        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', load);
        } else {
            load();
        }
    })();
    </script>
    <?php
}
