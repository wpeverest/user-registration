<?php
/**
 * UserRegistration Uninstall
 *
 * Uninstalls the plugin and associated data.
 *
 * @package  UserRegistration/Uninstaller
 * @version  1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;
wp_clear_scheduled_hook( 'user_registration_cleanup_logs' );
wp_clear_scheduled_hook( 'user_registration_cleanup_sessions' );
/*
 * Only remove ALL product and page data if UR_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */

$uninstall_option = get_option( 'user_registration_general_setting_uninstall_option', false );

if ( defined( 'UR_REMOVE_ALL_DATA' ) && true === UR_REMOVE_ALL_DATA || 'yes' == $uninstall_option || true == $uninstall_option ) {
	include_once __DIR__ . '/includes/class-ur-install.php';

	// Roles + caps.
	UR_Install::remove_roles();

	// Pages.
	$page_option_keys = array(
		'user_registration_registration_page_id',
		'user_registration_login_page_id',
		'user_registration_myaccount_page_id',
		'user_registration_lost_password_page_id',
		'user_registration_membership_registration_page_id',
		'user_registration_membership_pricing_page_id',
		'user_registration_membership_thankyou_page_id',
		'user_registration_member_registration_page_id',
		'user_registration_thank_you_page_id',
		'user_registration_default_form_page_id',
		'user_registration_registration_form',
	);

	$page_ids = array();

	foreach ( $page_option_keys as $opt_key ) {
		$val = get_option( $opt_key );
		if ( is_numeric( $val ) && (int) $val > 0 ) {
			$page_ids[] = (int) $val;
		}
	}

	$page_ids = array_values( array_unique( $page_ids ) );

	foreach ( $page_ids as $page_id ) {
		if ( 'page' === get_post_type( $page_id ) ) {
			wp_trash_post( $page_id );
		}
	}



	// Tables.
	UR_Install::drop_tables();

	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'user_registration\_%';" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'ur\_%';" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'urmc\_%';" );

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%';" );

	// Delete form id and confirm key.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key IN ( 'ur_form_id', 'ur_confirm_email', 'ur_confirm_email_token' ) " );

	$args      = array(
		'order'         => 'ASC',
		'numberposts'   => -1,
		'status'        => 'publish',
		'post_type'     => array( 'user_registration', 'ur_membership', 'ur_membership_groups' ),
		'orderby'       => 'ID',
		'order'         => 'DESC',
		'no_found_rows' => true,
		'nopaging'      => true,
	);
	$all_forms = get_posts( $args );

	foreach ( $all_forms as $form ) {
		$result   = wp_delete_post( $form->ID );
		$del_meta = $wpdb->delete( $wpdb->postmeta, array( 'post_id' => $form->ID ) );
	}

	// Clear any cached data that has been removed.
	wp_cache_flush();
}
