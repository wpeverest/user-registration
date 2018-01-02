<?php
/**
 * UserRegistration Email Settings
 *
 * @class    UR_Settings_Email
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Email' ) ) :

	/**
	 * UR_Settings_Email Class
	 */
	class UR_Settings_Email extends UR_Settings_Page {


		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'email';
			$this->label = __( 'Emails', 'user-registration' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings(){
			$settings = apply_filters( 'user_registration_email_settings', array(

					array(
						'title' => __( 'Email Sender Options', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'email_options',
					),

					array(
						'title'    => __( '"From" name', 'user-registration' ),
						'desc'     => __( 'How the sender name appears in outgoing user registration emails.', 'user-registration' ),
						'id'       => 'user_registration_email_from_name',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => esc_attr( get_bloginfo( 'name', 'display' ) ),
						'autoload' => false,
						'desc_tip' => true,
					),

					array(
						'title'             => __( '"From" address', 'user-registration' ),
						'desc'              => __( 'How the sender email appears in outgoing user registration emails.', 'online-restaurant-reservation' ),
						'id'                => 'user_registration_email_from_address',
						'type'              => 'email',
						'custom_attributes' => array(
							'multiple' => 'multiple',
						),
						'css'               => 'min-width:300px;',
						'default'           => get_option( 'admin_email' ),
						'autoload'          => false,
						'desc_tip'          => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),
				)
			);
				return apply_filters( 'user_registration_get_email_settings_' . $this->id, $settings );
		}
	
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}

	}

endif;

return new UR_Settings_Email();