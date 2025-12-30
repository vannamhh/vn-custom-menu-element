<?php
/**
 * Assets Handler Class
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
 * Class VN_Menu_Assets
 *
 * Handles CSS and JavaScript enqueuing.
 */
class VN_Menu_Assets {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue CSS styles.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'vn-custom-menu-element',
			VN_MENU_PLUGIN_URL . 'assets/css/style.css',
			array(),
			VN_MENU_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue JavaScript files.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		// Menu navigation script (always loaded).
		wp_enqueue_script(
			'vn-custom-menu-navigation',
			VN_MENU_PLUGIN_URL . 'assets/js/menu-navigation.js',
			array(),
			VN_MENU_VERSION,
			true
		);

		// Page loader script (always loaded, will check for .ajax-menu in JS).
		wp_enqueue_script(
			'vn-custom-menu-page-loader',
			VN_MENU_PLUGIN_URL . 'assets/js/page-loader.js',
			array( 'jquery' ),
			VN_MENU_VERSION,
			true
		);

		// Localize script with AJAX data.
		wp_localize_script(
			'vn-custom-menu-page-loader',
			'vnMenuPageLoader',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'vn_menu_page_loader_nonce' ),
				'i18n'    => array(
					'loading' => __( 'Đang tải...', 'vn-custom-menu-element' ),
					'error'   => __( 'Đã xảy ra lỗi. Vui lòng thử lại sau.', 'vn-custom-menu-element' ),
				),
			)
		);
	}
}
