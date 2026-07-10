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
		if ( ! function_exists( 'ur_check_module_activation' ) || ! ur_check_module_activation( 'content-restriction' ) ) {
			return '';
		}

		$d5_values        = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$user_role        = sanitize_text_field( $d5_values['userRole'] ?? 'all_logged_in_users' );
		$restrict_content = wp_kses_post( $d5_values['restrictContent'] ?? '' );
		$the_id           = get_the_ID();
		$post_id          = $the_id ? $the_id : 0;

		// selectedRoles may be a PHP array (from divi/checkboxes) or a comma-separated string.
		$selected_roles_raw = $d5_values['selectedRoles'] ?? '';
		$selected_roles_arr = is_array( $selected_roles_raw )
			? array_map( 'sanitize_text_field', $selected_roles_raw )
			: array_filter( array_map( 'trim', explode( ',', sanitize_text_field( (string) $selected_roles_raw ) ) ) );
		$selected_roles_str = implode( ',', $selected_roles_arr );

		// In the VB canvas AJAX preview context, return a lightweight preview that does not
		// inject a <style> block into the canvas DOM (which would leak styles to other modules).
		if ( wp_doing_ajax() && isset( $_POST['action'] ) && 'urm_d5_preview' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
			$label_map = array(
				'all_logged_in_users'   => 'All Logged In Users',
				'guest_users'           => 'Guest Users',
				'choose_specific_roles' => 'Selected Roles: ' . esc_html( implode( ', ', $selected_roles_arr ) ),
			);
			$label = $label_map[ $user_role ] ?? $user_role;
			return '<div style="padding:10px 14px;border:1px dashed #bbb;border-radius:4px;font-family:sans-serif;">'
				. '<p style="margin:0 0 6px;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.5px;">Visible to: ' . esc_html( $label ) . '</p>'
				. '<div>' . wp_kses_post( $restrict_content ) . '</div>'
				. '</div>';
		}

		return do_shortcode(
			'[urcr_restrict'
			. ' access_all_roles="' . esc_attr( $user_role ) . '"'
			. ' access_specific_role="' . esc_attr( $selected_roles_str ) . '"'
			. ' enable_content_restriction="true"'
			. ' access_control="access"'
			. ' post_id="' . absint( $post_id ) . '"'
			. ']'
			. $restrict_content
			. '[/urcr_restrict]'
		);
	}
}
