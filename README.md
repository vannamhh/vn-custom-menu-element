# VN Custom Menu Element

Plugin WordPress cho phép chèn menu vào bất kỳ vị trí nào trong nội dung trang thông qua Flatsome UX Builder.

## Tính năng

- ✅ Tạo element cho Flatsome UX Builder
- ✅ Chọn menu từ danh sách menu đã tạo trong Giao diện > Menus
- ✅ Thêm CSS class tùy chỉnh
- ✅ Tùy chọn responsive: Ẩn/hiện trên Mobile, Tablet, Desktop
- ✅ Hỗ trợ submenu
- ✅ Tự động highlight menu item hiện tại
- ✅ Sử dụng shortcode

## Cài đặt

1. Upload thư mục `vn-custom-menu-element` vào `/wp-content/plugins/`
2. Kích hoạt plugin trong WordPress Admin > Plugins
3. Plugin sẽ tự động đăng ký element với UX Builder

## Sử dụng

### Với UX Builder

1. Mở trang/bài viết bằng UX Builder
2. Tìm element "VN Custom Menu" trong danh sách Content
3. Kéo thả element vào vị trí mong muốn
4. Cấu hình:
   - **Chọn Menu**: Chọn menu từ dropdown
   - **CSS Class**: Thêm class tùy chỉnh (tùy chọn)
   - **Tùy chọn Responsive**:
     - Ẩn trên Mobile
     - Ẩn trên Tablet
     - Ẩn trên Desktop

### Với Shortcode

```php
[vn_custom_menu menu="main-menu" class="custom-class" hide_on_mobile="false" hide_on_tablet="false" hide_on_desktop="false"]
```

**Parameters:**
- `menu` (required): Tên hoặc slug của menu
- `class` (optional): CSS class tùy chỉnh
- `hide_on_mobile` (optional): true/false - Ẩn trên mobile
- `hide_on_tablet` (optional): true/false - Ẩn trên tablet
- `hide_on_desktop` (optional): true/false - Ẩn trên desktop

**Ví dụ:**

```php
// Hiển thị menu chính
[vn_custom_menu menu="main-menu"]

// Menu với class tùy chỉnh
[vn_custom_menu menu="footer-menu" class="my-custom-menu"]

// Menu ẩn trên mobile
[vn_custom_menu menu="desktop-menu" hide_on_mobile="true"]

// Menu chỉ hiển thị trên mobile
[vn_custom_menu menu="mobile-menu" hide_on_tablet="true" hide_on_desktop="true"]
```

## Tùy chỉnh CSS

Plugin đã bao gồm CSS cơ bản. Để tùy chỉnh thêm, thêm CSS vào file child theme:
`/wp-content/themes/vn-theme/sass/vntheme.scss`

**Ví dụ tùy chỉnh:**

```scss
// Tùy chỉnh menu
.vn-custom-menu-wrapper {
    background: #f5f5f5;
    padding: 1rem;
    
    .vn-menu-list {
        justify-content: center;
        
        li a {
            color: #333;
            font-weight: 600;
            
            &:hover {
                color: #0066cc;
            }
        }
    }
}

// Tùy chỉnh submenu
.vn-menu-list .sub-menu {
    border-radius: 5px;
    border: 1px solid #e0e0e0;
    
    li a {
        font-size: 0.9rem;
        
        &:hover {
            background: #f0f0f0;
        }
    }
}
```

## CSS Classes có sẵn

- `.vn-custom-menu-wrapper` - Container chính
- `.vn-custom-menu` - Nav element
- `.vn-menu-list` - UL menu list
- `.sub-menu` - Submenu
- `.current-menu-item` - Menu item hiện tại
- `.current-menu-ancestor` - Parent của menu item hiện tại
- `.menu-item-has-children` - Menu item có submenu
- `.hide-for-small` - Ẩn trên mobile
- `.hide-for-medium` - Ẩn trên tablet
- `.hide-for-large` - Ẩn trên desktop

## Responsive Breakpoints

- **Mobile**: < 550px
- **Tablet**: 550px - 849px
- **Desktop**: ≥ 850px

## Yêu cầu

- WordPress 5.0 trở lên
- PHP 7.4 trở lên
- Flatsome theme (cho UX Builder)

## Tác giả

**VN**  
Website: [https://wpmasterynow.com/](https://wpmasterynow.com/)

## Hỗ trợ

Nếu bạn gặp vấn đề hoặc có câu hỏi, vui lòng liên hệ qua website.

## Changelog

### 1.0.0 - 2025-11-04
- Phiên bản đầu tiên
- Tích hợp UX Builder element
- Hỗ trợ responsive
- Shortcode với đầy đủ tùy chọn
