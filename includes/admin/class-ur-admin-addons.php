<?php
/**
 * Addons Page
 *
 * @package  UserRegistration/Admin
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin_Addons Class.
 */
class UR_Admin_Addons {

	/**
	 * Get sections for the addons screen
	 *
	 * @return array of objects
	 */
	public static function get_sections() {

		$raw_sections   = ur_file_get_contents( '/assets/extensions-json/addon-section.json' );
		$addon_sections = array();

		if ( ! is_wp_error( $raw_sections ) ) {
			$sections = json_decode( $raw_sections );

			if ( $sections ) {
				foreach ( $sections as $sections_id => $section ) {
					if ( empty( $sections_id ) ) {
						continue;
					}
					$addon_sections[ $sections_id ]           = new stdClass();
					$addon_sections[ $sections_id ]->title    = ur_clean( $section->title );
					$addon_sections[ $sections_id ]->endpoint = ur_clean( $section->endpoint );
				}
			}
		}

		/**
		 * Filter the addons section
		 *
		 * @param array $addon_sections Section of Addons
		 */
		return apply_filters( 'user_registration_addons_sections', $addon_sections );
	}

	/**
	 * Get section for the addons screen.
	 *
	 * @param  string $section_id Section Id.
	 *
	 * @return object|bool
	 */
	public static function get_section( $section_id ) {
		$sections = self::get_sections();
		if ( isset( $sections[ $section_id ] ) ) {
			return $sections[ $section_id ];
		}

		return false;
	}

	/**
	 * Get section content for the addons screen.
	 *
	 * @param  string $section_id Section Id.
	 *
	 * @return array
	 */
	public static function get_section_data( $section_id ) {

		$section      = self::get_section( $section_id );
		$section_data = '';

		if ( ! empty( $section->endpoint ) ) {
			$section_data = get_transient( 'ur_addons_section_' . $section_id );

			$raw_section = ur_file_get_contents( '/assets/' . $section->endpoint );

			if ( ! is_wp_error( $raw_section ) ) {
				$section_data = json_decode( $raw_section );
			}
		}

		/**
		 * Filter the addons section data
		 *
		 * @param array $section_data->products Products from Section Data
		 * @param int $section_id Section Id
		 */
		return apply_filters( 'user_registration_addons_section_data', $section_data->products, $section_id );
	}
}
