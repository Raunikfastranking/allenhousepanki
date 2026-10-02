<?php

declare(strict_types=1);

/**
 * Nested header nav (e.g. Achievements → Board Toppers → Class X/XII).
 */

if (!function_exists('cmsNavNestedFlyoutCss')) {
    function cmsNavNestedFlyoutCss(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        ?>
<style>
    /* Level-2 dropdown (Achievements, Admission, etc.) */
    .desktop-header .navbar > ul > li > ul.submenu-open {
        display: block !important;
        opacity: 1 !important;
        transform: translateY(0) !important;
        pointer-events: auto !important;
    }
    /* Level-3 flyout (Board Toppers → Class X / XII) opens to the right */
    @media (min-width: 1111px) {
        .desktop-header .navbar,
        .desktop-header .navbar > ul,
        .desktop-header .navbar > ul > li,
        .desktop-header .navbar > ul > li > ul {
            overflow: visible !important;
        }
        .desktop-header .navbar ul li ul li.nav-has-children {
            position: relative;
            z-index: 1;
        }
        .desktop-header .navbar ul li ul li.nav-has-children:hover,
        .desktop-header .navbar ul li ul li.nav-has-children:focus-within {
            z-index: 80;
        }
        .desktop-header .navbar ul li ul li.nav-has-children > ul {
            position: absolute !important;
            top: 0 !important;
            left: 100% !important;
            right: auto !important;
            bottom: auto !important;
            min-width: 220px;
            margin: 0;
            padding: 0;
            transform: none !important;
            z-index: 90;
            display: none;
            opacity: 1 !important;
            pointer-events: auto !important;
            background-color: #fff;
            box-shadow: 0 3px 5px rgb(0 0 0 / 20%);
        }
        .desktop-header .navbar ul li ul li.nav-has-children:hover > ul,
        .desktop-header .navbar ul li ul li.nav-has-children > ul.submenu-open {
            display: block !important;
            opacity: 1 !important;
            transform: none !important;
            pointer-events: auto !important;
        }
        .desktop-header .navbar ul li ul li.nav-has-children.submenu-flip-left > ul {
            left: auto !important;
            right: 100% !important;
        }
    }
    .mobile-nav-item.is-open > .mobile-submenu {
        display: block !important;
    }
</style>
        <?php
    }
}

