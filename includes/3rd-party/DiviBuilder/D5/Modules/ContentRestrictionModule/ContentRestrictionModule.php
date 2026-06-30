<?php
/**
 * DiviBuilder D5: Content Restriction Module
 *
 * Registered for D5 block rendering only — intentionally excluded from the
 * VB module-library JS so new users cannot insert it. Existing D4 pages
 * that used the urm-content-restriction module continue to render correctly
 * on the frontend via this render callback.
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\ContentRestrictionModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Content Restriction module.
 *
 * @since xx.xx.xx
 */
class ContentRestrictionModule implements DependencyInterface {

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
		if ( ! class_exists( 'URCR_Shortcodes' ) ) {
			return '';
		}

		$d5_values        = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$user_role        = sanitize_text_field( $d5_values['userRole'] ?? '' );
		$restrict_content = wp_kses_post( $d5_values['restrictContent'] ?? '' );

		return do_shortcode(
			'[urcr_restrict access_role="' . esc_attr( $user_role ) . '"]'
			. $restrict_content
			. '[/urcr_restrict]'
		);
	}
}
