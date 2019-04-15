<?php
/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
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
		if ( isset( $_GET['ur_preview'] ) ) {
			add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
			add_action( 'template_redirect', array( $this, 'handle_preview' ) );
		}
	}

	/**
	 * Limit page templates to singular pages only.
	 *
	 * @return string
	 */
	public static function template_include() {
		return locate_template( array( 'page.php', 'single.php', 'index.php' ) );
	}

	public function handle_preview() {
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

	public function form_preview_content( $content ) {
		$form_id = absint( $_GET['form_id'] );

		remove_filter( 'the_content', array( $this, 'form_preview_content_filter' ) );
		$content = do_shortcode( '[user_registration_form id="' . $form_id . '"]' );

		return $content;
	}
}

new UR_Preview();
