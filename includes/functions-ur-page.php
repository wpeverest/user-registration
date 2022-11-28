<?php
/**
 * UserRegistration Page Functions
 *
 * Functions related to pages and menus.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'body_class', 'ur_body_class' );

// Hooks for my account section.
add_action( 'user_registration_account_navigation', 'user_registration_account_navigation' );
add_action( 'user_registration_account_content', 'user_registration_account_content' );
add_action( 'user_registration_account_edit-profile_endpoint', 'user_registration_account_edit_profile' );
add_action( 'user_registration_account_edit-password_endpoint', 'user_registration_account_edit_account' );

/**
 * Replace a page title with the endpoint title.
 *
 * @param  string $title
 *
 * @return string
 */
function ur_page_endpoint_title( $title ) {
	global $wp_query;

	if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_ur_endpoint_url() ) {
		$endpoint = UR()->query->get_current_endpoint();

		if ( $endpoint_title = UR()->query->get_endpoint_title( $endpoint ) ) {
			$title = $endpoint_title;
		}

		remove_filter( 'the_title', 'ur_page_endpoint_title' );
	}

	return $title;
}

add_filter( 'the_title', 'ur_page_endpoint_title', 10 );


/**
 * Retrieve page ids - used for myaccount, edit_profile. returns -1 if no page is found.
 *
 * @param  string $page
 *
 * @return int
 */
function ur_get_page_id( $page ) {
	$my_account_page_id = get_option( 'user_registration_myaccount_page_id' );
	$page_id            = get_the_ID();

	/**
	 * Check if the page sent as parameter is My Account page and return the id,
	 * Else use the page's page_id sent as parameter.
	 */
	if ( 'myaccount' === $page && ur_post_content_has_shortcode( 'user_registration_my_account' ) && $page_id === $my_account_page_id ) {
		$page = $page_id;
	} elseif ( 'myaccount' !== $page && ur_post_content_has_shortcode( 'user_registration_login' ) && $page_id !== $my_account_page_id ) {
		$page = $page_id;
	} else {
		$page = apply_filters( 'user_registration_get_' . $page . '_page_id', get_option( 'user_registration_' . $page . '_page_id' ) );
	}

	if ( $page > 0 && function_exists( 'pll_current_language' ) && ! empty( pll_current_language() ) ) {
		$translations = pll_get_post_translations( $page );
		$page         = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $page;
	}

	return $page ? absint( $page ) : - 1;
}

/**
 * Retrieve page permalink.
 *
 * @param string $page
 *
 * @return string
 */
function ur_get_page_permalink( $page ) {
	$page_id   = ur_get_page_id( $page );
	$permalink = 0 < $page_id ? get_permalink( $page_id ) : get_home_url();

	return apply_filters( 'user_registration_get_' . $page . '_page_permalink', $permalink );
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @param  string $endpoint
 * @param  string $value
 * @param  string $permalink
 *
 * @return string
 */
function ur_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options
	$endpoint = ! empty( UR()->query->query_vars[ $endpoint ] ) ? UR()->query->query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	if (
		$endpoint === get_option( 'user_registration_logout_endpoint', 'user-logout' ) &&
		'yes' === get_option( 'user_registration_disable_logout_confirmation', 'no' ) ) {
		$url = wp_nonce_url( $url, 'user-logout' );
	}

	return apply_filters( 'user_registration_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

/**
 * Hide menu items conditionally.
 *
 * @param  array $items Navigation items.
 * @return array
 */
function ur_nav_menu_items( $items ) {

	if ( ! is_user_logged_in() ) {
		$customer_logout = get_option( 'user_registration_logout_endpoint', 'user-logout' );

		if ( ! empty( $customer_logout ) && is_array( $items ) ) {
			foreach ( $items as $key => $item ) {
				if ( empty( $item->url ) ) {
					continue;
				}
				$path  = parse_url( $item->url, PHP_URL_PATH );
				$query = parse_url( $item->url, PHP_URL_QUERY );

				if ( null !== $path && null !== $customer_logout ) {
					if ( strstr( $path, $customer_logout ) || strstr( $query, $customer_logout ) ) {
						unset( $items[ $key ] );
					}
				}
			}
		}
	}
	$customer_logout = get_option( 'user_registration_logout_endpoint', 'user-logout' );

	foreach ( $items as $item ) {

		if ( $item->post_name === 'logout' && ! empty( $customer_logout ) && 'yes' === get_option( 'user_registration_disable_logout_confirmation', 'no' ) ) {
			 $item->url = wp_nonce_url( $item->url, 'user-logout' );
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'ur_nav_menu_items', 10 );
