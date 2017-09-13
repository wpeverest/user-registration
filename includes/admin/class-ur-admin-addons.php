<?php
/**
 * Addons Page
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistration/Admin
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		if ( false === ( $sections = get_transient( 'ur_addons_sections' ) ) ) {
			$raw_sections = wp_safe_remote_get( 'https://raw.githubusercontent.com/wpeverest/extensions-json/master/user-registration/addon-section.json', array( 'user-agent' => 'UserRegistration Addons Page' ) );
			if ( ! is_wp_error( $raw_sections ) ) {
				$sections = json_decode( wp_remote_retrieve_body( $raw_sections ) );

				if ( $sections ) {
					set_transient( 'ur_addons_sections', $sections, WEEK_IN_SECONDS );
				}
			}
		}

		$addon_sections = array();

		if ( $sections ) {
			foreach ( $sections as $sections_id => $section ) {
				if ( empty( $sections_id ) ) {
					continue;
				}
				$addon_sections[ $sections_id ]           = new stdClass;
				$addon_sections[ $sections_id ]->title    = ur_clean( $section->title );
				$addon_sections[ $sections_id ]->endpoint = ur_clean( $section->endpoint );
			}
		}

		return apply_filters( 'user_registration_addons_sections', $addon_sections );
	}

	/**
	 * Get section for the addons screen.
	 *
	 * @param  string $section_id
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
	 * @param  string $section_id
	 *
	 * @return array
	 */
	public static function get_section_data( $section_id ) {
		$section      = self::get_section( $section_id );
		$section_data = '';

		if ( ! empty( $section->endpoint ) ) {
			if ( false === ( $section_data = get_transient( 'ur_addons_section_' . $section_id ) ) ) {
				$raw_section = wp_safe_remote_get( esc_url_raw( $section->endpoint ), array( 'user-agent' => 'UserRegistration Addons Page' ) );

				if ( ! is_wp_error( $raw_section ) ) {
					$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

					if ( ! empty( $section_data->products ) ) {
						set_transient( 'ur_addons_section_' . $section_id, $section_data, WEEK_IN_SECONDS );
					}
				}
			}
		}

		return apply_filters( 'user_registration_addons_section_data', $section_data->products, $section_id );
	}

	/**
	 * Handles output of the addons page in admin.
	 */
	public static function output() {
		$sections        = self::get_sections();
		$theme           = wp_get_theme();
		$section_keys    = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : current( $section_keys );
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-addons.php' );
	}
}
