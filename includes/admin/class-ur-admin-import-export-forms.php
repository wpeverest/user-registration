<?php
/**
 * Import / Export Forms
 *
 * @package  UserRegistration/Admin
 * @since    1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin_Import_Export_Forms Class.
 */
class UR_Admin_Import_Export_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'export_json' ) );
	}

	/**
	 * Outputs Export Users Page
	 *
	 * @return void
	 */
	public static function output() {
		$all_forms = ur_get_all_user_registration_form();
		include_once dirname( __FILE__ ) . '/views/html-admin-page-import-export-forms.php';
	}

	/**
	 * Exports form data along with settings in JSON format.
	 *
	 * @return void
	 */
	public function export_json() {

		global $wpdb;

		// Check for non empty $_POST.
		if ( ! isset( $_POST['user_registration_export_form'] ) ) {
			return;
		}

		// Nonce check.
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-registration-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
		}

		$form_id = isset( $_POST['formid'] ) ? $_POST['formid'] : 0;

		// Return if form id is not set and current user doesnot have export capability.
		if ( ! isset( $form_id ) || ! current_user_can( 'export' ) ) {
			return;
		}

		$form_post       = get_post( $form_id );
		$meta_key_prefix = 'user_registration';
		$form_post_meta  = $this->get_post_meta_by_prefix( $form_id, $meta_key_prefix );

		$export_data = array(
			'form_post'      => array(
				'post_content' => $form_post->post_content,
				'post_title'   => $form_post->post_title,
				'post_name'    => $form_post->post_name,
				'post_type'    => $form_post->post_type,
				'post_status'  => $form_post->post_status,
			),
			'form_post_meta' => (array) $form_post_meta,
		);

		$form_name = strtolower( str_replace( ' ', '-', get_the_title( $form_id ) ) );
		$file_name = $form_name . '-' . current_time( 'Y-m-d_H:i:s' ) . '.json';

		if ( ob_get_contents() ) {
			ob_clean();
		}

		$export_json = wp_json_encode( $export_data );
		// Force download.
		header( 'Content-Type: application/force-download' );

		// Disposition / Encoding on response body.
		header( "Content-Disposition: attachment;filename={$file_name}" );
		header( 'Content-type: application/json' );

		echo $export_json; // phpcs:ignore WordPress.Security.EscapeOutput
		exit();
	}


	/**
	 * Get post meta for a given key prefix.
	 *
	 * @param int    $post_id User ID of the user being edited.
	 * @param string $key_prefix Prefix.
	 * @return array
	 */
	protected function get_post_meta_by_prefix( $post_id, $key_prefix ) {

		$values        = get_post_meta( $post_id );
		$return_values = array();

		if ( gettype( $values ) !== 'array' ) {
			return $return_values;
		}

		foreach ( $values as $meta_key => $value ) {
			if ( substr( $meta_key, 0, strlen( $key_prefix ) ) === $key_prefix ) {
				if ( isset( $value[0] ) ) {
					$return_values[ $meta_key ] = $value[0];
				} elseif ( 'string' === gettype( $values ) ) {
					$return_values[ $meta_key ] = $value;
				}
			}
		}

		return $return_values;
	}

}

new UR_Admin_Import_Export_Forms();
