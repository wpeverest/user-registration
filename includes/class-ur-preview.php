<?php
/**
 * Form Preview.
 *
 * @package UserRegistration\Classes
 * @version 1.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle frontend forms.
 *
 * @class       UR_Preview
 * @version     1.0.0
 * @package     UserRegistration/Classes/
 */
class UR_Preview {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init hook function.
	 */
	public function init() {
		if ( is_user_logged_in() && ! is_admin() && isset( $_GET['ur_preview'] ) ) {
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_filter( 'template_include', array( $this, 'template_include' ) );
			add_action( 'template_redirect', array( $this, 'handle_preview' ) );
		}
	}

	/**
	 * Hook into pre_get_posts to limit posts.
	 *
	 * @param WP_Query $q Query instance.
	 */
	public function pre_get_posts( $q ) {
		// Limit one post to query.
		if ( $q->is_main_query() ) {
			$q->set( 'posts_per_page', 1 );
		}
	}

	/**
	 * Limit page templates to singular pages only.
	 *
	 * @return string
	 */
	public function template_include() {
		return locate_template( array( 'page.php', 'single.php' ) );
	}

	/**
	 * Handles the preview of form.
	 */
	public function handle_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( isset( $_GET['form_id'] ) ) {
			add_filter( 'the_title', array( $this, 'form_preview_title' ) );
			add_filter( 'the_content', array( $this, 'form_preview_content' ) );
		}
	}

	/**
	 * Filter the title and insert form preview title.
	 *
	 * @param  string $title Existing title.
	 * @return string
	 */
	public static function form_preview_title( $title ) {
		$form_id   = absint( $_GET['form_id'] );
		$form_data = get_post( $form_id );

		if ( in_the_loop() ) {
			/* translators: %s - Form name. */
			return sprintf( esc_html__( '%s &ndash; Preview', 'user-registration' ), sanitize_text_field( $form_data->post_title ) );
		}

		return $title;
	}

	/**
	 * Displays content of form preview.
	 *
	 * @param string $content Page/Post content.
	 * @return string
	 */
	public function form_preview_content( $content ) {
		$form_id = absint( $_GET['form_id'] );

		remove_filter( 'the_content', array( $this, 'form_preview_content_filter' ) );
		$content = do_shortcode( '[user_registration_form id="' . $form_id . '"]' );

		return $content;
	}
}

new UR_Preview();
