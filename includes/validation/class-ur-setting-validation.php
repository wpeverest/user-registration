<?php
/**
 * User Registration Setting Validation.
 *
 * @class    UR_Setting_Validation
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Setting_Validation Class
 *
 * This class handles validations for all admin settings submission.
 */
class UR_Setting_Validation {

	/**
	 * Default validations array.
	 * Stores validations based on setting type.
	 *
	 * @var [array]
	 */
	private $validations;


	/**
	 * Custom validations array.
	 * Stores validations for unique setting based on setting id.
	 *
	 * @var [array]
	 */
	private $custom_validations;


	/**
	 * Error Messages array.
	 * Stores error messages for different validation errors.
	 *
	 * @var [array]
	 */
	private $error_messages;


	/**
	 * Class Constructor.
	 */
	public function __construct() {
		$this->set_validations();
		$this->set_custom_validations();
		$this->set_error_messages();

		add_filter( 'user_registration_admin_settings_sanitize_option', array( $this, 'validate_setting' ), 10, 3 );
	}


	/**
	 * Validate setting based on setting type.
	 *
	 * @param [mixed] $value Value.
	 * @param [array] $option Option.
	 * @return mixed
	 */
	public function validate_setting( $value, $option ) {
		$setting_label = isset( $option['title'] ) ? $option['title'] : '';
		$setting_type  = isset( $option['type'] ) ? $option['type'] : null;

		if ( ! is_null( $setting_type ) ) {
			$setting_key = $option['id'];
			$value       = $this->sanitize( $value, $setting_type );

			$validations = $this->get_custom_validations( $setting_key );
			if ( ! is_array( $validations ) ) {
				$validations = $this->get_setting_validations( $setting_type );
				$validations = apply_filters( 'user_registration_validate_' . $setting_type, $validations );
			}
			foreach ( $validations as $validation ) {
				if ( method_exists( 'UR_Validation', $validation ) ) {
					$result = UR_Validation::$validation( $value );

					if ( is_wp_error( $result ) ) {
						$error = self::get_error_message( $result->get_error_code(), $setting_label );
						UR_Admin_Settings::add_error( $error );
					}
				}
			}
		}

		return $value;
	}


	/**
	 * Set the default validations for setting based on setting type.
	 *
	 * @return void
	 */
	private function set_validations() {
		$this->validations = apply_filters(
			'user_registration_validation_settings',
			array(
				'number' => array( 'is_numeric', 'is_integer', 'is_non_negative' ),
			)
		);
	}


	/**
	 * Returns default setting validations for setting based on type.
	 *
	 * @param [stirng] $type Setting Type.
	 * @return array
	 */
	public function get_setting_validations( $type ) {
		$setting_validations = $this->validations;

		$validations = isset( $setting_validations[ $type ] ) ? $setting_validations[ $type ] : array();

		return apply_filters( 'user_registration_validation_settings', $validations );
	}


	/**
	 * Here we set custom validation for single settings.
	 *
	 * @return void
	 */
	public function set_custom_validations() {
		$this->custom_validations = array(
			'user_registration_integration_setting_recaptcha_threshold_score_v3' => array( 'is_numeric' ),
		);
	}


	/**
	 * Gets custom validations for a setting.
	 *
	 * @param string $setting_key Setting Key ( ID ).
	 *
	 * @return array or False
	 */
	public function get_custom_validations( $setting_key ) {
		$custom_validations = $this->custom_validations;

		if ( isset( $custom_validations[ $setting_key ] ) ) {
			return $custom_validations[ $setting_key ];
		}
		return false;
	}


	/**
	 * Set Error Messages for validation fails.
	 *
	 * @return void
	 */
	private function set_error_messages() {
		$this->error_messages = apply_filters(
			'user_registration_setting_validation_messages',
			array(
				// phpcs:disable
				'negative_value'   => esc_html__( 'Please enter a value greater than 0 for %s.', 'user-registration' ),
				'non_integer'      => esc_html__( 'Please enter an integer value for %s.', 'user-registration' ),
				'non_numeric_data' => esc_html__( 'Please enter a numeric value for %s.', 'user-registration' ),
				// phpcs:enable
			)
		);
	}


	/**
	 * Returns error message for the error code.
	 *
	 * @param [string] $error_code Error Code.
	 * @param [string] $setting_label Setting Label.
	 *
	 * @return string
	 */
	public function get_error_message( $error_code, $setting_label ) {
		$messages = $this->error_messages;

		$error_code = str_replace( 'user_registration_validation_', '', $error_code );

		$message = isset( $messages[ $error_code ] ) ? $messages[ $error_code ] : '';

		if ( empty( $message ) ) {
			return __( 'The specified setting value cannot be saved.', 'user-registration' );
		} else {
			$message = sprintf( $message, $setting_label );
			return $message;
		}
	}


	/**
	 * Sanitize setting values based on type.
	 *
	 * @param [mixed]  $value Setting Value.
	 * @param [string] $setting_type Setting Type.
	 * @return mixed
	 */
	private function sanitize( $value, $setting_type ) {
		switch ( $setting_type ) {
			case 'checkbox':
				$value = ur_string_to_bool( $value );
				$value = $value ? 1 : 0;
				break;
			case 'toggle':
				$value = ur_string_to_bool( $value );
				$value = $value ? 1 : 0;
				break;
			case 'number':
				$floatval = floatval( $value );
				$value    = ! empty( $floatval ) ? $value : 0;
				break;
		}

		return $value;
	}

}

new UR_Setting_Validation();
