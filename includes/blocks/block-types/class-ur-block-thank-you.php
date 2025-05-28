<?php
/**
 * UR_Block_Thank_You
 *
 * @since 4.2.2
 * @package user-registration
 */

use WPEverest\URMembership\ShortCodes;

defined( 'ABSPATH' ) || exit;

/**
 * Block registration form class.
 */
class UR_Block_Thank_You extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'thank-you';

	/**
	 * Build html.
	 *
	 * @param string $content Build html content.
	 *
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr = $this->attributes;

		$header            = isset( $attr['header'] ) ? ( $attr['header'] ) : '';
		$footer            = isset( $attr['footer'] ) ? ( $attr['footer'] ) : '';
		$notice_message    = isset( $attr['notice_message'] ) ? sanitize_text_field( $attr['notice_message'] ) : '';
		$transaction_info  = isset( $attr['transaction_info'] ) ? sanitize_text_field( $attr['transaction_info'] ) : '';
		$view_payment_info = isset( $attr['view_payment_info'] ) ? absint( $attr['view_payment_info'] ) : '';
		$is_preview        = isset( $attr['is_preview'] ) ? absint( $attr['is_preview'] ) : false;
		$show_notice_1     = isset( $attr['show_notice_1'] ) ? ( $attr['show_notice_1'] ) : false;
		$show_notice_2     = isset( $attr['show_notice_2'] ) ? ( $attr['show_notice_2'] ) : false;


		return ShortCodes::thank_you(
			array(
				'header'            => $header,
				'footer'            => $footer,
				'view_payment_info' => $view_payment_info,
				'notice_message'    => $notice_message,
				'transaction_info'  => $transaction_info,
				'is_preview'        => $is_preview,
				'show_notice_1'     => $show_notice_1,
				'show_notice_2'     => $show_notice_2
			),
			'user_registration_membership_thank_you'
		);
	}
}
