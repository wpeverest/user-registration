<?php
/**
 * UR_Setting_User_url Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_User_url Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_User_url extends UR_Field_Settings {

	/**
	 * UR_Setting_User_url Class Constructor.
	 */
	public function __construct() {
		$this->field_id = 'user_url_advance_setting';
	}

	/**
	 * Outputs settings html.
	 *
	 * @param array $field_data Field data array.
	 * @return string $fields_html.
	 */
	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register new settings field.
	 */
	public function register_fields() {
		$fields = array(

			'custom_class' => array(
				'label'       => __( 'Custom Class', 'user-registration' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Custom Class', 'user-registration' ),
				'tip'         => __( 'Add a CSS class for custom styling.', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify the user url custom advance settings.
		 *
		 * @param string $fields Advance Settings Fields.
		 * @param int field_id Field ID.
		 * @param class default_class Default Class.
		 *
		 * @return string $fields.
		 */
		$fields = apply_filters( 'user_url_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_User_url();
