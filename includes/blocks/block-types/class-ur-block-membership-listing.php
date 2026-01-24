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

		$button_text         = isset( $attr['button_text'] ) ? sanitize_text_field( $attr['button_text'] ) : '';
		$group_id            = isset( $attr['group_id'] ) ? absint( $attr['group_id'] ) : 0;
		$uuid                = isset( $attr['id'] ) ? sanitize_text_field( $attr['id'] ) : ur_generate_random_key();
		$redirection_page_id = isset( $attr['redirection_page_id'] ) ? absint( $attr['redirection_page_id'] ) : 0;
		$thank_you_page_id   = isset( $attr['thank_you_page_id'] ) ? absint( $attr['thank_you_page_id'] ) : 0;
		$type                = isset( $attr['type'] ) ? sanitize_text_field( $attr['type'] ) : 'list';
		$column_number       = isset( $attr['columnNumber'] ) ? absint( $attr['columnNumber'] ) : 0;

		$open_in_new_tab  = isset( $attr['openInNewTab'] ) ? $attr['openInNewTab'] : false;
		$show_description = isset( $attr['showDescription'] ) ? $attr['showDescription'] : false;

		$style = array();

		$style['buttonTextColor']      = isset( $attr['buttonTextColor'] ) ? $attr['buttonTextColor'] : '';
		$style['buttonBgColor']        = isset( $attr['buttonBgColor'] ) ? $attr['buttonBgColor'] : '';
		$style['buttonTextHoverColor'] = isset( $attr['buttonTextHoverColor'] ) ? $attr['buttonTextHoverColor'] : '';
		$style['buttonBgHoverColor']   = isset( $attr['buttonBgHoverColor'] ) ? $attr['buttonBgHoverColor'] : '';
		$style['radioColor']           = isset( $attr['radioColor'] ) ? $attr['radioColor'] : '';
		$style['buttonFontSize']       = isset( $attr['buttonFontSize'] ) ? $attr['buttonFontSize'] : '';
		$style['buttonTypography']     = $attr['buttonTypography'];
		$style['buttonPadding']        = $attr['buttonPadding'];
		$style['buttonMargin']         = $attr['buttonMargin'];

		return ShortCodes::membership_listing(
			array(
				'id'                   => $group_id,
				'uuid'                 => $uuid,
				'button_text'          => $button_text,
				'list_type'            => $type,
				'registration_page_id' => $redirection_page_id,
				'thank_you_page_id'    => $thank_you_page_id,
				'column_number'        => $column_number,
				'open_in_new_tab'      => $open_in_new_tab,
				'show_description'     => $show_description,
				'style'                => $style,
			),
			'user_registration_groups'
		);
	}
}
