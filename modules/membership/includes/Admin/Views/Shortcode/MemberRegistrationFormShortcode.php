<?php

namespace WPEverest\URMembership\Admin\Views\Shortcode;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;

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
			$group_id = isset( $attributes['membership_group'] ) ? $attributes['membership_group'] : 0;
			$group_status = true;
			if($group_id) {
				$group_service = new MembershipGroupService();
				$group = $group_service->get_membership_group_by_id($group_id);
				if( ! empty( $group ) ) {
					$content = json_decode( wp_unslash( $group['post_content'] ), true );
					$group_status = ur_string_to_bool($content['status']);
				}
			}

			$memberships = isset( $attributes['options'] ) && $group_status ? $attributes['options'] : array();

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
