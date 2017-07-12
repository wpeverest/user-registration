<?php
/**
 * UserRegistration Admin Functions
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Admin/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all UserRegistration screen ids.
 *
 * @return array
 */
function ur_get_screen_ids() {

	$ur_screen_id = sanitize_title( __( 'User Registration', 'user-registration' ) );
	$screen_ids   = array(
		'toplevel_page_' . $ur_screen_id,
		$ur_screen_id . '_page_add-new-registration',
		$ur_screen_id . '_page_user-registration-settings',
		'profile',
		'user-edit',
	);

	return apply_filters( 'user_registration_screen_ids', $screen_ids );
}

/**
 * Create a page and store the ID in an option.
 *
 * @param  mixed  $slug         Slug for the new page
 * @param  string $option       Option name to store the page's ID
 * @param  string $page_title   (default: '') Title for the new page
 * @param  string $page_content (default: '') Content for the new page
 * @param  int    $post_parent  (default: 0) Parent for the new page
 *
 * @return int page ID
 */
function ur_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && ( $page_object = get_post( $option_value ) ) ) {
		if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array(
				'pending',
				'trash',
				'future',
				'auto-draft'
			) )
		) {
			// Valid page is already in place
			return $page_object->ID;
		}
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	$valid_page_found = apply_filters( 'user_registration_create_page_id', $valid_page_found, $slug, $page_content );

	if ( $valid_page_found ) {
		if ( $option ) {
			update_option( $option, $valid_page_found );
		}

		return $valid_page_found;
	}

	// Search for a matching valid trashed page
	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $trashed_page_found ) {
		$page_id   = $trashed_page_found;
		$page_data = array(
			'ID'          => $page_id,
			'post_status' => 'publish',
		);
		wp_update_post( $page_data );
	} else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post( $page_data );
	}

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

/**
 * Output admin fields.
 *
 * Loops though the user registration options array and outputs each field.
 *
 * @param array $options Opens array to output
 */
function user_registration_admin_fields( $options ) {

	if ( ! class_exists( 'UR_Admin_Settings', false ) ) {
		include( dirname( __FILE__ ) . '/class-ur-admin-settings.php' );
	}

	UR_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @param array $options
 * @param array $data
 */
function user_registration_update_options( $options, $data = null ) {

	if ( ! class_exists( 'UR_Admin_Settings', false ) ) {
		include( dirname( __FILE__ ) . '/class-ur-admin-settings.php' );
	}

	UR_Admin_Settings::save_fields( $options, $data );
}

/**
 * Get a setting from the settings API.
 *
 * @param mixed $option_name
 * @param mixed $default
 *
 * @return string
 */
function user_registration_settings_get_option( $option_name, $default = '' ) {

	if ( ! class_exists( 'UR_Admin_Settings', false ) ) {
		include( dirname( __FILE__ ) . '/class-ur-admin-settings.php' );
	}

	return UR_Admin_Settings::get_option( $option_name, $default );
}

/**
 * @param $form_id
 */
function ur_admin_form_settings_fields( $form_id ) {

	$all_roles = ur_get_default_admin_roles();

	$arguments =
		array(
			array(
				'type'              => 'select',
				'label'             => __( 'Default user role', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_default_user_role',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => $all_roles,
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_default_user_role', 'subscriber' ),


			),
			array(
				'type'              => 'select',
				'label'             => __( 'Prevent dashboard access', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_disabled_user_roles',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => $all_roles,
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_disabled_user_roles', 'subscriber' ),


			),
			array(
				'type'              => 'select',
				'label'             => __( 'Enable strong password', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_strong_password',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array( 'yes', 'no' ),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password', 'yes' ),


			),
			array(
				'type'              => 'text',
				'label'             => __( 'Form submit button label', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_form_submit_label',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_label', 'Register' ),


			)
		);

	return $arguments;

}

/**
 * @param int $form_id
 */
function ur_admin_form_settings( $form_id = 0 ) {

	$arguments = ur_admin_form_settings_fields( $form_id );

	foreach ( $arguments as $args ) {
		user_registration_form_field( $args['id'], $args );
	}

}

/**
 * @param      $post_id
 * @param      $meta_key
 * @param null $default
 *
 * @return null
 */
function ur_get_single_post_meta( $post_id, $meta_key, $default = null ) {

	$post_meta = get_post_meta( $post_id, $meta_key );

	if ( isset( $post_meta[0] ) ) {

		return $post_meta[0];
	}

	$form_setting_meta_key = str_replace( 'form_setting', 'general_setting', $meta_key );

	$options = get_option( $form_setting_meta_key, $default );

	if ( isset( $options[0] ) && is_array( $options ) ) {

		return $options[0];
	}
	if ( '' != $options && 'string' == gettype( $options ) ) {

		return $options;
	}

	return $default;


}


/**
 * @param $setting_data
 * @param $form_id
 */
function ur_update_form_settings( $setting_data, $form_id ) {

	$remap_setting_data = array();

	foreach ( $setting_data as $setting ) {

		if ( isset( $setting['name'] ) ) {

			$remap_setting_data[ $setting['name'] ] = $setting;
		}

	}


	$setting_fields = ur_admin_form_settings_fields( $form_id );

	foreach ( $setting_fields as $field_data ) {

		if ( isset( $field_data['id'] ) && isset( $remap_setting_data[ $field_data['id'] ] ) ) {

			if ( isset( $remap_setting_data[ $field_data['id'] ]['value'] ) ) {
				update_post_meta( $form_id, $field_data['id'], $remap_setting_data[ $field_data['id'] ]['value'] );
			}

		}

	}


}
