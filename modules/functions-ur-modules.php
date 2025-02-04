<?php
/**
 * UserRegistration Modules
 *
 * Functions for the modules.
 *
 * @package  UserRegistration/Functions
 * @version  4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'ur_check_module_activation' ) ) {
	/**
	 * Check if provided module is activated or not.
	 *
	 * @param string Module key to check.
	 *
	 * @return bool
	 */
	function ur_check_module_activation( $module ) {
		$enabled_features = get_option( 'user_registration_enabled_features', array() );

		return in_array( 'user-registration-' . $module, $enabled_features, true ) ? true : false;
	}
}

if ( ! function_exists( 'ur_payment_integration_get_currencies' ) ) {
	/**
	 * Get supported currencies.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function ur_payment_integration_get_currencies() {

		$currencies = array(
			'USD' => array(
				'name'                => esc_html__( 'U.S. Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'GBP' => array(
				'name'                => esc_html__( 'Pound Sterling', 'user-registration' ),
				'symbol'              => '&pound;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'EUR' => array(
				'name'                => esc_html__( 'Euro', 'user-registration' ),
				'symbol'              => '&euro;',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'AUD' => array(
				'name'                => esc_html__( 'Australian Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'BRL' => array(
				'name'                => esc_html__( 'Brazilian Real', 'user-registration' ),
				'symbol'              => 'R$',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'CAD' => array(
				'name'                => esc_html__( 'Canadian Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'CZK' => array(
				'name'                => esc_html__( 'Czech Koruna', 'user-registration' ),
				'symbol'              => '&#75;&#269;',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'DKK' => array(
				'name'                => esc_html__( 'Danish Krone', 'user-registration' ),
				'symbol'              => 'kr.',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'HKD' => array(
				'name'                => esc_html__( 'Hong Kong Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'right',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'HUF' => array(
				'name'                => esc_html__( 'Hungarian Forint', 'user-registration' ),
				'symbol'              => 'Ft',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'ILS' => array(
				'name'                => esc_html__( 'Israeli New Sheqel', 'user-registration' ),
				'symbol'              => '&#8362;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'MYR' => array(
				'name'                => esc_html__( 'Malaysian Ringgit', 'user-registration' ),
				'symbol'              => '&#82;&#77;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'MXN' => array(
				'name'                => esc_html__( 'Mexican Peso', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'NOK' => array(
				'name'                => esc_html__( 'Norwegian Krone', 'user-registration' ),
				'symbol'              => 'Kr',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'NZD' => array(
				'name'                => esc_html__( 'New Zealand Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'PHP' => array(
				'name'                => esc_html__( 'Philippine Peso', 'user-registration' ),
				'symbol'              => 'Php',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'PLN' => array(
				'name'                => esc_html__( 'Polish Zloty', 'user-registration' ),
				'symbol'              => '&#122;&#322;',
				'symbol_pos'          => 'left',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'RUB' => array(
				'name'                => esc_html__( 'Russian Ruble', 'user-registration' ),
				'symbol'              => 'pyб',
				'symbol_pos'          => 'right',
				'thousands_separator' => ' ',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'SGD' => array(
				'name'                => esc_html__( 'Singapore Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'ZAR' => array(
				'name'                => esc_html__( 'South African Rand', 'user-registration' ),
				'symbol'              => 'R',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'SEK' => array(
				'name'                => esc_html__( 'Swedish Krona', 'user-registration' ),
				'symbol'              => 'Kr',
				'symbol_pos'          => 'right',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'decimals'            => 2,
			),
			'CHF' => array(
				'name'                => esc_html__( 'Swiss Franc', 'user-registration' ),
				'symbol'              => 'CHF',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'TWD' => array(
				'name'                => esc_html__( 'Taiwan New Dollar', 'user-registration' ),
				'symbol'              => '&#36;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
			'THB' => array(
				'name'                => esc_html__( 'Thai Baht', 'user-registration' ),
				'symbol'              => '&#3647;',
				'symbol_pos'          => 'left',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'decimals'            => 2,
			),
		);

		return apply_filters( 'user_registration_payments_currencies', $currencies );
	}
}

if ( ! function_exists( 'paypal_supported_currencies_list' ) ) {
	/**
	 * Paypal Supported Currencies list.
	 * From https://developer.paypal.com/docs/reports/reference/paypal-supported-currencies/
	 *
	 * @since 1.4.3
	 */
	function paypal_supported_currencies_list() {
		return array(
			'AUD',
			'BRL',
			'CAD',
			'CNY',
			'CZK',
			'DKK',
			'EUR',
			'HKD',
			'HUF',
			'ILS',
			'JPY',
			'MYR',
			'MXN',
			'TWD',
			'NZD',
			'NOK',
			'PHP',
			'PLN',
			'GBP',
			'RUB',
			'SGD',
			'SEK',
			'CHF',
			'THB',
			'USD',
		);
	}
}

