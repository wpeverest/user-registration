<?php
/**
 * DiviBuilder D5: Membership Groups Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\MembershipGroupsModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WPEverest\URMembership\ShortCodes;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Membership Groups module.
 *
 * @since xx.xx.xx
 */
class MembershipGroupsModule implements DependencyInterface {

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
		$d5_values   = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$group_id    = absint( $d5_values['groupId'] ?? 0 );
		$button_text = sanitize_text_field( $d5_values['buttonText'] ?? __( 'Sign Up', 'user-registration' ) );

		if ( 0 === $group_id ) {
			return sprintf(
				'<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>',
				esc_html__( 'Please select the membership group.', 'user-registration' )
			);
		}

		if ( ! defined( 'UR_VERSION' ) ) {
			return sprintf(
				'<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>',
				esc_html__( 'Please activate the membership module.', 'user-registration' )
			);
		}

		return ShortCodes::membership_listing(
			array(
				'id'          => $group_id,
				'button_text' => $button_text,
			),
			'user_registration_groups'
		);
	}
}
