<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Textarea Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Textarea extends UR_Field_Settings {

	public function __construct() {
		$this->field_id = 'textarea_advance_setting';
	}

	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {

		$fields = array(
			'limit_length' => array(
				'label'       => __( 'Limit Length', 'user-registration' ),
				'data-id'     => $this->field_id . '_limit_length',
				'name'        => $this->field_id . '[limit_length]',
				'class'       => $this->default_class . ' ur-settings-limit-length',
				'type'        => 'toggle',
				'required'    => false,
				'default'     => 'false',
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'limit_length_limit_count' => array(
				'label'       => __( 'Limit Count', 'user-registration' ),
				'data-id'     => $this->field_id . '_limit_length_limit_count',
				'name'        => $this->field_id . '[limit_length_limit_count]',
				'class'       => $this->default_class . ' ur-settings-limit-length-limit-count',
				'type'        => 'number',
				'required'    => false,
				'default'     => 500,
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'limit_length_limit_mode' => array(
				'label'       => __( 'Limit Mode', 'user-registration' ),
				'data-id'     => $this->field_id . '_limit_length_limit_mode',
				'name'        => $this->field_id . '[limit_length_limit_mode]',
				'class'       => $this->default_class . ' ur-settings-limit-length-limit-mode',
				'type'        => 'select',
				'options'     => array(
					'characters' => esc_html__("Characters", 'user-registration'),
					'words' => esc_html__("Words Count", 'user-registration'),
				),
				'required'    => false,
				'default'     => 'characters',
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'minimum_length' => array(
				'label'       => __( 'Minimum Length', 'user-registration' ),
				'data-id'     => $this->field_id . '_minimum_length',
				'name'        => $this->field_id . '[minimum_length]',
				'class'       => $this->default_class . ' ur-settings-minimum-length',
				'type'        => 'toggle',
				'required'    => false,
				'default'     => 'false',
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'minimum_length_limit_count' => array(
				'label'       => __( 'Limit Count', 'user-registration' ),
				'data-id'     => $this->field_id . '_minimum_length_limit_count',
				'name'        => $this->field_id . '[minimum_length_limit_count]',
				'class'       => $this->default_class . ' ur-settings-minimum-length-limit-count',
				'type'        => 'number',
				'required'    => false,
				'default'     => 100,
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'minimum_length_limit_mode' => array(
				'label'       => __( 'Limit Mode', 'user-registration' ),
				'data-id'     => $this->field_id . '_minimum_length_limit_mode',
				'name'        => $this->field_id . '[minimum_length_limit_mode]',
				'class'       => $this->default_class . ' ur-settings-minimum-length-limit-mode',
				'type'        => 'select',
				'options'     => array(
					'characters' => esc_html__("Characters", 'user-registration'),
					'words' => esc_html__("Words Count", 'user-registration'),
				),
				'required'    => false,
				'default'     => 'characters',
				'placeholder' => '',
				'tip'         => __( 'Allowed maximum number of characters / words.', 'user-registration' ),
			),
			'default_value' => array(
				'label'       => __( 'Default Value', 'user-registration' ),
				'data-id'     => $this->field_id . '_default_value',
				'name'        => $this->field_id . '[default_value]',
				'class'       => $this->default_class . ' ur-settings-default-value',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Default Value', 'user-registration' ),
				'tip'         => __( 'Default value for this field.', 'user-registration' ),

			),
			'custom_class'  => array(
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

		$fields = apply_filters( 'textarea_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Textarea();
