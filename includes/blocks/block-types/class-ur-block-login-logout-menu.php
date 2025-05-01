<?php
/**
 * User registration login logout menu.
 *
 * @since 4.2.3
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration Login logout menu class.
 */
class UR_Block_Login_Logout_Menu extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'login-logout-menu';

	/**
	 * Build html.
	 *
	 * @param string $content Build html content.
	 * @return string
	 */
	protected function build_html( $content ) {
		$attr = $this->attributes;

		if ( is_user_logged_in() ) {
			$itemUrl = esc_url( ur_logout_url() );
			if ( ! empty( $attr[ 'logoutLabel' ] ) ) {
				$itemLabel = esc_html( $attr[ 'logoutLabel' ] );
			} else {
				$itemLabel = esc_html__( 'Logout', 'user-registration' );
			}
		} else {
			$itemUrl = empty( $attr['loginPage'] ) ? esc_url( ur_get_login_url() ) : esc_url( get_page_link( $attr[ 'loginPage' ] ) );
			if ( ! empty( $attr[ 'loginLabel' ] ) ) {
				$itemLabel = esc_html( $attr[ 'loginLabel' ] );
			} else {
				$itemLabel = esc_html__( 'Login', 'user-registration' );
			}
		}
		return sprintf( '<a href="%s">%s</a>', $itemUrl, $itemLabel );
	}
}
