<?php
/**
 * User registration content restriction block.
 *
 * @since 4.0
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Content_Restriction extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'content-restriction-enhanced';

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function build_html( $content ) {
		return $content;
	}
}
