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

		$wizard_ran = get_option( 'user_registration_first_time_activation_flag', false );

		// If Wizard was ran already, then do not proceed to Wizard page again.
		if ( $wizard_ran ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'welcome_page' ), 30 );

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

		if ( isset( $_GET['tab'] ) && 'setup-wizard' === $_GET['tab'] ) {
			update_option( 'user_registration_first_time_activation_flag', true );
		}

		wp_register_script( 'ur-setup-wizard-script', UR()->plugin_url() . '/build/main.js', array(), UR()->version, true );
		wp_enqueue_style( 'ur-setup-wizard-style', UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css', array(), UR()->version );
		wp_enqueue_script( 'ur-setup-wizard-script' );

		wp_localize_script(
			'ur-setup-wizard-script',
			'_UR_',
			array(
				'adminURL'       => esc_url( admin_url() ),
				'siteURL'        => esc_url( home_url( '/' ) ),
				'defaultFormURL' => esc_url( admin_url( '/admin.php?page=add-new-registration&edit-form=' . get_option( 'user_registration_default_form_page_id' ) ) ),
				'newFormURL'     => esc_url( admin_url( '/admin.php?page=add-new-registration' ) ),
				'urRestApiNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

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
					<script>
						// To play welcome video.
						jQuery(document).on(
							"click",
							"#user-registration-welcome .welcome-video-play",
							function (event) {
								var video =
									'<div class="welcome-video-container"><iframe width="560" height="315" src="https://www.youtube.com/embed/tMaG6pnfYg0?start=15&amprel=0&amp;showinfo=0&amp;autoplay=1" frameborder="0" allowfullscreen></iframe></div>';

								event.preventDefault();

								jQuery(this).find(".user-registration-welcome-thumb").remove();
								jQuery(this).append(video);
							}
						);
					</script>
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
							<div class="user-registration-welcome-container">
								<div class="user-registration-welcome-container__header">
									<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-logo.png' ); ?>" alt="">
									<h2><?php esc_html_e( 'Welcome to User Registration', 'user-registration' ); ?></h2>
									<p><?php esc_html_e( 'Thank you for choosing User Registration - the most powerful and easy drag & drop WordPress form builder in the market.', 'user-registration' ); ?></p>
								</div>
								<div class="user-registration-welcome-video">
									<a class="welcome-video-play">
										<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-feature.png' ); ?>" alt="<?php esc_attr_e( 'Watch how to create your first form with User Registration', 'user-registration' ); ?>" class="user-registration-welcome-thumb">
										<button class="user-registration-welcome-video__button dashicons dashicons-controls-play"></button>
									</a>
								</div>
								<div class="user-registration-welcome-container__action">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-welcome&tab=setup-wizard' ) ); ?>" class="button button-primary">
											<h3><?php esc_html_e( 'Get Started', 'user-registration' ); ?></h3>
									</a>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=add-new-registration' ) ); ?>" class="button button-secondary">
											<h3><?php esc_html_e( 'Create a First Form', 'user-registration' ); ?></h3>
									</a>
									<a href="https://docs.wpeverest.com/docs/user-registration/" class="button button-tertiary" target="blank">
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
								<h2><?php echo wp_kses_post( 'Feeling Lost? </br> Contact Our Support Team', 'user-registration' ); ?></h2>
							</div>
							<div class="user-registration-support-container__body">
								<p>Feel free to get in touch with one of our sales representative if you have any queries related to our product.</p>
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
