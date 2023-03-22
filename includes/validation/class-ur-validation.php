<?php
/**
 * User Registration Validation.
 *
 * @class    UR_Validation
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'UR_Validation' ) ) {
	return;
}

/**
 * UR_Validation Class
 */
class UR_Validation {

	/**
	 * Validates if a required field is empty.
	 *
	 * @param [string] $value Value.
	 * @return boolean or WP_Error.
	 */
	public static function required( $value ) {
		if ( empty( $value ) ) {
			return new WP_Error(
				'user_registration_validation_empty_field',
				__( 'Please enter a valid value', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validates if a field is numeric.
	 *
	 * @param [mixed] $value Value to check.
	 * @return boolean or WP_Error.
	 */
	public static function is_numeric( $value ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error(
				'user_registration_validation_non_numeric_data',
				__( 'Please enter a numeric value', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validates if a string is a valid email.
	 *
	 * @param [string] $value Value.
	 * @return boolean or WP_Error.
	 */
	public static function is_email( $value ) {
		if ( false === is_email( $value ) ) {
			return new WP_Error(
				'user_registration_validation_invalid_email',
				__( 'Please input a valid email', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validate url field.
	 *
	 * @param [string] $url Url.
	 * @return boolean or WP_Error.
	 */
	public static function is_url( $url ) {

		$url_pattern = "/^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}(\\.[a-zA-Z0-9()]{1,6})?\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/";

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) || ! preg_match( $url_pattern, $url ) ) {
			return new WP_Error(
				'user_registration_validation_invalid_url',
				__( 'Please input a valid url', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validates if the value is a valid date.
	 *
	 * @param [string] $date Date String.
	 * @return boolean or WP_Error.
	 */
	public static function is_date( $date ) {
		if ( ! empty( $date ) && false === strtotime( $date ) ) {
			return new WP_Error(
				'user_registration_validation_invalid_date',
				__( 'Please input a valid date', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validates if the value is boolean.
	 *
	 * @param [mixed] $value Value to check.
	 * @return boolean or WP_Error.
	 */
	public static function is_boolean( $value ) {
		$boolean_values = array( true, false, null, 0, 1, '0', '1', 'yes', 'Yes', 'YES', 'no', 'No', 'NO', 'true', 'True', 'TRUE', 'false', 'False', 'FALSE' );

		if ( ! in_array( $value, $boolean_values, true ) ) {
			return new WP_Error(
				'user_registration_validation_non_boolean_value',
				__( 'Please input a valid value', 'user-registration' )
			);
		}
		return true;
	}

	/**
	 * Validate if a string is longer than max length.
	 *
	 * @param [mixed] $value Value to validate.
	 * @param [int]   $size Max Size.
	 * @return boolean or WP_Error.
	 */
	public static function validate_length( $value, $size ) {
		if ( strlen( $value ) > $size ) {
			return new WP_Error(
				'user_registration_validation_max_size_exceeded',
				'Please enter value of length less than ' . $size
			);
		}
		return true;
	}

	/**
	 * Validates if a value is an integer.
	 *
	 * @param [mixed] $value Value.
	 * @return boolean or WP_Error
	 */
	public static function is_integer( $value ) {
		if ( intval( $value ) != floatval( $value ) ) { //phpcs:ignore
			return new WP_Error(
				'user_registration_validation_non_integer',
				'Please enter an integer value'
			);
		}
		return true;
	}

	/**
	 * Validates if a value is not negative.
	 *
	 * @param [mixed] $value Value.
	 * @return boolean or WP_Error.
	 */
	public static function is_non_negative( $value ) {
		if ( ! self::is_numeric( $value ) || intval( $value ) < 0 ) {
			return new WP_Error(
				'user_registration_validation_negative_value',
				'Please enter a non negative value'
			);
		}
		return true;
	}
}

new UR_Validation();
