<?php
/**
 * Class UR_Setings_General
 *
 * Handles the General settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * - License Settings for Pro version until activation.
 * - Pages Settings
 *
 * @package   UserRegistration\Admin
 */
if ( ! class_exists( 'UR_Settings_General' ) ) {
	/**
	 * UR_Settings_General Class
	 */
	class UR_Settings_General extends UR_Settings_Page {
		private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General', 'user-registration' );
			parent::__construct();
			$this->handle_hooks();
		}
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		/**
		 * Register hooks for submenus and section UI.
		 *
		 * @return void
		 */
		public function handle_hooks() {
			add_filter( "user_registration_get_sections_{$this->id}", array( $this, 'get_sections_callback' ), 1, 1 );
			add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
		}
		/**
		 * Filter to provide sections submenu for membership settings.
		 */
		public function get_sections_callback( $sections ) {
			$sections['pages'] = __( 'Pages', 'user-registration' );
			$sections['style'] = __( 'Style', 'user-registration' );
			return $sections;
		}
		/**
		 * Filter to provide sections UI for membership settings.
		 */
		public function get_settings_callback( $settings ) {
			global $current_section;
			if ( 'pages' === $current_section ) {
				$settings = array(
					'title'    => '',
					'sections' => array(
						'general_pages_settings' => array(
							'type'     => 'card',
							'title'    => __( 'Pages', 'user-registration' ),
							'settings' => array(
								array(
									'title'    => __( 'Registration Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Displays the registration form where new users can sign up. Add the Registration Form block or [%s] shortcode to this page.', 'user-registration' ), apply_filters( 'user_registration_registration_shortcode_tag', 'user_registration_form' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_member_registration_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Login Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Displays the login form where existing users can access their account. Add the Login Form block or [%s] shortcode to this page. ', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_login' ) ),
									'desc_tip' => true,
									'css'      => '',
									'class'    => 'ur-enhanced-select-nostd',
									'default'  => '',
									'type'     => 'single_select_page',
									'id'       => 'user_registration_login_page_id',
								),
								array(
									'title'    => __( 'My Account Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Member dashboard for managing account details, subscriptions, and profile information. Add the My Account block or [%s] shortcode to this page.', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ), //phpcs:ignore
									'id'       => 'user_registration_myaccount_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'     => __( 'Lost Password Page', 'user-registration' ),
									'desc'      => sprintf( __( 'Allows users to reset their password if forgotten. A password reset link will be sent to their registered email address. Add the [%s] shortcode to this page.', 'user-registration' ), apply_filters( '', 'user_registration_lost_password' ) ),
									'id'        => 'user_registration_lost_password_page_id',
									'type'      => 'single_select_page',
									'default'   => '',
									'class'     => 'ur-enhanced-select-nostd',
									'css'       => '',
									'desc_tip'  => true,
									'field-key' => 'lost-password',
								),
								array(
									'title'    => __( 'Membership Pricing Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Displays all available membership plans and pricing options. Users can view and select which membership to purchase. Add the Membership Pricing block or [%s] shortcode to this page.', 'user-registration' ), apply_filters( 'user_registration_membership_pricing_shortcode_tag', 'user_registration_membership_pricing' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_membership_pricing_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Thank You Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Confirmation page shown after successful registration or membership purchase. Use it to welcome new members and provide next steps. Add the Thank You block or [%s] shortcode to this page.', 'user-registration' ), apply_filters( 'user_registration_membership_thank_you_shortcode_tag', 'user_registration_membership_thank_you' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_thank_you_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
							),
						),
					),
				);
			}

			if ( 'style' === $current_section ) {
				$settings = apply_filters(
					'user_registration_style_settings',
					array(
						'title'    => '',
						'sections' => array(
							'style_settings' => array(
								'title'    => __( 'Style', 'user-registration' ),
								'type'     => 'card',
								'desc'     => '',
								'settings' => array(
									array(
										'title'    => __( 'Primary', 'user-registration' ),
										'desc'     => __( 'Choose color to match your brand or site', 'user-registration' ),
										'id'       => 'user_registration_style_setting_primary_color',
										'default'  => '',
										'type'     => 'color',
										'class'    => '',
										'css'      => '',
										'desc_tip' => true,
									),
									array(
										'id'       => 'user_registration_style_setting_button_text_colors',
										'type'     => 'color-group',
										'desc'     => __( 'Choose color to match your brand or site', 'user-registration' ),
										'title'    => __( 'Button Text', 'user-registration' ),
										'states'   => array( 'normal', 'hover' ),
										'desc_tip' => true,
										'default'  => array(
											'normal' => '#FFFFFF',
											'hover'  => '#FFFFFF',
										),
									),
									array(
										'id'       => 'user_registration_style_setting_button_background_colors',
										'type'     => 'color-group',
										'desc'     => __( 'Choose color to match your brand or site', 'user-registration' ),
										'title'    => __( 'Button Background', 'user-registration' ),
										'states'   => array( 'normal', 'hover' ),
										'desc_tip' => true,
										'default'  => array(
											'normal' => '#475bb2',
											'hover'  => '#38488e',
										),
									),

								),
							),
						),
					)
				);
			}
			return $settings;
		}
	}
}

// Backward Compatibility.
return method_exists( 'UR_Settings_General', 'get_instance' ) ? UR_Settings_General::get_instance() : new UR_Settings_General();
