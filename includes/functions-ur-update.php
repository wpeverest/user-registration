<?php
/**
 * UserRegistration Updates
 *
 * Function for updating data, used by the background updater.
 *
 * @package UserRegistration\Functions
 * @version 1.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update DB Version.
 */
function ur_update_100_db_version() {
	UR_Install::update_db_version( '1.0.0' );
}

/**
 * Update usermeta.
 */
function ur_update_120_usermeta() {
	global $wpdb;

	// Get usermeta.
	$usermeta = $wpdb->get_results( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%';" );

	// Update old usermeta values.
	foreach ( $usermeta as $metadata ) {
		$user_id     = intval( $metadata->user_id );
		$json_val    = json_decode( $metadata->meta_value );
		$explode_val = explode( '__', $metadata->meta_value );

		if ( $json_val && $metadata->meta_value != $json_val ) {
			update_user_meta( $user_id, $metadata->meta_key, json_decode( $metadata->meta_value ) );
		} elseif ( $metadata->meta_value !== end( $explode_val ) ) {
			update_user_meta( $user_id, $metadata->meta_key, trim( end( $explode_val ) ) );
		}
	}

	// Delete old user keys from usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'ur_%_params';" );
}

/**
 * Update DB Version.
 */
function ur_update_120_db_version() {
	UR_Install::update_db_version( '1.2.0' );
}

/**
 * Update usermeta.
 */
function ur_update_125_usermeta() {

	$users = get_users( array( 'fields' => array( 'ID' ) ) );

	foreach ( $users as $user_id ) {

		if ( metadata_exists( 'user', $user_id->ID, 'user_registration_user_first_name' ) ) {
			$first_name = get_user_meta( $user_id->ID, 'user_registration_user_first_name', true );
			update_user_meta( $user_id->ID, 'first_name', $first_name );
			delete_user_meta( $user_id->ID, 'user_registration_user_first_name' );
		}

		if ( metadata_exists( 'user', $user_id->ID, 'user_registration_user_last_name' ) ) {
			$last_name = get_user_meta( $user_id->ID, 'user_registration_user_last_name', true );
			update_user_meta( $user_id->ID, 'last_name', $last_name );
			delete_user_meta( $user_id->ID, 'user_registration_user_last_name' );
		}

		if ( metadata_exists( 'user', $user_id->ID, 'user_registration_user_description' ) ) {
			$description = get_user_meta( $user_id->ID, 'user_registration_user_description', true );
			update_user_meta( $user_id->ID, 'description', $description );
			delete_user_meta( $user_id->ID, 'user_registration_user_description' );
		}

		if ( metadata_exists( 'user', $user_id->ID, 'user_registration_user_nickname' ) ) {
			$nickname = get_user_meta( $user_id->ID, 'user_registration_user_nickname', true );
			update_user_meta( $user_id->ID, 'nickname', $nickname );
			delete_user_meta( $user_id->ID, 'user_registration_user_nickname' );
		}
	}
}
/**
 * Update DB Version.
 */
function ur_update_125_db_version() {
	UR_Install::update_db_version( '1.2.5' );
}

/**
 * Update DB Version.
 */
function ur_update_130_db_version() {
	UR_Install::update_db_version( '1.3.0' );
}

/**
 * Update usermeta.
 */
function ur_update_130_post() {
	$posts = get_posts( 'post_type=user_registration' );
	foreach ( $posts as $post ) {
		$post_content       = isset( $post->post_content ) ? $post->post_content : '';
		$post_content_array = json_decode( $post_content );

		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {

					if ( isset( $field->field_key ) && isset( $field->general_setting->field_name ) ) {
						switch ( $field->field_key ) {
							case 'user_username':
								$field->general_setting->field_name = $field->field_key = 'user_login';
								break;
							case 'user_password':
								$field->general_setting->field_name = $field->field_key  = 'user_pass';
								break;
							case 'user_display_name':
								$field->general_setting->field_name = $field->field_key = 'display_name';
								break;
							case 'user_description':
								$field->general_setting->field_name = $field->field_key = 'description';
								break;
							case 'user_first_name':
								$field->general_setting->field_name = $field->field_key = 'first_name';
								break;
							case 'user_last_name':
								$field->general_setting->field_name = $field->field_key = 'last_name';
								break;
							case 'user_nickname':
								$field->general_setting->field_name = $field->field_key = 'nickname';
								break;
						}
					}
				}
			}
			$post_content       = json_encode( $post_content_array );
			$post->post_content = $post_content;
		}
		wp_update_post( $post );
	}

	$mailchimp_settings = get_option( 'urmc_mailchimp_settings' );

	if ( $mailchimp_settings && is_array( $mailchimp_settings ) ) {

		if ( isset( $mailchimp_settings['data'] ) && is_array( $mailchimp_settings['data'] ) ) {

			foreach ( $mailchimp_settings['data'] as $id => $mailchimp_data ) {

				if ( isset( $mailchimp_data['fields'] ) ) {

					foreach ( $mailchimp_data['fields'] as $key => $field ) {

						switch ( $field ) {
							case 'user_username':
								$mailchimp_data['fields'][ $key ] = 'user_login';
								break;
								$mailchimp_data['fields'][ $key ] = 'user_pass';
								break;
							case 'user_display_name':
								$mailchimp_data['fields'][ $key ] = 'display_name';
								break;
							case 'user_description':
								$mailchimp_data['fields'][ $key ] = 'description';
								break;
							case 'user_first_name':
								$mailchimp_data['fields'][ $key ] = 'first_name';
								break;
							case 'user_last_name':
								$mailchimp_data['fields'][ $key ] = 'last_name';
								break;
							case 'user_nickname':
								$mailchimp_data['fields'][ $key ] = 'nickname';
								break;
						}
					}
				}
				$mailchimp_settings['data'][ $id ] = $mailchimp_data;
			}
		}
		update_option( 'urmc_mailchimp_settings', $mailchimp_settings );
	}
}

