<?php
/**
 * VN Custom Menu Element - Examples
 *
 * File này chứa các ví dụ về cách sử dụng plugin trong code
 *
 * @package VN_Custom_Menu_Element
 * @author VN
 * @link https://wpmasterynow.com/
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ============================================
 * CÁCH 1: SỬ DỤNG SHORTCODE TRONG TEMPLATE
 * ============================================
 */

// Ví dụ 1: Hiển thị menu đơn giản
echo do_shortcode( '[vn_custom_menu menu="main-menu"]' );

// Ví dụ 2: Menu với class tùy chỉnh
echo do_shortcode( '[vn_custom_menu menu="footer-menu" class="my-footer-menu"]' );

// Ví dụ 3: Menu ẩn trên mobile
echo do_shortcode( '[vn_custom_menu menu="desktop-only" hide_on_mobile="true"]' );

// Ví dụ 4: Menu chỉ hiển thị trên mobile
echo do_shortcode( '[vn_custom_menu menu="mobile-menu" hide_on_tablet="true" hide_on_desktop="true"]' );

// Ví dụ 5: Menu với đầy đủ tùy chọn
echo do_shortcode( '[vn_custom_menu menu="primary" class="custom-nav" hide_on_mobile="false" hide_on_tablet="false" hide_on_desktop="false"]' );

/**
 * ============================================
 * CÁCH 2: SỬ DỤNG TRONG PAGE BUILDER
 * ============================================
 */

/*
Sử dụng trong UX Builder:
1. Mở page/post editor với UX Builder
2. Tìm element "VN Custom Menu" trong tab Content
3. Kéo thả vào vị trí mong muốn
4. Cấu hình các tùy chọn:
	- Chọn menu
	- Thêm CSS class
	- Tùy chọn responsive
*/

/**
 * ============================================
 * CÁCH 3: TÙY CHỈNH CSS TRONG CHILD THEME
 * ============================================
 */

/*
Thêm vào file: /wp-content/themes/vn-theme/sass/vntheme.scss

// Tùy chỉnh menu chính
.vn-custom-menu-wrapper.my-custom-menu {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	padding: 1.5rem;
	border-radius: 10px;

	.vn-menu-list {
		justify-content: space-between;

		li a {
			color: #ffffff;
			font-weight: 600;
			text-transform: uppercase;
			font-size: 14px;
			letter-spacing: 0.5px;

			&:hover {
				color: #ffd700;
				transform: translateY(-2px);
			}
		}
	}

	// Submenu style
	.sub-menu {
		background: rgba(255, 255, 255, 0.95);
		backdrop-filter: blur(10px);
		border-radius: 8px;
		border: none;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);

		li a {
			color: #333;

			&:hover {
				background: #f8f9fa;
				color: #667eea;
			}
		}
	}
}

// Menu cho mobile
@media screen and (max-width: 549px) {
	.vn-custom-menu-wrapper {
		padding: 1rem;

		.vn-menu-list {
			flex-direction: column;

			li a {
				padding: 1rem;
				border-bottom: 1px solid rgba(255, 255, 255, 0.1);
			}
		}
	}
}
*/

/**
 * ============================================
 * CÁCH 4: TẠO MENU ĐỘNG TRONG FUNCTIONS.PHP
 * ============================================
 */

/**
 * Hiển thị menu dựa trên điều kiện
 */
function vn_display_conditional_menu() {
	if ( is_user_logged_in() ) {
		// Menu cho user đã đăng nhập
		echo do_shortcode( '[vn_custom_menu menu="member-menu" class="logged-in-menu"]' );
	} else {
		// Menu cho guest
		echo do_shortcode( '[vn_custom_menu menu="guest-menu" class="guest-menu"]' );
	}
}

/**
 * Hiển thị menu theo loại trang
 */
