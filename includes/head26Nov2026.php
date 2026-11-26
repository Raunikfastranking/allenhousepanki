<?php
require_once __DIR__ . '/environment.php';
require_once dirname(__DIR__) . '/proxy/config.php';
$GLOBALS['school_assets_css_version'] = '0.51';
require dirname(__DIR__) . '/shared/includes/school-head-assets.php';
$apsCssBase = $GLOBALS['apsCssBase'] ?? site_asset_url('');
?>
<link rel="icon" type="image/x-icon"
    href="https://myschool-assets.s3.ap-south-1.amazonaws.com/uploads/ZORSdJXNGlZAVQJ4ZNWZsMEVZ1thgvBkL8JeOSXH.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"></noscript>
<meta name="robots" content="index, follow" />
<meta name="google-site-verification" content="ilUYw9yqaZ8YswHCwQpjWw2TjtB4f57isvfeXH2Mfv4" />
<?php require dirname(__DIR__) . '/shared/includes/school-google-fonts.php'; ?>

<?php require dirname(__DIR__) . '/shared/includes/cms-editor-styles.php'; ?>

<?php if (!empty($GLOBALS['panki_hero_lcp_preload']) && is_array($GLOBALS['panki_hero_lcp_preload'])): ?>
<?php foreach ($GLOBALS['panki_hero_lcp_preload'] as $heroPreload): ?>
<link rel="preload" as="image" href="<?= htmlspecialchars((string) ($heroPreload['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" media="<?= htmlspecialchars((string) ($heroPreload['media'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" fetchpriority="high">
<?php endforeach; ?>
<?php endif; ?>


<?php if (!empty($GLOBALS['panki_approach_bg_preload'])): ?>
<link rel="preload" as="image" href="<?= htmlspecialchars((string) $GLOBALS['panki_approach_bg_preload'], ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>


<script>
var baseUrl = "<?= rtrim(API_BASE_URL, '/') ?>";
var branchId = "<?= BRANCH_ID ?>";
var cmsBranchId = <?= (int) BRANCH_ID ?>;

function fixCmsAssetUrl(url) {
    if (!url) return '';
    var cmsHosts = [
        'https://cms.allenhouseschools.com/'
    ];
    for (var i = 0; i < cmsHosts.length; i++) {
        var prefix = cmsHosts[i];
        if (url.indexOf(prefix + 'https://') === 0) {
            return url.replace(prefix, '');
        }
    }
    return url;
}

function normalizeCmsGalleryItem(item) {
    var medias = (item.media || []).map(function(m, i) {
        var url = fixCmsAssetUrl(m.media_url || m.media_file || '');
        return {
            urls: url,
            pivot: { is_cover: i === 0 ? '1' : '0' }
        };
    });
    var subType = (item.gallery_sub_type && item.gallery_sub_type.sub_type_name) || item.gallery_type || '';
    return {
        id: item.id,
        title: item.heading || item.gallery_title || '',
        achievementtitle: item.heading || item.gallery_title || '',
        achivementdescription: item.content || '',
        description: item.content || '',
        achevementdate: item.date,
        date: item.date,
        created_at: item.created_at || item.date,
        achivementtype: subType,
        type: subType,
        subTypeId: (item.gallery_sub_type && item.gallery_sub_type.id) || null,
        subTypeName: subType,
        galleryType: item.gallery_type || '',
        medias: medias
    };
}

var CMS_GALLERY_SUBTYPE = { achievements: 1, awards: 2, x_toppers: 3, xii_toppers: 4, photo: 5, photo_gallery: 5, video: 6, video_gallery: 6, print: 7, print_media: 7, events: 8 };

window.onerror = function(msg, url, line, col, error) {
    console.error("GLOBAL ERROR:", {
        msg: msg,
        url: url,
        line: line,
        col: col,
        error: error
    });
};

window.onunhandledrejection = function(event) {
    console.error("PROMISE REJECTION:", event.reason);
};

</script>
<?php require_once dirname(__DIR__) . '/shared/includes/school-head-perf.php'; ?>
<?php if (schoolNeedsGalleryScripts()): ?>
<?php require dirname(__DIR__) . '/shared/includes/cms-gallery-client-init.php'; ?>
<script defer src="<?= htmlspecialchars($apsCssBase, ENT_QUOTES, 'UTF-8') ?>assets/js/gallery-year-api.js?v=19"></script>
<?php endif; ?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5DCR9LHW');</script>
<!-- End Google Tag Manager -->
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DCR9LHW"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "@id": "https://allenhousepanki.com/#website",
  "url": "https://allenhousepanki.com/",
  "name": "Allenhouse Panki",
  "alternateName": "Allenhouse School Panki",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://allenhousepanki.com/?s={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "School",
  "@id": "https://allenhousepanki.com/#school",
  "name": "Allenhouse Panki",
  "alternateName": "Allenhouse School Panki",
  "url": "https://allenhousepanki.com/",
  "logo": "https://allenhousepanki.com/assets/images/logo.jpg",
  "image": "https://lh3.googleusercontent.com/p/AF1QipNPwWH924UVeGTHuj4r3Eq58k2moEjejNyGmxwC=w408-h271-k-no",
  "telephone": "+91-8853075656",
  "email": "contact@allenhousepanki.com",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "884 B Block, Panki Rd, MIG, Ratanpur Colony, Panki",
    "addressLocality": "Kanpur",
    "addressRegion": "Uttar Pradesh",
    "postalCode": "208020",
    "addressCountry": "IN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "26.4641855",
    "longitude": "80.2527465"
  },
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday"
    ],
    "opens": "07:30",
    "closes": "16:00"
  },
  "sameAs": [
    "https://www.facebook.com/Allenhousepublicschoolpanki/",
    "https://www.instagram.com/allenhousepanki/",
    "https://in.linkedin.com/school/allenhouse-public-school-panki/",
    "https://www.youtube.com/@allenp884"
  ]
}
</script>
<?php
require_once dirname(__DIR__) . '/shared/includes/cms-breadcrumb-schema.php';
cmsRenderBreadcrumbSchemaJsonLd();
$requestPath = trim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
$basePath = rtrim(site_base_url(), '/');
$homePaths = array_filter([
    '',
    '/',
    $basePath,
    $basePath . '/',
    $basePath . '/index.php',
]);
$isPankiHomeRequest = in_array($requestPath, $homePaths, true);

$GLOBALS['cms_og_site_name'] = 'Allenhouse Panki';
$GLOBALS['cms_og_domain'] = 'allenhousepanki.com';
$GLOBALS['cms_og_image_default'] = 'https://lh3.googleusercontent.com/p/AF1QipNPwWH924UVeGTHuj4r3Eq58k2moEjejNyGmxwC=w408-h271-k-no';
if ($isPankiHomeRequest) {
    $homeSeoDefaults = [
        'canonical_url' => 'https://allenhousepanki.com/',
        'meta_title' => 'Best CBSE Board School in Kanpur | Allenhouse Panki',
        'document_title' => 'Best CBSE Board School in Kanpur | Allenhouse Panki',
        'meta_description' => 'Searching for the best CBSE school in Kanpur? APS Panki delivers English medium education, affordable fees, and a safe, trusted learning environment.',
        'og_image' => $GLOBALS['cms_og_image_default'],
    ];
    $GLOBALS['cms_seo'] = array_merge($homeSeoDefaults, is_array($GLOBALS['cms_seo'] ?? null) ? $GLOBALS['cms_seo'] : []);
}
require dirname(__DIR__) . '/shared/includes/cms-head-og-dynamic.php';
?>
