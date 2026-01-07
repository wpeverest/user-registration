<?php
/**
 * Class UR_Settings_Integration
 *
 * Handles the integration related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Integration' ) ) {
	/**
	 * UR_Settings_Integration Class
	 */
	class UR_Settings_Integration extends UR_Settings_Page {
        private static $_instance = null;
        public $integrations = array();
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'integration';
			$this->label = __( 'Integration', 'user-registration' );
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
            if ( ! empty( $this->integrations ) ) {
                add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
                add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
                add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
            }
        }

        /**
         * Filter to provide sections submenu for integration settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'email-marketing' ] = __( 'Email Marketing', 'user-registration' );
            $sections[ 'pdf-submission' ] = __( 'PDF Submission' , 'user-registration' );
            $sections[ 'google-sheets' ] = __( 'Google Sheets' , 'user-registration' );
            $sections[ 'cloud-storage' ] = __( 'Cloud Storage', 'user-registration' );
            $sections[ 'salesforce' ] = __( 'Salesforce' , 'user-registration' );
            $sections[ 'geolocation' ] = __( 'Geolocation' , 'user-registration' );
            $sections[ 'woocommerce' ] = __( 'WooCommerce', 'user-registration' );
            
            return $sections;
        }
        /**
         * Filter to provide sections UI for integration settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            if( ! in_array( $current_section, array( '', 'email-marketing', 'pdf-submission', 'google-sheets', 'salesforce', 'geolocation', 'cloud-storage', 'woocommerce' ) ) ) return $settings;
            return $this->upgrade_to_pro_setting();
        }

        public function get_integrations() {
            return $this->integrations;
        }
        public function save() {
            $settings = $this->get_settings();
            UR_Admin_Settings::save_fields( $settings );
        }
        public function output() {
            $settings = $this->get_settings();
            add_filter( 'user_registration_settings_hide_save_button', '__return_true' );
            UR_Admin_Settings::output_fields( $settings );
        }
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Integration', 'get_instance' ) ? UR_Settings_Integration::get_instance() : new UR_Settings_Integration();
