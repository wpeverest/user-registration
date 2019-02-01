<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract UR_Form_Field Class
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
	protected $id                       = 0;
	protected $field_defaults           = array();
	protected $admin_data               = array();
	protected $registered_fields_config = array();

	/**
	 * Form ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	abstract public function get_registered_admin_fields();

	public function get_general_setting_data( $key ) {

		if ( isset( $this->admin_data->general_setting->$key ) ) {
			return $this->admin_data->general_setting->$key;
		}

		if ( isset( $this->field_defaults[ 'default_' . $key ] ) ) {
			return $this->field_defaults[ 'default_' . $key ];
		}

		return '';
	}

	/**
	 * Include admin template for each form fields
	 *
	 * @param  array $admin_data
	 */
	public function get_admin_template( $admin_data = array() ) {

		ob_start();

		$this->admin_data = $admin_data;

		$template_path       = str_replace( '_', '-', str_replace( 'user_registration_', 'admin-', $this->id ) );
		$admin_template_path = apply_filters( $this->id . '_admin_template', UR_FORM_PATH . 'views' . UR_DS . 'admin' . UR_DS . $template_path . '.php' );
		include $admin_template_path;

		$this->admin_data = array();

		return ob_get_clean();
	}


	/**
	 * Includes any classes we need within frontend.
	 */
	public function frontend_includes( $data = array(), $form_id, $field_type, $field_key ) {
		$this->form_id = $form_id;

		$form_data         = (array) $data['general_setting'];
		$form_data['type'] = $field_type;

		if ( isset( $form_data['hide_label'] ) && 'yes' === $form_data['hide_label'] ) {
			unset( $form_data['label'] );
		}

		if ( isset( $data['general_setting']->required ) ) {

			if ( in_array( $field_key, ur_get_required_fields() )
				|| 'yes' === $data['general_setting']->required ) {

				$form_data['required']                      = true;
				$form_data['custom_attributes']['required'] = 'required';
			}
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

		if ( 'country' === $field_key ) {
			$form_data['options'] = UR_Form_Field_Country::get_instance()->get_country();
		}

		/**  Redundant codes. **/
			if ( 'select' === $field_key ) {
				$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array(); // Backward compatibility. Modified since 1.5.7
				$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $option_data;

				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$form_data['options'][ $index_data ] = $option;
					}
				}
			}

			if ( 'radio' === $field_key ) {
				$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array(); // Backward compatibility. Modified since 1.5.7
				$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $option_data;

				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$form_data['options'][ $index_data ] = $option;
					}
				}
			}

			if ( 'checkbox' === $field_key ) {
				$choices = isset( $data['advance_setting']->choices ) ? explode( ',', $data['advance_setting']->choices ) : array(); // Backward compatibility. Modified since 1.5.7
				$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $choices;

				if ( is_array( $choices ) ) {
					foreach ( $choices as $index_data => $choice ) {
						$form_data['choices'][ $index_data ] = $choice;
					}
				}
			}
		/** Redundant Codes End. **/

		$filter_data = array(
			'form_data' => $form_data,
			'data'      => $data,
		);

		$form_data_array = apply_filters( 'user_registration_' . $field_key . '_frontend_form_data', $filter_data );

		$form_data = isset( $form_data_array['form_data'] ) ? $form_data_array['form_data'] : $form_data;

		if ( isset( $data['general_setting']->field_name ) ) {
			user_registration_form_field( $data['general_setting']->field_name, $form_data );
		}

	}

	/**
	 * Inlcude advance settings file if exists

	public function get_field_advance_settings() {

		$file_name  = str_replace( 'user_registration_', '', $this->id );
		$file_path  = UR_FORM_PATH . 'settings' . UR_DS . 'class-ur-setting-' . strtolower( $file_name ) . '.php';
		$class_name = 'UR_Setting_' . ucwords( $file_name );

		if ( ! class_exists( $class_name ) ) {
			$file_path_array = apply_filters(
				'user_registration_' . strtolower( $file_name ) . '_advance_class',
				array(

					'file_name' => strtolower( $file_name ),
					'file_path' => $file_path,
				)
			);
			$file_path       = isset( $file_path_array['file_path'] ) ? $file_path_array['file_path'] : $file_path;

			if ( file_exists( $file_path ) ) {
				$advance_setting_instance = include_once $file_path;
				return $advance_setting_instance->output( $this->admin_data );
			}
		} else {

			$instance = new $class_name();
			return $instance->output( $this->admin_data );
		}

		return '';
	}

	/**
	 * @return string
	 * @param string $id Form field name
	 */
	public function get_field_general_settings() {

		$general_settings     = ur_get_general_settings( $this->id );
		$general_setting_html = '';

		foreach ( $general_settings as $setting_key => $setting_value ) {
			$general_setting_wrapper  = '<div class="ur-general-setting ur-setting-' . $setting_value['type'] . ' ur-general-setting-' . str_replace( ' ', '-', strtolower( $setting_value['label'] ) ) . '">';
			$general_setting_wrapper .= '<label for="ur-type-' . $setting_value['type'] . '">' . $setting_value['label'] . '</label>';
			$sub_string_key           = substr( $this->id, strlen( 'user_registration_' ), 5 );
			$strip_prefix             = substr( $this->id, 18 );

			switch ( $setting_value['type'] ) {
				case 'text':
					$extra_attribute          = in_array( $strip_prefix, ur_get_fields_without_prefix() ) && 'field_name' == $setting_key ? "disabled='disabled'" : '';
					$value                    = in_array( $strip_prefix, ur_get_fields_without_prefix() ) && 'field_name' == $setting_key ? trim( str_replace( 'user_registration_', '', $this->id ) ) : $this->get_general_setting_data( $setting_key );
					$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="text" name="' . $setting_value['name'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}

					$general_setting_wrapper .= $extra_attribute . ' />';
					break;

				case 'radio':
					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
					$default_options 	 = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
					$old_options         = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $default_options;
					$options    		 = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
					$options 			 = array_map( 'trim', $options );

					$default_value = $this->get_general_setting_data( 'default_value' );
					$default_value = ! empty( $default_value ) ? $default_value : '';

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique = uniqid();

					foreach ( $options as  $option ) {

						$general_setting_wrapper .= '<li>';
						$general_setting_wrapper .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
						<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
						</div>';
						$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-value" type="radio" name="' . $unique . '_value" ';

						if ( true == $setting_value['required'] ) {
							$general_setting_wrapper .= ' required ';
						}

						$general_setting_wrapper .= '' . checked( $option, $default_value, false ) . ' />';
						$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-label" type="text" name="' . $setting_value['name'] . '_label" >';

						$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
						$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a><br/>';
						$general_setting_wrapper .= '</li>';

					}
						$general_setting_wrapper .= '</ul>';
					break;

				case 'checkbox':

					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
					$default_options 	 = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
					$old_options         = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : $default_options;
					$options    		 = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
					$options 			 = array_map( 'trim', $options );

					$default_values = $this->get_general_setting_data( 'default_value' );
					$default_values = ! empty( $default_values ) ? $default_values : array();
					$default_values = array_map( 'trim', $default_values );

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique = uniqid();

					foreach ( $options as  $option ) {

						$general_setting_wrapper .= '<li>';
						$general_setting_wrapper .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
						<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
						</div>';
						$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-value" type="checkbox" name="' . $unique . '_value" ';

						if ( true == $setting_value['required'] ) {
							$general_setting_wrapper .= ' required ';
						}

						if( in_array( $option, $default_values ) ) {
							$general_setting_wrapper .= 'checked ="checked" />';
						} else {
							$general_setting_wrapper .= '/>';
						}

						$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-label" type="text" name="' . $setting_value['name'] . '_label" >';

						$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
						$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a><br/>';
						$general_setting_wrapper .= '</li>';

					}
						$general_setting_wrapper .= '</ul>';
					break;

				case 'select':
					if ( isset( $setting_value['options'] )
						&& gettype( $setting_value['options'] ) == 'array' ) {

						$general_setting_wrapper .= '<select data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . $setting_value['name'] . '">';

						foreach ( $setting_value['options'] as $option_key => $option_value ) {
							$selected                 = $this->get_general_setting_data( $setting_key ) == $option_key ? "selected='selected'" : '';
							$general_setting_wrapper .= '<option ' . $selected . " value='" . $option_key . "'>" . $option_value . '</option>';
						}

						$general_setting_wrapper .= '</select>';
					}
					break;

				case 'textarea':
					$general_setting_wrapper .= '<textarea data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . $setting_value['name'] . '" placeholder= "' . esc_attr( $setting_value['placeholder'] ) . '" ';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required >';
					}

					$general_setting_wrapper .= $this->get_general_setting_data( $setting_key ) . '</textarea>';
					break;

				case 'hidden':
					$value = isset( $setting_value['default'] ) ? $setting_value['default'] : '';
					if ( ! empty( $value ) ) {

						$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="hidden" name="' . $setting_value['name'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

						if ( true == $setting_value['required'] ) {
							$general_setting_wrapper .= ' required ';
						}

						$general_setting_wrapper .= '/>';
					}
					break;

				default:
			}// End switch().

			$general_setting_wrapper .= '</div>';
			$general_setting_html    .= $general_setting_wrapper;

		}// End foreach().

		return $general_setting_html;
	}

	/**
	 * Display Setting for each fields in options tab
	 *
	 * @return void
	 */
	public function get_setting() {

		$sub_string_key = substr( $this->id, strlen( 'user_registration_' ), 5 );
		$strip_prefix   = substr( $this->id, 18 );
		$class          = 'ur-general-setting-' . $strip_prefix;

		echo "<div class='ur-general-setting-block " . esc_attr( $class ) . "'>";
		echo '<h2>' . esc_html__( 'General Settings', 'user-registration' ) . '</h2>';
		echo $this->get_field_general_settings();
		echo '</div>';

		$advance_settings = $this->get_field_advance_settings();

		if ( '' != $advance_settings ) {
			echo "<div class='ur-advance-setting-block'>";
			echo '<h2>' . __( 'Advance Settings', 'user-registration' ) . '</h2>';
			echo $advance_settings;
			echo '</div>';
		}

		do_action( 'user_registration_after_advance_settings', $this->id, $this->admin_data );
	}

	abstract public function validation( $single_form_field, $form_data, $filter_hook, $form_id );
}
