<?php

namespace WPEverest\URMembership\Admin\Views\Shortcode;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;

/**
 * Registration Shortcodes
 *
 * Show the Registration form
 *
 * @class    MemberRegistrationFormShortcode
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Shortcode_Login Class.
 */
class MemberRegistrationFormShortcode {


	/**
	 * Output the shortcode.
	 *
	 * @param array $attributes Shortcode attributes.
	 */
	public static function display_form( $attributes ) {
		global $wp, $post;
		$allow = ( is_user_logged_in() && $attributes['preview'] ) || ! is_user_logged_in();

		if ( $allow ) {
			$memberships = isset( $attributes['options'] ) ? $attributes['options'] : array();
			$form_id     = isset( $attributes['form_id'] ) ? $attributes['form_id'] : array();

			$template_file = locate_template( 'membership-registration-form.php' );

			if ( ! $template_file ) {
				$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/membership-registration-form.php';
			}
			require $template_file;

		} else {
			/**
			 * Filters to modify logged_in_message.
			 *
			 * @param mixed $ur_logout_url UR logout URL.
			 */
			/* translators: %s - Link to logout. */
			echo wp_kses_post( apply_filters( 'user_registration_logged_in_message', sprintf( __( 'You are already logged in. <a href="%s">Log out?</a>', 'user-registration' ), ur_logout_url() ) ) );
		}
	}
}
