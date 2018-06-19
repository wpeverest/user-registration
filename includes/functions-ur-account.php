<?php
/**
 * UserRegistration Account Functions
 *
 * Functions for account specific things.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'login_errors', 'login_error_message' );

//Modify error message on invalid username or password
function login_error_message( $error ) {

	// Don't change login error messages on admin site.
	if ( isset( $_POST['redirect_to'] ) && false !== strpos( $_POST['redirect_to'], network_admin_url() ) ) {
		return $error;
	}

    //check if that's the error you are looking for
    $pos = strpos( $error, 'incorrect' );

	if ( is_int( $pos ) ) {
        //its the correct username with incorrect password
        $error = sprintf( __( 'The password you entered for username %1s is incorrect. %2s' , 'user-registraion' ),  $_POST['username'], "<a href='". $_POST['redirect'] . get_option( 'user_registration_myaccount_lost_password_endpoint', 'lost-password' ) ."'>".__('Lost Your Password?','user-registration').'</a>' );
    } 
    return $error;
}

/**
 * Returns the url to the lost password endpoint url.
 *
 * @param  string $default_url
 *
 * @return string
 */
function ur_lostpassword_url( $default_url = '' ) {
	
	// Don't redirect to the user registration endpoint on global network admin lost passwords.
	if ( is_multisite() && isset( $_GET['redirect_to'] ) && false !== strpos( $_GET['redirect_to'], network_admin_url() ) ) {
		return $default_url;
	}

	$ur_account_page_url    = ur_get_page_permalink( 'myaccount' );
	$ur_account_page_exists = ur_get_page_id( 'myaccount' ) > 0;
	$lost_password_endpoint = get_option( 'user_registration_myaccount_lost_password_endpoint', 'lost-password' );

	if ( $ur_account_page_exists && ! empty( $lost_password_endpoint ) ) {
		return ur_get_endpoint_url( $lost_password_endpoint, '', $ur_account_page_url );
	} else {
		return $default_url;
	}
}

add_filter( 'lostpassword_url', 'ur_lostpassword_url', 20, 1 );

/**
 * Get My Account menu items.
 *
 * @return array
 */
function ur_get_account_menu_items() {
	$endpoints = array(
		'edit-profile' => get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' ),
		'edit-account' => get_option( 'user_registration_myaccount_edit_account_endpoint', 'edit-account' ),
		'user-logout'  => get_option( 'user_registration_logout_endpoint', 'user-logout' ),
	);

	$items = array(
		'dashboard'    => __( 'Dashboard', 'user-registration' ),
		'edit-profile' => __( 'Profile Details', 'user-registration' ),
		'edit-account' => __( 'Account details', 'user-registration' ),
		'user-logout'  => __( 'Logout', 'user-registration' ),
	);

	$user_id = get_current_user_id();
	$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
	$form_id = 0;

	if ( isset( $form_id_array[0] ) ) {
		$form_id = $form_id_array[0];
	}

	$profile = user_registration_form_data( $user_id, $form_id );
	
	if ( count( $profile ) < 1 ) {
		unset($items['edit-profile']);
	}

	// Remove missing endpoints.
	foreach ( $endpoints as $endpoint_id => $endpoint ) {
		if ( empty( $endpoint ) ) {
			unset( $items[ $endpoint_id ] );
		}
	}

	return apply_filters( 'user_registration_account_menu_items', $items );
}

/**
 * Get account menu item classes.
 *
 * @param  string $endpoint
 *
 * @return string
 */
function ur_get_account_menu_item_classes( $endpoint ) {
	global $wp;

	$classes = array(
		'user-registration-MyAccount-navigation-link',
		'user-registration-MyAccount-navigation-link--' . $endpoint,
	);

	// Set current item class.
	$current = isset( $wp->query_vars[ $endpoint ] );
	if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
		$current = true; // Dashboard is not an endpoint, so needs a custom check.
	}

	if ( $current ) {
		$classes[] = 'is-active';
	}

	$classes = apply_filters( 'user_registration_account_menu_item_classes', $classes, $endpoint );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Get account endpoint URL.
 *
 * @since 2.6.0
 *
 * @param string $endpoint
 *
 * @return string
 */
function ur_get_account_endpoint_url( $endpoint ) {
	if ( 'dashboard' === $endpoint ) {
		return ur_get_page_permalink( 'myaccount' );
	}

	return ur_get_endpoint_url( $endpoint, '', ur_get_page_permalink( 'myaccount' ) );
}
