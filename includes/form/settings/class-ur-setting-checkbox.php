<?php
/**
 * UR_Setting_Checkbox.
 *
 * @package  UserRegistration/Form/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Checkbox Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Checkbox extends UR_Field_Settings {

	/** Class constructor. */
	public function __construct() {
		$this->field_id = 'checkbox_advance_setting';
	}

	/** Output
	 *
	 * @param array $field_data field data.
	 */
	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	/** Register fields */
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
			'choice_limit' => array(
				'label'       => __( 'Choice Limit', 'user-registration' ),
				'data-id'     => $this->field_id . '_choice_limit',
				'name'        => $this->field_id . '[choice_limit]',
				'class'       => $this->default_class . ' ur-settings-min',
				'type'        => 'number',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Choice Limit', 'user-registration' ),
				'tip'         => __( 'Enter maximum number choices that can be selected.', 'user-registration' ),
			),
			'select_all'   => array(
				'label'       => __( 'Select All ', 'user-registration' ),
				'data-id'     => $this->field_id . '_select_all',
				'name'        => $this->field_id . '[select_all]',
				'class'       => $this->default_class . ' ur-settings-select',
				'type'        => 'toggle',
				'required'    => false,
				'default'     => 'false',
				'placeholder' => '',
				'tip'         => __( 'Enable this option to select all the options', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify the checkbox custom advance settings.
		 *
		 * @param string $fields Fields to be modified.
		 * @param int field_id Field ID.
		 * @param class default_class.
		 * @return string $fields.
		 */
		$fields = apply_filters( 'checkbox_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Checkbox();
