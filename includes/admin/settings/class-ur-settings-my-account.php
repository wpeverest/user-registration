<?php
/**
 * Class UR_Settings_My_Account
 *
 * Handles the my_account related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_My_Account' ) ) {
	/**
	 * UR_Settings_My_Account Class
	 */
	class UR_Settings_My_Account extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'my_account';
			$this->label = __( 'My Account', 'user-registration' );
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
         * Filter to provide sections submenu for my_account settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'general' ] = __( 'General', 'user-registration' );
            $sections[ 'customize-my-account' ] = __( 'Customize My Account', 'user-registration' );
            $sections[ 'endpoint' ] = __( 'Endpoints', 'user-registration' );

            return $sections;
        }
        /**
         * Filter to provide sections UI for my_account settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            
            return $settings;
        }
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_My_Account', 'get_instance' ) ? UR_Settings_My_Account::get_instance() : new UR_Settings_My_Account();
