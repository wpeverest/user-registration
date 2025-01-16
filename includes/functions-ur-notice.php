<?php
/**
 * UserRegistration Message Functions
 *
 * Functions for error/message handling and display.
 *
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the count of notices added, either for all notices (default) or for one.
 * particular notice type specified by $notice_type.
 *
 * @since 1.0
 *
 * @param string $notice_type The name of the notice type - either error, success or notice. [optional].
 *
 * @return int
 */
function ur_notice_count( $notice_type = '' ) {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}

	$notice_count = 0;
	$all_notices  = UR()->session->get( 'ur_notices', array() );

	if ( isset( $all_notices[ $notice_type ] ) ) {
		$notice_count = absint( count( $all_notices[ $notice_type ] ) );
	} elseif ( empty( $notice_type ) ) {

		foreach ( $all_notices as $notices ) {
			$notice_count += absint( count( $all_notices ) );
		}
	}

	return $notice_count;
}

/**
 * Check if a notice has already been added.
 *
 * @since 1.0
 *
 * @param string $message     The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional].
 *
 * @return bool
 */
function ur_has_notice( $message, $notice_type = 'success' ) {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );
		return;
	}

	$notices = UR()->session->get( 'ur_notices', array() );
	$notices = isset( $notices[ $notice_type ] ) ? $notices[ $notice_type ] : array();

	return array_search( $message, $notices ) !== false;
}

/**
 * Add and store a notice.
 *
 * @since 1.0
 *
 * @param string $message     The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional].
 */
function ur_add_notice( $message, $notice_type = 'success' ) {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}

	$notices = UR()->session->get( 'ur_notices', array() );

	// Backward compatibility.
	if ( 'success' === $notice_type ) {
		/**
		 * Filters the message added.
		 *
		 * The 'user_registration_add_message' filter allows developers to modify
		 * the message added. It provides an opportunity
		 * to customize the message based on the original message.
		 *
		 * @param string $message The original message added in User Registration.
		 */
		$message = apply_filters( 'user_registration_add_message', $message );
	}
	/**
	 * Filters the message added based on notice type.
	 *
	 * The dynamic 'user_registration_add_{notice_type}' filter allows developers to modify
	 * the message added in based on a specific notice type.
	 * The {notice_type} placeholder is replaced with the actual notice type, providing a
	 * flexible way to customize the message based on the original message and notice type.
	 *
	 * @param string $message     The original message added in User Registration.
	 * @param string $notice_type The specific notice type.
	 */
	$notices[ $notice_type ][] = apply_filters( 'user_registration_add_' . $notice_type, $message );

	UR()->session->set( 'ur_notices', $notices );
}

/**
 * Set all notices at once.
 *
 * @since 1.0.0
 *
 * @param mixed $notices Notices.
 */
function ur_set_notices( $notices ) {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}
	UR()->session->set( 'ur_notices', $notices );
}

/**
 * Unset all notices.
 *
 * @since 1.0
 */
function ur_clear_notices() {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}
	UR()->session->set( 'ur_notices', null );
}

/**
 * Prints messages and errors which are stored in the session, then clears them.
 *
 * @since 1.0
 */
function ur_print_notices() {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}

	$all_notices = UR()->session->get( 'ur_notices', array() );
	/**
	 * Filters the available types for messages.
	 *
	 * The 'user_registration_types' filter allows developers to modify the array of
	 * available types for messages. It provides an
	 * opportunity to customize the types based on the original array.
	 *
	 * @param array $types The original array of available message types.
	 */
	$notice_types = apply_filters( 'user_registration_types', array( 'notice', 'error', 'success' ) );
	foreach ( $notice_types as $notice_type ) {
		if ( ur_notice_count( $notice_type ) > 0 ) {

			ur_get_template(
				"notices/{$notice_type}.php",
				array(
					'messages' => array_filter( $all_notices[ $notice_type ] ),
				)
			);
		}
	}

	ur_clear_notices();
}

/**
 * Print a single notice immediately.
 *
 * @since 2.1
 *
 * @param string $message     The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional].
 */
function ur_print_notice( $message, $notice_type = 'success' ) {
	if ( 'success' === $notice_type ) {
		$message = apply_filters( 'user_registration_add_message', $message );
	}

	ur_get_template(
		"notices/{$notice_type}.php",
		array(
			/**
			 * Filters the message added based on notice type.
			 *
			 * The dynamic 'user_registration_add_{notice_type}' filter allows developers to modify
			 * the message added in based on a specific notice type.
			 * The {notice_type} placeholder is replaced with the actual notice type, providing a
			 * flexible way to customize the message based on the original message and notice type.
			 *
			 * @param string $notice_type The specific notice type.
			 * @param string $message     The original message added in User Registration.
			 */
			'messages' => array( apply_filters( 'user_registration_add_' . $notice_type, $message ) ),
		)
	);
}

/**
 * Returns all queued notices, optionally filtered by a notice type.
 *
 * @since 2.1
 *
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional].
 *
 * @return array|mixed
 */
function ur_get_notices( $notice_type = '' ) {
	if ( ! did_action( 'user_registration_init' ) ) {
		ur_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before user_registration_init.', 'user-registration' ), '1.0' );

		return;
	}

	$all_notices = UR()->session->get( 'ur_notices', array() );

	if ( empty( $notice_type ) ) {
		$notices = $all_notices;
	} elseif ( isset( $all_notices[ $notice_type ] ) ) {
		$notices = $all_notices[ $notice_type ];
	} else {
		$notices = array();
	}

	return $notices;
}

/**
 * Add notices for WP Errors.
 *
 * @param WP_Error $errors Errors.
 */
function ur_add_wp_error_notices( $errors ) {
	if ( is_wp_error( $errors ) && $errors->get_error_messages() ) {
		foreach ( $errors->get_error_messages() as $error ) {
			ur_add_notice( $error, 'error' );
		}
	}
}
