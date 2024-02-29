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

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}
		$installed_plugin_slugs = array_keys( get_plugins() );
		$allowed_plugin_slugs   = array(
			'everest-forms/everest-forms.php',
			'blockart-blocks/blockart.php',
			'learning-management-system/lms.php',
			'magazine-blocks/magazine-blocks.php',
		);

		$installed_theme_slugs = array_keys( wp_get_themes() );
		$current_theme         = get_stylesheet();

		wp_localize_script(
			'ur-about-script',
			'_UR_',
			array(
				'adminURL'       => esc_url( admin_url() ),
				'siteURL'        => esc_url( home_url( '/' ) ),
				'assetsURL'      => esc_url( UR()->plugin_url() . '/assets/' ),
				'urRestApiNonce' => wp_create_nonce( 'wp_rest' ),
				'newFormURL'     => esc_url( admin_url( '/admin.php?page=add-new-registration' ) ),
				'restURL'        => rest_url(),
				'version'        => UR()->version,
				'isPro'          => is_plugin_active( 'user-registration-pro/user-registration.php' ),
				'licensePlan'    => ur_get_license_plan(),
				'upgradeURL'     => esc_url_raw( 'https://wpuserregistration.com/pricing/?utm_source=addons-page&utm_medium=upgrade-button&utm_campaign=ur-upgrade-to-pro' ),
				'plugins'        => array_reduce(
					$allowed_plugin_slugs,
					function ( $acc, $curr ) use ( $installed_plugin_slugs ) {
						if ( in_array( $curr, $installed_plugin_slugs, true ) ) {

							if ( is_plugin_active( $curr ) ) {
								$acc[ $curr ] = 'active';
							} else {
								$acc[ $curr ] = 'inactive';
							}
						} else {
							$acc[ $curr ] = 'not-installed';
						}
						return $acc;
					},
					array()
				),
				'themes'         => array(
					'zakra'    => strpos( $current_theme, 'zakra' ) !== false ? 'active' : (
						in_array( 'zakra', $installed_theme_slugs, true ) ? 'inactive' : 'not-installed'
					),
					'colormag' => strpos( $current_theme, 'colormag' ) !== false || strpos( $current_theme, 'colormag-pro' ) !== false ? 'active' : (
						in_array( 'colormag', $installed_theme_slugs, true ) || in_array( 'colormag-pro', $installed_theme_slugs, true ) ? 'inactive' : 'not-installed'
					),
				),
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
