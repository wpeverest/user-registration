<?php
/**
 * Preview Class
 *
 * Takes new users to Preview Page.
 *
 * @package UserRegistration/Admin
 * @version 2.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preview class.
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

		wp_register_script( 'ur-form-preview-admin-script', UR()->plugin_url() . '/assets/js/admin/admin.js', array( 'wp-element', 'wp-blocks', 'wp-editor' ), UR()->version, true );
		wp_register_style( 'ur-form-preview-admin-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR()->version );
		// wp_register_style( 'ur-form-preview-theme-style', UR()->plugin_url() . '/assets/css/user-registration-default.css', array(), UR()->version );
		wp_register_style( 'ur-form-preview-default-style', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR()->version );
		wp_register_style( 'ur-form-preview-smallscreens', UR()->plugin_url() . '/assets/css/user-registration-smallscreen.css', array(), UR()->version );
		wp_enqueue_style( 'ur-form-preview-admin-style' );
		wp_enqueue_style( 'ur-form-preview-smallscreens' );
		wp_enqueue_style( 'ur-form-preview-default-style' );
		// wp_enqueue_style( 'ur-form-preview-theme-style' );
		wp_enqueue_script( 'ur-form-preview-admin-script' );

		wp_localize_script(
			'ur-form-preview-admin-script',
			'user_registration_form_preview',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'form_preview_nonce'    => wp_create_nonce( 'ur_form_preview_nonce' ),
				'pro_upgrade_link' => 'https://wpeverest.com/wordpress-plugins/user-registration/?utm_source=plugin&utm_medium=form-preview&utm_campaign=pro-upgrade',
			)
		);

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
		<svg class="ur-form-preview-device" data-device="desktop" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M10.7574 14.6212H16.0604C17.3156 14.6212 18.3332 13.6037 18.3332 12.3485V4.77273C18.3332 3.51753 17.3156 2.5 16.0604 2.5H3.93923C2.68404 2.5 1.6665 3.51753 1.6665 4.77273V12.3485C1.6665 13.6037 2.68404 14.6212 3.93923 14.6212H9.24226V16.1364H6.96953C6.55114 16.1364 6.21196 16.4755 6.21196 16.8939C6.21196 17.3123 6.55114 17.6515 6.96953 17.6515H13.0301C13.4485 17.6515 13.7877 17.3123 13.7877 16.8939C13.7877 16.4755 13.4485 16.1364 13.0301 16.1364H10.7574V14.6212ZM3.93923 4.01515C3.52083 4.01515 3.18166 4.35433 3.18166 4.77273V12.3485C3.18166 12.7669 3.52083 13.1061 3.93923 13.1061H16.0604C16.4788 13.1061 16.818 12.7669 16.818 12.3485V4.77273C16.818 4.35433 16.4788 4.01515 16.0604 4.01515H3.93923Z" fill="#475BB2"/>
		</svg>
		<svg class="ur-form-preview-device" data-device="tablet" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
		<path d="M10.1517 13.7877C9.73328 13.7877 9.3941 14.1269 9.3941 14.5453C9.3941 14.9637 9.73328 15.3029 10.1517 15.3029H10.1593C10.5777 15.3029 10.9168 14.9637 10.9168 14.5453C10.9168 14.1269 10.5777 13.7877 10.1593 13.7877H10.1517Z" fill="#383838"/>
		<path fill-rule="evenodd" clip-rule="evenodd" d="M5.60622 1.6665C4.35103 1.6665 3.3335 2.68404 3.3335 3.93923V16.0604C3.3335 17.3156 4.35103 18.3332 5.60622 18.3332H14.6971C15.9523 18.3332 16.9699 17.3156 16.9699 16.0604V3.93923C16.9699 2.68404 15.9523 1.6665 14.6971 1.6665H5.60622ZM4.84865 3.93923C4.84865 3.52083 5.18783 3.18166 5.60622 3.18166H14.6971C15.1155 3.18166 15.4547 3.52083 15.4547 3.93923V16.0604C15.4547 16.4788 15.1155 16.818 14.6971 16.818H5.60622C5.18783 16.818 4.84865 16.4788 4.84865 16.0604V3.93923Z" fill="#383838"/>
		</svg>
		<svg class="ur-form-preview-device" data-device="mobile" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
		<path d="M10.2271 13.7877C9.80871 13.7877 9.46953 14.1269 9.46953 14.5453C9.46953 14.9637 9.80871 15.3029 10.2271 15.3029H10.2347C10.6531 15.3029 10.9923 14.9637 10.9923 14.5453C10.9923 14.1269 10.6531 13.7877 10.2347 13.7877H10.2271Z" fill="#383838"/>
		<path fill-rule="evenodd" clip-rule="evenodd" d="M6.43923 1.6665C5.18404 1.6665 4.1665 2.68404 4.1665 3.93923V16.0604C4.1665 17.3156 5.18404 18.3332 6.43923 18.3332H14.015C15.2702 18.3332 16.2877 17.3156 16.2877 16.0604V3.93923C16.2877 2.68404 15.2702 1.6665 14.015 1.6665H6.43923ZM5.68166 3.93923C5.68166 3.52083 6.02083 3.18166 6.43923 3.18166H14.015C14.4334 3.18166 14.7726 3.52083 14.7726 3.93923V16.0604C14.7726 16.4788 14.4334 16.818 14.015 16.818H6.43923C6.02083 16.818 5.68166 16.4788 5.68166 16.0604V3.93923Z" fill="#383838"/>
		</svg>

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
		<svg class="ur-form-preview-sidepanel-toggler" xmlns="http://www.w3.org/2000/svg" width="27" height="48" viewBox="0 0 27 48" fill="none">
<mask id="path-1-inside-1_10273_358" fill="white">
<path d="M0 4C0 1.79086 1.79086 0 4 0H27V48H4C1.79086 48 0 46.2091 0 44V4Z"/>
</mask>
<path d="M0 4C0 1.79086 1.79086 0 4 0H27V48H4C1.79086 48 0 46.2091 0 44V4Z" fill="white"/>
<path d="M-1 4C-1 1.23858 1.23858 -1 4 -1H27V1H4C2.34315 1 1 2.34315 1 4H-1ZM27 49H4C1.23858 49 -1 46.7614 -1 44H1C1 45.6569 2.34315 47 4 47H27V49ZM4 49C1.23858 49 -1 46.7614 -1 44V4C-1 1.23858 1.23858 -1 4 -1V1C2.34315 1 1 2.34315 1 4V44C1 45.6569 2.34315 47 4 47V49ZM27 0V48V0Z" fill="#EDEFF7" mask="url(#path-1-inside-1_10273_358)"/>
<path d="M13.5 29L18.5 24L13.5 19" stroke="#9B9B9B" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

		<div class="ur-form-preview-main-content">

			<div class="ur-form-preview-form">
				<?php
				echo $form_content;

				?>
			</div>
			<aside class="ur-form-side-panel">
				<?php
				self::side_panel_content();
				?>
			</aside>


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
		wp_print_scripts( 'ur-form-preview-admin-script' );
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
			$html .= '<div>';
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
			$html .= '</div>';

			echo $html;


		}
	}

	/**
	 * Side panel content.
	 */
	public static function side_panel_content() {
		$pro_features = array(
			esc_html__( 'Stripe & PayPal Integration', 'user-registration' ),
			esc_html__( 'Style Export & Import', 'user-registration' ),
			esc_html__( 'Conditional Email Routing', 'user-registration' ),
			esc_html__( 'Advanced Form Fields', 'user-registration' ),
			esc_html__( 'Quiz & Survey Forms', 'user-registration' ),
			esc_html__( '40+ Integrations', 'user-registration' ),
			esc_html__( 'Multi-Step Forms', 'user-registration' ),
			esc_html__( 'SMS Notifications', 'user-registration' ),
			esc_html__( 'Calculated Fields', 'user-registration' ),
		);
		$is_theme_style = get_post_meta( $_GET['form_id'],'user_registration_enable_theme_style', 'no' );
		if ( 'no' === $is_theme_style || empty( $is_theme_style ) ) {
			$checked = '';
			$data_theme = 'default';
		} else {
			$checked = 'checked';
			$data_theme = 'theme_style';
		}
		$html ='';
		$html .= '<div class="ur-from-preview-theme-toggle">';
		$html .= '<label class="ur-form-preview-toggle-title">Apply Theme Style</label>';
		$html .= '<input type="checkbox" class="ur-form-preview-theme-toggle-checkbox" id="ur_toggle_form_preview_theme" ' . $checked . '>';
		$html .= '</div>';
		$html .= '<div class="ur-form-preview-save hidden" id="ur-form-save" data-theme="default" data-id="'.$_GET['form_id'].'">';
		$html .= '<img src="' . esc_url( UR()->plugin_url() . '/assets/images/save-frame.svg' ) . '" alt="Save">';
		$html .= '<div class="ur-form-preview-save-title">Save</div>';
		$html .= '</div>';
		$html .= '<div class="ur-form-preview-pro-features">';
		$html .= '<h3 class="ur-form-preview-pro-features-title">Our Pro Features</h3>';
		foreach ( $pro_features as $list ) {
			$html .= '<div class="ur-form-preview-sidebar__body--list-item">';

			$html .= '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 18 18" fill="none">
						<path d="M15 5.25L6.75 13.5L3 9.75" stroke="#4CC741" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>';

			$html .= '<span>';
			$html .= wp_kses_post( $list );
			$html .= '</span>';
			$html .= '</div>';

		}
		$html .= '<div class="ur-form-preview-upgrade  id="ur-form-save" data-theme="default" ">';
		$html .= '<img src="' . esc_url( UR()->plugin_url() . '/assets/images/upgrade-icon.svg' ) . '" alt="Save">';
		$html .= '<div class="ur-form-preview-upgrade-title">Upgrade to Pro</div>';
		$html .= '</div>';

		echo $html;

		?>



		<?php
	}



}

UR_Admin_Form_Preview::init();
