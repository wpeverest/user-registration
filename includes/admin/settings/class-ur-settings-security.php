<?php
/**
 * Class UR_Settings_Security
 *
 * Handles the security related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 *
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Security' ) ) {
	/**
	 * UR_Settings_Security Class
	 */
	class UR_Settings_Security extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->id    = 'security';
			$this->label = __( 'Security', 'user-registration' );
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
         * Filter to provide sections submenu for security settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'general' ] = __( 'General', 'user-registration' );
            $sections[ '2fa' ] = __( '2FA', 'user-registration' );
            return $sections;
        }
		
        /**
         * Filter to provide sections UI for security settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;

			switch( $current_section ) {
				case 'general':
					return $this->get_general_settings();
					break;
				case '2fa':
					return $this->upgrade_to_pro_setting();
			}
            return $settings;
        }

		/**
		 * Get General settings settings
		 *
		 * @return array
		 */
		public function get_general_settings() {

			$all_roles = ur_get_default_admin_roles();

			$all_roles_except_admin = $all_roles;

			unset( $all_roles_except_admin['administrator'] );

			/**
			 * Filter to add the options settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_settings_security_general_section',
				array(
					'title'    => '',
					'sections' => array(
						'general_options'    => array(
							'title'    => __( 'General', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Prevent WP Dashboard Access', 'user-registration' ),
									'desc'     => __( 'Selected user roles will not be able to view and access the WP Dashboard area.', 'user-registration' ),
									'id'       => 'user_registration_general_setting_disabled_user_roles',
									'default'  => array( 'subscriber' ),
									'type'     => 'multiselect',
									'class'    => 'ur-enhanced-select ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
									'options'  => $all_roles_except_admin,
								),
								array(
									'title'    => __( 'Enable Hide/Show Password', 'user-registration' ),
									'desc'     => __( 'Check this option to enable hide/show password icon beside the password field in both registration and login form.', 'user-registration' ),
									'id'       => 'user_registration_login_option_hide_show_password',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => '',
									'default'  => 'no',
								),
							),
						),
					),
				)
			);
			return $settings;
		}
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Security', 'get_instance' ) ? UR_Settings_Security::get_instance() : new UR_Settings_Security();
