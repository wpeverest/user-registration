<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract UR Form FieldClass
 *
 * Implemented by classes using the same CRUD(s) pattern.
 *
 * @version  2.6.0
 * @package  UserRegistration/Abstracts
 * @category Abstract Class
 * @author   WPEverest
 */
abstract class UR_Form_Field {

	/**
	 * ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;


	protected $field_defaults = array();

	protected $admin_data = array();


	protected $registered_fields_config = array();

	/**
	 * Form ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	public abstract function get_registered_admin_fields();

	public function get_general_setting_data( $key ) {

		if ( isset( $this->admin_data->general_setting->$key ) ) {

			return $this->admin_data->general_setting->$key;
		}

		if ( isset( $this->field_defaults[ 'default_' . $key ] ) ) {

			return $this->field_defaults[ 'default_' . $key ];
		}

		return '';
	}


	public function get_admin_template( $admin_data = array() ) {

		ob_start();

		$this->admin_data = $admin_data;

		$template_path = str_replace( '_', '-', str_replace( 'user_registration_', 'admin-', $this->id ) );

		$admin_template_path = apply_filters( $this->id . '_admin_template', UR_FORM_PATH . 'views' . UR_DS . 'admin' . UR_DS . $template_path . '.php' );

		include( $admin_template_path );

		$this->admin_data = array();

		return ob_get_clean();
	}


	/**
	 * Includes any classes we need within frontend.
	 */
	public function frontend_includes( $data = array(), $form_id, $field_type, $field_key ) {

		$this->form_id = $form_id;

		$form_data = array(

			'label' => $data['general_setting']->label,

			'placeholder' => $data['general_setting']->placeholder,

			'type' => $field_type,

		);

		if ( in_array( $field_key, ur_get_required_fields() ) || 'yes' === $data['general_setting']->required ) {

			$form_data['required'] = true;

			$form_data['custom_attributes']['required'] = 'required';
		}

		if ( isset( $data['advance_setting']->size ) ) {

			$form_data['size'] = $data['advance_setting']->size;

		}
		if ( isset( $data['advance_setting']->default_value ) ) {

			$form_data['default'] = $data['advance_setting']->default_value;

		}
		$form_data['input_class'] = array( 'ur-frontend-field ' );

		if ( isset( $data['advance_setting']->custom_class ) ) {

			array_push( $form_data['input_class'], $data['advance_setting']->custom_class );

		}
		$form_data['custom_attributes']['data-label'] = $data['general_setting']->label;

		if ( 'country' == $field_key ) {

			$form_data['options'] = UR_Country::get_instance()->get_country();

		}
		if ( 'select' == $field_key ) {

			$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array();

			if ( is_array( $option_data ) ) {

				foreach ( $option_data as $index_data => $option ) {

					$form_data['options'][ $index_data . '__' . $option ] = $option;

				}
			}
		}

		user_registration_form_field( $data['general_setting']->field_name, $form_data );

	}


	public function get_field_advance_settings() {

		$file_name = str_replace( 'user_registration_', '', $this->id );

		$file_path = UR_FORM_PATH . 'settings' . UR_DS . 'class-ur-setting-' . strtolower( $file_name ) . '.php';

		$class_name = 'UR_Setting_' . ucwords( $file_name );

		if ( ! class_exists( $class_name ) ) {

			if ( file_exists( $file_path ) ) {

				$advance_setting_instance = include_once( $file_path );

				return $advance_setting_instance->output( $this->admin_data );
			}
		} else {

			$instance = new $class_name;

			return $instance->output( $this->admin_data );
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function get_field_general_settings() {

		$general_settings = ur_get_general_settings();

		$general_setting_html = '';

		foreach ( $general_settings as $setting_key => $setting_value ) {

			$general_setting_wrapper = '<div class="ur-general-setting ur-setting-' . $setting_value['type'] . '">';

			$general_setting_wrapper .= '<label for="ur-type-' . $setting_value['type'] . '">' . $setting_value['label'] . '</label>';

			$sub_string_key = substr( $this->id, strlen( 'user_registration_' ), 5 );

			switch ( $setting_value['type'] ) {

				case 'text':

					$extra_attribute = 'user_' == $sub_string_key && 'field_name' == $setting_key ? "disabled='disabled'" : '';

					$value = 'user_' == $sub_string_key && 'field_name' == $setting_key ? trim( str_replace( 'user_registration_', '', $this->id ) ) : $this->get_general_setting_data( $setting_key );

					$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="text" name="' . $setting_value['name'] . '" id="' . $setting_value['id'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

					if ( true == $setting_value['required'] ) {

						$general_setting_wrapper .= ' required ';

					}

					$general_setting_wrapper .= $extra_attribute . ' />';

					break;

				case 'radio':

					if ( isset( $setting_value['options'] ) && gettype( $setting_value['options'] ) == 'array' ) {

						foreach ( $setting_value['options'] as $option_key => $option_value ) {

							$general_setting_wrapper .= '<span>' . $option_value . '</span><input data-field="' . $setting_key . '"  value="' . $option_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="radio" name="' . $setting_value['name'] . '" id="' . $setting_value['id'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

							$general_setting_wrapper .= ' />';

						}
					} else {
						$general_setting_wrapper .= '<input data-field="' . $setting_key . '"  class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="radio" name="' . $setting_value['name'] . '" id="' . $setting_value['id'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

						if ( true == $setting_value['required'] ) {

							$general_setting_wrapper .= ' required ';

						}

						$general_setting_wrapper .= ' />';
					}
					break;
				case 'select':

					if ( isset( $setting_value['options'] ) && gettype( $setting_value['options'] ) == 'array' ) {

						$general_setting_wrapper .= '<select data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . $setting_value['name'] . '" id="' . $setting_value['id'] . '" >';

						foreach ( $setting_value['options'] as $option_key => $option_value ) {

							$selected = $this->get_general_setting_data( $setting_key ) == $option_key ? "selected='selected'" : '';

							$general_setting_wrapper .= '<option ' . $selected . " value='" . $option_key . "'>" . $option_value . '</option>';
						}
						$general_setting_wrapper .= '</select>';

					}

					break;
				case 'textarea':

					$general_setting_wrapper .= '<textarea data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . $setting_value['name'] . '" id="' . $setting_value['id'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

					if ( true == $setting_value['required'] ) {

						$general_setting_wrapper .= ' required ';

					}

					$general_setting_wrapper .= $this->get_general_setting_data( $setting_key ) . '</textarea>';

					break;

				default:

			}// End switch().

			$general_setting_wrapper .= '</div>';

			$general_setting_html .= $general_setting_wrapper;

		}// End foreach().

		return $general_setting_html;
	}


	public function get_setting() {

		echo "<div class='ur-general-setting-block'>";

		echo '<h2>' . __( 'General Settings', 'user-registration' ) . '</h2>';

		echo $this->get_field_general_settings();

		echo '</div>';

		$advance_settings = $this->get_field_advance_settings();

		if ( '' != $advance_settings ) {

			echo "<div class='ur-advance-setting-block'>";

			echo '<h2>' . __( 'Advance Settings', 'user-registration' ) . '</h2>';

			echo $advance_settings;

			echo '</div>';
		}

	}

	public abstract function validation( $single_form_field, $form_data, $filter_hook, $form_id );

}
