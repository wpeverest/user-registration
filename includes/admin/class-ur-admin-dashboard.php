<?php
/**
 * Dashboard us Class
 *
 * Takes new users to Dashboard us Page.
 *
 * @package UserRegistration/Admin
 * @version 2.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard class.
 */
class UR_Admin_Dashboard {

	/**
	 * Show the Dashboard Page.
	 */
	public static function output() {
		wp_enqueue_script( 'ur-dashboard-script', UR()->plugin_url() . '/chunks/dashboard.js', array( 'wp-element', 'wp-blocks', 'wp-editor' ), UR()->version, true );

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
			'ur-dashboard-script',
			'_UR_DASHBOARD_',
			array(
				'adminURL'             => esc_url( admin_url() ),
				'settingsURL'          => esc_url( admin_url( '/admin.php?page=user-registration-settings' ) ),
				'siteURL'              => esc_url( home_url( '/' ) ),
				'liveDemoURL'          => esc_url_raw( 'https://userregistration.demoswp.net/' ),
				'assetsURL'            => esc_url( UR()->plugin_url() . '/assets/' ),
				'urRestApiNonce'       => wp_create_nonce( 'wp_rest' ),
				'newFormURL'           => esc_url( admin_url( '/admin.php?page=add-new-registration' ) ),
				'allFormsURL'          => esc_url( admin_url( '/admin.php?page=user-registration' ) ),
				'restURL'              => rest_url(),
				'version'              => UR()->version,
				'isPro'                => is_plugin_active( 'user-registration-pro/user-registration.php' ),
				'licensePlan'          => ur_get_license_plan(),
				'licenseActivationURL' => esc_url_raw( admin_url( '/admin.php?page=user-registration-settings&tab=license' ) ),
				'utmCampaign'          => UR()->utm_campaign,
				'upgradeURL'           => esc_url_raw( 'https://wpuserregistration.com/upgrade/?utm_campaign=' . UR()->utm_campaign ),
				'plugins'              => array_reduce(
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
				'themes'               => array(
					'zakra'    => strpos( $current_theme, 'zakra' ) !== false ? 'active' : (
						in_array( 'zakra', $installed_theme_slugs, true ) ? 'inactive' : 'not-installed'
					),
					'colormag' => strpos( $current_theme, 'colormag' ) !== false || strpos( $current_theme, 'colormag-pro' ) !== false ? 'active' : (
						in_array( 'colormag', $installed_theme_slugs, true ) || in_array( 'colormag-pro', $installed_theme_slugs, true ) ? 'inactive' : 'not-installed'
					),
				),
			)
		);

		if ( ! empty( $_GET['page'] ) && 'user-registration-dashboard' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification

			ob_start();
			self::dashboard_page_body();
			self::dashboard_page_footer();
			exit;
		}
	}

	/**
	 * Dashboard Page body content.
	 *
	 * @since 1.0.0
	 */
	public static function dashboard_page_body() {
		?>
			<body class="user-registration-dashboard notranslate" translate="no">
				<hr class="wp-header-end">
				<?php echo user_registration_plugin_main_header(); ?>
				<div id="user-registration-dashboard"></div>
			</body>
		<?php
	}

	/**
	 * Dashboard Page footer content.
	 *
	 * @since 1.0.0
	 */
	public static function dashboard_page_footer() {
		if ( function_exists( 'wp_print_media_templates' ) ) {
			wp_print_media_templates();
		}
		wp_print_footer_scripts();
		wp_print_scripts( 'ur-dashboard-script' );
		?>
		</html>
		<?php
	}
}
