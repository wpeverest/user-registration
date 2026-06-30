<?php
/**
 * DiviBuilder D5: Login Form Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\LoginFormModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Login Form module.
 *
 * @since xx.xx.xx
 */
class LoginFormModule implements DependencyInterface {

	/**
	 * Register this module with Divi 5.
	 *
	 * @since xx.xx.xx
	 */
	public function load(): void {
		ModuleRegistration::register_module(
			__DIR__,
			array(
				'render_callback' => array( self::class, 'render_callback' ),
			)
		);
	}

	/**
	 * Server-side render callback.
	 *
	 * @since xx.xx.xx
	 *
	 * @param array    $attrs   Block attributes.
	 * @param string   $content Inner block content (unused).
	 * @param WP_Block $block   Block instance (unused).
	 * @return string Rendered HTML.
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block ): string {
		$d5_values       = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$redirect_url    = esc_url_raw( $d5_values['redirectUrl'] ?? '' );
		$logout_redirect = esc_url_raw( $d5_values['logoutRedirect'] ?? '' );

		$parameters = array();

		if ( ! empty( $redirect_url ) ) {
			$parameters['redirect_url'] = $redirect_url;
		}

		if ( ! empty( $logout_redirect ) ) {
			$parameters['logout_redirect'] = $logout_redirect;
		}

		// Do NOT pass userState — UR_Shortcode_Login::output() treats userState='logged_out'
		// as a force-render flag that bypasses its own is_user_logged_in() check, causing
		// the form to appear for logged-in users. The shortcode's own logic already handles
		// both states: "You are already logged in. Log out?" for logged-in users, the
		// login form for guests.
		return \UR_Shortcodes::login( $parameters );
	}
}
