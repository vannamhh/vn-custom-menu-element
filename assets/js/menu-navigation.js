/**
 * VN Custom Menu Navigation
 * Handles horizontal scrolling for menu with navigation buttons
 *
 * @package VN_Custom_Menu_Element
 * @author VN
 * @version 1.0.0
 */

(function () {
  "use strict";

  /**
   * Initialize menu navigation
   */
  function initMenuNavigation() {
    // Tìm tất cả các menu có navigation buttons
    const menuWrappers = document.querySelectorAll(".vn-custom-menu-wrapper");

    if (!menuWrappers.length) {
      return;
    }

    menuWrappers.forEach(function (wrapper) {
      const viewport = wrapper.querySelector(".vn-menu-viewport");
      const prevBtn = wrapper.querySelector(".vn-prev");
      const nextBtn = wrapper.querySelector(".vn-next");

      // Kiểm tra có đầy đủ elements không
      if (!viewport || !prevBtn || !nextBtn) {
        return;
      }

      // Scroll menu function
      function scrollMenu(direction) {
        const firstItem = viewport.querySelector("li");
        if (!firstItem) {
          return;
        }

        // Lấy chiều rộng item + gap/margin
        const itemWidth = firstItem.offsetWidth;
        const computedStyle = window.getComputedStyle(
          viewport.querySelector(".vn-menu-list"),
        );
        const gap = parseInt(computedStyle.gap) || 0;
        const scrollAmount = itemWidth + gap;

        const scrollLeft = direction === "next" ? scrollAmount : -scrollAmount;

        viewport.scrollBy({
          left: scrollLeft,
          behavior: "smooth",
        });
      }

      // Update button states
      function updateButtonStates() {
        const isAtStart = viewport.scrollLeft <= 0;
        const isAtEnd =
          viewport.scrollLeft + viewport.clientWidth >=
          viewport.scrollWidth - 1;

        prevBtn.disabled = isAtStart;
        nextBtn.disabled = isAtEnd;

        // Add visual classes
        prevBtn.classList.toggle("disabled", isAtStart);
        nextBtn.classList.toggle("disabled", isAtEnd);
      }

      // Check if scrolling is needed
      function checkScrollNeeded() {
        const needsScroll = viewport.scrollWidth > viewport.clientWidth;

        if (!needsScroll) {
          prevBtn.style.display = "none";
          nextBtn.style.display = "none";
        } else {
          prevBtn.style.display = "";
          nextBtn.style.display = "";
          updateButtonStates();
        }
      }

      // Event listeners
      prevBtn.addEventListener("click", function () {
        scrollMenu("prev");
      });

      nextBtn.addEventListener("click", function () {
        scrollMenu("next");
      });

      viewport.addEventListener("scroll", updateButtonStates);

      // Check on load and resize
      checkScrollNeeded();
      window.addEventListener("resize", function () {
        checkScrollNeeded();
      });

      // Initial button states
      updateButtonStates();
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMenuNavigation);
  } else {
    initMenuNavigation();
  }
})();

document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // DOM Portal — Level 2 Submenu
  // ============================================
  const portal = document.createElement('div');
  portal.id = 'vn-submenu-portal';
  portal.className = 'curriculum-menu';
  document.body.appendChild(portal);

  /**
   * Di chuyển submenu Level 2 vào portal container trên body.
   * Dùng position: absolute theo body để tránh bị cắt bởi overflow.
   */
  function moveToPortal(parentLi, subMenu) {
    var placeholder = document.createComment('portal-placeholder');
    subMenu._placeholder = placeholder;
    subMenu._topLevelItem = parentLi;
    parentLi._portaledSubmenu = subMenu;
    parentLi.insertBefore(placeholder, subMenu);

    var rect = parentLi.getBoundingClientRect();
    subMenu.style.position = 'absolute';
    subMenu.style.top = (rect.bottom + window.scrollY) + 'px';
    subMenu.style.left = (rect.left + window.scrollX) + 'px';
    subMenu.style.width = rect.width + 'px';
    subMenu.style.zIndex = '99';
    subMenu.classList.add('vn-menu-list');

    // Boundary check: tràn phải màn hình
    if (rect.left + rect.width > window.innerWidth) {
      subMenu.style.left = (rect.right - rect.width + window.scrollX) + 'px';
    }

    portal.appendChild(subMenu);
  }

  /**
   * Trả submenu từ portal về vị trí gốc trong DOM.
   * Xóa sạch inline styles và references.
   */
  function returnFromPortal(subMenu) {
    if (!subMenu || !subMenu._placeholder) return;
    var placeholder = subMenu._placeholder;
    var parentNode = placeholder.parentNode;
    if (!parentNode) return;

    parentNode.insertBefore(subMenu, placeholder);
    parentNode.removeChild(placeholder);

    delete subMenu._placeholder;
    delete subMenu._topLevelItem;
    if (parentNode._portaledSubmenu === subMenu) {
      delete parentNode._portaledSubmenu;
    }
    subMenu.classList.remove('vn-menu-list');
    subMenu.style.cssText = '';
  }

  /**
   * Đóng tất cả Level 2 submenus và trả portal về DOM gốc.
   */
  function closeAllLevel2Menus() {
    var portalSubs = portal.querySelectorAll('.sub-menu');
    portalSubs.forEach(function(sub) {
      sub.classList.remove('is-open');
      sub.querySelectorAll('.sub-menu.is-open').forEach(function(s) {
        s.classList.remove('is-open');
      });
      sub.querySelectorAll('.toggle-icon').forEach(function(i) {
        i.textContent = '+';
      });
      returnFromPortal(sub);
    });
    // Reset active states
    document.querySelectorAll('.curriculum-menu .vn-menu-list>li.active').forEach(function(el) {
      el.classList.remove('active');
    });
  }

  // ============================================
  // Xử lý Accordion bằng CLICK (Thay cho Hover)
  // ============================================
  const menuItems = document.querySelectorAll(
    ".vn-menu-list li",
  );

  menuItems.forEach((item) => {
    // Gán sự kiện Click cho thẻ li
    item.addEventListener("click", function (e) {
      // 1. Ngăn sự kiện nổi bọt
      e.stopPropagation();
      //e.preventDefault(); // Dòng này quan trọng: Chặn thẻ A chuyển trang lần đầu để mở menu

      // 2. Tìm menu con trực tiếp (R1: fallback khi submenu đang ở portal)
      const subMenu = this.querySelector(":scope > .sub-menu") || this._portaledSubmenu || null;
      const icon = this.querySelector(":scope > .toggle-icon");

      // 3. Tìm menu cha cấp cao nhất (R2: fallback khi click item trong portal)
      //    Dùng .vn-menu-viewport prefix để chỉ match top-level items gốc, không match
      //    items bên trong portal (portal'd submenu cũng có class .vn-menu-list)
      const topLevelItem = this.closest(".vn-menu-viewport .vn-menu-list > li")
        || (this.closest(".sub-menu") && this.closest(".sub-menu")._topLevelItem)
        || null;
      if (!topLevelItem) return; // Safety guard — portal edge case
      const menuList = topLevelItem.parentElement;
      
      // 4. Kiểm tra xem item hiện tại đã active chưa
      const isCurrentlyActive = topLevelItem.classList.contains("active");
      const isSubMenuOpen = subMenu && subMenu.classList.contains("is-open");

      // 5. ĐÓNG TẤT CẢ menu khác cùng cấp (top level siblings)
      const allTopLevelItems = menuList.querySelectorAll(":scope > li");
      allTopLevelItems.forEach((sibling) => {
        if (sibling !== topLevelItem) {
          // Trả portal submenu về trước khi đóng (R3)
          if (sibling._portaledSubmenu) returnFromPortal(sibling._portaledSubmenu);
          // Remove active class
          sibling.classList.remove("active");
          
          // Đóng tất cả submenu của sibling và submenu con bên trong
          const allSubMenus = sibling.querySelectorAll(".sub-menu");
          allSubMenus.forEach((sub) => {
            sub.classList.remove("is-open");
          });
          
          // Reset icons
          const allIcons = sibling.querySelectorAll(".toggle-icon");
          allIcons.forEach((ico) => {
            ico.textContent = "+";
          });
        }
      });

      // 6. Xử lý toggle cho menu hiện tại
      if (isCurrentlyActive && topLevelItem === this) {
        // Trả portal submenu về trước khi đóng
        if (topLevelItem._portaledSubmenu) returnFromPortal(topLevelItem._portaledSubmenu);
        // Nếu click vào menu top-level đang active -> Đóng và remove active
        topLevelItem.classList.remove("active");
        
        // Đóng tất cả submenu bên trong
        const allSubMenus = topLevelItem.querySelectorAll(".sub-menu");
        allSubMenus.forEach((sub) => {
          sub.classList.remove("is-open");
        });
        
        // Reset icons
        const allIcons = topLevelItem.querySelectorAll(".toggle-icon");
        allIcons.forEach((ico) => {
          ico.textContent = "+";
        });
        
        // Ngăn chuyển trang
        // e.preventDefault();
        return;
      }

      // 7. Thêm active class vào menu top level
      topLevelItem.classList.add("active");

      // 8. Xử lý submenu nếu có
      if (subMenu) {
        // Ngăn chuyển trang khi click vào item có submenu
        // e.preventDefault();
        
        // Đóng các submenu cùng cấp (không phải top level)
        const siblings = this.parentElement.querySelectorAll(
          ":scope > li.menu-item-has-children"
        );
        siblings.forEach((sibling) => {
          if (sibling !== this) {
            // Đóng submenu trực tiếp của sibling
            const siblingSub = sibling.querySelector(":scope > .sub-menu");
            const siblingIcon = sibling.querySelector(":scope > .toggle-icon");
            if (siblingSub) siblingSub.classList.remove("is-open");
            if (siblingIcon) siblingIcon.textContent = "+";
            
            // Đóng TẤT CẢ submenu lồng nhau bên trong sibling
            const allNestedSubMenus = sibling.querySelectorAll(".sub-menu");
            allNestedSubMenus.forEach((nestedSub) => {
              nestedSub.classList.remove("is-open");
            });
            
            // Reset tất cả icons bên trong sibling
            const allNestedIcons = sibling.querySelectorAll(".toggle-icon");
            allNestedIcons.forEach((nestedIcon) => {
              nestedIcon.textContent = "+";
            });
          }
        });

        // Toggle submenu hiện tại
        if (isSubMenuOpen) {
          // Trả portal trước khi đóng
          if (subMenu._placeholder) returnFromPortal(subMenu);
          // Đóng submenu và tất cả submenu con bên trong
          subMenu.classList.remove("is-open");
          if (icon) icon.textContent = "+";
          
          // Đóng tất cả submenu lồng nhau bên trong
          const allNestedSubMenus = subMenu.querySelectorAll(".sub-menu");
          allNestedSubMenus.forEach((nestedSub) => {
            nestedSub.classList.remove("is-open");
          });
          
          // Reset tất cả icons bên trong
          const allNestedIcons = subMenu.querySelectorAll(".toggle-icon");
          allNestedIcons.forEach((nestedIcon) => {
            nestedIcon.textContent = "+";
          });
        } else {
          // Mở submenu
          subMenu.classList.add("is-open");
          // Portal CHỈ cho Level 2 (con trực tiếp của top-level li trong viewport gốc)
          // Kiểm tra parentElement + phải nằm trong .vn-menu-viewport (không phải portal)
          if (subMenu.parentElement === topLevelItem && topLevelItem.closest('.vn-menu-viewport')) {
            moveToPortal(topLevelItem, subMenu);
          }
          if (icon) icon.textContent = "-";
        }
      } else {
        // Nếu là menu item không có submenu
        // Kiểm tra xem có phải là link thực không
        const link = this.querySelector(":scope > a");
        if (link && link.getAttribute("href") && link.getAttribute("href") !== "#") {
          // Cho phép chuyển trang
          return;
        } else {
          // Ngăn chuyển trang nếu là link giả (#)
          e.preventDefault();
        }
      }
    });
  });

  // Click ra ngoài phạm vi <li> thì đóng tất cả menu và remove active class
  document.addEventListener("click", function (e) {
    // Kiểm tra xem click có nằm trong bất kỳ <li> nào của menu không (R5: bao gồm portal)
    const clickedMenuItem = e.target.closest(".vn-menu-list li")
      || e.target.closest("#vn-submenu-portal li");
    
    if (!clickedMenuItem) {
      // Trả tất cả portal submenu về DOM gốc trước
      closeAllLevel2Menus();
      // Click ra ngoài tất cả <li> (có thể là khoảng trắng trong <ul> hoặc bên ngoài menu)
      // Đóng tất cả submenu
      document.querySelectorAll(".sub-menu.is-open").forEach((el) => {
        el.classList.remove("is-open");
      });
      document.querySelectorAll(".toggle-icon").forEach((el) => {
        el.textContent = "+";
      });
      
      // Remove tất cả active class
      document.querySelectorAll(".vn-menu-list > li.active").forEach((el) => {
        el.classList.remove("active");
      });
    }
  });

  // ============================================
  // Environment Listeners — đóng portal khi môi trường thay đổi
  // ============================================
  window.addEventListener('resize', closeAllLevel2Menus);
  document.querySelectorAll('.vn-menu-viewport').forEach(function(vp) {
    vp.addEventListener('scroll', closeAllLevel2Menus);
  });
});
