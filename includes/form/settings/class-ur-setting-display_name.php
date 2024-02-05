<?php
/**
 * UR_Setting_Display_name Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Display_name Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Display_name extends UR_Field_Settings {

	/**
	 * UR_Setting_Display_name Class Constructor.
	 */
	public function __construct() {
		$this->field_id = 'display_name_advance_setting';
	}

	/**
	 * Output Field Data.
	 *
	 * @param  array $field_data Field Data.
	 * @return string $field_html.
	 */
	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register fields.
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
				'tip'         => __( 'Class name to embed in this field.', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify display name custom advance settings.
		 *
		 * @param string $fields Custom date fields.
		 * @param int field_id Field ID.
		 * @param class default_class Default class for field.
		 * @return string $fields.
		 */
		$fields = apply_filters( 'display_name_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Display_name();
