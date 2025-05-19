<?php
/**
 * URMembership CoreFunctions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  URMembership/Handler
 * @version  1.0.0
 */

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;

if ( ! function_exists( 'ur_membership_get_all_roles' ) ) {
	/**
	 * Retrieves all the roles available in the WordPress system.
	 *
	 * This function checks if the WP_Roles class exists and returns an array containing all the roles and their names.
	 *
	 * @return array An associative array where the keys are the role keys and the values are the role names.
	 * @global WP_Roles $wp_roles The global WP_Roles object.
	 */
	function ur_membership_get_all_roles() {
		global $wp_roles; // phpcs:ignore

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		$roles = array();
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$roles = $wp_roles->roles;

		$all_roles = array();

		foreach ( $roles as $role_key => $role ) {

			$all_roles[ $role_key ] = $role['name'];
		}

		return $all_roles;
	}
}

if ( ! function_exists( 'ur_membership_remove_unrelated_notices' ) ) {
	/**
	 * Removes unrelated notices from the WordPress admin.
	 *
	 * This function iterates over the 'user_admin_notices', 'admin_notices', and 'all_admin_notices'
	 * hooks and removes all notices that are not related to the 'user_registration_' plugin.
	 *
	 * @return void
	 * @global array $wp_filter The global WordPress filter
	 */
	function ur_membership_remove_unrelated_notices() {
		global $wp_filter;
		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
			if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {
				foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						// Remove all notices except user registration plugins notices.
						if ( ! strstr( $name, 'user_registration_' ) ) {
							unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'ur_membership_verify_nonce' ) ) {
	/**
	 * Verify the nonce for AJAX requests.
	 *
	 * This function checks the AJAX referer nonce to ensure that the request is valid.
	 * If the nonce is invalid, an error response is sent back to the client.
	 *
	 * @param string $nonce The nonce value to verify.
	 *
	 * @return void
	 * @throws WP_Error If the nonce is invalid.
	 */
	function ur_membership_verify_nonce( $nonce ) {
		if ( ! check_ajax_referer( $nonce, 'security' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error please reload.', 'user-registration-membership' ),
				)
			);
		}
	}
}

if ( ! function_exists( 'ur_membership_get_currencies' ) ) {
	/**
	 * ur_membership_get_currencies
	 *
	 * This function returns a list of currencies of different countries, this is a copy of a function in payment gateways
	 *
	 * @return mixed|void
	 */
	function ur_membership_get_currencies() {

		$currencies = array(
			'USD' => array(
				'name'                => esc_html__( 'U.S. Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'GBP' => array(
				'name'                => esc_html__( 'Pound Sterling', 'user-registration-membership' ),
				'symbol'              => '&pound;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'EUR' => array(
				'name'                => esc_html__( 'Euro', 'user-registration-membership' ),
				'symbol'              => '&euro;',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'AUD' => array(
				'name'                => esc_html__( 'Australian Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'BRL' => array(
				'name'                => esc_html__( 'Brazilian Real', 'user-registration-membership' ),
				'symbol'              => 'R$',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'CAD' => array(
				'name'                => esc_html__( 'Canadian Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'CZK' => array(
				'name'                => esc_html__( 'Czech Koruna', 'user-registration-membership' ),
				'symbol'              => '&#75;&#269;',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'DKK' => array(
				'name'                => esc_html__( 'Danish Krone', 'user-registration-membership' ),
				'symbol'              => 'kr.',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'HKD' => array(
				'name'                => esc_html__( 'Hong Kong Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'right',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'HUF' => array(
				'name'                => esc_html__( 'Hungarian Forint', 'user-registration-membership' ),
				'symbol'              => 'Ft',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'ILS' => array(
				'name'                => esc_html__( 'Israeli New Sheqel', 'user-registration-membership' ),
				'symbol'              => '&#8362;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'MYR' => array(
				'name'                => esc_html__( 'Malaysian Ringgit', 'user-registration-membership' ),
				'symbol'              => '&#82;&#77;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'MXN' => array(
				'name'                => esc_html__( 'Mexican Peso', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'NOK' => array(
				'name'                => esc_html__( 'Norwegian Krone', 'user-registration-membership' ),
				'symbol'              => 'Kr',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'NZD' => array(
				'name'                => esc_html__( 'New Zealand Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'PHP' => array(
				'name'                => esc_html__( 'Philippine Peso', 'user-registration-membership' ),
				'symbol'              => 'Php',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'PLN' => array(
				'name'                => esc_html__( 'Polish Zloty', 'user-registration-membership' ),
				'symbol'              => '&#122;&#322;',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'RUB' => array(
				'name'                => esc_html__( 'Russian Ruble', 'user-registration-membership' ),
				'symbol'              => 'pyб',
				'symbol_pos'          => 'right',
				'thousands_separator' => ' ',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'SGD' => array(
				'name'                => esc_html__( 'Singapore Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'ZAR' => array(
				'name'                => esc_html__( 'South African Rand', 'user-registration-membership' ),
				'symbol'              => 'R',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'SEK' => array(
				'name'                => esc_html__( 'Swedish Krona', 'user-registration-membership' ),
				'symbol'              => 'Kr',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'CHF' => array(
				'name'                => esc_html__( 'Swiss Franc', 'user-registration-membership' ),
				'symbol'              => 'CHF',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'TWD' => array(
				'name'                => esc_html__( 'Taiwan New Dollar', 'user-registration-membership' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'THB' => array(
				'name'                => esc_html__( 'Thai Baht', 'user-registration-membership' ),
				'symbol'              => '&#3647;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
		);

		return apply_filters( 'user_registration_membership_payments_currencies', $currencies );
	}
}

if ( ! function_exists( 'ur_membership_redirect_to_thank_you_page' ) ) {
	/**
	 * Redirect to thank you page
	 *
	 * @param $member_id
	 * @param $member_order
	 *
	 * @return void
	 */
	function ur_membership_redirect_to_thank_you_page( $member_id, $member_order ) {

		$thank_you_page = urm_get_thank_you_page();
		$user              = get_userdata( $member_id );
		$params            = array(
			'username'       => $user->user_login,
			'transaction_id' => empty( $member_order['transaction_id'] ) ? $member_order['ID'] : $member_order['transaction_id'],
			'payment_type'   => 'paid',
		);
		$url               = $thank_you_page . '?' . http_build_query( $params );

		wp_redirect( $url );
		exit;
	}
}

if ( function_exists( 'add_filter' ) ) {
	add_filter( 'build_membership_list_frontend', 'build_membership_list_frontend', 10, 1 );
}

if ( ! function_exists( 'build_membership_list_frontend' ) ) {
	/**
	 * Builds the frontend membership list.
	 *
	 * This function takes an array of memberships and transforms it into a new array
	 * with specific properties for each membership. The properties include:
	 * - ID: The ID of the membership.
	 * - title: The title of the membership.
	 * - type: The type of the membership.
	 * - amount: The amount of the membership.
	 * - period: The period of the membership, calculated based on the type.
	 * - active_payment_gateways: An array of active payment gateways for the membership.
	 *
	 * @param array $memberships The array of memberships.
	 *
	 * @return array The transformed membership list.
	 */
	function build_membership_list_frontend( $memberships ) {
		$currency                = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies              = ur_payment_integration_get_currencies();
		$symbol                  = $currencies[ $currency ]['symbol'];
		$new_mem                 = array();
		$active_payment_gateways = array();
		foreach ( $memberships as $k => $membership ) {
			$new_mem[ $k ] = array(
				'ID'                => $membership['ID'],
				'title'             => $membership['post_title'],
				'description'       => !empty($membership['post_content']['description']) ?  $membership['post_content']['description'] : get_post_meta($membership['ID'] , 'ur_membership_description' ,true),
				'type'              => $membership['meta_value']['type'],
				'amount'            => $membership['meta_value']['amount'] ?? 0,
				'currency_symbol'   => $symbol,
				'calculated_amount' => 'free' === $membership['meta_value']['type'] ? 0 : round( $membership['meta_value']['amount'] ),
				'period'            => 'free' === $membership['meta_value']['type'] ? __( 'Free', 'user-registration' ) : ( 'subscription' === $membership['meta_value']['type'] ? $symbol . $membership['meta_value']['amount'] . ' / ' . number_format( $membership['meta_value']['subscription']['value'] ) . ' ' . ucfirst( $membership['meta_value']['subscription']['duration'] ) . ( $membership['meta_value']['subscription']['value'] > 1 ? '(s)' : '' ) : $symbol . round( $membership['meta_value']['amount'] ) ),
			);
			if ( isset( $membership['meta_value']['payment_gateways'] ) ) {
				foreach ( $membership['meta_value']['payment_gateways'] as $key => $gateways ) {
					if ( 'on' !== $gateways['status'] ) {
						continue;
					}
					$active_payment_gateways[ $key ] = $gateways['status'];
				}

				$new_mem[ $k ]['active_payment_gateways'] = ( wp_unslash( wp_json_encode( $active_payment_gateways ) ) );
			}
			$active_payment_gateways = array();
		}

		return $new_mem;
	}
}

if ( ! function_exists( 'get_membership_menus' ) ) {
	/**
	 * get_memberhsip_menus
	 *
	 * @return array[]
	 */
	function get_membership_menus() {
		return array(
			'memberships'       => array(
				'label'  => __( 'Memberships', 'user-registration' ),
				'url'    => admin_url( 'admin.php?page=user-registration-membership' ),
				'active' => isset( $_GET['page'] ) &&
				            $_GET['page'] === 'user-registration-membership' &&
				            ( isset( $_GET['action'] ) ? ! in_array( $_GET['action'], array(
					            'list_groups',
					            'add_groups'
				            ) ) : true ),
			),
			'membership_groups' => array(
				'label'  => __( 'Membership Groups', 'user-registration' ),
				'url'    => admin_url( 'admin.php?page=user-registration-membership&action=list_groups' ),
				'active' => isset( $_GET['page'], $_GET['action'] ) &&
				            $_GET['page'] === 'user-registration-membership' &&
				            in_array( $_GET['action'], array( 'list_groups', 'add_groups' ) ),
			),
			'members'           => array(
				'label'  => __( 'Members', 'user-registration' ),
				'url'    => admin_url( 'admin.php?page=user-registration-members' ),
				'active' => isset( $_GET['page'] ) && $_GET['page'] === 'user-registration-members',
			),
			'settings'          => array(
				'label'  => __( 'Settings', 'user-registration' ),
				'url'    => admin_url( 'admin.php?page=user-registration-settings&tab=membership' ),
				'active' => false,
			)
		);
	}
}

if ( ! function_exists( 'get_active_membership_id_name' ) ) {
	/**
	 * get_active_membership_id_name
	 *
	 * @return array
	 */
	function get_active_membership_id_name() {
		$new_membership     = array();
		$membership_service = new MembershipRepository();
		$memberships        = $membership_service->get_all_membership();
		foreach ( $memberships as $membership ) {
			$new_membership[ $membership['ID'] ] = $membership['post_title'];
		}

		return $new_membership;
	}
}

if ( ! function_exists( 'urm_is_divi_active' ) ) {
	/**
	 * Check divi is active or not.
	 *
	 * @return array
	 */
	function urm_is_divi_active() {
		$active_theme_details = wp_get_theme();
		$theme_name           = $active_theme_details->Name;

		return 'Divi' === $theme_name;
	}
}


if ( ! function_exists( 'convert_to_days' ) ) {
	/**
	 * convert_to_days
	 *
	 * @param $value
	 * @param $unit
	 *
	 * @return float|int|mixed
	 */
	function convert_to_days( $value, $unit ) {
		switch ( strtolower( $unit ) ) {
			case 'year':
			case 'years':
				return $value * 365;
			case 'month':
			case 'months':
				return $value * 30;
			case 'week':
			case 'weeks':
				return $value * 7;
			case 'day':
			case 'days':
			default:
				return $value;
		}
	}
}

if ( ! function_exists( 'urm_get_thank_you_page' ) ) {
	/**
	 * Get Thank Yu page url
	 *
	 * @return array
	 */
	function urm_get_thank_you_page() {
		$thank_you_page_id = get_option( 'user_registration_thank_you_page_id' );
		$thank_you_page    = get_permalink( $thank_you_page_id );
		if ( ! empty( $_GET['urm_uuid'] ) ) {
			$uuid           = sanitize_text_field( $_GET['urm_uuid'] );
			$transient_id   = "uuid_{$uuid}_thank_you";
			$thank_you_page = get_transient( $transient_id );

		}
		return $thank_you_page;
	}
}
