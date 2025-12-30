<?php
/**
 * Shortcode Handler Class
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
 * Class VN_Menu_Shortcode
 *
 * Handles shortcode registration and rendering.
 */
class VN_Menu_Shortcode {

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
		add_shortcode( 'vn_custom_menu', array( $this, 'render_menu_shortcode' ) );
	}

	/**
	 * Render menu shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_menu_shortcode( $atts ): string {
		// Ensure $atts is an array.
		$atts = is_array( $atts ) ? $atts : array();

		// Parse shortcode attributes.
		$atts = shortcode_atts(
			array(
				'menu'               => '',
				'class'              => '',
				'hide_on_mobile'     => 'false',
				'hide_on_tablet'     => 'false',
				'hide_on_desktop'    => 'false',
				'show_nav_buttons'   => 'false',
				'columns'            => '4',
				'columns__md'        => '',
				'columns__sm'        => '',
				'enable_ajax_loader' => 'false',
				'menu_selector'      => '.ajax-menu',
				'content_selector'   => '.curriculum-description .col-inner',
				'hero_selector'      => '.northern-hero',
			),
			$atts,
			'vn_custom_menu'
		);

		// Sanitize inputs.
		$menu_id_or_slug    = sanitize_text_field( $atts['menu'] );
		$custom_class       = sanitize_text_field( $atts['class'] );
		$hide_mobile        = filter_var( $atts['hide_on_mobile'], FILTER_VALIDATE_BOOLEAN );
		$hide_tablet        = filter_var( $atts['hide_on_tablet'], FILTER_VALIDATE_BOOLEAN );
		$hide_desktop       = filter_var( $atts['hide_on_desktop'], FILTER_VALIDATE_BOOLEAN );
		$show_nav_buttons   = filter_var( $atts['show_nav_buttons'], FILTER_VALIDATE_BOOLEAN );
		$enable_ajax_loader = filter_var( $atts['enable_ajax_loader'], FILTER_VALIDATE_BOOLEAN );
		$columns            = absint( $atts['columns'] );
		$columns_md         = ! empty( $atts['columns__md'] ) ? absint( $atts['columns__md'] ) : $columns;
		$columns_sm         = ! empty( $atts['columns__sm'] ) ? absint( $atts['columns__sm'] ) : $columns_md;

		// Validate menu exists.
		if ( empty( $menu_id_or_slug ) ) {
			return '';
		}

		// Build responsive classes.
		$responsive_classes = $this->build_responsive_classes( $hide_mobile, $hide_tablet, $hide_desktop );

		// Build container classes.
		$container_classes = array( 'vn-custom-menu-wrapper' );

		// Add ajax-menu class if AJAX loader is enabled.
		if ( $enable_ajax_loader ) {
			$container_classes[] = 'ajax-menu';
		}

		// Add custom classes (handle multiple classes separated by space).
		if ( ! empty( $custom_class ) ) {
			$custom_class_array = array_filter( array_map( 'sanitize_html_class', explode( ' ', $custom_class ) ) );
			$container_classes  = array_merge( $container_classes, $custom_class_array );
		}

		// Add responsive classes.
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
				'container_class' => esc_attr( $nav_class ),
				'menu_class'      => esc_attr( $menu_list_class ),
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
			$prev_button = '<button class="vn-arrow vn-prev fb-icon" aria-label="' . esc_attr__( 'Previous', 'vn-custom-menu-element' ) . '"><span class="fb-icon-arrow"></span></button>';
			$next_button = '<button class="vn-arrow vn-next fb-icon" aria-label="' . esc_attr__( 'Next', 'vn-custom-menu-element' ) . '"><span class="fb-icon-arrow"></span></button>';
		}

		// Build data attributes for AJAX loader.
		$data_attrs = '';
		if ( $enable_ajax_loader ) {
			$data_attrs = sprintf(
				' data-menu-selector="%s" data-content-selector="%s" data-hero-selector="%s"',
				esc_attr( sanitize_text_field( $atts['menu_selector'] ) ),
				esc_attr( sanitize_text_field( $atts['content_selector'] ) ),
				esc_attr( sanitize_text_field( $atts['hero_selector'] ) )
			);
		}

		// Build final output.
		$output = sprintf(
			'<div class="%s"%s>%s%s%s</div>',
			esc_attr( implode( ' ', $container_classes ) ),
			$data_attrs,
			$prev_button,
			$menu_html,
			$next_button
		);

		return $output;
	}

	/**
	 * Build responsive classes.
	 *
	 * @param bool $hide_mobile  Hide on mobile.
	 * @param bool $hide_tablet  Hide on tablet.
	 * @param bool $hide_desktop Hide on desktop.
	 * @return string Responsive classes.
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
}
