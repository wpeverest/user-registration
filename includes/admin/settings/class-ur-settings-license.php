<?php
/**
 * Class UR_Settings_License
 *
 * Handles the scaffold related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_License' ) ) {
	/**
	 * UR_Settings_License Class
	 */
	class UR_Settings_License extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'license';
			$this->label = __( 'License', 'user-registration' );
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
            add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
        }
        /**
         * Filter to provide sections UI for scaffold settings.
         */
        public function get_settings_callback( $settings ) {
            return $this->get_license_settings();   
        }
		public function get_license_settings() {
			$settings = array(
				'title' => '',
				'sections' => array(
					'license_options_settings' => array(
						'title'    => __( 'License Activation', 'user-registration' ),
						'type'     => 'card',
						'before_desc'     => '<strong>' . __( 'License: ', 'user-registration' ) . '</strong>' . __( 'Please enter the license key below in order to use our premium addons smoothly.', 'user-registration' ),
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
					)
				)
			);
			//only show the content on free version.
			if( is_plugin_active( 'user-registration/user-registration.php' ) ) {
				if ( get_option( 'user-registration_license_key' ) ) {
					$settings['sections']['license_options_settings']['desc'] = '';
					$settings['sections']['license_options_settings']['before_desc'] = wp_kses_post( '<div class="urm_license_setting_notice urm_install_pro_notice"><h3><span class="dashicons dashicons-info-outline notice-icon"></span>' . __('Complete Your Pro Setup', 'user-registration' ) . '</h3><p>' . __('Your license is activated, but User Registration & Membership pro plugin needs to be installed to unlock all features. This is a one-time setup that takes less than a minute.', 'user-registration' ) . '</p><button class="button install_pro_version_button">' . __( 'Install Pro Version', 'user-registration' ) . '</button></div>');
				} else {
					$settings[ 'sections' ][ 'license_options_settings' ][ 'before_desc' ] = __( 'You\'re currently using the free version of User Registration & Membership.<br>You can continue using all free features without any limitations.<br><br>Want more? <a target="_blank" href="' . esc_url( 'https://wpuserregistration.com/upgrade/?utm_source=ur-license-setting&utm_medium=upgrade-link&utm_campaign=' . UR()->utm_campaign ) . '">Upgrade to Pro</a> to unlock advanced features and premium support.<br>Already purchased Pro? Enter your license key below and we\'ll automatically upgrade you to Pro.', 'user-registration' );
				}
			}

			// Replace license input box and display deactivate license button when license is activated.
			if ( get_option( 'user-registration_license_key' ) ) {
				$settings['sections']['license_options_settings']['settings'] = array(
					array(
						'title'    => __( 'Deactivate License', 'user-registration' ),
						'desc'     => '',
						'desc_tip' => __( 'Deactivate the license of User Registration plugin', 'user-registration' ),
						'type'     => 'link',
						'id'       => 'user-registration_deactivate-license_key',
						'css'      => 'background:red; border:none; color:white;',
						'buttons'  => array(
							array(
								'title' => __( 'Deactivate License', 'user-registration' ),
								'href'  => wp_nonce_url( remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( 'user-registration_deactivate_license', 1 ), ), '_ur_license_nonce' ),
								'class' => 'user_registration-deactivate-license-key',
							),
						),
					),
					array(
						'type' => 'license_options',
						'id'   => 'user_registration_license_section_settings',
					),
				);

				if( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
					/* translators: %1$s - WPeverest My Account url */
					$settings['sections']['license_options_settings']['desc'] = sprintf( __( 'Your license has been activated. Enjoy using <strong>User Registration</strong>. Please go to %1$sMy Account Page%2$s for more details ', 'user-registration' ), '<a href="https://wpeverest.com/login/" rel="noreferrer noopener" target="_blank">', '</a>' );
				}
				// Hide save changes button from settings when license is activated.
				$GLOBALS['hide_save_button'] = true;
			}

			return $settings;
		}
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_License', 'get_instance' ) ? UR_Settings_License::get_instance() : new UR_Settings_License();
