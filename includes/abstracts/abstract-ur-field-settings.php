<?php
/**
 * Abstract UR Field Setting Class
 *
 * @version  1.0.0
 * @package  UserRegistration/Abstracts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Field_Settings Class
 */
abstract class UR_Field_Settings {

	/**
	 * Field id for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $field_id;

	/**
	 * Html Wrapper for Form Fields
	 *
	 * @var string
	 */
	public $fields_html;

	/**
	 * Field Datas.
	 *
	 * @var array
	 */
	public $field_data = array();

	/**
	 * Default class for advance settings.
	 *
	 * @var string
	 */
	public $default_class = 'ur_advance_setting';

	/**
	 * Retrieves Advance Setting Data.
	 *
	 * @param string $key Field Option Key.
	 */
	public function get_advance_setting_data( $key ) {

		if ( isset( $this->field_data->advance_setting->$key ) ) {
			return ur_string_to_bool( $this->field_data->advance_setting->$key );
		}

		return '';
	}

	/**
	 * Abstract function for output.
	 *
	 * @param array $field_data field Data.
	 *
	 * @param array $field_data field data.
	 */
	abstract public function output( $field_data = array() );


	/**
	 * Register Fields.
	 */
	abstract public function register_fields();


	/**
	 * Render Html for advanced settings field option.
	 *
	 * @param array $fields Fields data.
	 */
	public function render_html( $fields ) {

		$this->fields_html = '';

		foreach ( $fields as $field_key => $field ) {

			$tooltip_html = ! empty( $field['tip'] ) ? ur_help_tip( $field['tip'], false, 'ur-portal-tooltip' ) : '';
			$smart_tags   = '';
			if ( 'default_value' === $field_key ) {
				$smart_tags = apply_filters( 'ur_smart_tags_list_in_general', $smart_tags );
			}

			$this->fields_html .= '<div class="ur-advance-setting ur-advance-' . esc_attr( $field_key ) . '">';

			if ( 'toggle' !== $field['type'] ) {
				$this->fields_html .= '<label for="' . esc_attr( $field['class'] ) . '">' . ( isset( $field['label'] ) ? esc_attr( $field['label'] ) : '' ) . $tooltip_html . '</label>';
				$value              = $this->get_advance_setting_data( $field_key ) == '' && isset( $field['default'] ) ? $field['default'] : $this->get_advance_setting_data( $field_key );
			} else {
				if ( isset( $this->field_data->advance_setting->$field_key ) ) {
					if ( empty( $this->field_data->advance_setting->$field_key ) ) {
						$value = false;
					} else {
						$value = ur_string_to_bool( $this->field_data->advance_setting->$field_key );
					}
				} else {
					if ( isset( $field['default'] ) ) {
						$value = ur_string_to_bool( $field['default'] );
					}
				}
			}

			switch ( $field['type'] ) {

				case 'text':
					$this->fields_html .= '<input data-advance-field="' . esc_attr( $field_key ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $field['class'] ) . '" type="text" name="' . esc_attr( $field['name'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . ( isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '' ) . '"';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					$this->fields_html .= ' />';
					$this->fields_html .= $smart_tags;
					break;

				case 'select':
					$is_multiple        = isset( $field['multiple'] ) && true === $field['multiple'];
					$this->fields_html .= '<select data-advance-field="' . esc_attr( $field_key ) . '" class="' . esc_attr( $field['class'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '" name="' . esc_attr( $field['name'] ) . esc_attr( $is_multiple ? '[]' : '' ) . '"';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					if ( true === $is_multiple ) {
						$this->fields_html .= ' multiple ';
					}

					$field_options = isset( $field['options'] ) ? $field['options'] : array();

					$this->fields_html .= '>';

					foreach ( $field_options as $option_key => $option_value ) {
						$selected_value = '';

						if ( true === $is_multiple && is_array( $value ) ) {
							$selected_value = in_array( $option_key, $value, true ) ? 'selected="selected"' : '';
						} else {
							$selected_value = ( $value === $option_key ) ? 'selected="selected"' : '';
						}

						$this->fields_html .= '<option value="' . esc_attr( $option_key ) . '" ' . $selected_value . '>' . esc_html( $option_value ) . '</option>';
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

				case 'number':
					$this->fields_html .= '<input data-advance-field="' . esc_attr( $field_key ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $field['class'] ) . '" type="number" name="' . esc_attr( $field['name'] ) . '" data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '"';

					if ( true == $field['required'] ) {
						$this->fields_html .= ' required ';
					}

					$this->fields_html .= ' />';
					break;
				case 'toggle':
					$this->fields_html .= '<div class="ur-toggle-section ur-form-builder-toggle" style="justify-content: space-between;">';
					$this->fields_html .= '<label class="ur-label checkbox" for="ur-type-toggle">' . $field['label'] . $tooltip_html . '</label>';
					$this->fields_html .= '<span class="user-registration-toggle-form">';
					$checked            = ur_string_to_bool( $value ) ? 'checked' : '';
					$this->fields_html .= '<input type="checkbox" data-advance-field="' . esc_attr( $field_key ) . '" class="' . esc_attr( $field['class'] ) . '"  name="' . esc_attr( $field['name'] ) . '" ' . $checked . ' data-id="' . ( isset( $field['data-id'] ) ? esc_attr( $field['data-id'] ) : '' ) . '">';
					$this->fields_html .= '<span class="slider round"></span>';
					$this->fields_html .= '</span>';
					$this->fields_html .= '</div>';
					break;
				default:
			}

			$this->fields_html .= '</div>';
		}// End foreach().
	}
}
