<?php
/**
 * UR_Setting_Text Class.
 *
 * @class    UR_Setting_Text
 * @package  UserRegistration/Form/Settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Text Class.
 */
class UR_Setting_Text extends UR_Field_Settings {

	/**
	 * UserRegistration Form Text Settings Constructor.
	 */
	public function __construct() {
		$this->field_id = 'text_advance_setting';
	}

	/**
	 * Outputs settings html.
	 *
	 * @param array $field_data Field data array.
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
			'limit_length' => array(
				'label'       => __( 'Maximum Length', 'user-registration' ),
				'data-id'     => $this->field_id . '_limit_length',
				'name'        => $this->field_id . '[limit_length]',
				'class'       => $this->default_class . ' ur-settings-limit-length',
				'type'        => 'toggle',
				'required'    => false,
				'default'     => 'false',
				'placeholder' => '',
				'tip'         => __( 'Allow Limitation for Maximum Length.', 'user-registration' ),
			),
			'limit_length_limit_count' => array(
				'label'       => __( 'Maximum Count', 'user-registration' ),
				'data-id'     => $this->field_id . '_limit_length_limit_count',
				'name'        => $this->field_id . '[limit_length_limit_count]',
				'class'       => $this->default_class . ' ur-settings-limit-length-limit-count',
				'type'        => 'number',
				'required'    => false,
				'default'     => 50,
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
					'characters' => esc_html__( 'Characters', 'user-registration' ),
					'words' => esc_html__( 'Words Count', 'user-registration' ),
				),
				'required'    => false,
				'default'     => 'characters',
				'placeholder' => '',
				'tip'         => __( 'Maximize by characters / words.', 'user-registration' ),
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
				'tip'         => __( 'Allow Limitation for Minimum Length.', 'user-registration' ),
			),
			'minimum_length_limit_count' => array(
				'label'       => __( 'Minimum Count', 'user-registration' ),
				'data-id'     => $this->field_id . '_minimum_length_limit_count',
				'name'        => $this->field_id . '[minimum_length_limit_count]',
				'class'       => $this->default_class . ' ur-settings-minimum-length-limit-count',
				'type'        => 'number',
				'required'    => false,
				'default'     => 10,
				'placeholder' => '',
				'tip'         => __( 'Allowed minimum number of characters / words.', 'user-registration' ),
			),
			'minimum_length_limit_mode' => array(
				'label'       => __( 'Limit Mode', 'user-registration' ),
				'data-id'     => $this->field_id . '_minimum_length_limit_mode',
				'name'        => $this->field_id . '[minimum_length_limit_mode]',
				'class'       => $this->default_class . ' ur-settings-minimum-length-limit-mode',
				'type'        => 'select',
				'options'     => array(
					'characters' => esc_html__( 'Characters', 'user-registration' ),
					'words' => esc_html__( 'Words Count', 'user-registration' ),
				),
				'required'    => false,
				'default'     => 'characters',
				'placeholder' => '',
				'tip'         => __( 'Minimize by characters / words.', 'user-registration' ),
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
				'id'          => $this->field_id . '_custom_class',
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
		 * Filter to modify the text custom advance settings.
		 *
		 * @param string $fields Advance Settings Fields.
		 * @param int field_id Field ID.
		 * @param class default_class Default Class.
		 *
		 * @return string $fields.
		 */
		$fields = apply_filters( 'text_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Text();
