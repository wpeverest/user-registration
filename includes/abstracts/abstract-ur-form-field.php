<?php
/**
 * Abstract UR_Form_Field Class
 *
 * Implemented by classes using the same CRUD(s) pattern.
 *
 * @version  2.6.0
 * @package  UserRegistration/Abstracts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field Class
 */
abstract class UR_Form_Field {

	/**
	 * ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;
	/**
	 * Default fields array.
	 *
	 * @var array
	 */
	public $field_defaults = array();
	/**
	 * Admin Data Object.
	 *
	 * @var object
	 */
	public $admin_data = array();
	/**
	 * Registered fields configuration.
	 *
	 * @var array
	 */
	public $registered_fields_config = array();

	/**
	 * Form ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Abstract function to get regestered admin fields.
	 */
	abstract public function get_registered_admin_fields();

	/**
	 * Get General Setting fields
	 *
	 * @param string $key Atrribute of fields.
	 */
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
	 * Get advance setting values.
	 *
	 * @param string $key Atrribute of fields.
	 */
	public function get_advance_setting_data( $key ) {

		if ( isset( $this->admin_data->advance_setting->$key ) ) {
			return $this->admin_data->advance_setting->$key;
		}

		if ( isset( $this->field_defaults[ 'default_' . $key ] ) ) {
			return $this->field_defaults[ 'default_' . $key ];
		}

		return '';
	}

	/**
	 * Include admin template for each form fields
	 *
	 * @param  array $admin_data Admin Data.
	 */
	public function get_admin_template( $admin_data = array() ) {

		$this->admin_data = $admin_data;

		ob_start();
		$template_path       = str_replace( '_', '-', str_replace( 'user_registration_', 'admin-', $this->id ) );
		$admin_template_path = apply_filters( $this->id . '_admin_template', UR_FORM_PATH . 'views' . UR_DS . 'admin' . UR_DS . $template_path . '.php' );

		if ( file_exists( $admin_template_path ) ) {
			include $admin_template_path;
		}
		$template = ob_get_clean();

		$settings = $this->get_setting();

		$this->admin_data = array();

		return array(
			'template' => $template . $settings,
			'settings' => $settings,
		);
	}


