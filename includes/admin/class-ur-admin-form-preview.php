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
class UR_Admin_Form_Preview {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'welcome_page' ), 30 );
	}

	/**
	 * Add admin menus/screens.
	 */
	public static function add_menu() {
		add_menu_page(
			esc_html__( 'Welcome to User Registration', 'user-registration' ),
			'user registration preview',
			'manage_options',
			'ur_form_preview',
			''
		);
	}

	/**
	 * Show the welcome page.
	 */
	public static function welcome_page() {

		wp_register_script( 'ur-setup-wizard-script', UR()->plugin_url() . '/assets/js/admin/admin.js', array( 'wp-element', 'wp-blocks', 'wp-editor' ), UR()->version, true );
		// wp_enqueue_style( 'ur-setup-wizard-style', UR()->plugin_url() . '/assets/css/user-registration-setup-wizard.css', array(), UR()->version );
		wp_register_style( 'ur-form-preview-admin-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR()->version );
		wp_register_style( 'ur-form-preview-user-style', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR()->version );

		wp_enqueue_style( 'ur-form-preview-admin-style' );
		wp_enqueue_style( 'ur-form-preview-user-style' );
		// wp_enqueue_script( 'ur-setup-wizard-script' );

		// wp_localize_script(
		// 	'ur-setup-wizard-script',
		// 	'_UR_WIZARD_',
		// 	array(
		// 		'adminURL'            => esc_url( admin_url() ),
		// 		'siteURL'             => esc_url( home_url( '/' ) ),
		// 		'defaultFormURL'      => esc_url( admin_url( '/admin.php?page=add-new-registration&edit-registration=' . get_option( 'user_registration_default_form_page_id' ) ) ),
		// 		'urRestApiNonce'      => wp_create_nonce( 'wp_rest' ),
		// 		'onBoardIconsURL'     => esc_url( UR()->plugin_url() . '/assets/images/onboard-icons' ),
		// 		'restURL'             => rest_url(),
		// 		'registrationPageURL' => get_permalink( get_option( 'user_registration_registration_page_id' ) ),
		// 	)
		// );

		if ( ! empty( $_GET['page'] ) && ! empty( $_GET['form_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

			ob_start();
			self::form_preview_header();
			self::form_preview_body();
			self::setup_wizard_footer();
			exit;
		}
	}

	/**
	 * Setup wizard header content.
	 *
	 * @since 1.0.0
	 */
	public static function form_preview_header() {
		?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
				<head>
					<meta name="viewport" content="width=device-width"/>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>
						<?php esc_html_e( 'User Registration - Setup Wizard', 'user-registration' ); ?>
					</title>
					<?php
						wp_print_head_scripts();
						$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					?>
				</head>
				<body class="ur-multi-device-form-preview">
    		<div id="nav-menu-header">
        	<div class="ur-brand-logo ur-px-2">

            <img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/logo.svg' ); ?>" alt="Logo">
		</div>
		<span class="ur-form-title"><?php esc_html_e( 'Form Preview', 'user-registration' ); ?></span>

        <div class="ur-form-preview-devices">
            <p class="hello-text">Hello</p>
			<p>hello2</p>
        </div>

        <div class="major-publishing-actions wp-clearfix">
            <div class="publishing-action">
                <input type="text" onfocus="this.select();" readonly="readonly"
                       value='[user_registration_form id="<?php echo esc_attr( $form_id ); ?>"]'
                       class="code" size="35">
                <button id="copy-shortcode" class="button button-primary button-large ur-copy-shortcode"
                        data-tip="<?php esc_attr_e( 'Copy Shortcode!', 'user-registration' ); ?>"
                        data-copied="<?php esc_attr_e( 'Copied!', 'user-registration' ); ?>">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path fill="#383838" fill-rule="evenodd" d="M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</body>

		<?php
	}

	/**
	 * Setup wizard body content.
	 *
	 * @since 1.0.0
	 */
	public static function form_preview_body() {

		ob_start();
        if ( is_user_logged_in() && isset( $_GET['form_id'] ) ) {
            self::handle_preview();
        }
        $form_content = ob_get_clean();
		?>
		<div class="ur-form-preview-main-content">
			<div class="ur-form-preview-form">
				<?php
				echo $form_content;

				?>
			</div>
			<aside class="ur-form-side-panel">side content</aside>

		</div>
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

		/**
	 * Handles the preview of form.
	 */
	public static function handle_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( isset( $_GET['form_id'] ) ) {
			$form_id = $_GET['form_id'];// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$html = '';
			$html .= '<span class="ur-form-preview-title">';
			$html .=  get_the_title( $form_id );
			$html .= '</span>';
			$html .= '<div class="ur-form-preview-content">';
			if ( function_exists( 'apply_shortcodes' ) ) {
				$content = apply_shortcodes( '[user_registration_form id="' . $form_id . '"]' );
			} else {
				$content = do_shortcode( '[user_registration_form id="' . $form_id . '"]' );
			}
			$html .= '</div>';
			$html .= $content;

			echo $html;

			// add_filter( 'the_content', array( $this, 'form_preview_content' ) );
			// add_filter( 'get_the_excerpt', array( $this, 'form_preview_content' ) );
			// add_filter( 'post_thumbnail_html', '__return_empty_string' );

		}
	}



}

UR_Admin_Form_Preview::init();
