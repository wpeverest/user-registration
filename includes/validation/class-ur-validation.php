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
		if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
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
		if ( false === strtotime( $date ) ) {
			return new WP_Error(
				'user_registration_validation_invalid_date',
				__( 'Please input a valid date', 'user-registration' )
			);
		}
		return true;
	}
}

new UR_Validation();