/**
 * Update DB Version.
 */
function ur_update_140_db_version() {
	UR_Install::update_db_version( '1.4.0' );
}

/**
 * Delete unused option.
 */
function ur_update_140_option() {
	$unused_options = array(
		'user_registration_general_setting_default_user_role',
		'user_registration_general_setting_enable_strong_password',
		'user_registration_general_setting_form_submit_label',
	);

	foreach ( $unused_options as $unused_option ) {
		delete_option( $unused_option );
	}
}

/**
 * Update DB Version.
 */
function ur_update_142_db_version() {
	UR_Install::update_db_version( '1.4.2' );
}

/**
 * Replace option name user_registration_myaccount_edit_account_endpoint to user_registration_myaccount_change_password_endpoint.
 */
function ur_update_142_option() {
	$value = get_option( 'user_registration_myaccount_edit_account_endpoint' );
	update_option( 'user_registration_myaccount_change_password_endpoint', $value );
	delete_option( 'user_registration_myaccount_edit_account_endpoint' );
}

/**
 * Update DB Version.
 */
function ur_update_1581_db_version() {
	UR_Install::update_db_version( '1.5.8.1' );
}

/**
 * Update DB Version.
 */
function ur_update_160_db_version() {
	UR_Install::update_db_version( '1.6.0' );
}

/**
 * Replace user meta key profile_pic_id to user_registration_profile_pic_id.
 *
 * @since 1.4.8.1
 *
 * @return void.
 */
function ur_update_1581_meta_key() {
	$users = get_users(
		array(
			'meta_key' => 'profile_pic_id',
		)
	);

	foreach ( $users as $user ) {
		$profile_picture_id = get_user_meta( $user->ID, 'profile_pic_id', true );
		update_user_meta( $user->ID, 'user_registration_profile_pic_id', $profile_picture_id );
		delete_user_meta( $user->ID, 'profile_pic_id' );
	}

	// Change ur_ prefix to user_registration_ for review notice skipped option.
	$value = get_option( 'ur_review_notice_dismissed' );
	update_option( 'user_registration_review_notice_dismissed', $value );
	delete_option( 'ur_review_notice_dismissed' );
}

/**
 * Migrate the redirect option from global settings to form-wise settings.
 *
 * @since  1.6.0
 *
 * @return void.
 */
function ur_update_160_option_migrate() {
	$redirect_url = get_option( 'user_registration_general_setting_redirect_options' );

	// Get all posts with user_registration post type.
	$posts = get_posts( 'post_type=user_registration' );

	foreach ( $posts as $post ) {

		// Update global setting to all user registration posts meta.
		update_post_meta( $post->ID, 'user_registration_form_setting_redirect_options', $redirect_url );
	}
}