if ( ! function_exists( 'user_registration_sanitize_amount' ) ) {
	/**
	 * Sanitize Amount.
	 *
	 * Returns a sanitized amount by stripping out thousands separators.
	 *
	 * @param string $amount Amount.
	 * @param string $currency Currency.
	 *
	 * @return string $amount
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/formatting.php#L24
	 *
	 * @since 1.0.0
	 */
	function user_registration_sanitize_amount( $amount, $currency = 'USD' ) {

		$currency      = strtoupper( $currency );
		$currencies    = ur_payment_integration_get_currencies();
		$thousands_sep = $currencies[ $currency ]['thousands_separator'];
		$decimal_sep   = $currencies[ $currency ]['decimal_separator'];
		$is_negative   = false;

		// Sanitize the amount.
		if ( ',' === $decimal_sep && false !== ( strpos( $amount, $decimal_sep ) ) ) {
			if ( ( '.' === $thousands_sep || ' ' === $thousands_sep ) && false !== ( strpos( $amount, $thousands_sep ) ) ) {
				$amount = str_replace( $thousands_sep, '', $amount );
			} elseif ( empty( $thousands_sep ) && false !== ( strpos( $amount, '.' ) ) ) {
				$amount = str_replace( '.', '', $amount );
			}
			$amount = str_replace( $decimal_sep, '.', $amount );
		} elseif ( ',' === $thousands_sep && false !== ( strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		}

		if ( $amount < 0 ) {
			$is_negative = true;
		}

		$amount   = preg_replace( '/[^0-9\.]/', '', $amount );
		$decimals = apply_filters( 'user_registration_sanitize_amount_decimals', 2, $amount );
		$amount   = number_format( (float) $amount, $decimals, '.', '' );

		if ( $is_negative ) {
			$amount *= - 1;
		}

		return $amount;
	}
}


if ( ! function_exists( 'ur_membership_install_required_pages' ) ) {

	/**
	 * Install required membership pages.
	 */
	function ur_membership_install_required_pages() {
		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		WPEverest\URMembership\Admin\Database\Database::create_tables();
		update_option( 'ur_membership_default_membership_field_name', $membership_field_name );
		$membership_id       = UR_Install::create_default_membership();
//		$membership_group_id = UR_Install::create_default_membership_group( array( array( 'ID' => "$membership_id" ) ) );

		$pages                = apply_filters( 'user_registration_create_pages', array() );
		$default_form_page_id = 0;

		$membership_field_name = 'membership_field_' . ur_get_random_number();
		$post_content          = '[[[{"field_key":"user_login","general_setting":{"label":"Username","description":"","field_name":"user_login","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":"","username_length":"","username_character":"1"},"icon":"ur-icon ur-icon-user"}],[{"field_key":"user_email","general_setting":{"label":"User Email","description":"","field_name":"user_email","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-email"}]],[[{"field_key":"user_pass","general_setting":{"label":"User Password","description":"","field_name":"user_pass","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password"}],[{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","description":"","field_name":"user_confirm_password","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password-confirm"}]],[[{"field_key":"membership","general_setting":{"membership_group":"0","label":"Membership Field","description":"","field_name":"' . $membership_field_name . '","hide_label":"false","membership_listing_option":"all"},"advance_setting":{},"icon":"ur-icon ur-icon-membership-field"}]]]';

		// Insert default form.
		$default_form_page_id = wp_insert_post(
			array(
				'post_type'      => 'user_registration',
				'post_title'     => esc_html__( 'Default Membership Registration Form', 'user-registration' ),
				'post_content'   => $post_content,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		$pages['membership_registration'] = array(
			'name'    => _x( 'membership-registration', 'Page slug', 'user-registration' ),
			'title'   => _x( 'Membership Registration', 'Page title', 'user-registration' ),
			'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $default_form_page_id ) . '"]',
			'option'  => 'user_registration_member_registration_page_id',
		);

		$pages['membership_pricing']  = array(
			'name'    => _x( 'membership-pricing', 'Page slug', 'user-registration' ),
			'title'   => _x( 'Membership Pricing', 'Page title', 'user-registration' ),
			'content' => '[user_registration_groups]',
			'option'  => ''
		);
		$pages['membership_thankyou'] = array(
			'name'    => _x( 'membership-thankyou', 'Page slug', 'user-registration' ),
			'title'   => _x( 'Membership ThankYou', 'Page title', 'user-registration' ),
			'content' => '[user_registration_membership_thank_you]',
			'option'  => 'user_registration_thank_you_page_id',
		);

		foreach ( $pages as $key => $page ) {
			$post_id = ur_create_page( esc_sql( $page['name'] ), 'user_registration_' . $key . '_page_id', wp_kses_post( ( $page['title'] ) ), wp_kses_post( $page['content'] ) );
			if ( ! empty( $page['option'] ) ) {
				update_option( $page['option'], $post_id );
			}
		}
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		array_push( $enabled_features, 'user-registration-membership' );
		array_push( $enabled_features, 'user-registration-payment-history' );
		array_push( $enabled_features, 'user-registration-content-restriction' );
		update_option( 'user_registration_enabled_features', $enabled_features );
		update_option( 'user_registration_membership_installed_flag', true );
	}
}


if ( ! function_exists( 'ur_get_all_roles' ) ) {

	/**
	 * Retrieve list of roles.
	 */
	function ur_get_all_roles() {
		global $wp_roles;

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

		return apply_filters( 'user_registration_all_roles', $all_roles );
	}
}


if ( ! function_exists( 'check_membership_field_in_form' ) ) {
	/**
	 * check_membership_field_in_form
	 *
	 * @return bool
	 */
	function check_membership_field_in_form($form_id) {

		$payment_fields       = ur_get_form_fields( $form_id );
		$has_membership_field = false;
		foreach ( $payment_fields as $k => $field ) {
			if ( 'membership' === $field->field_key ) {
				$has_membership_field = true;
			}
		}

		return $has_membership_field;
	}
}

/**
 * Deprecating function code start
 *
 * @deprecated
 */
$modules = array(
	'coupons'             => 'ur_pro_is_coupons_addon_activated',
	'payments'            => 'ur_pro_is_paypal_activated',
	'sms-integration'     => 'ur_pro_is_sms_integration_activated',
	'content-restriction' => 'ur_pro_is_content_restriction_activated',
	'payment-history'     => 'ur_pro_is_payment_history_activated',
);

foreach ( $modules as $module_key => $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		eval(
		"
		        function $function_name() {
		            return ur_check_module_activation('$module_key');
		        }
        "
		);
	}
}
// deprecating function code ends
