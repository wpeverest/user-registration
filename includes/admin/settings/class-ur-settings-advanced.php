<?php
/**
 * Class UR_Settings_Advanced
 *
 * Handles the advanced related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Advanced' ) ) {
	/**
	 * UR_Settings_Advanced Class
	 */
	class UR_Settings_Advanced extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'advanced';
			$this->label = __( 'Advanced', 'user-registration' );
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
         * Filter to provide sections submenu for advanced settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'import-export' ] = __( 'Import/Export', 'user-registration' );
            $sections[ 'plugin-deletion' ] = __( 'Plugin Deletion', 'user-registration' );
            $sections[ 'others' ] = __( 'Others', 'user-registration' );
            return $sections;
        }
        /**
         * Filter to provide sections UI for advanced settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            return $this->upgrade_to_pro_setting();
        }
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Advanced', 'get_instance' ) ? UR_Settings_Advanced::get_instance() : new UR_Settings_Advanced();
