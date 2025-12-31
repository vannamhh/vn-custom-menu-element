/**
 * VN Menu Page Loader - Load page content via AJAX
 *
 * @package VN_Custom_Menu_Element
 * @author VN
 * @version 2.0.0
 *
 * HƯỚNG DẪN:
 * - Link hash (#dai-hoc/nghe-thuat) -> Load content via AJAX
 * - Link full URL -> Chuyển trang bình thường
 */

(function ($) {
    'use strict';

    // Exit early if localized data is not available.
    if (typeof vnMenuPageLoader === 'undefined') {
        return;
    }

    // Cache để lưu nội dung (key là pathname + pagePath).
    const contentCache = {};

    // Cache để lưu path hiện tại.
    let currentPath = null;
    let currentPagePathname = null;
    let isLoading = false;

    /**
     * Tạo cache key unique từ pathname và pagePath
     *
     * @param {string} pagePath - Hash path (ví dụ: "dai-hoc/nghe-thuat")
     * @return {string} Cache key unique
     */
    function getCacheKey(pagePath) {
        return window.location.pathname + '|' + pagePath;
    }

    // Selectors - có thể override từ data attributes.
    let menuSelector = '.ajax-menu';
    let contentSelector = '.curriculum-description .col-inner';
    let heroSelector = '.northern-hero';

    /**
     * Initialize selectors from menu data attributes
     */
    function initSelectors() {
        const menuContainer = document.querySelector(menuSelector);
        if (menuContainer) {
            if (menuContainer.dataset.contentSelector) {
                contentSelector = menuContainer.dataset.contentSelector;
            }
            if (menuContainer.dataset.heroSelector) {
                heroSelector = menuContainer.dataset.heroSelector;
            }
        }
    }

    /**
     * Load page content via AJAX
     *
     * @param {string} pagePath - Đường dẫn trang (ví dụ: "dai-hoc/nghe-thuat")
     * @param {boolean} isPopState - Đánh dấu nếu đây là sự kiện back/forward
     */
    function loadPageContent(pagePath, isPopState) {
        isPopState = isPopState || false;

        var currentPathname = window.location.pathname;
        var cacheKey = getCacheKey(pagePath);

        // Kiểm tra nếu đang load cùng một nội dung (cùng pathname + pagePath).
        if (isLoading || (currentPath === pagePath && currentPagePathname === currentPathname)) {
            return;
        }

        const targetContainer = document.querySelector(contentSelector);
        if (!targetContainer) {
            return;
        }

        isLoading = true;
        currentPath = pagePath;
        currentPagePathname = currentPathname;

        // Cập nhật active class cho menu.
        updateActiveMenuLink(pagePath);

        // Thêm class fade-out cho container và hero.
        targetContainer.classList.add('ajax-fade-out');
        const heroSection = document.querySelector(heroSelector);
        if (heroSection) {
            heroSection.classList.add('ajax-hero-fade-out');
        }

        // Chờ animation fade-out hoàn thành.
        setTimeout(function () {
            // Kiểm tra cache trước (dùng cacheKey để đảm bảo đúng nội dung cho từng trang).
            if (contentCache[cacheKey]) {
                renderCachedContent(cacheKey, pagePath, targetContainer, heroSection, isPopState);
                return;
            }

            // Hiển thị loading indicator.
            targetContainer.innerHTML = '<div class="vn-loading-content"><i class="icon-spinner"></i> ' + vnMenuPageLoader.i18n.loading + '</div>';
            targetContainer.classList.remove('ajax-fade-out');
            targetContainer.classList.add('ajax-fade-in');
        }, 300);

        // Gọi AJAX.
        $.ajax({
            url: vnMenuPageLoader.ajaxUrl,
            type: 'POST',
            data: {
                action: 'vn_menu_load_page_content',
                page_path: pagePath,
                current_page: window.location.pathname,
                nonce: vnMenuPageLoader.nonce
            },
            success: function (response) {
                handleAjaxSuccess(response, pagePath, targetContainer, isPopState);
            },
            error: function () {
                targetContainer.innerHTML = '<div class="vn-error-content">' + vnMenuPageLoader.i18n.error + '</div>';
                applyFadeInEffect(targetContainer, null);
            },
            complete: function () {
                isLoading = false;
            }
        });
    }

    /**
     * Render cached content
     *
     * @param {string} cacheKey - Unique cache key (pathname + pagePath)
     * @param {string} pagePath - Hash path để cập nhật URL
     * @param {Element} targetContainer - Container element
     * @param {Element} heroSection - Hero section element
     * @param {boolean} isPopState - Có phải từ popstate event không
     */
    function renderCachedContent(cacheKey, pagePath, targetContainer, heroSection, isPopState) {
        const cached = contentCache[cacheKey];
        targetContainer.innerHTML = cached.content || cached;

        if (cached.featured_img) {
            updateFeaturedImage(cached.featured_img);
        }

        if (cached.title) {
            updateHeroTitle(cached.title);
        }

        applyFadeInEffect(targetContainer, heroSection);

        if (!isPopState) {
            updateUrlAndTitle(pagePath, cached.title);
        }

        reinitFlatsomeComponents(targetContainer);
        attachContentLinkListeners();

        isLoading = false;
        $(document).trigger('vnMenu:pageContentLoaded', [cached]);
    }

    /**
     * Handle AJAX success response
     */
    function handleAjaxSuccess(response, pagePath, targetContainer, isPopState) {
        if (response.success && response.data.content) {
            var cacheKey = getCacheKey(pagePath);
            contentCache[cacheKey] = response.data;

            // Kiểm tra cả pagePath và pathname để đảm bảo render đúng nội dung.
            var isCurrentRequest = currentPath === pagePath && 
                                   currentPagePathname === window.location.pathname;

            if (isCurrentRequest) {
                targetContainer.innerHTML = response.data.content;

                if (response.data.featured_img) {
                    updateFeaturedImage(response.data.featured_img);
                }

                if (response.data.title) {
                    updateHeroTitle(response.data.title);
                }

                const heroSection = document.querySelector(heroSelector);
                applyFadeInEffect(targetContainer, heroSection);
                reinitFlatsomeComponents(targetContainer);
                attachContentLinkListeners();
            }

            if (!isPopState) {
                updateUrlAndTitle(pagePath, response.data.title);
            }

            $(document).trigger('vnMenu:pageContentLoaded', [response.data]);
        } else {
            var errorMsg = response.data && response.data.message
                ? response.data.message
                : vnMenuPageLoader.i18n.error;
            targetContainer.innerHTML = '<div class="vn-error-content">' + errorMsg + '</div>';
            applyFadeInEffect(targetContainer, null);
        }
    }

    /**
     * Áp dụng fade-in effect cho content và hero section
     */
    function applyFadeInEffect(targetContainer, heroSection) {
        targetContainer.classList.remove('ajax-fade-out');
        if (heroSection) {
            heroSection.classList.remove('ajax-hero-fade-out');
        }

        targetContainer.classList.add('ajax-fade-in');

        setTimeout(function () {
            targetContainer.classList.remove('ajax-fade-in');
        }, 400);
    }

    /**
     * Re-initialize Flatsome components sau khi load AJAX content
     */
    function reinitFlatsomeComponents(container) {
        var $container = $(container);

        setTimeout(function () {
            // Re-initialize accordions.
            $container.find('.accordion').each(function () {
                var $accordion = $(this);
                $accordion.find('.accordion-title').off('click');

                $accordion.find('.accordion-title').on('click', function (e) {
                    e.preventDefault();

                    var $title = $(this);
                    var $item = $title.parent('.accordion-item');
                    var $content = $item.find('.accordion-inner');
                    var $parentAccordion = $item.closest('.accordion');

                    if ($title.hasClass('active')) {
                        $title.removeClass('active');
                        $content.slideUp(200);
                    } else {
                        if (!$parentAccordion.hasClass('accordion-multiple')) {
                            $parentAccordion.find('.accordion-title.active').each(function () {
                                var $activeTitle = $(this);
                                $activeTitle.removeClass('active');
                                $activeTitle.parent('.accordion-item').find('.accordion-inner').slideUp(200);
                            });
                        }

                        $title.addClass('active');
                        $content.slideDown(200);
                    }
                });
            });

            // Re-initialize tabs.
            $container.find('.tabbed-content').each(function () {
                var $tabs = $(this);

                $tabs.find('.nav-tabs a').off('click').on('click', function (e) {
                    e.preventDefault();
                    var $link = $(this);
                    var target = $link.attr('href');

                    $link.parent().addClass('active').siblings().removeClass('active');
                    $tabs.find('.tab-panels .panel').removeClass('active');
                    $tabs.find(target).addClass('active');
                });
            });

            // Trigger Flatsome's own init nếu có.
            if (typeof Flatsome !== 'undefined' && typeof Flatsome.plugin === 'function') {
                try {
                    Flatsome.plugin('accordion', $container);
                    Flatsome.plugin('tabs', $container);
                } catch (e) {
                    // Flatsome plugin có thể không support.
                }
            }

            // Trigger lazy load images.
            if (typeof $.fn.lazyload !== 'undefined') {
                $container.find('img[data-src]').lazyload();
            }

            // Re-initialize expandable accordions.
            initExpandableAccordions($container);

            // Re-initialize expandable content.
            if (typeof window.ExpandableContent !== 'undefined') {
                window.ExpandableContent.refresh();
            }

            // Trigger custom events.
            $(document).trigger('flatsome-init-after', [$container]);
            $container.trigger('flatsome-ready');

        }, 100);
    }

    /**
     * Re-initialize expandable accordions
     */
    function initExpandableAccordions($container) {
        var initialShow = 10;
        var textExpand = "Nhiều câu hỏi hơn";
        var textCollapse = "Thu gọn";

        $container.find('.expandable-accordion').each(function () {
            var $accordionGroup = $(this);

            if ($accordionGroup.data('expandable-initialized')) {
                return;
            }

            var $items = $accordionGroup.children('.accordion-item');

            if ($items.length > initialShow) {
                var $hiddenItems = $items
                    .slice(initialShow)
                    .wrapAll('<div class="hidden-accordion-items" style="display: none;"></div>')
                    .parent();

                var $buttonWrapper = $(
                    '<div class="wrapper-button"><button class="load-more-accordion"><span class="icon-plus"></span><span class="button-label">' +
                    textExpand +
                    '</span></button></div>'
                );

                $accordionGroup.after($buttonWrapper);

                $buttonWrapper.find('.load-more-accordion').on('click', function (e) {
                    e.preventDefault();

                    var $actualButton = $(this);
                    var $label = $actualButton.find('.button-label');

                    $actualButton.toggleClass('active');

                    if ($actualButton.hasClass('active')) {
                        $label.text(textCollapse);
                    } else {
                        $label.text(textExpand);
                    }

                    $hiddenItems.slideToggle();
                });

                $accordionGroup.data('expandable-initialized', true);
            }
        });
    }

    /**
     * Cập nhật featured image
     */
    function updateFeaturedImage(imageUrl) {
        if (!imageUrl) {
            return;
        }

        var heroImg = document.querySelector(heroSelector + ' img');
        if (heroImg) {
            heroImg.src = imageUrl;

            if (heroImg.hasAttribute('srcset')) {
                heroImg.setAttribute('srcset', imageUrl);
            }

            if (heroImg.hasAttribute('data-src')) {
                heroImg.setAttribute('data-src', imageUrl);
            }
        }
    }

    /**
     * Cập nhật tiêu đề hero
     */
    function updateHeroTitle(title) {
        if (!title) {
            return;
        }

        var heroTitle = document.querySelector(heroSelector + ' .title h1');
        if (heroTitle) {
            heroTitle.innerHTML = decodeHTMLEntities(title);
        }
    }

    /**
     * Decode HTML entities một cách an toàn
     */
    function decodeHTMLEntities(text) {
        var textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
    }

    /**
     * Cập nhật active class cho menu link
     */
    function updateActiveMenuLink(pagePath) {
        var hash = '#' + pagePath;
        var menuContainer = document.querySelector(menuSelector);

        if (menuContainer) {
            menuContainer.querySelectorAll('a.active').forEach(function (a) {
                a.classList.remove('active');
            });

            var activeLink = menuContainer.querySelector('a[href="' + hash + '"]');
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
    }

    /**
     * Cập nhật URL và tiêu đề trang
     */
    function updateUrlAndTitle(path, title) {
        var hash = '#' + path;
        var currentPathname = window.location.pathname;
        var newUrl = currentPathname + hash;

        if (!title) {
            var link = document.querySelector(menuSelector + ' a[href="' + hash + '"]');
            if (link) {
                title = link.textContent.trim();
            }
        }

        if (title) {
            document.title = title;
        }

        history.pushState({ pagePath: path }, title || document.title, newUrl);
    }

    /**
     * Kiểm tra xem link có nằm trong ajax-menu không
     */
    function isAjaxMenuLink(linkElement) {
        var menuContainer = document.querySelector(menuSelector);
        if (!menuContainer) {
            return false;
        }
        return menuContainer.contains(linkElement);
    }

    /**
     * Kiểm tra xem link có nằm trong content không
     */
    function isContentLink(linkElement) {
        var contentContainer = document.querySelector('.curriculum-description');
        if (!contentContainer) {
            return false;
        }
        return contentContainer.contains(linkElement);
    }

    /**
     * Kiểm tra 2 URLs có cùng page không
     */
    function isSamePage(url1, url2) {
        try {
            var urlObj1 = new URL(url1, window.location.origin);
            var urlObj2 = new URL(url2, window.location.origin);

            return urlObj1.origin === urlObj2.origin &&
                urlObj1.pathname === urlObj2.pathname;
        } catch (e) {
            return false;
        }
    }

    /**
     * Xử lý click event
     */
    function handleLinkClick(e) {
        var link = e.currentTarget;
        var href = link.getAttribute('href');

        if (!href) {
            return;
        }

        // Chỉ xử lý hash link nếu nằm trong .ajax-menu.
        if (href.charAt(0) === '#' && href.length > 1 && isAjaxMenuLink(link)) {
            e.preventDefault();
            var pagePath = href.substring(1);
            loadPageContent(pagePath);
            return;
        }

        // Xử lý links trong content.
        if (isContentLink(link)) {
            if (href.indexOf('#') !== -1) {
                try {
                    var linkUrl = new URL(href, window.location.origin);
                    var currentUrl = window.location.href;

                    if (isSamePage(linkUrl.href, currentUrl)) {
                        var hash = linkUrl.hash;

                        if (hash && hash.length > 1) {
                            e.preventDefault();
                            var hashPath = hash.substring(1);

                            if (/^[a-z0-9-]+(\/[a-z0-9-]+)*$/i.test(hashPath)) {
                                loadPageContent(hashPath);
                            }
                        }
                        return;
                    }
                } catch (err) {
                    // URL không hợp lệ.
                }
            }
        }
    }

    /**
     * Attach event listeners cho links trong content
     */
    function attachContentLinkListeners() {
        var contentContainer = document.querySelector('.curriculum-description');
        if (!contentContainer) {
            return;
        }

        var contentLinks = contentContainer.querySelectorAll('a');
        contentLinks.forEach(function (link) {
            link.removeEventListener('click', handleLinkClick);
            link.addEventListener('click', handleLinkClick, false);
        });
    }

    /**
     * Xử lý sự kiện Back/Forward
     */
    function handlePopState(event) {
        var state = event.state;
        if (state && state.pagePath) {
            loadPageContent(state.pagePath, true);
        } else {
            var currentHash = window.location.hash;
            var menuContainer = document.querySelector(menuSelector);

            if (currentHash && menuContainer) {
                var menuLink = menuContainer.querySelector('a[href="' + currentHash + '"]');
                if (!menuLink) {
                    return;
                }
            }

            var targetContainer = document.querySelector(contentSelector);
            if (targetContainer) {
                targetContainer.innerHTML = '';
            }
            currentPath = null;

            if (menuContainer) {
                menuContainer.querySelectorAll('a.active').forEach(function (a) {
                    a.classList.remove('active');
                });
            }
        }
    }

    /**
     * Xử lý khi tải trang với hash
     */
    function handleInitialHash() {
        var initialHash = window.location.hash;

        if (initialHash && initialHash.length > 1) {
            var menuContainer = document.querySelector(menuSelector);

            if (menuContainer) {
                var menuLink = menuContainer.querySelector('a[href="' + initialHash + '"]');

                if (menuLink) {
                    var initialPath = initialHash.substring(1);
                    loadPageContent(initialPath);
                    return;
                }
            }

            var hashPath = initialHash.substring(1);

            if (/^[a-z0-9-]+(\/[a-z0-9-]+)*$/i.test(hashPath)) {
                loadPageContent(hashPath);
            }
        }
    }

    /**
     * Xử lý khi hash thay đổi
     */
    function handleHashChange() {
        var newHash = window.location.hash;

        if (!newHash || newHash.length <= 1) {
            return;
        }

        var menuContainer = document.querySelector(menuSelector);

        if (menuContainer) {
            var menuLink = menuContainer.querySelector('a[href="' + newHash + '"]');
            if (menuLink) {
                return;
            }
        }

        var hashPath = newHash.substring(1);

        if (/^[a-z0-9-]+(\/[a-z0-9-]+)*$/i.test(hashPath)) {
            loadPageContent(hashPath, false);
        }
    }

    /**
     * Khởi tạo auto-close cho submenu
     */
    function initSubmenuAutoClose() {
        var menuContainer = document.querySelector(menuSelector);
        if (!menuContainer) {
            return;
        }

        var AUTO_CLOSE_DELAY = 1800;
        var closeTimeouts = new WeakMap();

        var menuItemsWithSubmenu = menuContainer.querySelectorAll('li.menu-item-has-children, li.has-dropdown');

        menuItemsWithSubmenu.forEach(function (menuItem) {
            var submenu = menuItem.querySelector('ul.sub-menu, .nav-dropdown, ul.children');
            if (!submenu) {
                return;
            }

            menuItem.addEventListener('mouseleave', function () {
                var isOpen = menuItem.classList.contains('current-dropdown') ||
                    menuItem.classList.contains('active') ||
                    menuItem.classList.contains('open');

                if (!isOpen) {
                    return;
                }

                if (closeTimeouts.has(menuItem)) {
                    clearTimeout(closeTimeouts.get(menuItem));
                }

                var timeoutId = setTimeout(function () {
                    menuItem.classList.remove('current-dropdown', 'active', 'open', 'is-open');

                    if (submenu) {
                        submenu.style.display = '';
                        submenu.classList.remove('open', 'active', 'is-open');
                    }

                    closeTimeouts.delete(menuItem);
                }, AUTO_CLOSE_DELAY);

                closeTimeouts.set(menuItem, timeoutId);
            });

            menuItem.addEventListener('mouseenter', function () {
                if (closeTimeouts.has(menuItem)) {
                    clearTimeout(closeTimeouts.get(menuItem));
                    closeTimeouts.delete(menuItem);
                }
            });
        });
    }

    /**
     * Khởi tạo page loader
     */
    function initPageLoader() {
        var menuContainer = document.querySelector(menuSelector);

        if (!menuContainer) {
            return;
        }

        // Initialize selectors from data attributes.
        initSelectors();

        // Thêm class transition cho content container.
        var targetContainer = document.querySelector(contentSelector);
        if (targetContainer) {
            targetContainer.classList.add('ajax-fade-transition');
        }

        // Attach click event cho links trong menu.
        var menuLinks = menuContainer.querySelectorAll('a');
        menuLinks.forEach(function (link) {
            link.addEventListener('click', handleLinkClick, false);
        });

        // Attach click event cho links trong content ban đầu.
        attachContentLinkListeners();

        // Observer để handle dynamic menu items.
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.tagName === 'A') {
                        node.addEventListener('click', handleLinkClick, false);
                    } else if (node.nodeType === 1 && node.querySelectorAll) {
                        var links = node.querySelectorAll('a');
                        links.forEach(function (link) {
                            link.addEventListener('click', handleLinkClick, false);
                        });
                    }
                });
            });
        });

        observer.observe(menuContainer, {
            childList: true,
            subtree: true
        });

        // Khởi tạo auto-close cho submenu.
        initSubmenuAutoClose();

        // Xử lý Back/Forward browser.
        window.addEventListener('popstate', handlePopState);

        // Xử lý khi hash thay đổi.
        window.addEventListener('hashchange', handleHashChange);

        // Xử lý hash khi tải trang.
        handleInitialHash();
    }

    // Khởi tạo khi document ready.
    $(document).ready(function () {
        initPageLoader();
    });

})(jQuery);
