<?php
/**
 * Main Plugin Class
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
 * Class VN_Custom_Menu_Element
 *
 * Main plugin class that initializes all components.
 */
class VN_Custom_Menu_Element {

	/**
	 * Plugin instance.
	 *
	 * @var VN_Custom_Menu_Element|null
	 */
	private static ?VN_Custom_Menu_Element $instance = null;

	/**
	 * Shortcode handler instance.
	 *
	 * @var VN_Menu_Shortcode|null
	 */
	private ?VN_Menu_Shortcode $shortcode = null;

	/**
	 * UX Builder handler instance.
	 *
	 * @var VN_Menu_UX_Builder|null
	 */
	private ?VN_Menu_UX_Builder $ux_builder = null;

	/**
	 * Assets handler instance.
	 *
	 * @var VN_Menu_Assets|null
	 */
	private ?VN_Menu_Assets $assets = null;

	/**
	 * AJAX handler instance.
	 *
	 * @var VN_Menu_Ajax_Handler|null
	 */
	private ?VN_Menu_Ajax_Handler $ajax_handler = null;

	/**
	 * Get plugin instance (Singleton pattern).
	 *
	 * @return VN_Custom_Menu_Element Plugin instance.
	 */
	public static function get_instance(): VN_Custom_Menu_Element {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - private to enforce singleton.
	 */
	private function __construct() {
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Initialize plugin components.
	 *
	 * @return void
	 */
	private function init_components(): void {
		$this->shortcode    = new VN_Menu_Shortcode();
		$this->ux_builder   = new VN_Menu_UX_Builder();
		$this->assets       = new VN_Menu_Assets();
		$this->ajax_handler = new VN_Menu_Ajax_Handler();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		// Load text domain for translations.
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'vn-custom-menu-element',
			false,
			dirname( plugin_basename( VN_MENU_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Get shortcode handler.
	 *
	 * @return VN_Menu_Shortcode|null Shortcode handler instance.
	 */
	public function get_shortcode(): ?VN_Menu_Shortcode {
		return $this->shortcode;
	}

	/**
	 * Get UX Builder handler.
	 *
	 * @return VN_Menu_UX_Builder|null UX Builder handler instance.
	 */
	public function get_ux_builder(): ?VN_Menu_UX_Builder {
		return $this->ux_builder;
	}

	/**
	 * Get Assets handler.
	 *
	 * @return VN_Menu_Assets|null Assets handler instance.
	 */
	public function get_assets(): ?VN_Menu_Assets {
		return $this->assets;
	}

	/**
	 * Get AJAX handler.
	 *
	 * @return VN_Menu_Ajax_Handler|null AJAX handler instance.
	 */
	public function get_ajax_handler(): ?VN_Menu_Ajax_Handler {
		return $this->ajax_handler;
	}
}
