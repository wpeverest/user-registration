<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	public $field_data = array();

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
	public abstract function output( $field_data = array() );


	public abstract function register_fields();


	/**
	 * @param $fields
	 */
	public function render_html( $fields ) {

		$this->fields_html = '';

		foreach ( $fields as $field_key => $field ) {

			$this->fields_html .= '<div class="ur-advance-setting ur-advance-' . $field_key . '">';

			$this->fields_html .= '<label for="' . $field['class'] . '">' . $field['label'] . '</label>';

			$value = $this->get_advance_setting_data( $field_key ) == '' ? $field['default'] : $this->get_advance_setting_data( $field_key );

			switch ( $field['type'] ) {

				case 'text':

					$this->fields_html .= '<input data-advance-field="' . $field_key . '" value="' . $value . '" class="' . $field['class'] . '" type="text" name="' . $field['name'] . '" id="' . $field['id'] . '"  placeholder="' . $field['placeholder'] . '"';

					if ( true == $field['required'] ) {

						$this->fields_html .= ' required ';

					}

					$this->fields_html .= ' />';

					break;

				case 'textarea':

					$this->fields_html .= '<textarea data-advance-field="' . $field_key . '" class="' . $field['class'] . '" type="text" name="' . $field['name'] . '" id="' . $field['id'] . '"  placeholder="' . $field['placeholder'] . '"';

					if ( true == $field['required'] ) {

						$this->fields_html .= ' required ';

					}

					$this->fields_html .= '>' . $value . '</textarea>';

					break;

				default:

			}

			$this->fields_html .= '</div>';
		}// End foreach().
	}

}
