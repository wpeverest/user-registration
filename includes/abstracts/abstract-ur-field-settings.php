<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract UR Field Setting Class
 *
 * @version  1.0.0
 * @package  UserRegistration/Abstracts
 * @category Abstract Class
 * @author   WPEverest
 */
abstract class UR_Field_Settings {

	public $field_id;
	public $fields_html;
	public $field_data    = array();
	public $default_class = 'ur_advance_setting';

	/**
	 * @param $key
	 *
	 * @return string
	 */
	public function get_advance_setting_data( $key ) {

		if ( isset( $this->field_data->advance_setting->$key ) ) {
			return $this->field_data->advance_setting->$key;
		}

		return '';
	}

	/**
	 * @param array $field_data
	 *
	 * @return mixed
	 */
	abstract public function output( $field_data = array() );


	abstract public function register_fields();


	/**
	 * @param $fields
	 */
	public function render_html( $fields ) {

		$this->fields_html = '';

		foreach ( $fields as $field_key => $field ) {

			$this->fields_html .= '<div class="ur-advance-setting ur-advance-' . esc_attr( $field_key ) . '">';
			$this->fields_html .= '<label for="' . esc_attr( $field['class'] ) . '">' . esc_html( $field['label'] ) . '</label>';

			$value = $this->get_advance_setting_data( $field_key ) == '' ? $field['default'] : $this->get_advance_setting_data( $field_key );

			switch ( $field['type'] ) {

				case 'text':
					$this->fields_html .= '<input data-advance-field="' . esc_attr( $field_key ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $field['class'] ) . '" type="text" name="' . esc_attr( $field['name'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '"';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					$this->fields_html .= ' />';
					break;

				case 'select':
					$this->fields_html .= '<select data-advance-field="' . esc_attr( $field_key ) . '" class="' . esc_attr( $field['class'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '" ';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					$field_options = isset( $field['options'] ) ? $field['options'] : array();

					$this->fields_html .= '>';

					foreach ( $field_options as $option_key => $option_value ) {
						$required           = $value === $option_key ? 'selected="selected"' : '';
						$this->fields_html .= '<option value="' . esc_attr( $option_key ) . '" ' . $required . '>' . esc_html( $option_value ) . '</option>';
					}

					$this->fields_html .= '</select>';
					break;

				case 'textarea':
					$this->fields_html .= '<textarea data-advance-field="' . esc_attr( $field_key ) . '" class="' . esc_attr( $field['class'] ) . '" type="text" name="' . esc_attr( $field['name'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '"';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					$this->fields_html .= '>' . esc_html( $value ) . '</textarea>';
					break;

				case 'radio':
					if ( ! empty( $value ) ) {
						$options = explode( ',', trim( $value ) );
					} else {
						$options = isset( $field['options'] ) ? $field['options'] : array();
					}

					foreach ( $options as  $option ) {
						$this->fields_html .= '<div class="">';
						$this->fields_html .= '<input data-advance-field="' . esc_attr( $field_key ) . '" value="' . esc_attr( $option ) . '" class="' . esc_attr( $field['class'] ) . '" type="radio" name="' . esc_attr( $field['name'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '"';

						if ( true === $field['required'] ) {
							$this->fields_html .= ' required ';
						}

						$this->fields_html .= '' . checked( $option, 1 ) . ' />';
						$this->fields_html .= '<input data-advance-field"' . esc_attr( $field_key ) . '" value="' . esc_attr( trim( $option ) ) . '"/>';
						$this->fields_html .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
						$this->fields_html .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a><br/>';
						$this->fields_html .= '</div>';
					}

					break;
				default:
			}

			$this->fields_html .= '</div>';
		}// End foreach().
	}
}
