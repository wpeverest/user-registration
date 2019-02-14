<?php
/**
 * UserRegistration Uninstall
 *
 * Uninstalls the plugin and associated data.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Uninstaller
 * @version  1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

/*
 * Only remove ALL product and page data if UR_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'UR_REMOVE_ALL_DATA' ) && true === UR_REMOVE_ALL_DATA || 'yes' === get_option( 'user_registration_general_setting_uninstall_option' ) ) {
	include_once( dirname( __FILE__ ) . '/includes/class-ur-install.php' );

	// Roles + caps.
	UR_Install::remove_roles();

	// Pages.
	wp_trash_post( get_option( 'user_registration_myaccount_page_id' ) );
	wp_trash_post( get_option( 'user_registration_default_form_page_id' ) );

	// Tables.
	UR_Install::drop_tables();

	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'user_registration\_%';" );

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%';" );

	// Clear any cached data that has been removed.
	wp_cache_flush();
}
