<?php
/**
 * DiviBuilder D5: My Account Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\MyAccountModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 My Account module.
 *
 * @since xx.xx.xx
 */
class MyAccountModule implements DependencyInterface {

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
		$parameters = array();

		$d5_values       = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$redirect_url    = esc_url_raw( $d5_values['redirectUrl'] ?? '' );
		$logout_redirect = esc_url_raw( $d5_values['logoutRedirect'] ?? '' );
		$user_state      = sanitize_text_field( $d5_values['userState'] ?? 'logged_in' );

		if ( ! empty( $redirect_url ) ) {
			$parameters['redirect_url'] = $redirect_url;
		}

		if ( ! empty( $logout_redirect ) ) {
			$parameters['logout_redirect'] = $logout_redirect;
		}

		if ( ! empty( $user_state ) ) {
			$parameters['userState'] = $user_state;
		}

		if ( empty( $user_state ) || 'logged_in' === $user_state ) {
			return \UR_Shortcodes::my_account( $parameters );
		}

		return \UR_Shortcodes::login( $parameters );
	}
}
