<?php
/**
 * Welcome Screen
 *
 * @package UserRegistration\Admin
 * @since 4.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class UR_Admin_Welcome
 *
 * Responsible for registering the welcome page in admin
 * and loading the React application instead of PHP-rendered HTML.
 *
 * @since 4.5.0
 */
class UR_Admin_Welcome {

	/**
	 * Initialize hooks.
	 *
	 * @since 4.5.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'render_welcome_page' ), 30 );
	}

	/**
	 * Register the welcome page inside WP Admin menu.
	 *
	 * @since 4.5.0
	 */
	public static function add_menu() {
		add_menu_page(
			esc_html__( 'Welcome to User Registration', 'user-registration' ),
			'user registration onboard',
			'manage_options',
			'user-registration-welcome',
			''
		);
	}

	/**
	 * Enqueue
	 *
	 * @since 4.5.0
	 */
	public static function render_welcome_page() {

		$handle = 'ur-welcome-react-app';

		$welcome_asset = file_exists( UR()->plugin_path() . '/chunks/welcome.asset.php' ) ? require_once UR()->plugin_path() . '/chunks/welcome.asset.php' : array(
			'dependencies' => array(),
			'version'      => UR()->version,
		);
		wp_register_script( 'ur-setup-wizard-script', UR()->plugin_url() . '/chunks/welcome.js', $welcome_asset['dependencies'], $welcome_asset['version'], true );
		wp_enqueue_style( 'ur-setup-wizard-style', UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css', array(), UR()->version );
		wp_enqueue_script( 'ur-setup-wizard-script' );

		wp_enqueue_script( $handle );

		wp_enqueue_style(
			'ur-inter-font',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		/**
		 * Localized variables available inside React via window._UR_WIZARD_
		 */
		wp_localize_script(
			$handle,
			'_UR_WIZARD_',
			array(
				'adminURL'        => esc_url( admin_url() ),
				'siteURL'         => esc_url( home_url( '/' ) ),
				'urRestApiNonce'  => wp_create_nonce( 'wp_rest' ),
				'onBoardIconsURL' => esc_url( UR()->plugin_url() . '/assets/images/onboard-icons' ),
				'restURL'         => rest_url(),
				'adminEmail'      => get_option( 'admin_email' ),
				'isPro'           => defined( 'UR_PRO_ACTIVE' ) && UR_PRO_ACTIVE,
			)
		);

		/**
		 * Render the React page only on our admin page.
		 */
		if ( ! empty( $_GET['page'] ) && 'user-registration-welcome' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1"/>
				<title><?php esc_html_e( 'User Registration – Welcome', 'user-registration' ); ?></title>

				<?php wp_print_head_scripts(); ?>
			</head>

			<body class="ur-react-welcome-page">

				<!-- React Root Element -->
				<div id="user-registration-setup-wizard"></div>

				<?php
					wp_print_footer_scripts();
					wp_print_scripts( $handle );
				?>
			</body>
			</html>
			<?php

			exit;
		}
	}
}

UR_Admin_Welcome::init();
