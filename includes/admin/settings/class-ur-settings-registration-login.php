<?php
/**
 * Class UR_Settings_Registration_Login
 *
 * Handles the registration_login related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Registration_Login' ) ) {
	/**
	 * UR_Settings_Registration_Login Class
	 */
	class UR_Settings_Registration_Login extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'registration_login';
			$this->label = __( 'Registration Login', 'user-registration' );
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
         * Filter to provide sections submenu for registration_login settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'messages' ] =  __( 'Messages', 'user-registration' );
            $sections[ 'captcha' ] =  __( 'Captcha', 'user-registration' );
            $sections[ 'social_connect' ] =  __( 'Social Connect', 'user-registration' );
            $sections[ 'profile_connect' ] =  __( 'Profile Connect', 'user-registration' );
            $sections[ 'popup' ] =  __( 'Popups', 'user-registration' );
            $sections[ 'invite_code' ] =  __( 'Invite Codes', 'user-registration' );
            $sections[ 'file_upload' ] =  __( 'File Upload', 'user-registration' );
            $sections[ 'role_based_redirection' ] =  __( 'Role Based Redirection', 'user-registration' );

            return $sections;
        }
        /**
         * Filter to provide sections UI for registration_login settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            
            return $settings;
        }
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Registration_Login', 'get_instance' ) ? UR_Settings_Registration_Login::get_instance() : new UR_Settings_Registration_Login();
