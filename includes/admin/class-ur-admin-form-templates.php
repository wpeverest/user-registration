<?php
/**
 * UserRegistration From Templates
 *
 * @package  UserRegistration/Admin/From Templates
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * UR_Admin_Form_Templates class
 */
class UR_Admin_Form_Templates {

	/**
	 * Get default template.
	 *
	 * @return array
	 */
	private static function get_default_template() {
		$template        = new stdClass();
		$template->title = __( 'Start From Scratch', 'user-registration' );
		$template->slug  = 'blank';
		$template->image = untrailingslashit( plugin_dir_url( UR_PLUGIN_FILE ) ) . '/assets/images/templates/blank.png';
		$template->plan  = array( 'free' );

		return array( $template );
	}

	/**
	 * Get section content for the template screen.
	 *
	 * @return array
	 */
	public static function get_template_data() {
		$template_data = get_transient( 'ur_template_section_list' );

		$template_url = 'https://d13ue4sfmuf7fw.cloudfront.net/';

		if ( false === $template_data ) {

			$template_json_url = $template_url . 'templates.json';
			try {
				$content       = wp_remote_get( $template_json_url );
				$content_json  = wp_remote_retrieve_body( $content );
				$template_data = json_decode( $content_json );
			} catch ( Exception $e ) {
				$e->getMessage();
			}

			// Removing directory so the templates can be reinitialized.
			$folder_path = untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) . '/assets/images/templates' );
			if ( isset( $template_data->templates ) ) {

				foreach ( $template_data->templates as $template_tuple ) {

					$image_url = isset( $template_tuple->image ) ? $template_tuple->image : ( $template_url . 'images/' . $template_tuple->slug . '.png' );

					$template_tuple->image = $image_url;

					$temp_name     = explode( '/', $image_url );
					$relative_path = $folder_path . '/' . end( $temp_name );
					$exists        = file_exists( $relative_path );

					// If it exists, utilize this file instead of remote file.
					if ( $exists ) {
						$template_tuple->image = untrailingslashit( plugin_dir_url( UR_PLUGIN_FILE ) ) . '/assets/images/templates/' . untrailingslashit( $template_tuple->slug ) . '.png';
					}
				}

				set_transient( 'ur_template_section_list', $template_data, WEEK_IN_SECONDS );
			}
		}

		/**
		 * Filter the Template section data
		 *
		 * @param array $template_data->templates templates data
		 */
		return isset( $template_data->templates ) ? apply_filters( 'user_registration_template_section_data', $template_data->templates ) : self::get_default_template();
	}

	/**
	 * Load the template view.
	 */
	public static function load_template_view() {
		$templates       = array();
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '_all'; // phpcs:ignore WordPress.Security.NonceVerification
		$category        = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'free'; // phpcs:ignore WordPress.Security.NonceVerification
		$templates       = self::get_template_data();

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'ur-form-templates' );
		wp_localize_script(
			'ur-form-templates',
			'ur_templates',
			array(
				'ur_template_all'  => self::get_template_data(),
				'i18n_get_started' => esc_html__( 'Get Started', 'user-registration' ),
				'i18n_get_preview' => esc_html__( 'Preview', 'user-registration' ),
				'i18n_pro_feature' => esc_html__( 'Pro', 'user-registration' ),
				'template_refresh' => esc_html__( 'Updating Templates', 'user-registration' ),
				'ur_plugin_url'    => esc_url( UR()->plugin_url() ),
			)
		);

		// Forms template area.
		include_once dirname( __FILE__ ) . '/views/html-admin-page-form-templates.php';
	}
}
