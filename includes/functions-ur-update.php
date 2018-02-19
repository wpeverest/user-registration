<?php
/**
 * UserRegistration Updates
 *
 * Function for updating data, used by the background updater.
 *
 * @package UserRegistration\Functions
 * @version 1.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update DB Version.
 */
function ur_update_100_db_version() {
	UR_Install::update_db_version( '1.0.0' );
}

/**
 * Update usermeta.
 */
function ur_update_120_usermeta() {
	global $wpdb;

	// Get usermeta.
	$usermeta = $wpdb->get_results( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE '%ur_%'" );

	// Delete old user keys from usermeta.
	foreach ( $usermeta as $metadata ) {
		$user_id = intval( $metadata->user_id );
		$exp_key = explode( '_', $metadata->meta_key );

		if ( 'ur' === current( $exp_key ) && 'params' === end( $exp_key ) ) {
			delete_user_meta( $user_id, $metadata->meta_key );
		}
	}
}

/**
 * Update meta values.
 */
function ur_update_120_meta_values() {
	global $wpdb;

	// Get usermeta.
	$usermeta = $wpdb->get_results( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE '%user_registration_%'" );

	// Delete old user keys from usermeta.
	foreach ( $usermeta as $metadata ) {
		$user_id = intval( $metadata->user_id );
		$exp_key = explode( '__', $metadata->meta_value );

		// Check and make sure the stored value matches new value.
		if ( get_user_meta( $user_id, $metadata->meta_key, true ) !== end( $exp_key ) ) {
			update_user_meta( $user_id, $metadata->meta_key, end( $exp_key ) );
		}
	}
}

/**
 * Update DB Version.
 */
function ur_update_120_db_version() {
	UR_Install::update_db_version( '1.2.0' );
}
