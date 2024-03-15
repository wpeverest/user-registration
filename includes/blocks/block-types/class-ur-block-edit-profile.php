<?php
/**
 * User registration edit profile block.
 *
 * @since 3.1.5
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Edit_Profile extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'editprofile';

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr       = $this->attributes;
		$parameters = array();

		return UR_Shortcodes::edit_profile(
			$parameters
		);
	}
}
