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
 * UR_Form_Field_Setting Class.
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
	 * Fields html.
	 *
	 * @var string
	 */
	public $fields_html;

	/**
	 * Field Data.
	 *
	 * @var array
	 */
	public $field_data = array();

	/**
	 * Default Class.
	 *
	 * @var string
	 */
	public $default_class = 'ur_advance_setting';

	/**
	 * Get Advance setting data.
	 *
	 * @param string $key Atrribute of fields.
	 */
	public function get_advance_setting_data( $key ) {

		if ( isset( $this->field_data->advance_setting->$key ) ) {
			return $this->field_data->advance_setting->$key;
		}

		return '';
	}

	/**
	 * Output.
	 *
	 * @param array $field_data field data.
	 */
	abstract public function output( $field_data = array() );


	/**
	 * Register fields.
	 */
	abstract public function register_fields();


	/**
	 * Render html.
	 *
	 * @param array $fields list of fieds.
	 */
	public function render_html( $fields ) {

		$this->fields_html = '';

		foreach ( $fields as $field_key => $field ) {

			$tooltip_html = ! empty( $field['tip'] ) ? ur_help_tip( $field['tip'], false, 'ur-portal-tooltip' ) : '';
			$smart_tags   = '';
			if ( 'default_value' === $field_key ) {
				$smart_tags_list = UR_Smart_Tags::smart_tags_list();
				$smart_tags     .= '<a href="#" class="button ur-smart-tags-list-button"><span class="dashicons dashicons-editor-code"></span></a>';
				$smart_tags     .= '<div class="ur-smart-tags-list" style="display: none">';
				$smart_tags     .= '<div class="smart-tag-title ur-smart-tag-title">Smart Tags</div><ul class="ur-smart-tags">';
				foreach ( $smart_tags_list as $key => $value ) {
					$smart_tags .= "<li class='ur-select-smart-tag' data-key = '" . esc_attr( $key ) . "'> " . esc_html( $value ) . '</li>';
				}
				$smart_tags .= '</ul></div>';
			}
			$this->fields_html .= '<div class="ur-advance-setting ur-advance-' . esc_attr( $field_key ) . '">';
			$this->fields_html .= '<label for="' . esc_attr( $field['class'] ) . '">' . ( isset( $field['label'] ) ? esc_attr( $field['label'] ) : '' ) . $tooltip_html . '</label>';

			$value = $this->get_advance_setting_data( $field_key ) == '' && isset( $field['default'] ) ? $field['default'] : $this->get_advance_setting_data( $field_key );

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
				default:
			}

			$this->fields_html .= '</div>';
		}// End foreach().
	}
}
