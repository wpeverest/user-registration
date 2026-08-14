<?php
/**
 * Email Delivery Checkup screen.
 *
 * Renders the checkup as a full-page takeover rather than a settings section,
 * the same way the setup wizard does: its own HTML document, only its own
 * styles, no admin chrome. The checkup is a guided run with its own step
 * sequence, and framing it inside the settings navigation invited the admin to
 * wander off mid-run.
 *
 * @package UserRegistration\Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class UR_Admin_Email_Checkup
 */
class UR_Admin_Email_Checkup {

	/**
	 * Page slug for the takeover screen.
	 */
	const PAGE = 'user-registration-email-checkup';

	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect_legacy_section' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'render' ), 30 );
	}

	/**
	 * Register the page so admin.php will serve the URL at all.
	 *
	 * Without a registered hook for the slug, admin.php answers "Sorry, you are
	 * not allowed to access this page" with a 403 before render() gets a chance.
	 *
	 * Registered only on its own request — this class is also loaded on the
	 * settings page, to hand the old section off — so the slug never becomes a
	 * stray item in the sidebar. The callback stays empty because render()
	 * prints the document and exits long before any callback would run.
	 *
	 * @return void
	 */
	public static function add_menu() {
		// phpcs:ignore WordPress.Security.NonceVerification -- Read-only navigation.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( self::PAGE !== $page ) {
			return;
		}

		add_menu_page(
			esc_html__( 'Email Delivery Checkup', 'user-registration' ),
			'',
			'manage_options',
			self::PAGE,
			''
		);
	}

	/**
	 * Address of the checkup screen.
	 *
	 * @return string
	 */
	public static function url() {
		return admin_url( 'admin.php?page=' . self::PAGE );
	}

	/**
	 * Where the close button goes back to.
	 *
	 * @return string
	 */
	public static function exit_url() {
		return admin_url( 'admin.php?page=user-registration-settings&tab=email&section=general' );
	}

	/**
	 * The checkup used to live at Emails → Health Checkup. That section is still
	 * in the settings navigation because it is where admins look for it — it now
	 * hands off to the full-screen run instead of rendering inside the page.
	 *
	 * @return void
	 */
	public static function redirect_legacy_section() {
		// phpcs:disable WordPress.Security.NonceVerification -- Read-only navigation.
		$page    = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$section = isset( $_GET['section'] ) ? sanitize_title( wp_unslash( $_GET['section'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification

		if ( 'user-registration-settings' !== $page || 'email' !== $tab || 'health-checkup' !== $section ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_safe_redirect( self::url() );
		exit;
	}

	/**
	 * Print the checkup as a standalone document and stop.
	 *
	 * @return void
	 */
	public static function render() {
		// phpcs:ignore WordPress.Security.NonceVerification -- Read-only navigation.
		if ( empty( $_GET['page'] ) || self::PAGE !== $_GET['page'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to run the email checkup.', 'user-registration' ) );
		}

		$handle = 'ur-email-health-checkup';

		$asset_path   = UR()->plugin_path() . '/chunks/health-checkup.asset.php';
		$asset_config = file_exists( $asset_path )
			? require $asset_path
			: array(
				'dependencies' => array( 'wp-element', 'wp-i18n' ),
				'version'      => UR()->version,
			);

		wp_register_script(
			$handle,
			UR()->plugin_url() . '/chunks/health-checkup.js',
			$asset_config['dependencies'],
			$asset_config['version'],
			true
		);

		wp_enqueue_script( $handle );

		// The setup wizard's stylesheet, for its reset and base type — the point
		// of this screen is that the two look like one product.
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
			'_UR_EMAIL_HEALTH_',
			array(
				'ajaxURL'        => admin_url( 'admin-ajax.php' ),
				'scanNonce'      => wp_create_nonce( 'email_health_scan_nonce' ),
				'confirmNonce'   => wp_create_nonce( 'email_health_confirm_nonce' ),
				'testEmailNonce' => wp_create_nonce( 'test_email_nonce' ),
				'adminEmail'     => get_option( 'admin_email' ),
				'siteUrl'        => home_url(),
				'wpVersion'      => get_bloginfo( 'version' ),
				'phpVersion'     => PHP_VERSION,
				'pluginVersion'  => defined( 'UR_VERSION' ) ? UR_VERSION : '',
				'smartSmtpUrl'   => admin_url( 'admin.php?page=smart-smtp#/primary-connection' ),
				'mailLogUrl'     => ur_get_mail_log_url(),
				'exitUrl'        => self::exit_url(),
				'logoUrl'        => UR()->plugin_url() . '/assets/images/logo.svg',
			)
		);
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1"/>
			<title><?php esc_html_e( 'Email Delivery Checkup', 'user-registration' ); ?></title>
			<?php
			wp_print_styles();
			wp_print_head_scripts();
			?>
		</head>
		<body class="ur-react-email-checkup">
			<div id="ur-email-health-checkup-root"></div>
			<?php wp_print_footer_scripts(); ?>
		</body>
		</html>
		<?php
		exit;
	}
}

UR_Admin_Email_Checkup::init();
