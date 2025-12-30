# VN Custom Menu Element

Plugin WordPress cho phép chèn menu vào bất kỳ vị trí nào trong nội dung trang thông qua Flatsome UX Builder với tính năng AJAX Page Loader.

## Tính năng

- ✅ Tạo element cho Flatsome UX Builder
- ✅ Chọn menu từ danh sách menu đã tạo trong Giao diện > Menus
- ✅ Thêm CSS class tùy chỉnh
- ✅ Tùy chọn responsive: Ẩn/hiện trên Mobile, Tablet, Desktop
- ✅ Hỗ trợ submenu với accordion style
- ✅ Tự động highlight menu item hiện tại
- ✅ **AJAX Page Loader**: Tải nội dung động không reload trang
- ✅ **Content Caching**: Lưu cache nội dung đã tải để tăng tốc
- ✅ **History API**: Hỗ trợ browser back/forward navigation
- ✅ **Flatsome Integration**: Tự động reinitialize Accordion, Tabs sau AJAX

## Cấu trúc Plugin

```
vn-custom-menu-element/
├── vn-custom-menu-element.php    # Main plugin file
├── includes/
│   ├── class-vn-custom-menu-element.php   # Main class (Singleton)
│   ├── class-vn-menu-shortcode.php        # Shortcode handler
│   ├── class-vn-menu-ux-builder.php       # UX Builder integration
│   ├── class-vn-menu-assets.php           # CSS/JS enqueuing
│   ├── class-vn-menu-ajax-handler.php     # AJAX endpoint
│   └── index.php                          # Security file
├── assets/
│   ├── css/
│   │   ├── style.css             # All plugin styles
│   │   └── index.php
│   ├── js/
│   │   ├── menu-navigation.js    # Menu scroll navigation
│   │   ├── page-loader.js        # AJAX page loader
│   │   └── index.php
│   └── index.php
├── README.md
└── index.php
```

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
   - **AJAX Menu**: Bật để sử dụng AJAX Page Loader
   - **Content Container**: CSS selector để tải nội dung (mặc định: `.curriculum-description`)
   - **Hero Container**: CSS selector cho hero section (mặc định: `.northern-hero`)
   - **Tùy chọn Responsive**:
     - Ẩn trên Mobile
     - Ẩn trên Tablet
     - Ẩn trên Desktop

### Với Shortcode

```php
[vn_custom_menu menu="main-menu" class="custom-class" ajax="true" content_container=".my-content"]
```

**Parameters:**
- `menu` (required): Tên hoặc slug của menu
- `class` (optional): CSS class tùy chỉnh
- `ajax` (optional): true/false - Bật AJAX Page Loader
- `content_container` (optional): CSS selector cho container nội dung
- `hero_container` (optional): CSS selector cho hero section
- `hide_on_mobile` (optional): true/false - Ẩn trên mobile
- `hide_on_tablet` (optional): true/false - Ẩn trên tablet
- `hide_on_desktop` (optional): true/false - Ẩn trên desktop

**Ví dụ:**

```php
// Menu cơ bản
[vn_custom_menu menu="main-menu"]

// Menu với AJAX Page Loader
[vn_custom_menu menu="curriculum-menu" ajax="true" class="curriculum-menu"]

// Menu AJAX với custom containers
[vn_custom_menu menu="my-menu" ajax="true" content_container=".my-content" hero_container=".my-hero"]

// Menu ẩn trên mobile
[vn_custom_menu menu="desktop-menu" hide_on_mobile="true"]
```

## AJAX Page Loader

### Cách hoạt động

1. Khi user click vào menu link hoặc link trong nội dung:
   - Kiểm tra link có phải cùng page với hash không
   - Nếu đúng, lấy hash path (ví dụ: `#chuong-trinh/toan-hoc`)
   - Gửi AJAX request để lấy nội dung từ page path
   - Cập nhật nội dung với hiệu ứng fade
   - Cập nhật browser URL với History API

2. Cache:
   - Nội dung được cache trong session
   - Click lại link đã cache sẽ lấy từ cache ngay lập tức

### Link Format

Menu link cần có format:
```
https://your-site.com/page-slug/#path/to/content
```

Trong đó:
- `page-slug` là slug của trang hiện tại
- `#path/to/content` là đường dẫn đến page cần load nội dung

### CSS Classes

- `.ajax-menu` - Đánh dấu menu sử dụng AJAX (tự động thêm khi ajax="true")
- `.ajax-fade-transition` - Container có hiệu ứng fade
- `.ajax-fade-out` - Class thêm khi đang fade out
- `.ajax-fade-in` - Class thêm khi đang fade in
- `.ajax-hero-fade-out` - Class cho hero fade effect

## Tùy chỉnh CSS

Plugin đã bao gồm CSS đầy đủ cho menu và AJAX animations. Để tùy chỉnh thêm:

```scss
// Tùy chỉnh menu
.vn-custom-menu-wrapper {
    .vn-menu-list {
        // Custom styles
    }
}

// Tùy chỉnh AJAX animations
.ajax-fade-transition {
    transition-duration: 0.5s; // Thay đổi thời gian animation
}

// Tùy chỉnh curriculum menu
.curriculum-menu {
    --height-menu: 10rem; // Thay đổi chiều cao menu
    --brand-primary: #your-color;
}
```

## CSS Classes có sẵn

### Menu Classes
- `.vn-custom-menu-wrapper` - Container chính
- `.vn-custom-menu` - Nav element
- `.vn-menu-list` - UL menu list
- `.sub-menu` - Submenu
- `.current-menu-item` - Menu item hiện tại
- `.current-menu-ancestor` - Parent của menu item hiện tại
- `.menu-item-has-children` - Menu item có submenu

### Curriculum Menu Classes
- `.curriculum-menu` - Style đặc biệt cho curriculum
- `.camp-menu` - Variant cho camp menu

### AJAX Classes
- `.ajax-menu` - Đánh dấu menu AJAX
- `.ajax-fade-transition` - Container có hiệu ứng fade
- `.ajax-fade-out` - Fade out animation
- `.ajax-fade-in` - Fade in animation

### Responsive Classes
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
- jQuery (được WordPress tự động load)

## Security Features

- WordPress nonce verification cho AJAX requests
- Input sanitization với `sanitize_text_field()`
- Output escaping với `esc_html()`, `esc_attr()`, `esc_url()`
- Direct file access prevention

## Tác giả

**VN**  
Website: [https://wpmasterynow.com/](https://wpmasterynow.com/)

## Hỗ trợ

Nếu bạn gặp vấn đề hoặc có câu hỏi, vui lòng liên hệ qua website.

## Changelog

### 2.0.0 - 2025-01-xx
- Tái cấu trúc plugin với OOP pattern
- Thêm AJAX Page Loader với content caching
- Thêm History API support (back/forward navigation)
- Tích hợp Flatsome Accordion/Tabs reinitialize
- Thêm curriculum-menu styles
- Thêm fade animations cho AJAX transitions
- Cải thiện security với nonce verification
- Modular class structure

### 1.0.0 - 2025-11-04
- Phiên bản đầu tiên
- Tích hợp UX Builder element
- Hỗ trợ responsive
- Shortcode với đầy đủ tùy chọn
