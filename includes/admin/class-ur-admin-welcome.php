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
			add_action( 'admin_head', array( __CLASS__, 'hide_menu' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public static function add_menu() {
		$welcome_page = add_dashboard_page(
			esc_html__( 'Welcome to User Registration', 'user-registration' ),
			esc_html__( 'Welcome to User Registration', 'user-registration' ),
			'manage_user_registration',
			'user-registration-welcome',
			array( __CLASS__, 'welcome_page' )
		);

		add_action( 'load-' . $welcome_page, array( __CLASS__, 'welcome_page_init' ) );
	}

	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 1.0.0
	 */
	public static function hide_menu() {
		remove_submenu_page( 'index.php', 'user-registration-welcome' );
	}

	/**
	 * Welcome page init.
	 */
	public static function welcome_page_init() {
		delete_transient( '_ur_activation_redirect' );
	}

	/**
	 * Show the welcome page.
	 */
	public static function welcome_page() {
		wp_enqueue_script( 'ur-setup-wizard-script', UR()->plugin_url() . '/build/main.js', array(), UR()->version, false );
		wp_enqueue_style( 'ur-setup-wizard-style', UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css', array(), UR()->version );

		if ( ! empty( $_GET['tab'] ) && 'setup-wizard' === $_GET['tab'] ) {
			?>
				<div id="user-registration-setup-wizard" ></div>
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
	}
}

UR_Admin_Welcome::init();
