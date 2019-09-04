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
			'custom_class' => array(
				'label'       => __( 'Custom Class', 'user-registration' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Custom Class', 'user-registration' ),

			),

			'date_format'  => array(
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
					'm/d/Y'  => date( 'm/d/Y' ) . ' (m/d/Y)',
					'd/m/Y'  => date( 'd/m/Y' ) . ' (d/m/Y)',
				),
			),

			'min_date'     => array(
				'label'       => __( 'Min Date', 'user-registration' ),
				'data-id'     => $this->field_id . '_min_date',
				'name'        => $this->field_id . '[min_date]',
				'class'       => $this->default_class . ' ur-settings-min_date',
				'type'        => 'date',
				'required'    => false,
				'default'     => '',
			),

			'max_date'     => array(
				'label'       => __( 'Max Date', 'user-registration' ),
				'data-id'     => $this->field_id . '_max_date',
				'name'        => $this->field_id . '[max_date]',
				'class'       => $this->default_class . ' ur-settings-max_date',
				'type'        => 'date',
				'required'    => false,
				'default'     => '',
			),
		);

			$this->render_html( $fields );
	}
}

return new UR_Setting_Date();
