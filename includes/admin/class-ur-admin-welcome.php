<?php
/**
 * Admin Welcome Screen.
 *
 * Loads the React-based welcome/setup wizard inside WordPress admin.
 *
 * @package UserRegistration\Admin
 * @since 4.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class UR_Admin_Welcome
 *
 * Registers the welcome admin page and bootstraps
 * the React application with localized data.
 *
 * @since 4.5.0
 */
class UR_Admin_Welcome {

	/**
	 * Initialize admin hooks.
	 *
	 * @since 4.5.0
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'render_welcome_page' ), 30 );
	}

	/**
	 * Register the welcome page in the admin menu.
	 *
	 * @since 4.5.0
	 * @return void
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
	 * Render the welcome page and enqueue React assets.
	 *
	 * @since 4.5.0
	 * @return void
	 */
	public static function render_welcome_page() {
		if ( empty( $_GET['page'] ) || 'user-registration-welcome' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$handle = 'ur-setup-wizard-script';

		$asset_path   = UR()->plugin_path() . '/chunks/welcome.asset.php';
		$asset_config = file_exists( $asset_path )
			? require $asset_path
			: array(
				'dependencies' => array(),
				'version'      => UR()->version,
			);

		wp_register_script(
			$handle,
			UR()->plugin_url() . '/chunks/welcome.js',
			$asset_config['dependencies'],
			$asset_config['version'],
			true
		);

		wp_enqueue_script( $handle );

		wp_enqueue_style(
			'ur-setup-wizard-style',
			UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css',
			array(),
			UR()->version
		);

		wp_enqueue_style(
			'ur-inter-font',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		wp_localize_script(
			$handle,
			'_UR_WIZARD_',
			array(
				'adminURL'        => esc_url( admin_url() ),
				'siteURL'         => esc_url( home_url( '/' ) ),
				'urRestApiNonce'  => wp_create_nonce( 'wp_rest' ),
				'onBoardIconsURL' => esc_url( UR()->plugin_url() . '/assets/images/onboard-icons' ),
				'restURL'         => esc_url_raw( rest_url() ),
				'adminEmail'      => sanitize_email( get_option( 'admin_email' ) ),
				'isPro'           => (bool) ( defined( 'UR_PRO_ACTIVE' ) && UR_PRO_ACTIVE ),
			)
		);
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1"/>
			<title><?php esc_html_e( 'User Registration & Membership – Welcome', 'user-registration' ); ?></title>
			<?php
			wp_print_styles();
			wp_print_head_scripts();
			?>
		</head>
		<body class="ur-react-welcome-page">
			<div id="user-registration-setup-wizard"></div>
			<?php wp_print_footer_scripts(); ?>
		</body>
		</html>
		<?php
		exit;
	}
}

UR_Admin_Welcome::init();
