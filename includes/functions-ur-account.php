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

add_filter( 'login_errors', 'ur_login_error_message' );
add_filter( 'get_avatar', 'ur_replace_gravatar_image', 99, 6 );
add_filter( 'ajax_query_attachments_args', 'ur_show_current_user_attachments' );

/**
 * Limit media library access to own uploads.
 *
 * @since 1.5.8
 *
 * @param  array $query
 *
 * @return array
 */
function ur_show_current_user_attachments( $query ) {
	$user_id = get_current_user_id();

	if ( $user_id && ! current_user_can( 'edit_others_posts' ) ) {
		$query['author'] = $user_id;
	}

	return $query;
}

// Modify error message on invalid username or password.
function ur_login_error_message( $error ) {
	// Don't change login error messages on admin site .
	if ( isset( $_POST['redirect_to'] ) && false !== strpos( $_POST['redirect_to'], network_admin_url() ) ) {
		return $error;
	}

	$pos  = strpos( $error, 'incorrect' );     // Check if the error contains incorrect string.
	$pos2 = strpos( $error, 'Invalid' );       // Check if the error contains Invalid string.

	// Its the correct username with incorrect password.
	if ( is_int( $pos ) && isset( $_POST['username'] ) ) {

		$error = sprintf( '<strong>' . ur_string_translation( null, 'ur_error', __( 'ERROR:', 'user-registration' ) ) . '</strong>' . ur_string_translation( null, 'ur_incorrect_password', __( 'The password you entered for username %1$1s is incorrect. %2$2s', 'user-registration' ) ), $_POST['username'], "<a href='" . esc_url( wp_lostpassword_url() ) . "'>" . ur_string_translation( null, 'ur_lost_your_password', __( 'Lost Your Password?', 'user-registration' ) ) . '</a>' );
	} // It's invalid username.
	elseif ( is_int( $pos2 ) && isset( $_POST['username'] ) ) {
		$error = sprintf( '<strong>' . ur_string_translation( null, 'ur_error', __( 'ERROR:', 'user-registration' ) ) . '</strong>' . ur_string_translation( null, 'ur_invalid_username', __( 'Invalid username. %1s', 'user-registration' ) ), "<a href='" . esc_url( wp_lostpassword_url() ) . "'>" . ur_string_translation( null, 'ur_lost_your_password', __( 'Lost Your Password?', 'user-registration' ) ) . '</a>' );
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

	// Don't  change default url if admin side login form.
	if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
		return $default_url;
	}

	$ur_account_page_url = ur_get_page_permalink( 'myaccount' );

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
		'edit-profile'  => get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' ),
		'edit-password' => get_option( 'user_registration_myaccount_change_password_endpoint', 'edit-password' ),
		'user-logout'   => get_option( 'user_registration_logout_endpoint', 'user-logout' ),
	);

	$items = array(
		'dashboard'     => ur_string_translation( null, 'ur_dashboard_menu', __( 'Dashboard', 'user-registration' ) ),
		'edit-profile'  => ur_string_translation( null, 'ur_profile_details_menu', __( 'Profile Details', 'user-registration' ) ),
		'edit-password' => ur_string_translation( null, 'ur_change_password_menu', __( 'Change Password', 'user-registration' ) ),
		'user-logout'   => ur_string_translation( null, 'ur_logout_menu', __( 'Logout', 'user-registration' ) ),
	);

	$user_id = get_current_user_id();
	$form_id = ur_get_form_id_by_userid( $user_id );

	$profile = user_registration_form_data( $user_id, $form_id );

	if ( count( $profile ) < 1 ) {
		unset( $items['edit-profile'] );
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

/**
 * Custom function to override get_gavatar function.
 *
 * @param [type] $avatar
 * @param [type] $id_or_email
 * @param [type] $size
 * @param [type] $default
 * @param [type] $alt
 * @param array  $args
 */
function ur_replace_gravatar_image( $avatar, $id_or_email, $size, $default, $alt, $args = array() ) {
	global $wp_filter;

	remove_all_filters( 'get_avatar' );

	add_filter( 'get_avatar', 'ur_replace_gravatar_image', 100, 6 );

	// Process the user identifier.
	$user = false;
	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', absint( $id_or_email ) );
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	} elseif ( $id_or_email instanceof WP_User ) {
		// User Object.
		$user = $id_or_email;
	} elseif ( $id_or_email instanceof WP_Post ) {
		// Post Object.
		$user = get_user_by( 'id', (int) $id_or_email->post_author );
	} elseif ( $id_or_email instanceof WP_Comment ) {

		if ( ! empty( $id_or_email->user_id ) ) {
			$user = get_user_by( 'id', (int) $id_or_email->user_id );
		}
	}

	if ( ! $user || is_wp_error( $user ) ) {
		return $avatar;
	}

	$profile_picture_url = get_user_meta( $user->ID, 'user_registration_profile_pic_url', true );
	$class               = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );

	if ( ( isset( $args['found_avatar'] ) && ! $args['found_avatar'] ) || ( isset( $args['force_default'] ) && $args['force_default'] ) ) {
		$class[] = 'avatar-default';
	}

	if ( $args['class'] ) {
		if ( is_array( $args['class'] ) ) {
			$class = array_merge( $class, $args['class'] );
		} else {
			$class[] = $args['class'];
		}
	}

	if ( $profile_picture_url ) {
		$avatar = sprintf(
			"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
			esc_attr( $args['alt'] ),
			esc_url( $profile_picture_url ),
			esc_url( $profile_picture_url ) . ' 2x',
			esc_attr( join( ' ', $class ) ),
			(int) $args['height'],
			(int) $args['width'],
			$args['extra_attr']
		);
	}

	return $avatar;
}
