<?php
/**
 * Welcome Class
 *
 * Takes new users to Welcome Page.
 *
 * @package UserRegistration/Admin
 * @version 2.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Welcome class.
 */
class UR_Admin_Welcome {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		if (
		apply_filters( 'user_registration_show_welcome_page', true )
		&& current_user_can( 'manage_user_registration' )
		) {
			add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
			add_action( 'admin_init', array( __CLASS__, 'welcome_page' ), 30 );
		}
	}

	/**
	 * Add admin menus/screens.
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
	 * Show the welcome page.
	 */
	public static function welcome_page() {
		wp_register_script( 'ur-setup-wizard-script', UR()->plugin_url() . '/build/main.js', array(), UR()->version, true );
		wp_enqueue_style( 'ur-setup-wizard-style', UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css', array(), UR()->version );
		wp_enqueue_script( 'ur-setup-wizard-script' );

		if ( ! empty( $_GET['page'] ) && 'user-registration-welcome' === $_GET['page'] ) {

			ob_start();
			self::setup_wizard_header();
			self::setup_wizard_body();
			self::setup_wizard_footer();
			exit;
		}

	}

	/**
	 * Setup wizard header content.
	 *
	 * @since 1.0.0
	 */
	public static function setup_wizard_header() {
		?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
				<head>
					<meta name="viewport" content="width=device-width"/>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>
						<?php esc_html_e( 'User Registration - Setup Wizard', 'user-registration' ); ?>
					</title>
					<?php wp_print_head_scripts(); ?>
				</head>
		<?php
	}

	/**
	 * Setup wizard body content.
	 *
	 * @since 1.0.0
	 */
	public static function setup_wizard_body() {
		?>
			<body class="user-registration-welcome notranslate" translate="no">
				<?php
				if ( ! empty( $_GET['tab'] ) && 'setup-wizard' === $_GET['tab'] ) {
					?>
					<div id="user-registration-setup-wizard"></div>
					<?php
				} else {
					?>
					<div id="user-registration-welcome" >
						<div class="user-registration-welcome-card" >
							<div class="user-registration-welcome-header">
								<div class="user-registration-welcome-header__logo-wrap">
									<div class="user-registration-welcome-header__logo-icon">
										<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/logo.svg' ); ?>" alt="">
									</div>
									<span><?php esc_html_e( 'Getting Started', 'user-registration' ); ?></span>
								</div>
								<a class="user-registration-welcome__skip" href="<?php echo esc_url( admin_url() ); ?>">
									<span class="dashicons dashicons-no-alt"></span>
								</a>
							</div>
							<div class="user-registration-welcome-container">
								<div class="user-registration-welcome-container__header">
									<h2><?php esc_html_e( 'Welcome to User Registration', 'user-registration' ); ?></h2>
									<p><?php esc_html_e( 'Thank you for choosing User Registration - the most powerful and easy drag & drop WordPress form builder in the market.', 'user-registration' ); ?></p>
								</div>
								<a class="user-registration-welcome-video welcome-video-play">
									<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-feature.png' ); ?>" alt="<?php esc_attr_e( 'Watch how to create your first form with User Registration', 'user-registration' ); ?>" class="user-registration-welcome-thumb">
									<button class="user-registration-welcome-video__button dashicons dashicons-controls-play"></button>
								</a>
								<div class="user-registration-welcome-container__action">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-welcome&tab=setup-wizard' ) ); ?>" class="button button-primary">
											<h3><?php esc_html_e( 'Get Started', 'user-registration' ); ?></h3>
									</a>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=add-new-registration' ) ); ?>" class="button button-primary">
											<h3><?php esc_html_e( 'Create a First Form', 'user-registration' ); ?></h3>
									</a>
									<a href="https://docs.wpeverest.com/docs/user-registration/" class="button button-secondary" target="blank">
											<h3><?php esc_html_e( 'Visit Documentation', 'user-registration' ); ?></h3>
									</a>
								</div>
							</div>
						</div>
						<div class="user-registration-extensions-card" >
							<div class="user-registration-extensions-container__header">
								<h2><?php esc_html_e( 'Check Our Awesome Extensions', 'user-registration' ); ?></h2>
							</div>
							<div class="user-registration-extensions-container__body">
								<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-extensions.png' ); ?>" alt="<?php esc_attr_e( 'Watch how to create your first form with User Registration', 'user-registration' ); ?>" class="user-registration-welcome-thumb">
							</div>
							<div class="user-registration-extensions-container__footer">
								<a href=<?php echo esc_url( admin_url( 'admin.php' ) . '?page=user-registration-addons' ); ?> class="button button-secondary" target="blank">
									<h3><?php esc_html_e( 'See All Extensions', 'user-registration' ); ?></h3>
								</a>
							</div>
						</div>
						<div class="user-registration-support-card" >
							<div class="user-registration-support-container__header">
								<h2><?php echo wp_kses_post( 'Feeling Lost? </br></br> Contact Our Support Team', 'user-registration' ); ?></h2>
							</div>
							<div class="user-registration-support-container__footer">
								<a href=<?php echo esc_url_raw( 'https://wpeverest.com/wordpress-plugins/user-registration/support/' ); ?> class="button button-secondary" target="blank">
									<h3><?php esc_html_e( 'Contact support', 'user-registration' ); ?></h3>
								</a>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</body>
		<?php
	}

	/**
	 * Setup wizard footer content.
	 *
	 * @since 1.0.0
	 */
	public static function setup_wizard_footer() {
		if ( function_exists( 'wp_print_media_templates' ) ) {
			wp_print_media_templates();
		}
		wp_print_footer_scripts();
		wp_print_scripts( 'ur-setup-wizard-script' );
		?>
		</html>
		<?php
	}
}

UR_Admin_Welcome::init();
