<?php
/**
 * User registration login form block.
 *
 * @since 3.1.5
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Login_Form extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'login-form';

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
		
		return UR_Shortcodes::login(
			$parameters
		);
	}
}
