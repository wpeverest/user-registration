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
 * @version   5.0.0
 * @since     5.0.0
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
         * @return void
         */
        public function handle_hooks() {
            add_filter( "user_registration_get_sections_{$this->id}",  array( $this, 'get_sections_callback' ), 1, 1 );
            add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
        }
        /**
         * Filter to provide sections submenu for membership settings.
         */
        public function get_sections_callback( $sections ) {
			//if license key is not set, show license section in general settings.
			if ( empty( get_option( 'user-registration_license_key', '' ) ) ) {
				$sections[ 'license' ] = __( 'License', 'user-registration' );
			}
            $sections[ 'pages' ] = __( 'Pages', 'user-registration' );
            return $sections;
        }
        /**
         * Filter to provide sections UI for membership settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            if( 'license' === $current_section ) {
				$settings = array(
					'title'    => '',
					'sections' => array(
						'license_options_settings' => array(
							'title'    => __( 'License Activation', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '<strong>' . __( 'License: ', 'user-registration' ) . '</strong>' . __( 'Please enter the license key below in order to use our premium addons smoothly.', 'user-registration' ),
							'settings' => array(
								array(
									'title'    => __( 'License Key', 'user-registration' ),
									'desc'     => __( 'Please enter the license key', 'user-registration' ),
									'id'       => 'user-registration_license_key',
									'default'  => '',
									'type'     => 'text',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'id'     => 'ur_license_nonce',
									'action' => '_ur_license_nonce',
									'type'   => 'nonce',
								),
							),
						),
					),
				);

				// if license key is already set, show install pro notice.
				if ( ! empty( get_option( 'user-registration_license_key', '' ) ) ) {
					$settings['sections']['license_options_settings']['desc'] = '';
					$settings['sections']['license_options_settings']['before_desc'] = wp_kses_post( '<div class="urm_license_setting_notice urm_install_pro_notice"><h3><span class="dashicons dashicons-info-outline notice-icon"></span>' . __('Complete Your Pro Setup', 'user-registration' ) . '</h3><p>' . __('Your license is activated, but User Registration & Membership pro plugin needs to be installed to unlock all features. This is a one-time setup that takes less than a minute.', 'user-registration' ) . '</p><button class="button install_pro_version_button">' . __( 'Install Pro Version', 'user-registration' ) . '</button></div>');
				} else {
					$img             = UR()->plugin_url() . '/assets/images/rocket.gif';
					$license_message = false !== ur_get_license_plan() ? '' : '<br>No license is required. Enjoy!';
					$settings['sections']['license_options_settings']['before_desc'] = __( 'You\'re currently using the free version of User Registration & Membership.' . $license_message . '<br><br>To unlock advanced features and extended functionality, consider <a target="_blank" href="' . esc_url( 'https://wpuserregistration.com/upgrade/?utm_source=ur-license-setting&utm_medium=upgrade-link&utm_campaign=' . UR()->utm_campaign ) . '">Upgrading to Pro.</a>',
						'user-registration' );
					$settings['sections']['license_options_settings']['desc']        = wp_kses_post( __( '<img style="width:20px;height:20px;" src="' . $img . '" /> <span>Already purchased a license? Enter your license key below to activate PRO features.</span>',
						'user-registration' ) );
				}
				/**
				 * Filter to allow modification of license settings if license is not already activated.
				 * @param array $settings License settings array.
				 */
				$settings = apply_filters( 'user_registration_general_license_settings', $settings );
				$settings = apply_filters(
					'user_registration_license_settings', //@deprecated 5.0.0
					$settings,
				);
            } elseif ( 'pages' === $current_section ) {
                $settings = array(
					'title' => '',
					'sections' => array(
						'general_pages_settings' => array(
							'type' => 'card',
							'title' => __( 'Pages', 'user-registration' ),
							'settings' => array(
								array(
									'title' => __( 'My Account Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains your login form: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ), //phpcs:ignore
									'id' => 'user_registration_myaccount_page_id',
									'type' => 'single_select_page',
									'default' => '',
									'class' => 'ur-enhanced-select-nostd',
									'css' => '',
									'desc_tip' => true,
								),
								array(
									'title' => __( 'Registration Page', 'user-registration' ),
									'desc' => sprintf( __( 'Select the page which contains your registration form: [%s]', 'user-registration' ), apply_filters( 'user_registration_registration_shortcode_tag', 'user_registration_form' ) ), //phpcs:ignore
									'id' => 'user_registration_registration_page_id',
									'type' => 'single_select_page',
									'default' => '',
									'class' => 'ur-enhanced-select-nostd',
									'css' => '',
									'desc_tip' => true,
								),
								array(
									'title' => __( 'Login Page', 'user-registration' ),
									'desc'     => __( 'Select the page which contains your login form: [user_registration_login]', 'user-registration' ), //phpcs:ignore
									'id' => 'user_registration_login_page_id',
									'type' => 'single_select_page',
									'default' => '',
									'class' => 'ur-enhanced-select-nostd',
									'css' => '',
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
									'title'    => __( 'Member Registration Form Page', 'user-registration' ),
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

//Backward Compatibility.
return method_exists( 'UR_Settings_General', 'get_instance' ) ? UR_Settings_General::get_instance() : new UR_Settings_General();
