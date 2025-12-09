<?php
/**
 * Class UR_Settings_Membership
 *
 * Handles the membership related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * - Membership Settings.
 * - Content Restriction Settings.
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Membership' ) ) {
	/**
	 * UR_Settings_Membership Class
	 */
	class UR_Settings_Membership extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'membership';
			$this->label = __( 'Membership', 'user-registration' );
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
            $sections[ 'general' ] = __( 'General', 'user-registration' );
            $sections[ 'content-rules' ] = __( 'Content Rules', 'user-registration' );
            return $sections;
        }
        /**
         * Filter to provide sections UI for membership settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            if( 'general' === $current_section ) {
                
            } elseif ( 'content-rules' === $current_section ) {
                
            }
            return $settings;
        }
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Membership', 'get_instance' ) ? UR_Settings_Membership::get_instance() : new UR_Settings_Membership();
