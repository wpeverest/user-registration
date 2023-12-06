<?php
/**
 * UserRegistration Page Functions
 *
 * Functions related to pages and menus.
 *
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_filter( 'body_class', 'ur_body_class' );

// Hooks for my account section.
add_action( 'user_registration_account_navigation', 'user_registration_account_navigation' );
add_action( 'user_registration_account_content', 'user_registration_account_content' );
add_action( 'user_registration_account_dashboard_endpoint', 'user_registration_account_dashboard' );
add_action( 'user_registration_account_edit-profile_endpoint', 'user_registration_account_edit_profile' );
add_action( 'user_registration_account_edit-password_endpoint', 'user_registration_account_edit_account' );

/**
 * Replace a page title with the endpoint title.
 *
 * @param  string $title Page Title.
 *
 * @return string
 */
function ur_page_endpoint_title( $title ) {
	global $wp_query;

	if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_ur_endpoint_url() ) {
		$endpoint       = UR()->query->get_current_endpoint();
		$endpoint_title = UR()->query->get_endpoint_title( $endpoint );

		if ( ! empty( $endpoint_title ) ) {
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
 * @param  string $page Page ID.
 *
 * @return int
 */
function ur_get_page_id( $page ) {
	$my_account_page_id = get_option( 'user_registration_myaccount_page_id' );
	$page_id            = get_the_ID();

	if ( 'myaccount' == $page || 'login' == $page ) {
		$page_id = ! empty( $my_account_page_id ) ? $my_account_page_id : $page_id;
	}

	/**
	 * Check if the page sent as parameter is My Account page and return the id,
	 * Else use the page's page_id sent as parameter.
	 */
	$page = ur_find_my_account_in_page( $page_id );

	if ( $page > 0 && function_exists( 'pll_current_language' ) ) {
		$current_language = pll_current_language();
		if ( ! empty( $current_language ) ) {
			$translations = pll_get_post_translations( $page_id );
			$page_id      = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $page_id;
		}
	} elseif ( $page > 0 && class_exists( 'SitePress', false ) ) {
		$page_id = ur_get_wpml_page_language( $page_id );
	}

	return $page_id ? absint( $page_id ) : - 1;
}

/**
 * Get the right page id for the current language.
 *
 * @param int $page_id Page ID.
 */
function ur_get_wpml_page_language( $page_id ) {
	global $wpdb;
	$current_language = apply_filters( 'wpml_current_language', 'en' );

	$element_prepared = $wpdb->prepare(
		"SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type=%s",
		array( $page_id, 'post_page' )
	);
	$trid       = $wpdb->get_var( $element_prepared ); //phpcs:ignore.

	if ( $trid > 0 ) {
		$page_id = $trid;
	}

	$element_prepared = $wpdb->prepare(
		"SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND element_type=%s AND language_code=%s",
		array( $page_id, 'post_page', $current_language )
	);
	$element_id       = $wpdb->get_var( $element_prepared ); //phpcs:ignore.
	return $element_id > 0 ? $element_id : $page_id;
}

/**
 * Retrieve page permalink.
 *
 * @param string $page Page ID.
 *
 * @return string
 */
function ur_get_page_permalink( $page ) {
	$page_id = ur_get_page_id( $page );
	$page    = $page_id;

	if ( $page_id > 0 && function_exists( 'pll_current_language' ) ) {
		$current_language = pll_current_language();
		if ( ! empty( $current_language ) ) {
			$translations = pll_get_post_translations( $page_id );
			$page         = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $page_id;
		}
	} elseif ( $page_id > 0 && class_exists( 'SitePress', false ) ) {
		$page = ur_get_wpml_page_language( $page_id );
	}

	$permalink = 0 < $page ? get_permalink( $page ) : ( 0 < $page_id ? get_permalink( $page_id ) : get_home_url() );

	return apply_filters( 'user_registration_get_' . $page . '_page_permalink', $permalink );
}

if ( ! function_exists( 'ur_get_login_url' ) ) {
	/**
	 * Returns the full url of the login redirection page.
	 *
	 * @return string Complete Login Page address.
	 */
	function ur_get_login_url() {
		$my_account_page_id = absint( get_option( 'user_registration_login_options_login_redirect_url', 'unset' ) );

		if ( $my_account_page_id > 0 && function_exists( 'pll_current_language' ) ) {
			$current_language = pll_current_language();
			if ( ! empty( $current_language ) ) {
				$translations       = pll_get_post_translations( $my_account_page_id );
				$my_account_page_id = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $my_account_page_id;
			}
		} elseif ( $my_account_page_id > 0 && class_exists( 'SitePress', false ) ) {
			$my_account_page_id = ur_get_wpml_page_language( $my_account_page_id );
		}

		$permalink = 0 < $my_account_page_id ? get_permalink( $my_account_page_id ) : '';

		return $permalink;
	}
}

if ( ! function_exists( 'ur_get_my_account_url' ) ) {
	/**
	 * Returns the full url of the selected My Account page.
	 *
	 * If My Account Page is not set:
	 * 1. Checks if Prevent Core Login is enabled.
	 * 2. Returns Login Redirection Page url if set.
	 * 3. Else, returns default WordPress login url.
	 *
	 * @return string
	 */
	function ur_get_my_account_url() {
		$my_account_page_id = get_option( 'user_registration_myaccount_page_id' );

		if ( $my_account_page_id > 0 && function_exists( 'pll_current_language' ) ) {
			$current_language = pll_current_language();
			if ( ! empty( $current_language ) ) {
				$translations       = pll_get_post_translations( $my_account_page_id );
				$my_account_page_id = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $my_account_page_id;
			}
		} elseif ( $my_account_page_id > 0 && class_exists( 'SitePress', false ) ) {
			$my_account_page_id = ur_get_wpml_page_language( $my_account_page_id );
		}

		$permalink = 0 < $my_account_page_id ? get_permalink( $my_account_page_id ) : '';

		if ( $permalink ) {
			return $permalink;
		}

		$prevent_core_login = ur_option_checked( 'user_registration_login_options_prevent_core_login', false );

		if ( $prevent_core_login ) {
			$login_redirect_page_id = get_option( 'user_registration_login_options_login_redirect_url', 'unset' );

			if ( 0 < $login_redirect_page_id ) {
				return get_permalink( $login_redirect_page_id );
			}
		}

		return wp_login_url();
	}
}

if ( ! function_exists( 'ur_get_current_language' ) ) {
	/**
	 * Returns the Current Language Code.
	 *
	 * @return string Current Language Code.
	 */
	function ur_get_current_language() {
		$current_language = get_bloginfo( 'language' );

		if ( function_exists( 'pll_current_language' ) ) {
			$current_language = pll_current_language();
		} elseif ( class_exists( 'SitePress', false ) ) {
			$current_language = apply_filters( 'wpml_current_language', $current_language );
		}
		return $current_language;
	}
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @param  string $endpoint Endpoint.
 * @param  string $value Value.
 * @param  string $permalink Permalink.
 *
 * @return string
 */
function ur_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options.
	$endpoint = isset( UR()->query->query_vars[ $endpoint ] ) ? UR()->query->query_vars[ $endpoint ] : $endpoint;

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
		get_option( 'user_registration_logout_endpoint', 'user-logout' ) === $endpoint &&
		ur_option_checked( 'user_registration_disable_logout_confirmation', false ) ) {
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
				$path  = parse_url( $item->url, PHP_URL_PATH ) ?? '';
				$query = parse_url( $item->url, PHP_URL_QUERY ) ?? '';

				$customer_logout = $customer_logout ?? '';

				if ( strstr( $path, $customer_logout ) !== false || strstr( $query, $customer_logout ) !== false ) {
						unset( $items[ $key ] );
				}
			}
		}
	}
	$customer_logout = get_option( 'user_registration_logout_endpoint', 'user-logout' );

	foreach ( $items as $item ) {

		if ( 0 === strpos( $item->post_name, 'logout' ) && ! empty( $customer_logout ) && ur_option_checked( 'user_registration_disable_logout_confirmation', false ) ) {
			$item->url = wp_nonce_url( $item->url, 'user-logout' );
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'ur_nav_menu_items', 10 );
