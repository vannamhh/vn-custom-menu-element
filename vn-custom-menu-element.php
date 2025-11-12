<?php
/**
 * Plugin Name: VN Custom Menu Element
 * Plugin URI: https://wpmasterynow.com/
 * Description: Tạo element Flatsome UX Builder cho phép chèn menu vào bất kỳ vị trí nào trong nội dung trang với đầy đủ tùy chọn responsive.
 * Version: 1.0.0
 * Author: VN
 * Author URI: https://wpmasterynow.com/
 * Text Domain: vn-custom-menu-element
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

declare(strict_types=1);

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
class VN_Custom_Menu_Element {

	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin instance
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		// Đăng ký shortcode
		add_shortcode( 'vn_custom_menu', array( $this, 'render_menu_shortcode' ) );

		// Đăng ký UX Builder element nếu Flatsome active
		add_action( 'ux_builder_setup', array( $this, 'register_ux_builder_element' ) );

		// Enqueue styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render menu shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_menu_shortcode( $atts ): string {
		// Parse shortcode attributes
		$atts = shortcode_atts(
			array(
				'menu'             => '',
				'class'            => '',
				'hide_on_mobile'   => 'false',
				'hide_on_tablet'   => 'false',
				'hide_on_desktop'  => 'false',
				'show_nav_buttons' => 'false',
				'columns'          => '4',
				'columns__md'      => '',
				'columns__sm'      => '',
			),
			$atts,
			'vn_custom_menu'
		);

		// Sanitize inputs.
		$menu_id_or_slug  = sanitize_text_field( $atts['menu'] );
		$custom_class     = sanitize_text_field( $atts['class'] );
		$hide_mobile      = filter_var( $atts['hide_on_mobile'], FILTER_VALIDATE_BOOLEAN );
		$hide_tablet      = filter_var( $atts['hide_on_tablet'], FILTER_VALIDATE_BOOLEAN );
		$hide_desktop     = filter_var( $atts['hide_on_desktop'], FILTER_VALIDATE_BOOLEAN );
		$show_nav_buttons = filter_var( $atts['show_nav_buttons'], FILTER_VALIDATE_BOOLEAN );
		$columns          = absint( $atts['columns'] );
		$columns_md       = ! empty( $atts['columns__md'] ) ? absint( $atts['columns__md'] ) : $columns;
		$columns_sm       = ! empty( $atts['columns__sm'] ) ? absint( $atts['columns__sm'] ) : $columns_md;

		// Validate menu exists
		if ( empty( $menu_id_or_slug ) ) {
			return '';
		}

		// Build responsive classes
		$responsive_classes = $this->build_responsive_classes( $hide_mobile, $hide_tablet, $hide_desktop );

		// Build container classes
		$container_classes = array( 'vn-custom-menu-wrapper' );

		// Add custom classes (handle multiple classes separated by space)
		if ( ! empty( $custom_class ) ) {
			$custom_class_array = array_filter( array_map( 'sanitize_html_class', explode( ' ', $custom_class ) ) );
			$container_classes  = array_merge( $container_classes, $custom_class_array );
		}

		// Add responsive classes
		if ( ! empty( $responsive_classes ) ) {
			$responsive_class_array = explode( ' ', $responsive_classes );
			$container_classes      = array_merge( $container_classes, $responsive_class_array );
		}

		// Build nav container class.
		$nav_class = 'vn-custom-menu';
		if ( $show_nav_buttons ) {
			$nav_class .= ' vn-menu-viewport';
		}

		// Build menu list classes with columns.
		$menu_list_class = 'vn-menu-list';
		if ( $show_nav_buttons && $columns > 0 ) {
			$menu_list_class .= sprintf(
				' large-columns-%d medium-columns-%d small-columns-%d',
				$columns,
				$columns_md,
				$columns_sm
			);
		}

		// Get menu HTML.
		$menu_html = wp_nav_menu(
			array(
				'menu'            => $menu_id_or_slug,
				'container'       => 'nav',
				'container_class' => $nav_class,
				'menu_class'      => $menu_list_class,
				'echo'            => false,
				'fallback_cb'     => false,
			)
		);

		// Return empty if menu not found.
		if ( empty( $menu_html ) ) {
			return '';
		}

		// Build navigation buttons.
		$prev_button = '';
		$next_button = '';

		if ( $show_nav_buttons ) {
			$prev_button = '<button class="vn-arrow vn-prev fb-icon" id="vnMenuPrev"><span class="fb-icon-arrow"></span></button>';

			$next_button = '<button class="vn-arrow vn-next fb-icon" id="vnMenuNext"><span class="fb-icon-arrow"></span></button>';
		}

		// Build final output.
		$output = sprintf(
			'<div class="%s">%s%s%s</div>',
			esc_attr( implode( ' ', $container_classes ) ),
			$prev_button,
			$menu_html,
			$next_button
		);

		return $output;
	}

	/**
	 * Build responsive classes
	 *
	 * @param bool $hide_mobile Hide on mobile
	 * @param bool $hide_tablet Hide on tablet
	 * @param bool $hide_desktop Hide on desktop
	 * @return string Responsive classes
	 */
	private function build_responsive_classes( bool $hide_mobile, bool $hide_tablet, bool $hide_desktop ): string {
		$classes = array();

		if ( $hide_mobile ) {
			$classes[] = 'hide-for-small';
		}
		if ( $hide_tablet ) {
			$classes[] = 'hide-for-medium';
		}
		if ( $hide_desktop ) {
			$classes[] = 'hide-for-large';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Get all available menus
	 *
	 * @return array Menu options for select field
	 */
	private function get_menu_options(): array {
		$menus   = wp_get_nav_menus();
		$options = array( '' => __( '-- Chọn menu --', 'vn-custom-menu-element' ) );

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				$options[ $menu->slug ] = esc_html( $menu->name );
			}
		}

		return $options;
	}

	/**
	 * Register UX Builder element
	 */
	public function register_ux_builder_element(): void {
		// Check if function exists
		if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
			return;
		}

		// Register element.
		add_ux_builder_shortcode(
			'vn_custom_menu',
			array(
				'name'     => __( 'VN Custom Menu', 'vn-custom-menu-element' ),
				'category' => __( 'Content', 'vn-custom-menu-element' ),
				'icon'     => 'text',
				'priority' => 1,
				'options'  => array(
					'menu'               => array(
						'type'    => 'select',
						'heading' => __( 'Chọn Menu', 'vn-custom-menu-element' ),
						'default' => '',
						'options' => $this->get_menu_options(),
					),
					'class'              => array(
						'type'        => 'textfield',
						'heading'     => __( 'CSS Class', 'vn-custom-menu-element' ),
						'description' => __( 'Thêm class CSS tùy chỉnh cho menu', 'vn-custom-menu-element' ),
						'default'     => '',
					),
					'show_nav_buttons'   => array(
						'type'    => 'radio-buttons',
						'heading' => __( 'Hiện nút điều hướng', 'vn-custom-menu-element' ),
						'default' => 'false',
						'options' => array(
							'false' => array( 'title' => __( 'No', 'vn-custom-menu-element' ) ),
							'true'  => array( 'title' => __( 'Yes', 'vn-custom-menu-element' ) ),
						),
					),
					'columns'            => array(
						'type'       => 'slider',
						'heading'    => __( 'Số cột hiển thị', 'vn-custom-menu-element' ),
						'responsive' => true,
						'default'    => 4,
						'max'        => 12,
						'min'        => 1,
						'conditions' => 'show_nav_buttons === "true"',
					),
					'responsive_options' => array(
						'type'    => 'group',
						'heading' => __( 'Tùy chọn Responsive', 'vn-custom-menu-element' ),
						'options' => array(
							'hide_on_mobile'  => array(
								'type'    => 'radio-buttons',
								'heading' => __( 'Ẩn trên Mobile', 'vn-custom-menu-element' ),
								'default' => 'false',
								'options' => array(
									'false' => array( 'title' => __( 'No', 'vn-custom-menu-element' ) ),
									'true'  => array( 'title' => __( 'Yes', 'vn-custom-menu-element' ) ),
								),
							),
							'hide_on_tablet'  => array(
								'type'    => 'radio-buttons',
								'heading' => __( 'Ẩn trên Tablet', 'vn-custom-menu-element' ),
								'default' => 'false',
								'options' => array(
									'false' => array( 'title' => __( 'No', 'vn-custom-menu-element' ) ),
									'true'  => array( 'title' => __( 'Yes', 'vn-custom-menu-element' ) ),
								),
							),
							'hide_on_desktop' => array(
								'type'    => 'radio-buttons',
								'heading' => __( 'Ẩn trên Desktop', 'vn-custom-menu-element' ),
								'default' => 'false',
								'options' => array(
									'false' => array( 'title' => __( 'No', 'vn-custom-menu-element' ) ),
									'true'  => array( 'title' => __( 'Yes', 'vn-custom-menu-element' ) ),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Enqueue plugin styles and scripts
	 */
	public function enqueue_assets(): void {
		// Enqueue CSS
		wp_enqueue_style(
			'vn-custom-menu-element',
			plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
			array(),
			self::VERSION,
			'all'
		);

		// Enqueue JavaScript for navigation
		wp_enqueue_script(
			'vn-custom-menu-navigation',
			plugin_dir_url( __FILE__ ) . 'assets/js/menu-navigation.js',
			array(),
			self::VERSION,
			true
		);
	}
}

/**
 * Initialize plugin
 */
function vn_custom_menu_element_init() {
	return VN_Custom_Menu_Element::get_instance();
}

// Start the plugin
add_action( 'plugins_loaded', 'vn_custom_menu_element_init' );
