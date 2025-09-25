<?php
/**
 * UR_Setting_Membership Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Membership Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Membership extends UR_Field_Settings {

	/**
	 * UR_Setting_Membership Class Constructor.
	 */
	public function __construct() {
		$this->field_id = 'membership_advance_setting';
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
            'field_name'  => array(
				'setting_id'  => 'field-name',
				'type'        => 'text',
				'label'       => __( 'Field Name', 'user-registration' ),
				'class'       => $this->default_class . ' ur-settings-field-name',
				'name'        => $this->field_id . '[field_name]',
				'placeholder' => __( 'Field Name', 'user-registration' ),
				'required'    => true,
				'tip'         => __( 'Unique key for the field.', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify the first name custom advance settings.
		 *
		 * @param string $fields Advance Settings Fields.
		 * @param int field_id Field ID.
		 * @param class default_class Field Default Class.
		 *
		 * @return string $fields.
		 */
		$fields = apply_filters( 'membership_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Membership();
