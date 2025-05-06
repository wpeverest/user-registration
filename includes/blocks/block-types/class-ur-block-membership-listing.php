<?php
/**
 * UR_Block_Membership_Listing
 *
 * @since 4.2.2
 * @package user-registration
 */

use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\ShortCodes;

defined( 'ABSPATH' ) || exit;

/**
 * Block registration form class.
 */
class UR_Block_Membership_Listing extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'membership-listing';

	/**
	 * Build html.
	 *
	 * @param string $content Build html content.
	 *
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr       = $this->attributes;
		$parameters = array();


		$button_text = isset( $attr['button_text'] ) ? sanitize_text_field( $attr['button_text'] ) : '';

		$group_id            = isset( $attr['group_id'] ) ? absint( $attr['group_id'] ) : 0;
		$redirection_page_id = isset( $attr['redirection_page_id'] ) ? absint( $attr['redirection_page_id'] ) : 0;
		$type                = isset( $attr['type'] ) ? sanitize_text_field( $attr['type'] ) : 'list';

//		if ( $redirection_page_id ) {
//			$membership_service = new MembershipService();
//			$response           = $membership_service->verify_page_content( 'user_registration_member_registration_page_id', $redirection_page_id );
//			if ( ! $response['status'] ) {
//				return $response['message'];
//			}
//		}
//		if ( $thank_you_page_id ) {
//			$membership_service = new MembershipService();
//			$response           = $membership_service->verify_page_content( 'user_registration_member_thank_you_page_id', $thank_you_page_id );
//			if ( ! $response['status'] ) {
//				return $response['message'];
//			}
//		}

		return ShortCodes::membership_listing(
			array(
				'id'                   => $group_id,
				'button_text'          => $button_text,
				'list_type'            => $type,
				'registration_page_id' => $redirection_page_id,
			),
			'user_registration_groups'
		);
	}
}
