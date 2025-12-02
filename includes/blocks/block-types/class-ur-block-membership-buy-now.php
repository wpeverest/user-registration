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
		do_action( 'wp_enqueue_membership_scripts' );
		$attr       = $this->attributes;
		$parameters = array();
		if ( ! isset( $attr['pageID'] ) ) {
			return '';
		}
		$page_url = get_permalink( absint( $attr['pageID'] ) );

		return '<a href="' . esc_url( $page_url ) . '" target="__blank"><button type="button" class="urm-buy-now-btn"><span class="label">' . esc_html( $attr['buttonText'] ) . '</span></button></a>';
	}
}