function vn_display_page_specific_menu() {
	if ( is_front_page() ) {
		echo do_shortcode( '[vn_custom_menu menu="home-menu"]' );
	} elseif ( is_shop() || is_product() ) {
		echo do_shortcode( '[vn_custom_menu menu="shop-menu"]' );
	} elseif ( is_single() ) {
		echo do_shortcode( '[vn_custom_menu menu="post-menu"]' );
	} else {
		echo do_shortcode( '[vn_custom_menu menu="default-menu"]' );
	}
}

/**
 * Hook menu vào vị trí cụ thể trong theme
 */
add_action( 'flatsome_after_header', 'vn_add_custom_menu_after_header' );
function vn_add_custom_menu_after_header() {
	if ( is_page( 'about' ) ) {
		echo do_shortcode( '[vn_custom_menu menu="about-submenu" class="page-submenu"]' );
	}
}

/**
 * ============================================
 * CÁCH 5: TẠO WIDGET MENU TÙY CHỈNH
 * ============================================
 */

/**
 * Widget hiển thị menu trong sidebar
 */
class VN_Custom_Menu_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'vn_custom_menu_widget',
			__( 'VN Custom Menu', 'vn-custom-menu-element' ),
			array( 'description' => __( 'Hiển thị custom menu', 'vn-custom-menu-element' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$menu  = ! empty( $instance['menu'] ) ? $instance['menu'] : '';
		$class = ! empty( $instance['class'] ) ? $instance['class'] : '';

		if ( $menu ) {
			echo do_shortcode( sprintf( '[vn_custom_menu menu="%s" class="%s"]', esc_attr( $menu ), esc_attr( $class ) ) );
		}

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$menu  = ! empty( $instance['menu'] ) ? $instance['menu'] : '';
		$class = ! empty( $instance['class'] ) ? $instance['class'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'vn-custom-menu-element' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
					type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'menu' ) ); ?>">
				<?php esc_html_e( 'Menu Slug:', 'vn-custom-menu-element' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'menu' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'menu' ) ); ?>" 
					type="text" value="<?php echo esc_attr( $menu ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>">
				<?php esc_html_e( 'CSS Class:', 'vn-custom-menu-element' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>" 
					type="text" value="<?php echo esc_attr( $class ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['menu']  = ( ! empty( $new_instance['menu'] ) ) ? sanitize_text_field( $new_instance['menu'] ) : '';
		$instance['class'] = ( ! empty( $new_instance['class'] ) ) ? sanitize_html_class( $new_instance['class'] ) : '';
		return $instance;
	}
}

// Đăng ký widget
add_action(
	'widgets_init',
	function () {
		register_widget( 'VN_Custom_Menu_Widget' );
	}
);

/**
 * ============================================
 * CÁCH 6: SỬ DỤNG VỚI GUTENBERG BLOCK
 * ============================================
 */

/**
 * Thêm vào Gutenberg như shortcode block
 *
 * 1. Trong editor, thêm block "Shortcode"
 * 2. Nhập: [vn_custom_menu menu="your-menu"]
 * 3. Preview và publish
 */

/**
 * ============================================
 * CÁCH 7: API USAGE - LẤY MENU DATA
 * ============================================
 */

/**
 * Lấy instance của plugin
 */
function vn_get_menu_instance() {
	return VN_Custom_Menu_Element::get_instance();
}

/**
 * Render menu programmatically
 */
function vn_render_menu( $menu_slug, $args = array() ) {
	$defaults = array(
		'menu'            => $menu_slug,
		'class'           => '',
		'hide_on_mobile'  => 'false',
		'hide_on_tablet'  => 'false',
		'hide_on_desktop' => 'false',
	);

	$atts = wp_parse_args( $args, $defaults );

	$instance = vn_get_menu_instance();
	return $instance->render_menu_shortcode( $atts );
}

// Sử dụng
echo vn_render_menu(
	'main-menu',
	array(
		'class'          => 'my-custom-class',
		'hide_on_mobile' => 'true',
	)
);
