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

	/**
	 * Import Form from backend.
	 */
	public static function import_form() {

		// Check for $_FILES set or not.
		if ( isset( $_FILES['jsonfile'] ) ) {

			$filename = esc_html( sanitize_text_field( $_FILES['jsonfile']['name'] ) ); // Get file name.
			$ext      = pathinfo( $filename, PATHINFO_EXTENSION ); // Get file extention.

			// Check for file format.
			if ( 'json' === $ext ) {

				// read json file.
				$form_data = json_decode( file_get_contents( $_FILES['jsonfile']['tmp_name'] ) ); // @codingStandardsIgnoreLine

				// check for non empty json file.
				if ( ! empty( $form_data ) ) {

					// check for non empty post data array.
					if ( ! empty( $form_data->form_post ) ) {

						// If Form Title already exist concat it with imported tag.
						$args  = array( 'post_type' => 'user_registration' );
						$forms = get_posts( $args );
						foreach ( $forms as $key => $form_obj ) {
							if ( $form_data->form_post->post_title === $form_obj->post_title ) {
								$form_data->form_post->post_title = $form_data->form_post->post_title . ' (Imported)';
								break;
							}
						}

						$post_id = wp_insert_post( $form_data->form_post );

						// Check for any error while inserting.
						if ( is_wp_error( $post_id ) ) {
							return $post_id;
						}
						if ( $post_id ) {

							// check for non empty post_meta array.
							if ( ! empty( $form_data->form_post_meta ) ) {
								$all_roles = ur_get_default_admin_roles();

								foreach ( $form_data->form_post_meta  as $meta_key => $meta_value ) {

									// If user role does not exists in new site then set default as subscriber.
									if ( 'user_registration_form_setting_default_user_role' === $meta_key ) {
										$meta_value = array_key_exists( $meta_value, $all_roles ) ? $meta_value : 'subscriber';
									}
									add_post_meta( $post_id, $meta_key, $meta_value );
								}
								wp_send_json_success(
									array(
										'message' => __( 'Imported Successfully.', 'user-registration' ),
									)
								);
							}
						}
					} else {
						wp_send_json_error(
							array(
								'message' => __( 'Invalid file content. Please export file from user registration plugin.', 'user-registration' ),
							)
						);
					}
				} else {
					wp_send_json_error(
						array(
							'message' => __( 'Invalid file content. Please export file from user registration plugin.', 'user-registration' ),
						)
					);
				}
			} else {
				wp_send_json_error(
					array(
						'message' => __( 'Invalid file format. Only Json File Allowed.', 'user-registration' ),
					)
				);
			}
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Please select json file to import form data.', 'user-registration' ),
				)
			);
		}
	}
}

new UR_Admin_Import_Export_Forms();
