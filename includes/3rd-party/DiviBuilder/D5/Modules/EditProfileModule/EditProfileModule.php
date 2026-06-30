<?php
/**
 * DiviBuilder D5: Edit Profile Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\EditProfileModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Edit Profile module.
 *
 * @since xx.xx.xx
 */
class EditProfileModule implements DependencyInterface {

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
		return \UR_Shortcodes::edit_profile( array() );
	}
}
