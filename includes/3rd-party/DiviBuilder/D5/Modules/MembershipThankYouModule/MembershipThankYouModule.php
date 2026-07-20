<?php
/**
 * DiviBuilder D5: Membership Thank You Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\MembershipThankYouModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WPEverest\URMembership\ShortCodes;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Membership Thank You module.
 *
 * @since xx.xx.xx
 */
class MembershipThankYouModule implements DependencyInterface {

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
		if ( ! defined( 'UR_VERSION' ) ) {
			return sprintf(
				'<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>',
				esc_html__( 'Please activate the membership module.', 'user-registration' )
			);
		}

		return ShortCodes::thank_you( array(), 'user_registration_membership_thank_you' );
	}
}
