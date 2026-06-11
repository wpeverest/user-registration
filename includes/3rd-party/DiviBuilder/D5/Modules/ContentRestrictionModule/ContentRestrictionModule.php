<?php
/**
 * DiviBuilder D5: Content Restriction Module
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
	 * @param WP_Block $block   Block instance.
	 * @return string Rendered HTML.
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block ): string {
		if ( ! ur_check_module_activation( 'content-restriction' ) ) {
			return sprintf(
				'<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>',
				esc_html__( 'Please activate the content restriction module.', 'user-registration' )
			);
		}

		$d5_values        = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$user_role        = sanitize_text_field( $d5_values['userRole'] ?? 'subscriber' );
		$restrict_content = wp_kses_post( $d5_values['restrictContent'] ?? '' );

		// Use the block's post context for the current post ID.
		$post_id = $block->context['postId'] ?? ( isset( $_POST['current_page']['id'] ) ? absint( $_POST['current_page']['id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification

		$shortcode = sprintf(
			'[urcr_restrict access_role="%s" post_id="%s"]%s[/urcr_restrict]',
			esc_attr( $user_role ),
			absint( $post_id ),
			$restrict_content
		);

		return do_shortcode( $shortcode );
	}
}
