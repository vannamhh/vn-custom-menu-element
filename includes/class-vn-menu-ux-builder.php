<?php
/**
 * UX Builder Handler Class
 *
 * @package VN_Custom_Menu_Element
 * @since 2.0.0
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_Menu_UX_Builder
 *
 * Handles Flatsome UX Builder element registration.
 */
class VN_Menu_UX_Builder {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'ux_builder_setup', array( $this, 'register_ux_builder_element' ) );
	}

	/**
	 * Get all available menus.
	 *
	 * @return array Menu options for select field.
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
	 * Register UX Builder element.
	 *
	 * @return void
	 */
	public function register_ux_builder_element(): void {
		// Check if function exists.
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
					'ajax_options'       => array(
						'type'    => 'group',
						'heading' => __( 'AJAX Page Loader', 'vn-custom-menu-element' ),
						'options' => array(
							'enable_ajax_loader' => array(
								'type'        => 'radio-buttons',
								'heading'     => __( 'Bật AJAX Loader', 'vn-custom-menu-element' ),
								'description' => __( 'Load nội dung trang bằng AJAX khi click menu', 'vn-custom-menu-element' ),
								'default'     => 'false',
								'options'     => array(
									'false' => array( 'title' => __( 'No', 'vn-custom-menu-element' ) ),
									'true'  => array( 'title' => __( 'Yes', 'vn-custom-menu-element' ) ),
								),
							),
							'content_selector'   => array(
								'type'        => 'textfield',
								'heading'     => __( 'Content Selector', 'vn-custom-menu-element' ),
								'description' => __( 'CSS selector cho vùng nội dung', 'vn-custom-menu-element' ),
								'default'     => '.curriculum-description .col-inner',
								'conditions'  => 'enable_ajax_loader === "true"',
							),
							'hero_selector'      => array(
								'type'        => 'textfield',
								'heading'     => __( 'Hero Selector', 'vn-custom-menu-element' ),
								'description' => __( 'CSS selector cho hero section', 'vn-custom-menu-element' ),
								'default'     => '.northern-hero',
								'conditions'  => 'enable_ajax_loader === "true"',
							),
						),
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
}
