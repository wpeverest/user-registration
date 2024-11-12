<?php
/**
 * User registration myaccountblock.
 *
 * @since 3.1.5
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Myaccount extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'myaccount';

	/**
	 * Build html.
	 *
	 * @param string $content Build html content.
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr       = $this->attributes;
		$parameters = array();

		if ( ! empty( $attr['redirectUrl'] ) ) {
			$parameters['redirect_url'] = $attr['redirectUrl'];
		}

		if ( ! empty( $attr['logoutUrl'] ) ) {
			$parameters['logout_redirect'] = $attr['logoutUrl'];
		}

		if ( ! empty( $attr['userState'] ) ) {
			$parameters['userState'] = $attr['userState'];
		}

		if ( ( empty( $parameters ) || ( isset( $parameters['userState'] ) && 'logged_in' === $parameters['userState'] ) ) || ( ! empty( $parameters ) && ! isset( $parameters['userState'] ) ) ) {
			return UR_Shortcodes::my_account(
				$parameters
			);
		} elseif ( isset( $parameters['userState'] ) && 'logged_out' === $parameters['userState'] ) {
			return UR_Shortcodes::login(
				$parameters
			);
		}
	}
}