	/**
	 * Includes any classes we need within frontend.
	 *
	 * @param integer $form_id Form ID.
	 * @param string  $field_type Field Type.
	 * @param string  $field_key Field Key.
	 * @param array   $data Form data.
	 */
	public function frontend_includes( $form_id, $field_type, $field_key, $data = array() ) {
		$this->form_id          = $form_id;
		$form_data              = (array) $data['general_setting'];
		$form_data['form_id']   = $form_id;
		$form_data['type']      = $field_type;
		$form_data['field_key'] = $field_key;
		$form_data['icon']      = $data['icon'];

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

		if ( isset( $data['advance_setting']->min ) ) {
			$form_data['min'] = $data['advance_setting']->min;
		}

		if ( isset( $data['advance_setting']->max ) ) {
			$form_data['max'] = $data['advance_setting']->max;
		}

		if ( isset( $data['advance_setting']->step ) ) {
			$form_data['step'] = $data['advance_setting']->step;
		}

		if ( isset( $data['advance_setting']->default_value ) ) {
			$form_data['default'] = $data['advance_setting']->default_value;
		}

		if ( isset( $data['general_setting']->max_files ) ) {
			$form_data['max_files'] = $data['general_setting']->max_files;
		}

		if ( isset( $data['advance_setting']->max_upload_size ) ) {
			$form_data['max_upload_size'] = $data['advance_setting']->max_upload_size;
		}

		if ( isset( $data['advance_setting']->valid_file_type ) ) {
			$form_data['valid_file_type'] = $data['advance_setting']->valid_file_type;
		}

		$form_data['input_class'] = array( 'ur-frontend-field ' );

		if ( isset( $data['advance_setting']->custom_class ) ) {
			array_push( $form_data['input_class'], $data['advance_setting']->custom_class );
		}

		if ( isset( $data['advance_setting']->date_format ) ) {
			update_option( 'user_registration_' . $data['general_setting']->field_name . '_date_format', $data['advance_setting']->date_format );
			$form_data['custom_attributes']['data-date-format'] = $data['advance_setting']->date_format;
		}

		if ( isset( $data['advance_setting']->enable_min_max ) && 'true' === $data['advance_setting']->enable_min_max ) {
			if ( isset( $data['advance_setting']->min_date ) ) {
				$min_date                                        = str_replace( '/', '-', $data['advance_setting']->min_date );
				$form_data['custom_attributes']['data-min-date'] = '' !== $min_date ? date_i18n( $data['advance_setting']->date_format, strtotime( $min_date ) ) : '';
			}

			if ( isset( $data['advance_setting']->max_date ) ) {
				$max_date                                        = str_replace( '/', '-', $data['advance_setting']->max_date );
				$form_data['custom_attributes']['data-max-date'] = '' !== $max_date ? date_i18n( $data['advance_setting']->date_format, strtotime( $max_date ) ) : '';
			}
		}

		if ( isset( $data['advance_setting']->set_current_date ) ) {
			$form_data['custom_attributes']['data-default-date'] = $data['advance_setting']->set_current_date;
		}

		if ( isset( $data['advance_setting']->enable_date_range ) ) {
			$form_data['custom_attributes']['data-mode'] = $data['advance_setting']->enable_date_range;
		}

		if ( isset( $data['advance_setting']->date_localization ) ) {
			wp_register_script( 'flatpickr-localization_' . $data['advance_setting']->date_localization, 'https://npmcdn.com/flatpickr/dist/l10n/' . $data['advance_setting']->date_localization . '.js', array(), '4.6.13' );
			wp_enqueue_script( 'flatpickr-localization_' . $data['advance_setting']->date_localization );
			$form_data['custom_attributes']['data-locale'] = $data['advance_setting']->date_localization;
		}

		$form_data['custom_attributes']['data-label'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_label', $data['general_setting']->label );

		if ( isset( $form_data['label'] ) ) {
			$form_data['label'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_label', $form_data['label'] );
		}
		if ( isset( $form_data['placeholder'] ) ) {
			$form_data['placeholder'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_placeholder', $form_data['placeholder'] );
		}
		if ( isset( $form_data['description'] ) ) {
			$form_data['description'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_description', $form_data['description'] );
		}

		// Filter only selected countries for `Country` fields.
		if ( 'country' === $field_key || 'billing_country' === $field_key || 'shipping_country' === $field_key ) {
			$form_data['options'] = UR_Form_Field_Country::get_instance()->get_country();
			$filtered_options     = array();
			$selected_countries   = $data['advance_setting']->selected_countries;

			if ( is_array( $selected_countries ) ) {
				foreach ( $form_data['options'] as $iso => $country_name ) {
					if ( in_array( $iso, $selected_countries, true ) ) {
						$filtered_options[ $iso ] = $country_name;
					}
				}

				$form_data['options'] = $filtered_options;
			}
		}

		/**  Redundant codes. */
		if ( 'select' === $field_key || 'select2' === $field_key || 'multi_select2' === $field_key ) {
			$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $option_data;
			$options     = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
				}

				$form_data['options'] = $options;
			}

			if ( 'multi_select2' === $field_key ) {
				$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
				$form_data['select_all']   = isset( $data['advance_setting']->select_all ) ? $data['advance_setting']->select_all : '';
			}
		}

		if ( 'radio' === $field_key ) {
			$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $option_data;

			$options = array();
			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
				}

				$form_data['options'] = $options;
			}
		}

		if ( 'checkbox' === $field_key ) {
			$form_data['select_all'] = isset( $data['advance_setting']->select_all ) ? $data['advance_setting']->select_all : '';
			$choices                 = isset( $data['advance_setting']->choices ) ? explode( ',', $data['advance_setting']->choices ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data             = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $choices;
			$options                 = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
				}

				$form_data['options'] = $options;
			}

			$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
		}

