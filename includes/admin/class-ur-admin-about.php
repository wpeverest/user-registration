<?php
/**
 * About us Class
 *
 * Takes new users to About us Page.
 *
 * @package UserRegistration/Admin
 * @version 2.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * About class.
 */
class UR_Admin_About {

	/**
	 * Show the about page.
	 */
	public static function output() {

		wp_enqueue_script( 'ur-about-script', UR()->plugin_url() . '/chunks/main.js', array( 'wp-element', 'wp-blocks', 'wp-editor' ), UR()->version, true );
		wp_enqueue_style( 'ur-about-style', UR()->plugin_url() . '/assets/css/user-registration-about.css', array(), UR()->version );

		wp_localize_script(
			'ur-about-script',
			'_UR_',
			array(
				'adminURL'       => esc_url( admin_url() ),
				'siteURL'        => esc_url( home_url( '/' ) ),
				'urRestApiNonce' => wp_create_nonce( 'wp_rest' ),
				'restURL'        => rest_url(),
			)
		);

		if ( ! empty( $_GET['page'] ) && 'user-registration-about' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification

			ob_start();
			self::about_us_body();
			self::about_us_footer();
			exit;
		}
	}

	/**
	 * About Page body content.
	 *
	 * @since 1.0.0
	 */
	public static function about_us_body() {
		?>
			<body class="user-registration-about notranslate" translate="no">
				<div id="user-registration-about"></div>
			</body>
		<?php
	}

	/**
	 * About Page footer content.
	 *
	 * @since 1.0.0
	 */
	public static function about_us_footer() {
		if ( function_exists( 'wp_print_media_templates' ) ) {
			wp_print_media_templates();
		}
		wp_print_footer_scripts();
		wp_print_scripts( 'ur-about-script' );
		?>
		</html>
		<?php
	}
}
