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
	
	foreach( $users as $user_id ) {

		if( metadata_exists( 'user', $user_id->ID, 'user_registration_user_first_name' ) ) {
			$first_name = get_user_meta ( $user_id->ID, 'user_registration_user_first_name', true );
			update_user_meta ( $user_id->ID, 'first_name', $first_name );
			delete_user_meta( $user_id->ID, 'user_registration_user_first_name');
		}

		if( metadata_exists( 'user', $user_id->ID, 'user_registration_user_last_name' ) ) {
			$last_name = get_user_meta ( $user_id->ID, 'user_registration_user_last_name', true );
			update_user_meta ( $user_id->ID, 'last_name', $last_name );
			delete_user_meta( $user_id->ID, 'user_registration_user_last_name');
		}
		
		if( metadata_exists( 'user', $user_id->ID, 'user_registration_user_description' ) ) {
			$description = get_user_meta ( $user_id->ID, 'user_registration_user_description', true );
			update_user_meta ( $user_id->ID, 'description', $description );
			delete_user_meta( $user_id->ID, 'user_registration_user_description');
		}

		if( metadata_exists( 'user', $user_id->ID, 'user_registration_user_nickname' ) ) {
			$nickname = get_user_meta ( $user_id->ID, 'user_registration_user_nickname', true );
			update_user_meta ( $user_id->ID, 'nickname', $nickname );
			delete_user_meta( $user_id->ID, 'user_registration_user_nickname');
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
	foreach( $posts as $post ) {
		$post_content       = isset( $post->post_content ) ? $post->post_content : '';
		$post_content_array = json_decode( $post_content );

		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {

					if( isset( $field->field_key ) && isset( $field->general_setting->field_name ) ) {
						switch( $field->field_key ) {
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
			$post_content = json_encode( $post_content_array ); 
			$post->post_content = $post_content;				
		}
		wp_update_post( $post );
	}

	$mailchimp_settings = get_option( 'urmc_mailchimp_settings' );

	 if( $mailchimp_settings && is_array( $mailchimp_settings ) ) {
	 
		if( isset( $mailchimp_settings['data'] ) && is_array( $mailchimp_settings['data'] ) ) {

	 		foreach( $mailchimp_settings['data'] as $id =>  $mailchimp_data ) {
	 			
				if( isset( $mailchimp_data['fields'] ) ) {
				
					foreach( $mailchimp_data['fields'] as $key => $field ) {

						switch( $field ) {
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
	 			$mailchimp_settings['data'][ $id ] =  $mailchimp_data ;
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

	foreach( $unused_options as $unused_option ) {
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