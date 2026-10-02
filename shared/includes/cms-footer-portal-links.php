<?php

declare(strict_types=1);

/**
 * Static footer portal links (Alumni + ERP) per branch — not loaded from CMS.
 */

if (!function_exists('cmsFooterPortalLinkTitles')) {
    /** @return list<string> */
    function cmsFooterPortalLinkTitles(): array
    {
        return [
            'alumni portal',
            'erp login',
            'teacher portal',
            'teachers portal',
            "teacher's portal",
        ];
    }
}

if (!function_exists('cmsFooterLinkIsPortal')) {
    function cmsFooterLinkIsPortal(string $title): bool
    {
        $normalized = strtolower(trim($title));

        return in_array($normalized, cmsFooterPortalLinkTitles(), true);
    }
}

if (!function_exists('cmsStaticFooterPortalLinks')) {
    /**
     * @return list<array{title: string, url: string, target: string}>
     */
    function cmsStaticFooterPortalLinks(): array
    {
        $branchId = defined('BRANCH_ID') ? (int) BRANCH_ID : 0;

        /** @var array<int, list<array{title: string, url: string, target: string}>> $byBranch */
        static $byBranch = [
            1 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100111173.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://allenj.superhouseerp.com/', 'target' => '_blank'],
            ],
            2 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100110206.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://apskhl.superhouseerp.com/', 'target' => '_blank'],
            ],
            3 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100111173.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://apsgzb.superhouseerp.com/', 'target' => '_blank'],
            ],
            4 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100110367.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://allenv.superhouseerp.com/', 'target' => '_blank'],
            ],
            5 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100206180.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://allenj.superhouseerp.com/', 'target' => '_blank'],
            ],
            6 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100110796.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://allenp.superhouseerp.com/', 'target' => '_blank'],
            ],
            7 => [
                ['title' => 'Alumni Portal', 'url' => 'https://alumni.superhouseeducation.com/group/100111174.dz', 'target' => '_blank'],
                ['title' => 'ERP Login', 'url' => 'https://apsr.edunexttechnologies.com/Index', 'target' => '_blank'],
            ],
        ];

        return $byBranch[$branchId] ?? [];
    }
}

if (!function_exists('cmsFooterQuickLinksForDisplay')) {
    /**
     * Portal links (Alumni/ERP/Teacher Portal) come from the CMS quick-links group.
     * Static per-branch URLs are kept only as a fallback for titles missing in CMS.
     *
     * @param list<array<string, mixed>> $cmsLinks
     * @return list<array<string, mixed>>
     */
    function cmsFooterQuickLinksForDisplay(array $cmsLinks): array
    {
        $portalLinks = [];
        $coveredTitles = [];
        $filtered = [];

        foreach ($cmsLinks as $link) {
            if (!is_array($link)) {
                continue;
            }
            $title = trim((string) ($link['title'] ?? $link['name'] ?? ''));
            if ($title === '') {
                continue;
            }
            if (cmsFooterLinkIsPortal($title)) {
                $normalized = strtolower($title);
                if (!isset($coveredTitles[$normalized])) {
                    $coveredTitles[$normalized] = true;
                    $portalLinks[] = $link;
                }
                continue;
            }
            $filtered[] = $link;
        }

        foreach (cmsStaticFooterPortalLinks() as $portal) {
            $normalized = strtolower($portal['title']);
            if (isset($coveredTitles[$normalized])) {
                continue;
            }
            $portalLinks[] = [
                'title' => $portal['title'],
                'url' => $portal['url'],
                'target' => $portal['target'],
            ];
        }

        return array_merge($portalLinks, $filtered);
    }
}
