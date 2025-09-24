<?php
/**
 * UR_Setting_Password Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Password Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Password extends UR_Field_Settings {

	/**
	 * UR_Setting_Password Class Constructor.
	 */
	public function __construct() {
		$this->field_id = 'password_advance_setting';
	}

	/**
	 * Output.
	 *
	 * @param array $field_data field data.
	 * @return string $field_html Field HTML.
	 */
	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register Fields.
	 */
	public function register_fields() {
		$fields = array(
			'size' => array(
				'label'       => __( 'Maximum Password Length', 'user-registration' ),
				'data-id'     => $this->field_id . '_size',
				'name'        => $this->field_id . '[size]',
				'class'       => $this->default_class . ' ur-settings-size',
				'type'        => 'text',
				'required'    => false,
				'default'     => '5',
				'placeholder' => __( 'Maximum Password Length', 'user-registration' ),
				'tip'         => __( 'Set the maximum number of characters allowed for the password.', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify the password custom advance settings.
		 *
		 * @param string $fields Advance Settings Fields.
		 * @param int field_id Field ID.
		 * @param class default_class Default Class.
		 *
		 * @return string $fields.
		 */
		$fields = apply_filters( 'password_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Password();
