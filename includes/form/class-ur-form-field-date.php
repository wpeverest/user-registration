<?php
/**
 * UR_Form_Field_Date.
 *
 * @class    UR_Form_Field_Date
 * @since    1.0.5
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Date Class
 */
class UR_Form_Field_Date extends UR_Form_Field {

	/**
	 * Instance Variable.
	 *
	 * @var [mixed]
	 */
	private static $_instance;

	/**
	 * Get Instance of class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id                       = 'user_registration_date';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Date', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-calendar',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Date', 'user-registration' ),
			'default_field_name' => 'date_box_' . ur_get_random_number(),
		);
	}

	/**
	 * Get Registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . esc_attr( $this->id ) . '_list " class="ur-registered-item draggable" data-field-id="' . esc_attr( $this->id ) . '"><span class="' . esc_attr( $this->registered_fields_config['icon'] ) . '"></span>' . esc_html( $this->registered_fields_config['label'] ) . '</li>';
	}

	/**
	 * Validate field.
	 *
	 * @param [object] $single_form_field Field Data.
	 * @param [object] $form_data Form Data.
	 * @param [string] $filter_hook Hook.
	 * @param [int]    $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		$value       = $form_data->value;
		$field_label = $single_form_field->general_setting->field_name;

		if ( empty( $value ) ) {
			return;
		}

		$is_enable_date_range = isset( $single_form_field->advance_setting->enable_date_range ) ? ur_string_to_bool( $single_form_field->advance_setting->enable_date_range ) : '';
		$enabled_min_max      = isset( $single_form_field->advance_setting->enable_min_max ) ? ur_string_to_bool( $single_form_field->advance_setting->enable_min_max ) : false;

		if ( $enabled_min_max ) {
			$min_date = isset( $single_form_field->advance_setting->min_date ) ? $single_form_field->advance_setting->min_date : '';
			$max_date = isset( $single_form_field->advance_setting->max_date ) ? $single_form_field->advance_setting->max_date : '';
		}

		if ( $is_enable_date_range ) {
			$dates = array();
			preg_match( '/(\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4}|\d{2}\/\d{2}\/\d{4}|[A-Za-z]+\s\d{1,2},\s\d{4})\s*[\w\s]+\s*(\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4}|\d{2}\/\d{2}\/\d{4}|[A-Za-z]+\s\d{1,2},\s\d{4})/', $value, $matches );

			if ( count( $matches ) == 3 ) {
				$dates = array( $matches[1], $matches[2] );
			}

			foreach ( $dates as $date ) {
				$result = UR_Validation::is_date( trim( $date ) );

				if ( is_wp_error( $result ) ) {
					$message = array(
						/* translators: %s - validation message */
						$field_label => sprintf( __( 'Please select a valid date range.', 'user-registration' ) ),
						'individual' => true,
					);
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $message, $form_data ) {
							$message = apply_filters( 'user_registration_modify_field_validation_response', $message, $form_data );
							return $message;
						}
					);
				}

				if ( $enabled_min_max ) {
					if ( ! empty( $min_date ) ) {
						$this->validate_min_date( $date, $min_date, $filter_hook, $field_label, $form_data );
					}

					if ( ! empty( $max_date ) ) {
						$this->validate_max_date( $date, $max_date, $filter_hook, $field_label, $form_data );
					}
				}
			}
			return;
		}

		// Check if the entered date is a valid date string.
		$result = UR_Validation::is_date( trim( $value ) );

		if ( is_wp_error( $result ) ) {
			$message = array(
				/* translators: %s - validation message */
				$field_label => sprintf( __( 'Please select a valid date.', 'user-registration' ) ),
				'individual' => true,
			);
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $message, $form_data ) {
					$message = apply_filters( 'user_registration_modify_field_validation_response', $message, $form_data );
					return $message;
				}
			);
		}

		// Handle Min-Max Date.

		if ( $enabled_min_max ) {
			if ( ! empty( $min_date ) ) {
				$this->validate_min_date( $value, $min_date, $filter_hook, $field_label, $form_data );
			}

			if ( ! empty( $max_date ) ) {
				$this->validate_max_date( $value, $max_date, $filter_hook, $field_label, $form_data );
			}
		}
	}


	/**
	 * Validate whether date is ahead of the min date.
	 *
	 * @param [string] $date Date.
	 * @param [string] $min_date Min Date.
	 * @param [string] $filter_hook Filter Hook.
	 * @param [string] $field_label Field Label.
	 * @param [object] $form_data Form Data.
	 * @return void
	 */
	private function validate_min_date( $date, $min_date, $filter_hook, $field_label, $form_data ) {
		$date_timestamp     = strtotime( str_replace( '/', '-', $date ) );
		$min_date_timestamp = strtotime( $min_date );

		if ( $date_timestamp < $min_date_timestamp ) {
			$message = array(
				/* translators: %s - validation message */
				$field_label => sprintf( __( 'Please select a date after %s.', 'user-registration' ), $min_date ),
				'individual' => true,
			);
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $message, $form_data ) {
					$message = apply_filters( 'user_registration_modify_field_validation_response', $message, $form_data );
					return $message;
				}
			);
		}
	}

	/**
	 * Validate whether date is past the max date.
	 *
	 * @param [string]           $date Date.
	 * @param [string]           $max_date Max Date.
	 * @param [string]           $filter_hook Filter Hook.
	 * @param [string]           $field_label Field Label.
	 * @param [object] Form Data.
	 * @return void
	 */
	private function validate_max_date( $date, $max_date, $filter_hook, $field_label, $form_data ) {
		$date_timestamp     = strtotime( str_replace( '/', '-', $date ) );
		$max_date_timestamp = strtotime( $max_date );

		if ( $date_timestamp > $max_date_timestamp ) {
			$message = array(
				/* translators: %s - validation message */
				$field_label => sprintf( __( 'Please select a date before %s', 'user-registration' ), $max_date ),
				'individual' => true,
			);
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $message, $form_data ) {
					$message = apply_filters( 'user_registration_modify_field_validation_response', $message, $form_data );
					return $message;
				}
			);
		}
	}


	/**
	 * Checks for valid date
	 *
	 * @param string $date_string Date.
	 */
	private function is_valid_date( $date_string ) {

		$date = date_parse( $date_string );

		if ( 0 == $date['error_count'] && checkdate( $date['month'], $date['day'], $date['year'] ) ) {
			return true;
		}

		return false;
	}
}

return UR_Form_Field_Date::get_instance();
