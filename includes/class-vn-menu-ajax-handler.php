<?php
/**
 * AJAX Handler Class
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
 * Class VN_Menu_Ajax_Handler
 *
 * Handles AJAX requests for page content loading.
 */
class VN_Menu_Ajax_Handler {

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
		add_action( 'wp_ajax_vn_menu_load_page_content', array( $this, 'load_page_content' ) );
		add_action( 'wp_ajax_nopriv_vn_menu_load_page_content', array( $this, 'load_page_content' ) );
	}

	/**
	 * Load page content via AJAX.
	 *
	 * @return void
	 */
	public function load_page_content(): void {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'vn_menu_page_loader_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Yêu cầu không hợp lệ.', 'vn-custom-menu-element' ),
				),
				403
			);
		}

		// Validate and sanitize page path.
		if ( empty( $_POST['page_path'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Đường dẫn trang không hợp lệ.', 'vn-custom-menu-element' ),
				),
				400
			);
		}

		$page_path = sanitize_text_field( wp_unslash( $_POST['page_path'] ) );

		// Validate page path format (only allow alphanumeric, hyphens, and slashes).
		if ( ! preg_match( '/^[a-z0-9\-]+(\/[a-z0-9\-]+)*$/i', $page_path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Đường dẫn trang không hợp lệ.', 'vn-custom-menu-element' ),
				),
				400
			);
		}

		// Get current page context.
		$current_page = isset( $_POST['current_page'] ) ? sanitize_text_field( wp_unslash( $_POST['current_page'] ) ) : '';

		// Try to find the page/post by path.
		$content_data = $this->get_page_content_by_path( $page_path, $current_page );

		if ( ! $content_data ) {
			wp_send_json_error(
				array(
					'message' => __( 'Không tìm thấy nội dung.', 'vn-custom-menu-element' ),
				),
				404
			);
		}

		wp_send_json_success( $content_data );
	}

	/**
	 * Get page content by path.
	 *
	 * Ưu tiên tìm kiếm theo thứ tự:
	 * 1. Kết hợp current_page path + page_path (để phân biệt context)
	 * 2. Tìm theo full page_path
	 * 3. Chỉ fallback theo slug nếu KHÔNG có current_page
	 *
	 * @param string $page_path    The page path (e.g., "dai-hoc/nghe-thuat").
	 * @param string $current_page The current page URL path.
	 * @return array|null Page content data or null if not found.
	 */
	private function get_page_content_by_path( string $page_path, string $current_page ): ?array {
		$page      = null;
		$post_type = 'page';

		// Ưu tiên 1: Nếu có current_page, kết hợp parent path trước.
		if ( ! empty( $current_page ) ) {
			$parsed_url  = wp_parse_url( $current_page );
			$parent_path = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';

			if ( ! empty( $parent_path ) ) {
				// Kết hợp parent path với page path.
				$full_path = $parent_path . '/' . $page_path;
				$page      = get_page_by_path( $full_path, OBJECT, $post_type );
			}
		}

		// Ưu tiên 2: Tìm theo full page_path (trường hợp user nhập đầy đủ).
		if ( ! $page ) {
			$page = get_page_by_path( $page_path, OBJECT, $post_type );
		}

		// Ưu tiên 3: CHỈ dùng fallback slug khi KHÔNG có current_page (ngữ cảnh không rõ).
		// Tránh trường hợp trùng slug: dai-hoc/nghe-thuat vs cao-hoc/nghe-thuat.
		if ( ! $page && empty( $current_page ) ) {
			$posts = get_posts(
				array(
					'name'           => basename( $page_path ),
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( ! empty( $posts ) ) {
				$page = $posts[0];
			}
		}

		// Nếu vẫn không tìm thấy, thử filter cho custom content types.
		if ( ! $page ) {
			$content_data = apply_filters( 'vn_menu_get_custom_content', null, $page_path, $current_page );

			if ( $content_data ) {
				return $content_data;
			}

			return null;
		}

		// Setup post data for proper shortcode/block rendering.
		setup_postdata( $page );

		// Get rendered content.
		$content = apply_filters( 'the_content', $page->post_content );

		// Get featured image.
		$featured_img = '';
		if ( has_post_thumbnail( $page->ID ) ) {
			$featured_img = get_the_post_thumbnail_url( $page->ID, 'full' );
		}

		// Reset post data.
		wp_reset_postdata();

		return array(
			'content'      => $content,
			'title'        => get_the_title( $page->ID ),
			'featured_img' => $featured_img,
			'id'           => $page->ID,
			'path'         => $page_path,
		);
	}
}
