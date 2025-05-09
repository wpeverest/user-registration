<?php

namespace WPEverest\URMembership\Admin\Views\Shortcode;

use WPEverest\URMembership\Admin\Members\MembersListTable;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

/**
 * Thank You Page Shortcodes
 *
 * Show the Registration form
 *
 * @class    ThankYouShortcode
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Shortcode_Login Class.
 */
class ThankYouShortcode {


	/**
	 * Output the shortcode.
	 *
	 * @param array $attributes Shortcode attributes.
	 */
	public static function render_template( $attributes ) {
		
		$membership_repository = new MembershipRepository();
		$memberships           = $membership_repository->get_all_membership();
		$template_file         = locate_template( 'thank-you-page.php' );
		if ( ! $template_file ) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/thank-you-page.php';
		}
		require $template_file;

	}
}
