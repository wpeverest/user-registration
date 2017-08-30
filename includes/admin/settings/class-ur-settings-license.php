<?php
/**
 * UserRegistration License Settings
 *
 * @class    UR_Settings_License
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UR_Settings_License extends UR_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'license';
		$this->label = __( 'License', 'user-registration' );

		add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'user_registration_license_settings', array(
			array(
				'title' => __( 'License Options', 'user-registration' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'license_options',
			),

			array(
				'title'    => __( 'License key', 'user-registration' ),
				'desc'     => __( 'This option let you activate addons with license key.', 'user-registration' ),
				'id'       => 'user_registration_license_key',
				'default'  => '',
				'type'     => 'text',
				'autoload' => false,
				'desc_tip' => true,
				'css'      => 'min-width: 350px;',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'license_options',
			),
		) );

		return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
	}
}

return new UR_Settings_License();
