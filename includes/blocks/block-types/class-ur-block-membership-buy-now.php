<?php
/**
 * User registration Membership buy now block.
 *
 * @since xx.xx.xx
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Membership_Buy_Now extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'membership-buy-now';

	/**
	 * Build html.
	 *
	 * @param string $content Build html content.
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr       = $this->attributes;
		$parameters = array();
		return '<button class="urm-buy-now-btn"><span class="label">' . esc_html( $attr['buttonText'] ) . '</span></button>';
	}
}
