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
		$uuid                = isset( $attr['id'] ) ? sanitize_text_field( $attr['id'] ) : ur_generate_random_key();
		$redirection_page_id = isset( $attr['redirection_page_id'] ) ? absint( $attr['redirection_page_id'] ) : 0;
		$thank_you_page_id   = isset( $attr['thank_you_page_id'] ) ? absint( $attr['thank_you_page_id'] ) : 0;
		$type                = isset( $attr['type'] ) ? sanitize_text_field( $attr['type'] ) : 'list';

		return ShortCodes::membership_listing(
			array(
				'id'                   => $group_id,
				'uuid'                 => $uuid,
				'button_text'          => $button_text,
				'list_type'            => $type,
				'registration_page_id' => $redirection_page_id,
				'thank_you_page_id'    => $thank_you_page_id,
			),
			'user_registration_groups'
		);
	}
}
