<?php
/**
 * User Registration Membership Shortcodes.
 *
 * @class    ShortCodes
 * @package  URMembership/ShortCodes
 */

namespace WPEverest\URMembership;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ShortCodes Class
 */
class ShortCodes {
	/**
	 * Init Shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'user_registration_membership_member_registration_form' => __CLASS__ . '::member_registration_form',
			'user_registration_membership_listing'   => __CLASS__ . '::membership_listing',
			'user_registration_membership_thank_you' => __CLASS__ . '::thank_you',
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function Callback function.
	 * @param array    $attributes (default: array()) Extra attributes.
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function = array(), $attributes = array()
	) {
		ob_start();
		call_user_func( $function, $attributes );

		return ob_get_clean();
	}

	/**
	 * Shortcode initialization for member registration form.
	 *
	 * @param mixed $attributes shortcode attributes.
	 *
	 * @return string
	 */
	public static function member_registration_form( $attributes ) {
		do_action( 'wp_enqueue_membership_scripts' );
		wp_enqueue_script( 'user-registration-membership-frontend-script' );
		wp_enqueue_script( 'ur-snackbar' );
		wp_enqueue_style( 'ur-snackbar' );

		return self::shortcode_wrapper(
			array(
				'WPEverest\URMembership\Admin\Views\Shortcode\MemberRegistrationFormShortcode',
				'display_form',
			),
			$attributes
		);
	}

	/**
	 * Shortcode initialization for membership listing.
	 *
	 * @param mixed $attributes shortcode attributes.
	 *
	 * @return string
	 */
	public static function membership_listing( $attributes ) {
		do_action( 'wp_enqueue_membership_scripts' );
		wp_enqueue_script( 'user-registration-membership-frontend-script' );

		return self::shortcode_wrapper(
			array(
				'WPEverest\URMembership\Admin\Views\Shortcode\MembershipListingShortcode',
				'render_template',
			),
			$attributes
		);
	}
	/**
	 * Shortcode initialization for thank you page.
	 *
	 * @param mixed $attributes shortcode attributes.
	 *
	 * @return string
	 */
	public static function thank_you( $attributes ) {
		do_action( 'wp_enqueue_membership_scripts' );
		wp_enqueue_script( 'user-registration-membership-frontend-script' );

		return self::shortcode_wrapper(
			array(
				'WPEverest\URMembership\Admin\Views\Shortcode\ThankYouShortcode',
				'render_template',
			),
			$attributes
		);
	}
}
