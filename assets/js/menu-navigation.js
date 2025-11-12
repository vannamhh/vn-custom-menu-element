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
  // Xử lý Accordion bằng CLICK (Thay cho Hover)
  const menuItems = document.querySelectorAll(
    ".vn-menu-list li",
  );

  menuItems.forEach((item) => {
    // Gán sự kiện Click cho thẻ li
    item.addEventListener("click", function (e) {
      // 1. Ngăn sự kiện nổi bọt
      e.stopPropagation();
      e.preventDefault(); // Dòng này quan trọng: Chặn thẻ A chuyển trang lần đầu để mở menu

      // 2. Tìm menu con trực tiếp
      const subMenu = this.querySelector(":scope > .sub-menu");
      const icon = this.querySelector(":scope > .toggle-icon");

      // 3. Tìm menu cha cấp cao nhất (top level)
      const topLevelItem = this.closest(".vn-menu-list > li");
      const menuList = topLevelItem.parentElement;
      
      // 4. Kiểm tra xem item hiện tại đã active chưa
      const isCurrentlyActive = topLevelItem.classList.contains("active");
      const isSubMenuOpen = subMenu && subMenu.classList.contains("is-open");

      // 5. ĐÓNG TẤT CẢ menu khác cùng cấp (top level siblings)
      const allTopLevelItems = menuList.querySelectorAll(":scope > li");
      allTopLevelItems.forEach((sibling) => {
        if (sibling !== topLevelItem) {
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
        e.preventDefault();
        return;
      }

      // 7. Thêm active class vào menu top level
      topLevelItem.classList.add("active");

      // 8. Xử lý submenu nếu có
      if (subMenu) {
        // Ngăn chuyển trang khi click vào item có submenu
        e.preventDefault();
        
        // Đóng các submenu cùng cấp (không phải top level)
        const siblings = this.parentElement.querySelectorAll(
          ":scope > li.menu-item-has-children"
        );
        siblings.forEach((sibling) => {
          if (sibling !== this) {
            const siblingSub = sibling.querySelector(":scope > .sub-menu");
            const siblingIcon = sibling.querySelector(":scope > .toggle-icon");
            if (siblingSub) siblingSub.classList.remove("is-open");
            if (siblingIcon) siblingIcon.textContent = "+";
          }
        });

        // Toggle submenu hiện tại
        if (isSubMenuOpen) {
          // Đóng submenu
          subMenu.classList.remove("is-open");
          if (icon) icon.textContent = "+";
        } else {
          // Mở submenu
          subMenu.classList.add("is-open");
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

  // Tùy chọn: Click ra ngoài khoảng trắng thì đóng tất cả menu và remove active class
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".vn-menu-list")) {
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
});
