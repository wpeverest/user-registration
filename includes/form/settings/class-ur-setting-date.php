<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Date Class
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Date extends UR_Field_Settings {

	/**
	 * Contructor.
	 */
	public function __construct() {
		$this->field_id = 'date_advance_setting';
	}

	/**
	 * Settings Feild Output.
	 *
	 * @param array $field_data Render field data in html.
	 */
	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;
		return $field_html;
	}

	/**
	 * Advance Fields.
	 */
	public function register_fields() {
		$fields = array(
			'custom_class'      => array(
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

			'date_format'       => array(
				'type'        => 'select',
				'data-id'     => $this->field_id . '_date_format',
				'label'       => __( 'Date Format', 'user-registration' ),
				'name'        => $this->field_id . '[date_format]',
				'class'       => $this->default_class . ' ur-settings-date-format',
				'placeholder' => '',
				'default'     => 'Y-m-d',
				'required'    => false,
				'options'     => array(
					'Y-m-d'  => date( 'Y-m-d' ) . ' (Y-m-d)',
					'F j, Y' => date( 'F j, Y' ) . ' (F j, Y)',
					'd/m/Y'  => date( 'd/m/Y' ) . ' (d/m/Y)',
				),
				'tip'         => __( 'Which format do you want to use to show date.', 'user-registration' ),
			),

			'enable_min_max'    => array(
				'type'     => 'select',
				'data-id'  => $this->field_id . '_enable_min_max',
				'label'    => __( 'Enable Min-Max Date', 'user-registration' ),
				'name'     => $this->field_id . '[enable_min_max]',
				'class'    => $this->default_class . ' ur-settings-enable-min-max',
				'default'  => 'false',
				'required' => false,
				'options'  => array(
					'true'  => 'Yes',
					'false' => 'No',
				),
				'tip'      => __( 'Enable min/max date.', 'user-registration' ),
			),

			'min_date'          => array(
				'label'    => __( 'Min Date', 'user-registration' ),
				'data-id'  => $this->field_id . '_min_date',
				'name'     => $this->field_id . '[min_date]',
				'class'    => $this->default_class . ' ur-settings-min-date',
				'type'     => 'text',
				'required' => false,
				'default'  => '',
				'tip'      => __( 'Enter min date.', 'user-registration' ),
			),

			'max_date'          => array(
				'label'    => __( 'Max Date', 'user-registration' ),
				'data-id'  => $this->field_id . '_max_date',
				'name'     => $this->field_id . '[max_date]',
				'class'    => $this->default_class . ' ur-settings-max-date',
				'type'     => 'text',
				'required' => false,
				'default'  => '',
				'tip'      => __( 'Enter max date.', 'user-registration' ),
			),

			'set_current_date'  => array(
				'type'     => 'select',
				'data-id'  => $this->field_id . '_set_current_date',
				'label'    => __( 'Set Current Date as Default Date', 'user-registration' ),
				'name'     => $this->field_id . '[set_current_date]',
				'class'    => $this->default_class . ' ur-settings-set-current-date',
				'default'  => '',
				'required' => false,
				'options'  => array(
					'today' => 'Yes',
					''      => 'No',
				),
				'tip'      => __( 'Set current as default.', 'user-registration' ),
			),

			'enable_date_range' => array(
				'type'     => 'select',
				'data-id'  => $this->field_id . '_enable_date_range',
				'label'    => __( 'Enable Date Range', 'user-registration' ),
				'name'     => $this->field_id . '[enable_date_range]',
				'class'    => $this->default_class . ' ur-settings-enable-date-range',
				'default'  => '',
				'required' => false,
				'options'  => array(
					'range' => 'Yes',
					''      => 'No',
				),
				'tip'      => __( 'Enable date range feature.', 'user-registration' ),
			),
		);

			$this->render_html( $fields );
	}
}

return new UR_Setting_Date();
