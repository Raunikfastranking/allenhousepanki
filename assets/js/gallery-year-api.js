(function (global) {
    var GALLERY_YEAR_START = 2020;
    var CACHE_TTL_MS = 2 * 60 * 1000;
    var inflightRaw = null;
    var inflightSubType = {};

    function fixCmsAssetUrlLocal(url) {
        if (typeof global.fixCmsAssetUrl === 'function') {
            return global.fixCmsAssetUrl(url);
        }
        if (!url) return '';
        if (url.indexOf('https://cms.allenhouseschools.com/https://') === 0) {
            return url.replace('https://cms.allenhouseschools.com/', '');
        }
        return url;
    }

    function galleryExtractYoutubeId(url) {
        if (!url || typeof url !== 'string') return '';
        var match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|shorts\/|watch\?v=)|youtube-nocookie\.com\/embed\/)([A-Za-z0-9_-]{11})/i);
        return match ? match[1] : '';
    }

    function galleryYoutubeEmbedUrl(videoId, query) {
        if (!videoId) return '';
        var params = [];
        if (query && typeof query === 'object') {
            Object.keys(query).forEach(function (key) {
                params.push(encodeURIComponent(key) + '=' + encodeURIComponent(String(query[key])));
            });
        }
        var qs = params.length ? '?' + params.join('&') : '';
        return 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + qs;
    }

    function galleryYoutubeThumb(videoId) {
        return videoId ? 'https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg' : '';
    }

    function galleryResolveYoutubeUrl(item, medias) {
        if (!item) return '';
        var list = medias || item.medias || [];
        for (var i = 0; i < list.length; i++) {
            var mediaUrl = fixCmsAssetUrlLocal(list[i].urls || list[i].media_url || list[i].media_file || '');
            if (!mediaUrl) continue;
            if (/youtu\.be|youtube\.com/i.test(mediaUrl)) return mediaUrl;
            var ytId = galleryExtractYoutubeId(mediaUrl);
            if (ytId) return 'https://www.youtube.com/watch?v=' + ytId;
        }
        return '';
    }

    function galleryIsPdfUrl(url) {
        if (!url || typeof url !== 'string') return false;
        var path = url.split('?')[0].toLowerCase();
        return /\.pdf$/.test(path);
    }

    function galleryResolveCoverUrl(item, medias) {
        if (!item) return '';
        var topImage = fixCmsAssetUrlLocal(item.image_link || item.thumbnail || item.thumbnail_url || '');
        if (topImage && !galleryIsPdfUrl(topImage) && !/youtu\.be|youtube\.com/i.test(topImage)) return topImage;

        var list = medias || item.medias || [];
        for (var i = 0; i < list.length; i++) {
            var m = list[i];
            var imageLink = fixCmsAssetUrlLocal(m.imageLink || m.image_link || '');
            if (imageLink && !galleryIsPdfUrl(imageLink) && !/youtu\.be|youtube\.com/i.test(imageLink)) return imageLink;
            var type = String(m.mediaType || m.media_type || '').toLowerCase();
            var url = fixCmsAssetUrlLocal(m.urls || m.media_url || m.media_file || '');
            if (type === 'image' && url && !galleryIsPdfUrl(url) && !/youtu\.be|youtube\.com/i.test(url)) return url;
        }

        for (var j = 0; j < list.length; j++) {
            var mediaUrl = fixCmsAssetUrlLocal(list[j].urls || list[j].media_url || list[j].media_file || '');
            if (galleryIsPdfUrl(mediaUrl)) continue;
            var ytId = galleryExtractYoutubeId(mediaUrl);
            if (ytId) return galleryYoutubeThumb(ytId);
        }

        return '';
    }

    function decodeHtmlEntities(text) {
        if (text == null || text === '') {
            return '';
        }
        var el = document.createElement('textarea');
        el.innerHTML = String(text);
        return el.value;
    }

    function normalizeCmsGalleryItem(item) {
        var medias = (item.media || []).map(function (m, i) {
            var url = fixCmsAssetUrlLocal(m.media_url || m.media_file || '');
            return {
                urls: url,
                imageLink: fixCmsAssetUrlLocal(m.image_link || ''),
                mediaType: String(m.media_type || m.type || '').toLowerCase(),
                pivot: { is_cover: i === 0 ? '1' : '0' }
            };
        });
        var subType = (item.gallery_sub_type && item.gallery_sub_type.sub_type_name) || item.gallery_type || '';
        var coverUrl = galleryResolveCoverUrl(item, medias);
        var isVideo = String(item.gallery_type || '').toLowerCase() === 'video_gallery'
            || medias.some(function (m) { return m.mediaType === 'video'; });
        var videoUrl = isVideo ? galleryResolveYoutubeUrl(item, medias) : '';
        var rawTitle = item.heading || item.gallery_title || '';
        var rawDescription = item.content || item.description || '';
        var title = decodeHtmlEntities(rawTitle);
        var description = decodeHtmlEntities(String(rawDescription).replace(/<[^>]*>/g, ' ')).replace(/\s+/g, ' ').trim();
        return {
            id: item.id,
            title: title,
            achievementtitle: title,
            achivementdescription: description,
            description: description,
            achevementdate: item.date,
            date: item.date,
            created_at: item.created_at || item.date,
            achivementtype: subType,
            type: subType,
            subTypeId: (item.gallery_sub_type && item.gallery_sub_type.id) || null,
            subTypeName: subType,
            galleryType: item.gallery_type || '',
            branchId: item.branch_id != null ? item.branch_id : item.branchId,
            coverUrl: coverUrl,
            isVideo: isVideo,
            videoUrl: videoUrl,
            medias: medias
        };
    }

    function cacheKey() {
        return 'cms_gallery_branch_' + cmsBranchId + '_v11';
    }

    function subTypeCacheKey(subTypeId) {
        return 'cms_gallery_subtype_' + cmsBranchId + '_' + subTypeId + '_v10';
    }

    function clearLegacyGalleryCache() {
        try {
            sessionStorage.removeItem('cms_gallery_branch_' + cmsBranchId + '_v5');
            sessionStorage.removeItem('cms_gallery_branch_' + cmsBranchId + '_v6');
            sessionStorage.removeItem('cms_gallery_branch_' + cmsBranchId + '_v7');
            sessionStorage.removeItem('cms_gallery_branch_' + cmsBranchId + '_v8');
            sessionStorage.removeItem('cms_gallery_branch_' + cmsBranchId + '_v9');
            Object.keys(sessionStorage).forEach(function (key) {
                if (key.indexOf('cms_gallery_subtype_' + cmsBranchId + '_') === 0 && /_v[45678]$/.test(key)) {
                    sessionStorage.removeItem(key);
                }
            });
        } catch (e) {}
    }

    clearLegacyGalleryCache();

    function readCache(keyFn) {
        try {
            var raw = sessionStorage.getItem(keyFn());
            if (!raw) return null;
            var parsed = JSON.parse(raw);
            if (!parsed || !parsed.expires || parsed.expires < Date.now()) {
                sessionStorage.removeItem(keyFn());
                return null;
            }
            return Array.isArray(parsed.data) ? parsed.data : null;
        } catch (e) {
            return null;
        }
    }

    function writeCache(keyFn, data) {
        if (!Array.isArray(data) || data.length === 0) {
            return;
        }
        try {
            sessionStorage.setItem(keyFn(), JSON.stringify({
                expires: Date.now() + CACHE_TTL_MS,
                data: data
            }));
        } catch (e) {
            /* quota exceeded */
        }
    }

    function normalizeFetchOpts(forceLive) {
        var opts = typeof forceLive === 'object' ? forceLive : { forceLive: !!forceLive };
        if (opts.waitForLive) {
            opts.forceLive = true;
        }
        return opts;
    }

    function galleryYearRange() {
        var endYear = new Date().getFullYear();
        var years = [];
        for (var y = GALLERY_YEAR_START; y <= endYear; y++) years.push(y);
        return years;
    }

    function galleryItemYear(item) {
        var d = item && (item.date || item.achevementdate || item.created_at);
        if (!d) return null;
        var dt = new Date(d);
        return isNaN(dt.getTime()) ? null : dt.getFullYear();
    }

    function galleryItemBranchId(item) {
        if (!item) return null;
        if (item.branchId != null && item.branchId !== '') return Number(item.branchId);
        if (item.branch_id != null && item.branch_id !== '') return Number(item.branch_id);
        return null;
    }

    function filterItemsForBranch(items) {
        var branch = Number(cmsBranchId);
        if (!branch) return items || [];
        return (items || []).filter(function (item) {
            var bid = galleryItemBranchId(item);
            return bid === null || bid === branch;
        });
    }

    function sortGalleryItems(items) {
        return (items || []).slice().sort(function (a, b) {
            var da = new Date(a.date || a.created_at || 0).getTime();
            var db = new Date(b.date || b.created_at || 0).getTime();
            return db - da;
        });
    }

    function subtypeKeysForId(subTypeId) {
        var keys = [];
        var map = global.CMS_GALLERY_SUBTYPE || {};
        Object.keys(map).forEach(function (key) {
            if (Number(map[key]) === Number(subTypeId)) {
                keys.push(String(key).toLowerCase());
            }
        });
        return keys;
    }

    function gallerySubTypeName(item) {
        return String((item && (item.subTypeName || item.type || item.achivementtype)) || '').toLowerCase().trim();
    }

    function isGalleryEventItem(item) {
        var t = gallerySubTypeName(item);
        return t === 'events' || t === 'event';
    }

    function isGalleryPhotoItem(item) {
        var t = gallerySubTypeName(item);
        return t === 'photo_gallery' || t === 'photo';
    }

    function isGalleryVideoItem(item) {
        var t = gallerySubTypeName(item);
        return t === 'video_gallery' || t === 'video';
    }

    function isGalleryPrintItem(item) {
        var t = gallerySubTypeName(item);
        return t === 'print_media' || t === 'print';
    }

    function isGalleryAwardItem(item) {
        return gallerySubTypeName(item) === 'awards';
    }

    function galleryItemTitle(item) {
        return String((item && (item.achievementtitle || item.title)) || '').toLowerCase().trim();
    }

    function isGalleryAchievementItem(item) {
        if (isGalleryXToppersItem(item) || isGalleryXIIToppersItem(item)) {
            return false;
        }
        var t = gallerySubTypeName(item);
        if (t !== 'achievements' && Number(item.subTypeId) !== 1) {
            return false;
        }
        return !/topper/i.test(galleryItemTitle(item));
    }

    function isGalleryXToppersItem(item) {
        var sub = gallerySubTypeName(item);
        if (sub === 'x_toppers' || sub === 'x-toppers') {
            return true;
        }
        if (Number(item.subTypeId) === 3) {
            return true;
        }

        var title = galleryItemTitle(item);
        if (!title) {
            return false;
        }
        // Prevent Class XII albums from leaking into Class X listings.
        if (/xii|xll|class\s*12|class\s*xii|\b12th\b/.test(title)) {
            return false;
        }
        if (/achievers?\s+of\s+cbse|cbse\s+achievers?/i.test(title)) {
            return true;
        }
        if (/\bcbse\s+board\b|\bboard\s+exam/i.test(title)) {
            return true;
        }
        if (/star\s+performers.*\bgrade\s*x\b/i.test(title)) {
            return true;
        }
        if (/topper/i.test(title) && (/\bclass\s*x\b/.test(title) || (/\bx\b/.test(title) && !/xii|xll|12/.test(title)))) {
            return true;
        }
        if (/\bgrade\s*x\b/.test(title) && !/xii|xll|12/.test(title)) {
            return true;
        }
        if (/\bclass\s*x\b/.test(title) && !/xii|xll|12/.test(title)) {
            return true;
        }
        return false;
    }

    function isGalleryXIIToppersItem(item) {
        var sub = gallerySubTypeName(item);
        if (sub === 'xii_toppers' || sub === 'xii-toppers') {
            return true;
        }
        if (Number(item.subTypeId) === 4) {
            return true;
        }

        var title = galleryItemTitle(item);
        if (!title) {
            return false;
        }
        if (/achievers?\s+of\s+cbse|cbse\s+achievers?/i.test(title)) {
            return true;
        }
        if (/\bcbse\s+board\b|\bboard\s+exam/i.test(title)) {
            return true;
        }
        if (/star\s+performers.*\b(xii|12)\b/i.test(title)) {
            return true;
        }
        if (/topper/i.test(title) && /xii|xll|class\s*12|class\s*xii|\b12th\b/.test(title)) {
            return true;
        }
        if ((/\bgrade\s*(xii|12)\b/.test(title) || /\bclass\s*(xii|12)\b/.test(title))
            && /topper|board|cbse|performer|achiever/i.test(title)) {
            return true;
        }
        return false;
    }

    function galleryMatcherForSubTypeId(subTypeId) {
        var matchers = {
            1: isGalleryAchievementItem,
            2: isGalleryAwardItem,
            3: isGalleryXToppersItem,
            4: isGalleryXIIToppersItem,
            5: isGalleryPhotoItem,
            6: isGalleryVideoItem,
            7: isGalleryPrintItem,
            8: isGalleryEventItem
        };
        return matchers[Number(subTypeId)] || null;
    }

    function galleryItemMatchesSubTypeId(item, subTypeId) {
        if (!item || !subTypeId) return false;

        var want = Number(subTypeId);
        var got = Number(item.subTypeId);
        if (!isNaN(got) && got > 0 && got === want) return true;

        var matcher = galleryMatcherForSubTypeId(want);
        if (matcher) return matcher(item);

        var keys = subtypeKeysForId(want);
        if (!keys.length) return false;

        var names = [item.subTypeName, item.type, item.achivementtype]
            .map(function (value) {
                return String(value || '').toLowerCase().trim();
            })
            .filter(function (name) {
                return name !== '' && name !== 'gallery';
            });

        return keys.some(function (key) {
            return names.indexOf(key) !== -1;
        });
    }

    function galleryProxyUrl(path) {
        var base = (typeof galleryProxyUrlBase !== 'undefined' && galleryProxyUrlBase)
            ? galleryProxyUrlBase
            : '';
        if (!base || /cms\.allenhouseschools\.com/i.test(base)) {
            var root = (typeof SITE_BASE_URL !== 'undefined' ? String(SITE_BASE_URL) : '/');
            if (!root || root.charAt(0) !== '/') {
                root = '/' + (root || '');
            }
            root = root.replace(/\/+$/, '');
            if (root === '' || root === '/') {
                root = '';
            }
            var origin = (global.location && global.location.origin) ? global.location.origin : '';
            base = origin + root + '/proxy/gallery-proxy';
        }
        if (!path) {
            return base;
        }
        return base + (base.indexOf('?') >= 0 ? '&' : '?') + path;
    }

    async function parseGalleryProxyJson(response) {
        var text = await response.text();
        var contentType = (response.headers.get('content-type') || '').toLowerCase();
        if (!response.ok) {
            console.error('Gallery proxy HTTP error', response.status, text.slice(0, 200));
            return null;
        }
        if (contentType.indexOf('json') === -1 && text.trim().charAt(0) !== '{') {
            console.error('Gallery proxy returned non-JSON', text.slice(0, 200));
            return null;
        }
        try {
            return JSON.parse(text);
        } catch (err) {
            console.error('Gallery proxy JSON parse failed', err);
            return null;
        }
    }

    async function fetchCmsGalleriesBranchYear(year) {
        var response = await fetch(galleryProxyUrl('year=' + encodeURIComponent(year)), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        });
        var result = await parseGalleryProxyJson(response);
        return result && Array.isArray(result.data) ? result.data : [];
    }

    async function fetchBranchGalleriesDirect() {
        var response = await fetch(galleryProxyUrl(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        });
        var result = await parseGalleryProxyJson(response);
        return result && Array.isArray(result.data) ? result.data : [];
    }

    function mergeGalleryItems(batches) {
        var seen = {};
        var merged = [];
        batches.forEach(function (items) {
            items.forEach(function (item) {
                if (!item || !item.id || seen[item.id]) return;
                seen[item.id] = true;
                merged.push(item);
            });
        });
        merged.sort(function (a, b) {
            var da = new Date(a.date || a.created_at || 0).getTime();
            var db = new Date(b.date || b.created_at || 0).getTime();
            return db - da;
        });
        return merged;
    }

    async function fetchAllBranchGalleryRows() {
        var years = galleryYearRange();
        var directPromise = fetchBranchGalleriesDirect();
        var yearBatches = await Promise.all(years.map(fetchCmsGalleriesBranchYear));
        var direct = await directPromise;
        return mergeGalleryItems([direct].concat(yearBatches));
    }

    async function fetchAllCmsGalleriesRawByBranch(forceLive) {
        var opts = normalizeFetchOpts(forceLive);
        if (!opts.forceLive) {
            var cached = readCache(cacheKey);
            if (cached) {
                var livePromise = refreshGalleryCacheInBackground({ silent: !!opts.waitForLive });
                if (opts.waitForLive && livePromise) {
                    try {
                        return await livePromise;
                    } catch (err) {
                        console.error('fetchAllCmsGalleriesRawByBranch live refresh failed', err);
                    }
                } else if (!opts.waitForLive && !livePromise) {
                    refreshGalleryCacheInBackground();
                }
                return cached;
            }
        }
        if (inflightRaw) return inflightRaw;

        inflightRaw = (async function () {
            var merged = await fetchAllBranchGalleryRows();
            writeCache(cacheKey, merged);
            return merged;
        })().finally(function () {
            inflightRaw = null;
        });

        return inflightRaw;
    }

    function refreshGalleryCacheInBackground(opts) {
        opts = opts || {};
        if (inflightRaw) return inflightRaw;

        inflightRaw = (async function () {
            var merged = await fetchAllBranchGalleryRows();
            writeCache(cacheKey, merged);
            if (!opts.silent) {
                global.dispatchEvent(new CustomEvent('cms:galleries-refreshed', {
                    detail: { subTypeId: null, count: merged.length }
                }));
            }
            return merged;
        })().finally(function () {
            inflightRaw = null;
        });

        return inflightRaw;
    }

    async function fetchAllCmsGalleriesByBranch(forceLive) {
        var raw = await fetchAllCmsGalleriesRawByBranch(forceLive);
        return raw.map(normalizeCmsGalleryItem);
    }

    async function loadCmsGalleriesSubTypeLive(subTypeId) {
        var id = Number(subTypeId);
        if (!id) return [];

        var all = await fetchAllCmsGalleriesByBranch(true);
        var normalized = (all || []).filter(function (item) {
            return galleryItemMatchesSubTypeId(item, id);
        });

        return filterItemsForBranch(sortGalleryItems(normalized));
    }

    function refreshSubtypeCacheInBackground(subTypeId, opts) {
        opts = opts || {};
        var id = Number(subTypeId);
        if (!id) return null;
        if (inflightSubType[id]) return inflightSubType[id];

        inflightSubType[id] = loadCmsGalleriesSubTypeLive(id).then(function (normalized) {
            writeCache(function () { return subTypeCacheKey(id); }, normalized);
            if (!opts.silent) {
                global.dispatchEvent(new CustomEvent('cms:galleries-refreshed', {
                    detail: { subTypeId: id, count: normalized.length }
                }));
            }
            return normalized;
        }).finally(function () {
            delete inflightSubType[id];
        });

        return inflightSubType[id];
    }

    async function fetchCmsGalleriesSubType(subTypeId, opts) {
        opts = normalizeFetchOpts(opts || {});
        var id = Number(subTypeId);
        if (!id) return [];

        var keyFn = function () { return subTypeCacheKey(id); };
        if (!opts.forceLive) {
            var cached = readCache(keyFn);
            if (cached) {
                var livePromise = refreshSubtypeCacheInBackground(id, { silent: !!opts.waitForLive });
                if (opts.waitForLive && livePromise) {
                    try {
                        return await livePromise;
                    } catch (err) {
                        console.error('fetchCmsGalleriesSubType live refresh failed for subtype', id, err);
                    }
                }
                return cached.map(function (item) {
                    return item && item.subTypeName ? item : normalizeCmsGalleryItem(item);
                });
            }
        }

        if (inflightSubType[id]) return inflightSubType[id];

        inflightSubType[id] = loadCmsGalleriesSubTypeLive(id).then(function (normalized) {
            writeCache(keyFn, normalized);
            return normalized;
        }).finally(function () {
            delete inflightSubType[id];
        });

        return inflightSubType[id];
    }

    async function fetchCmsGalleriesBranch() {
        return fetchAllCmsGalleriesByBranch();
    }

    function populateGalleryYearSelect(selectEl, items, selectedYear) {
        if (!selectEl) return;
        var endYear = new Date().getFullYear();
        var years = [];
        for (var y = endYear; y >= GALLERY_YEAR_START; y--) years.push(y);
        selectEl.innerHTML = '<option value="">All Years</option>';
        years.forEach(function (year) {
            var opt = document.createElement('option');
            opt.value = String(year);
            opt.textContent = String(year);
            if (selectedYear && String(year) === String(selectedYear)) opt.selected = true;
            selectEl.appendChild(opt);
        });
    }

    function filterGalleryItemsByYear(items, yearVal) {
        if (yearVal === '' || yearVal == null) return items || [];
        var want = String(yearVal);
        return (items || []).filter(function (item) {
            var y = galleryItemYear(item);
            return y != null && String(y) === want;
        });
    }

    global.CMS_GALLERY_YEAR_START = GALLERY_YEAR_START;
    global.galleryItemYear = galleryItemYear;
    global.galleryItemMatchesSubTypeId = galleryItemMatchesSubTypeId;
    global.fetchAllCmsGalleriesByBranch = fetchAllCmsGalleriesByBranch;
    global.fetchCmsGalleriesBranch = fetchCmsGalleriesBranch;
    global.fetchCmsGalleriesSubType = fetchCmsGalleriesSubType;
    global.populateGalleryYearSelect = populateGalleryYearSelect;
    global.filterGalleryItemsByYear = filterGalleryItemsByYear;
    global.isGalleryEventItem = isGalleryEventItem;
    global.isGalleryPhotoItem = isGalleryPhotoItem;
    global.isGalleryVideoItem = isGalleryVideoItem;
    global.isGalleryPrintItem = isGalleryPrintItem;
    global.isGalleryAwardItem = isGalleryAwardItem;
    global.isGalleryAchievementItem = isGalleryAchievementItem;
    global.isGalleryXToppersItem = isGalleryXToppersItem;
    global.isGalleryXIIToppersItem = isGalleryXIIToppersItem;
    global.isClassXTopperAlbum = isGalleryXToppersItem;
    global.isClassXiiTopperAlbum = isGalleryXIIToppersItem;
    global.gallerySubTypeName = gallerySubTypeName;
    global.galleryExtractYoutubeId = galleryExtractYoutubeId;
    global.galleryYoutubeEmbedUrl = galleryYoutubeEmbedUrl;
    global.galleryIsPdfUrl = galleryIsPdfUrl;
    global.galleryResolveCoverUrl = galleryResolveCoverUrl;
    global.galleryResolveYoutubeUrl = galleryResolveYoutubeUrl;
    global.decodeHtmlEntities = decodeHtmlEntities;
    global.normalizeCmsGalleryItem = normalizeCmsGalleryItem;
    global.clearCmsGalleryCache = function () {
        try {
            sessionStorage.removeItem(cacheKey());
            Object.keys(sessionStorage).forEach(function (k) {
                if (k.indexOf('cms_gallery_subtype_' + cmsBranchId + '_') === 0) {
                    sessionStorage.removeItem(k);
                }
            });
        } catch (e) {}
    };
})(typeof window !== 'undefined' ? window : this);
