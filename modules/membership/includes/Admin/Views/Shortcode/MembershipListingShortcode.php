<?php

namespace WPEverest\URMembership\Admin\Views\Shortcode;

use WPEverest\URMembership\Admin\Members\MembersListTable;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

/**
 * Registration Shortcodes
 *
 * Show the Registration form
 *
 * @class    MembershipListingShortcode
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Shortcode_Login Class.
 */
class MembershipListingShortcode {


	/**
	 * Output the shortcode.
	 *
	 * @param array $attributes Shortcode attributes.
	 */
	public static function render_template( $attributes ) {

		global $wp, $post;
		if ( ! is_user_logged_in() ) {
			$membership_repository = new MembershipRepository();
			$memberships           = $membership_repository->get_all_membership();
			$currency             = get_option( 'user_registration_payment_currency', 'USD' );
			$currencies           = ur_membership_get_currencies();
			$symbol               = $currencies[ $currency ]['symbol'];
			$registration_page_id = get_option( 'user_registration_member_registration_page_id', false );

			$redirect_page_url    = get_permalink( $registration_page_id );

			if ( empty( $memberships ) ) {
				echo wp_kses_post( apply_filters( 'user_registration_membership_no_membership_message', __( 'Please add at least one membership to allow user registration.', 'user-registration' ) ) );
				return;
			}

			$template_file = locate_template( 'membership-registration-form.php' );

			if ( ! $template_file ) {
				$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/membership-listing.php';
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
