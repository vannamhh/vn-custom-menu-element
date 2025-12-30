<?php
/**
 * Plugin Name: VN Custom Menu Element
 * Plugin URI: https://wpmasterynow.com/
 * Description: Tạo element Flatsome UX Builder cho phép chèn menu vào bất kỳ vị trí nào trong nội dung trang với đầy đủ tùy chọn responsive và AJAX page loader.
 * Version: 2.0.0
 * Author: VN
 * Author URI: https://wpmasterynow.com/
 * Text Domain: vn-custom-menu-element
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package VN_Custom_Menu_Element
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
if ( ! defined( 'VN_MENU_VERSION' ) ) {
	define( 'VN_MENU_VERSION', '2.0.0' );
}

if ( ! defined( 'VN_MENU_PLUGIN_DIR' ) ) {
	define( 'VN_MENU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'VN_MENU_PLUGIN_URL' ) ) {
	define( 'VN_MENU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'VN_MENU_PLUGIN_FILE' ) ) {
	define( 'VN_MENU_PLUGIN_FILE', __FILE__ );
}

// Load plugin classes.
require_once VN_MENU_PLUGIN_DIR . 'includes/class-vn-custom-menu-element.php';
require_once VN_MENU_PLUGIN_DIR . 'includes/class-vn-menu-ajax-handler.php';
require_once VN_MENU_PLUGIN_DIR . 'includes/class-vn-menu-shortcode.php';
require_once VN_MENU_PLUGIN_DIR . 'includes/class-vn-menu-ux-builder.php';
require_once VN_MENU_PLUGIN_DIR . 'includes/class-vn-menu-assets.php';

/**
 * Initialize plugin
 *
 * @return VN_Custom_Menu_Element Plugin instance.
 */
function vn_custom_menu_element_init(): VN_Custom_Menu_Element {
	return VN_Custom_Menu_Element::get_instance();
}

// Start the plugin.
add_action( 'plugins_loaded', 'vn_custom_menu_element_init' );

/**
 * Activation hook
 */
function vn_custom_menu_element_activate(): void {
	// Flush rewrite rules on activation.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vn_custom_menu_element_activate' );

/**
 * Deactivation hook
 */
function vn_custom_menu_element_deactivate(): void {
	// Cleanup on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vn_custom_menu_element_deactivate' );