if (!function_exists('cmsNavNestedFlyoutScript')) {
    function cmsNavNestedFlyoutScript(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        ?>
<script>
(function () {
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function initMobileDrawer() {
        var closeMenu = document.getElementById('closeMenu');
        var navMenu = document.getElementById('NavMenu');
        var menubrop = document.getElementById('menubrop');
        if (!navMenu) {
            return;
        }
        // Always start closed on page load/reload.
        navMenu.style.webkitTransform = 'translateX(-100%)';
        navMenu.style.transform = 'translateX(-100%)';
        if (closeMenu && closeMenu.dataset.cmsDrawerBound !== '1') {
            closeMenu.dataset.cmsDrawerBound = '1';
            closeMenu.addEventListener('click', function (e) {
                e.preventDefault();
                navMenu.style.webkitTransform = 'translateX(-100%)';
                navMenu.style.transform = 'translateX(-100%)';
            });
        }
        if (menubrop && menubrop.dataset.cmsDrawerBound !== '1') {
            menubrop.dataset.cmsDrawerBound = '1';
            menubrop.addEventListener('click', function (e) {
                e.preventDefault();
                navMenu.style.webkitTransform = 'translateX(0%)';
                navMenu.style.transform = 'translateX(0%)';
            });
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1110) {
                navMenu.style.webkitTransform = 'translateX(-100%)';
                navMenu.style.transform = 'translateX(-100%)';
            }
        });
    }

    onReady(function () {
        initMobileDrawer();

        document.querySelectorAll('.mobile-nav-item.has-submenu > a.nav-parent-link, .mobile-nav-item.has-submenu > a').forEach(function (item) {
            if (item.dataset.cmsNavBound === '1') {
                return;
            }
            item.dataset.cmsNavBound = '1';
            item.addEventListener('click', function (e) {
                var parentLi = item.parentElement;
                if (!parentLi) {
                    return;
                }
                var submenu = parentLi.querySelector(':scope > .mobile-submenu');
                if (!submenu) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                
                // Close all sibling submenus at the same level
                var parentUl = parentLi.parentElement;
                if (parentUl) {
                    Array.from(parentUl.children).forEach(function (sibling) {
                        if (sibling !== parentLi && sibling.classList.contains('mobile-nav-item') && sibling.classList.contains('has-submenu')) {
                            sibling.classList.remove('is-open');
                            var siblingSubmenu = sibling.querySelector(':scope > .mobile-submenu');
                            if (siblingSubmenu) {
                                siblingSubmenu.style.display = 'none';
                            }
                            var siblingArrow = sibling.querySelector('.arrow');
                            if (siblingArrow) {
                                siblingArrow.classList.remove('arrow-rotate');
                            }
                        }
                    });
                }
                
                var isOpen = parentLi.classList.toggle('is-open');
                submenu.style.display = isOpen ? 'block' : 'none';
                var arrow = item.querySelector('.arrow');
                if (arrow) {
                    arrow.classList.toggle('arrow-rotate', isOpen);
                }
            });
        });

        document.querySelectorAll('.desktop-header .navbar ul ul li.nav-has-children > a').forEach(function (item) {
            if (item.dataset.cmsNavBound === '1') {
                return;
            }
            item.dataset.cmsNavBound = '1';

            var parentLi = item.parentElement;
            if (!parentLi) {
                return;
            }
            var submenu = parentLi.querySelector(':scope > ul');
            if (!submenu) {
                return;
            }

            if (!item.classList.contains('nav-parent-link')) {
                item.classList.add('nav-parent-link');
            }
            if (!item.hasAttribute('aria-haspopup')) {
                item.setAttribute('aria-haspopup', 'true');
            }
            if (!item.hasAttribute('aria-expanded')) {
                item.setAttribute('aria-expanded', 'false');
            }

            function closeNestedSubmenu() {
                submenu.classList.remove('submenu-open');
                item.setAttribute('aria-expanded', 'false');
            }

            function positionNestedSubmenu() {
                parentLi.classList.remove('submenu-flip-left');
                if (window.innerWidth < 1111) {
                    return;
                }
                submenu.style.left = '';
                submenu.style.right = '';
                var rect = submenu.getBoundingClientRect();
                if (rect.right > window.innerWidth - 8) {
                    parentLi.classList.add('submenu-flip-left');
                }
            }

            function openNestedSubmenu() {
                document.querySelectorAll('.desktop-header .navbar ul.submenu-open').forEach(function (el) {
                    if (el !== submenu) {
                        el.classList.remove('submenu-open');
                    }
                });
                document.querySelectorAll('.desktop-header .navbar ul ul li.nav-has-children > a[aria-expanded="true"]').forEach(function (el) {
                    if (el !== item) {
                        el.setAttribute('aria-expanded', 'false');
                    }
                });
                submenu.classList.add('submenu-open');
                item.setAttribute('aria-expanded', 'true');
                positionNestedSubmenu();
            }

            item.addEventListener('click', function (e) {
                if (window.innerWidth < 1024) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                if (submenu.classList.contains('submenu-open')) {
                    closeNestedSubmenu();
                } else {
                    openNestedSubmenu();
                }
            });

            parentLi.addEventListener('mouseenter', function () {
                if (window.innerWidth >= 1024) {
                    openNestedSubmenu();
                }
            });
            parentLi.addEventListener('mouseleave', function (e) {
                if (window.innerWidth >= 1024 && !parentLi.contains(e.relatedTarget)) {
                    closeNestedSubmenu();
                }
            });
        });
    });
})();
</script>
        <?php
    }
}
