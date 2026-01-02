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
									'title'    => __( 'My Account Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains your login form: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ), //phpcs:ignore
									'id'       => 'user_registration_myaccount_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'     => __( 'Lost Password Page', 'user-registration' ),
									'desc'      => __( 'Select the page where your password reset form is placed.', 'user-registration' ),
									'id'        => 'user_registration_lost_password_page_id',
									'type'      => 'single_select_page',
									'default'   => '',
									'class'     => 'ur-enhanced-select-nostd',
									'css'       => '',
									'desc_tip'  => true,
									'field-key' => 'lost-password',
								),
								array(
									'title'    => __( 'Registration Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains membership registration shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_registration_shortcode_tag', 'user_registration_form' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_member_registration_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Membership Pricing Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains the membership pricing shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_membership_pricing_shortcode_tag', 'user_registration_membership_pricing' ) ),
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
									'desc'     => sprintf( __( 'Select the page which contains the membership thank you shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_membership_thank_you_shortcode_tag', 'user_registration_membership_thank_you' ) ),
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
			return $settings;
		}
	}
}

// Backward Compatibility.
return method_exists( 'UR_Settings_General', 'get_instance' ) ? UR_Settings_General::get_instance() : new UR_Settings_General();