		if ( 'multiple_choice' === $field_key ) {
			$form_data['select_all'] = isset( $data['advance_setting']->select_all ) ? $data['advance_setting']->select_all : '';
			$choices                 = isset( $data['advance_setting']->choices ) ? explode( ',', $data['advance_setting']->choices ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data             = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $choices;
			$options                 = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option->label ] = array(
						'label' => $option->label,
						'value' => $option->value,
					);
				}

				$form_data['options'] = $options;
			}

			$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
		}

		if ( 'user_login' === $field_key ) {
			$form_data['username_length'] = isset( $data['advance_setting']->username_length ) ? $data['advance_setting']->username_length : '';

			$form_data['username_character'] = isset( $data['advance_setting']->username_character ) ? $data['advance_setting']->username_character : '';
		}

		if ( 'range' === $field_key ) {
			$form_data['range_min']             = ( isset( $data['advance_setting']->range_min ) && '' !== $data['advance_setting']->range_min ) ? $data['advance_setting']->range_min : '0';
			$form_data['range_max']             = ( isset( $data['advance_setting']->range_max ) && '' !== $data['advance_setting']->range_max ) ? $data['advance_setting']->range_max : '10';
			$form_data['range_step']            = isset( $data['advance_setting']->range_step ) ? $data['advance_setting']->range_step : '';
			$enable_prefix_postfix              = isset( $data['advance_setting']->enable_prefix_postfix ) ? $data['advance_setting']->enable_prefix_postfix : 'false';
			$enable_text_prefix_postfix         = isset( $data['advance_setting']->enable_text_prefix_postfix ) ? $data['advance_setting']->enable_text_prefix_postfix : 'false';
			$form_data['enable_payment_slider'] = isset( $data['advance_setting']->enable_payment_slider ) ? $data['advance_setting']->enable_payment_slider : 'false';

			if ( 'true' === $enable_prefix_postfix ) {

				if ( 'true' === $enable_text_prefix_postfix ) {
					$form_data['range_prefix']  = isset( $data['advance_setting']->range_prefix ) ? $data['advance_setting']->range_prefix : '';
					$form_data['range_postfix'] = isset( $data['advance_setting']->range_postfix ) ? $data['advance_setting']->range_postfix : '';
				} else {

					$form_data['range_prefix']  = $form_data['range_min'];
					$form_data['range_postfix'] = $form_data['range_max'];
				}
			}
		}

		if ( 'timepicker' == $field_key ) {
			$form_data['current_time']  = isset( $data['advance_setting']->current_time ) ? $data['advance_setting']->current_time : '';
			$form_data['time_interval'] = isset( $data['advance_setting']->time_interval ) ? $data['advance_setting']->time_interval : '';
			$form_data['time_min']      = ( isset( $data['advance_setting']->time_min ) && '' !== $data['advance_setting']->time_min ) ? $data['advance_setting']->time_min : '';
			$form_data['time_max']      = ( isset( $data['advance_setting']->time_max ) && '' !== $data['advance_setting']->time_max ) ? $data['advance_setting']->time_max : '';
			$timemin                    = isset( $form_data['time_min'] ) ? strtolower( substr( $form_data['time_min'], -2 ) ) : '';
			$timemax                    = isset( $form_data['time_max'] ) ? strtolower( substr( $form_data['time_max'], -2 ) ) : '';
			$minampm                    = intval( $form_data['time_min'] ) <= 12 ? 'AM' : 'PM';
			$maxampm                    = intval( $form_data['time_max'] ) <= 12 ? 'AM' : 'PM';

			// Handles the time format.
			if ( 'am' === $timemin || 'pm' === $timemin ) {
				$form_data['time_min'] = $form_data['time_min'];
			} else {
				$form_data['time_min'] = $form_data['time_min'] . '' . $minampm;
			}

			if ( 'am' === $timemax || 'pm' === $timemax ) {
				$form_data['time_max'] = $form_data['time_max'];
			} else {
				$form_data['time_max'] = $form_data['time_max'] . '' . $maxampm;
			}
		}

		/** Redundant Codes End. */

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
	 */
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
				include_once $file_path;
				$instance = new $class_name();
				return $instance->output( $this->admin_data );
			}
		} else {

			$instance = new $class_name();
			return $instance->output( $this->admin_data );
		}

		return '';
	}

	/**
	 * Get field general settings.
	 *
	 * @return string
	 */
	public function get_field_general_settings() {

		$general_settings     = ur_get_general_settings( $this->id );
		$general_setting_html = '';

		foreach ( $general_settings as $setting_key => $setting_value ) {
			$tooltip_html             = ! empty( $setting_value['tip'] ) ? ur_help_tip( $setting_value['tip'], false, 'ur-portal-tooltip' ) : '';
			$setting_id               = isset( $setting_value['setting_id'] ) ? $setting_value['setting_id'] : str_replace( ' ', '-', strtolower( $setting_value['label'] ) );
			$general_setting_wrapper  = '<div class="ur-general-setting ur-setting-' . $setting_value['type'] . ' ur-general-setting-' . $setting_id . '">';
			$general_setting_wrapper .= '<label for="ur-type-' . $setting_value['type'] . '">' . $setting_value['label'] . $tooltip_html . '</label>';
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
					$disabled = '';
					// To make invite code field name non editable.
					if ( 'learndash_course' === $value || 'invite_code' === $value || 'profile_pic_url' === $value ) {
						$disabled = 'disabled';
					}
					$general_setting_wrapper .= $extra_attribute . ' ' . $disabled . '/>';
					break;

				case 'radio':
					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7.
					$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
					$old_options     = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $default_options;
					$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
					$options         = array_map( 'trim', $options );

					$default_value = $this->get_general_setting_data( 'default_value' );
					$default_value = ! empty( $default_value ) ? $default_value : '';

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique                   = uniqid();

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
					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7.
					$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
					$old_options     = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : $default_options;
					$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;

					if ( 'checkbox' === $strip_prefix ) {
						$options = array_map( 'trim', $options );
					}

					$default_values = $this->get_general_setting_data( 'default_value' );
					$default_values = ! empty( $default_values ) ? $default_values : array();
					$default_values = array_map( 'trim', $default_values );

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique                   = uniqid();

					if ( 'multiple_choice' === $strip_prefix ) {

						foreach ( $options as $key => $option ) {
							$label = is_array( $option ) ? $option['label'] : $option->label;
							$value = is_array( $option ) ? $option['value'] : $option->value;
							$currency   = get_option( 'user_registration_payment_currency', 'USD' );
							$currencies = ur_payment_integration_get_currencies();
							$currency = $currency . ' ' . $currencies[ $currency ]['symbol'];
							$general_setting_wrapper .= '<li>';
							$general_setting_wrapper .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . $label . '" data-field="default_value" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-value" type="checkbox" name="' . $unique . '_value" ';
							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							if ( in_array( $label, $default_values ) ) {
								$general_setting_wrapper .= 'checked ="checked" />';
							} else {
								$general_setting_wrapper .= '/>';
							}
							$general_setting_wrapper .= '<input value="' . $label . '" data-field="' . $setting_key . '" data-field-name="' . $strip_prefix . '" class="ur-general-setting-field  ur-type-' . $setting_value['type'] . '-label" type="text" name="' . $setting_value['name'] . '_label" >';
							$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" data-field-name="' . $strip_prefix . '" class="ur-general-setting-field  ur-type-' . $setting_value['type'] . '-money-input" type="text" name="' . $setting_value['name'] . '_value" data-currency=" ' . $currency . ' " >';
							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a><br/>';
							$general_setting_wrapper .= '</li>';
						}
					} else {

						foreach ( $options as  $option ) {

							$general_setting_wrapper .= '<li>';
							$general_setting_wrapper .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
						<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
						</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-value" type="checkbox" name="' . $unique . '_value" ';

							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							if ( in_array( $option, $default_values ) ) {
								$general_setting_wrapper .= 'checked ="checked" />';
							} else {
								$general_setting_wrapper .= '/>';
							}

							$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '-label" type="text" name="' . $setting_value['name'] . '_label" >';

							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a><br/>';
							$general_setting_wrapper .= '</li>';

						}
					}
						$general_setting_wrapper .= '</ul>';
					break;

				case 'select':
					if ( isset( $setting_value['options'] )
						&& gettype( $setting_value['options'] ) == 'array' ) {

						$disabled = '';
							// To make invite code required field non editable.
						if ( 'required' === $setting_key && 'invite_code' === $strip_prefix ) {
							$disabled = 'disabled';
						}

						$general_setting_wrapper .= '<select data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . $setting_value['name'] . '" ' . $disabled . '>';

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

					$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="hidden" name="' . $setting_value['name'] . '"  placeholder="' . $setting_value['placeholder'] . '"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}

					$general_setting_wrapper .= '/>';

					break;
				case 'number':
					$val                      = $this->get_general_setting_data( $setting_key );
					$value                    = ! empty( $val ) ? $val : $setting_value['default'];
					$general_setting_wrapper .= '<input value="' . $value . '" data-field="' . $setting_key . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '" type="number" name="' . $setting_value['name'] . '" min = "1"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}

					$general_setting_wrapper .= '/>';
					break;

				default:
					$general_setting_wrapper .= apply_filters( 'user_registration_form_field_general_setting_' . $setting_value['type'], $this );
			}// End switch().

			$general_setting_wrapper .= '</div>';
			$general_setting_html    .= $general_setting_wrapper;

		}// End foreach().

		return $general_setting_html;
	}

	/**
	 * Display Setting for each fields in options tab
	 *
	 * @return string $settings
	 */
	public function get_setting() {

		$strip_prefix = substr( $this->id, 18 );
		$class        = 'ur-general-setting-' . $strip_prefix;

		$settings  = "<div class='ur-general-setting-block " . esc_attr( $class ) . "'>";
		$settings .= '<h2 class="ur-toggle-heading">' . esc_html__( 'General Settings', 'user-registration' ) . '</h2><hr>';
		$settings .= '<div class="ur-toggle-content">';
		$settings .= $this->get_field_general_settings();
		$settings .= '</div>';
		$settings .= '</div>';

		$advance_settings = $this->get_field_advance_settings();

		if ( ! empty( $advance_settings ) ) {
			$settings .= "<div class='ur-advance-setting-block'>";
			$settings .= '<h2 class="ur-toggle-heading">' . __( 'Advance Settings', 'user-registration' ) . '</h2><hr>';
			$settings .= '<div class="ur-toggle-content">';
			$settings .= $advance_settings;
			$settings .= '</div>';
			$settings .= '</div>';
		}

		// Redundent code start.
		ob_start();
		do_action( 'user_registration_after_advance_settings', $this->id, $this->admin_data );
		$settings .= ob_get_clean();
		// Redundent code end.
		$settings = apply_filters( 'user_registration_after_advance_settings_filter', $settings, $this->id, $this->admin_data );
		return $settings;
	}

	/**
	 * Validation for form field.
	 *
	 * @param object $single_form_field The field being validate.
	 * @param object $form_data Form Data.
	 * @param string $filter_hook Filter for validation messages.
	 * @param int    $form_id Form ID.
	 */
	abstract public function validation( $single_form_field, $form_data, $filter_hook, $form_id );
}