/**
 * Update DB Version.
 */
function ur_update_162_db_version() {
	UR_Install::update_db_version( '1.6.2' );
}

/**
 * Replace user meta key profile_pic_id to user_registration_profile_pic_id.
 *
 * @since 1.4.8.1
 *
 * @return void.
 */
function ur_update_162_meta_key() {
	$users = get_users(
		array(
			'meta_key' => 'user_registration_profile_pic_id',
		)
	);

	foreach ( $users as $user ) {
		$profile_picture_id = get_user_meta( $user->ID, 'user_registration_profile_pic_id', true );
		if ( $profile_picture_id ) {
			$profile_picture_url = wp_get_attachment_thumb_url( $profile_picture_id );
			update_user_meta( $user->ID, 'user_registration_profile_pic_url', $profile_picture_url );
		}
		delete_user_meta( $user->ID, 'user_registration_profile_pic_id' );
	}

	// Delete Redirect options form general setting as previous version refered to do so.
	delete_option( 'user_registration_general_setting_redirect_options' );
}


/**
 * Set Redirect after Registration option in form settings.
 * Change Integration to Captcha Option names.
 *
 * @return void
 */
function ur_update_30_option_migrate() {

	// Get all posts with user_registration post type.
	$posts = get_posts( 'post_type=user_registration' );

	foreach ( $posts as $post ) {

		$redirect_url = ur_get_single_post_meta( $post->ID, 'user_registration_form_setting_redirect_options', get_option( 'user_registration_general_setting_redirect_options', '' ) );

		if ( ! empty( $redirect_url ) ) {
			update_post_meta( $post->ID, 'user_registration_form_setting_redirect_after_registration', 'external-url' );
		}
	}

	// Recaptcha Migrations.
	$recaptcha_type      			= get_option( 'user_registration_integration_setting_recaptcha_version', 'v2' );
	$invisible_recaptcha 			= get_option( 'user_registration_integration_setting_invisible_recaptcha_v2', 'no' );
	$recaptcha_invisible_site_key   = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_key' );
	$recaptcha_invisible_secret_key = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_secret' );
	$recaptcha_site_key   			= get_option( 'user_registration_integration_setting_recaptcha_site_key' );
	$recaptcha_secret_key 			= get_option( 'user_registration_integration_setting_recaptcha_site_secret' );
	$recaptcha_site_key_v3   		= get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
	$recaptcha_site_secret_v3 		= get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' );
	$recaptcha_threshold_score_v3 	= get_option( 'user_registration_integration_setting_recaptcha_threshold_score_v3' );
	$site_key_hcaptcha   			= get_option( 'user_registration_integration_setting_recaptcha_site_key_hcaptcha' );
	$site_secret_hcaptcha 			= get_option( 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha' );

	update_option( 'user_registration_captcha_setting_recaptcha_version', $recaptcha_type );
	update_option( 'user_registration_captcha_setting_invisible_recaptcha_v2', $invisible_recaptcha );
	update_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key', $recaptcha_invisible_site_key );
	update_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret', $recaptcha_invisible_secret_key );
	update_option( 'user_registration_captcha_setting_recaptcha_site_key', $recaptcha_site_key );
	update_option( 'user_registration_captcha_setting_recaptcha_site_secret', $recaptcha_secret_key );
	update_option( 'user_registration_captcha_setting_recaptcha_site_key_v3', $recaptcha_site_key_v3 );
	update_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3', $recaptcha_site_secret_v3 );
	update_option( 'user_registration_captcha_setting_recaptcha_threshold_score_v3', $recaptcha_threshold_score_v3 );
	update_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha', $site_key_hcaptcha );
	update_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha', $site_secret_hcaptcha );

	delete_option( 'user_registration_integration_setting_recaptcha_version' );
	delete_option( 'user_registration_integration_setting_invisible_recaptcha_v2' );
	delete_option( 'user_registration_integration_setting_recaptcha_invisible_site_key' );
	delete_option( 'user_registration_integration_setting_recaptcha_invisible_site_secret' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_key' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_secret' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' );
	delete_option( 'user_registration_integration_setting_recaptcha_threshold_score_v3' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_key_hcaptcha' );
	delete_option( 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha' );
}
