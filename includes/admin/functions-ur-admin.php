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
	exit; // Exit if accessed directly.
}

add_action( 'wp_dashboard_setup', 'ur_add_dashboard_widget' );

/**
 * Register the user registration user activity dashboard widget.
 *
 * @since 1.5.8
 */
function ur_add_dashboard_widget() {

	if ( ! current_user_can( 'manage_user_registration' ) ) {
		return;
	}

	wp_add_dashboard_widget( 'user_registration_dashboard_status', __( 'User Registration Activity', 'user-registration' ), 'ur_status_widget' );
}

/**
 * Content to the user_registration_dashboard_status widget.
 *
 * @since 1.5.8
 */
function ur_status_widget() {

	wp_enqueue_script( 'user-registration-dashboard-widget-js' );
	wp_localize_script(
		'user-registration-dashboard-widget-js',
		'ur_widget_params',
		array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'loading'      => __( 'loading...', 'user-registration' ),
			'widget_nonce' => wp_create_nonce( 'dashboard-widget' ),
		)
	);

	ur_get_template( 'dashboard-widget.php' );
}

/**
 * Report for the user registration activity.
 *
 * @return array
 */
function ur_get_user_report( $form_id ) {
	$current_date     = current_time( 'Y-m-d' );
	$users            = get_users(
		array(
			'meta_key' => 'ur_form_id',
		)
	);
	$total_users      = 0;
	$today_users      = 0;
	$last_week_users  = 0;
	$last_month_users = 0;

	foreach ( $users as $user ) {
		$user_registered = date_i18n( 'Y-m-d', strtotime( $user->data->user_registered ) );
		$user_form       = get_user_meta( $user->ID, 'ur_form_id', true );

		if ( (int) $form_id === (int) $user_form ) {

			// Count today users.
			if ( $user_registered === $current_date ) {
				$today_users++;
			}

			// Get last week date.
			$last_week = strtotime( 'now' ) - WEEK_IN_SECONDS;
			$last_week = date_i18n( 'Y-m-d', $last_week );

			// Get last month date.
			$last_month = strtotime( 'now' ) - MONTH_IN_SECONDS;
			$last_month = date_i18n( 'Y-m-d', $last_month );

			// Get last week users count.
			if ( $user_registered > $last_week ) {
				$last_week_users++;
			}

			// Get last month users count.
			if ( $user_registered > $last_month ) {
				$last_month_users++;
			}

			$total_users++; // Total users of selected form.
		}
	}

	$report = array(
		'total_users'      => $total_users,
		'today_users'      => $today_users,
		'last_week_users'  => $last_week_users,
		'last_month_users' => $last_month_users,
	);

	return $report;
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
		$ur_screen_id . '_page_user-registration-dashboard',
		$ur_screen_id . '_page_add-new-registration',
		$ur_screen_id . '_page_user-registration-settings',
		$ur_screen_id . '_page_user-registration-mailchimp',
		$ur_screen_id . '_page_user-registration-status',
		$ur_screen_id . '_page_user-registration-addons',
		$ur_screen_id . '_page_user-registration-export-users',
		$ur_screen_id . '_page_user-registration-email-templates',
		'profile',
		'user-edit',
		
	);

	return apply_filters( 'user_registration_screen_ids', $screen_ids );
}

// Hook into exporter and eraser tool.
add_filter( 'wp_privacy_personal_data_exporters', 'user_registration_register_data_exporter', 10 );
add_filter( 'wp_privacy_personal_data_erasers', 'user_registration_register_data_eraser' );

/**
 * Add user registration data to exporters
 *
 * @param  array $exporters
 * @return array
 */
function user_registration_register_data_exporter( $exporters ) {

	$exporters['user-registration'] = array(
		'exporter_friendly_name' => __( 'User Extra Information', 'user-registration' ),
		'callback'               => 'user_registration_data_exporter',
	);

	return $exporters;
}

/**
 * Get user registration data to export.
 *
 * @param  string  $email_address user's email address
 * @param  integer $page
 * @return array exporting data
 */
function user_registration_data_exporter( $email_address, $page = 1 ) {

	global $wpdb;

	$form_data = array();
	$posts     = get_posts( 'post_type=user_registration' );

	// Get array of field name label mapping of user registration fields.
	foreach ( $posts as $post ) {
		$post_content       = isset( $post->post_content ) ? $post->post_content : '';
		$post_content_array = json_decode( $post_content );
		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					if ( isset( $field->field_key ) && isset( $field->general_setting->field_name ) ) {
						$form_data[ $field->general_setting->field_name ] = $field->general_setting->label;
					}
				}
			}
		}
	}

	$user     = get_user_by( 'email', $email_address );
	$user_id  = isset( $user->ID ) ? $user->ID : 0;
	$usermeta = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%' AND user_id = " . $user_id . ' ;' );

	$export_items = array();
	if ( $usermeta && is_array( $usermeta ) ) {

		foreach ( $usermeta as $meta ) {
			$strip_prefix = substr( $meta->meta_key, 18 );

			if ( array_key_exists( $strip_prefix, $form_data ) ) {

				if ( is_serialized( $meta->meta_value ) ) {
					$meta->meta_value = unserialize( $meta->meta_value );
					$meta->meta_value = implode( ',', $meta->meta_value );
				}

				$data[] =
					array(
						'name'  => $form_data[ $strip_prefix ],
						'value' => $meta->meta_value,
					);
			}
		}

		$export_items[] = array(
			'group_id'    => 'user-registration',
			'group_label' => __( 'User Extra Information', 'user-registration' ),
			'item_id'     => "user-registration-{$meta->umeta_id}",
			'data'        => $data,
		);
	}

	return array(
		'data' => $export_items,
		'done' => true,
	);
}

/**
 * Add user registration data to the eraser tool.
 *
 * @param  array $erasers
 * @return array
 */
function user_registration_register_data_eraser( $erasers = array() ) {
	$erasers['user-registration'] = array(
		'eraser_friendly_name' => __( 'WordPress User Extra Information', 'user-registration' ),
		'callback'             => 'user_registration_data_eraser',
	);
	return $erasers;
}

/**
 * Get user registration data to erase
 *
 * @param  string  $email_address user's email address
 * @param  integer $page          [description]
 * @return array
 */
function user_registration_data_eraser( $email_address, $page = 1 ) {

	global $wpdb;

	if ( empty( $email_address ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$user = get_user_by( 'email', $email_address );

	$messages       = array();
	$items_removed  = false;
	$items_retained = false;

	if ( $user && $user->ID ) {
		$user_id         = $user->ID;
		$delete_usermeta = $wpdb->get_results( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%' AND user_id = " . $user_id . ' ;' );

		$delete_form_data = $wpdb->get_results( "DELETE FROM $wpdb->usermeta WHERE meta_key = 'ur_form_id' AND user_id = " . $user_id . ' ;' );

		if ( $delete_usermeta && $delete_form_data ) {
			$items_removed = true;
		}
	}

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => $items_retained,
		'messages'       => $messages,
		'done'           => true,
	);
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
		if ( 'page' === $page_object->post_type && ! in_array(
			$page_object->post_status,
			array(
				'pending',
				'trash',
				'future',
				'auto-draft',
			)
		)
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
		include dirname( __FILE__ ) . '/class-ur-admin-settings.php';
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
		include dirname( __FILE__ ) . '/class-ur-admin-settings.php';
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
		include dirname( __FILE__ ) . '/class-ur-admin-settings.php';
	}

	return UR_Admin_Settings::get_option( $option_name, $default );
}

/**
 * General settings area display
 *
 * @param int $form_id Form ID.
 */
function ur_admin_form_settings( $form_id = 0 ) {

	echo '<div id="general-settings" ><h3>' . esc_html__( 'General Settings', 'user-registration' ) . '</h3>';

	$arguments = ur_admin_form_settings_fields( $form_id );

	foreach ( $arguments as $args ) {
		user_registration_form_field( $args['id'], $args );
	}

	echo '</div>';
}

/**
 * Update Settings of the form.
 *
 * @param array $setting_data Settings data in name value array pair
 * @param int   $form_id      Form ID.
 */
function ur_update_form_settings( $setting_data, $form_id ) {
	$remap_setting_data = array();

	$setting_data = ur_format_setting_data( $setting_data );
	foreach ( $setting_data as $setting ) {

		if ( isset( $setting['name'] ) ) {

			if ( '[]' === substr( $setting['name'], -2 ) ) {
				$setting['name'] = substr( $setting['name'], 0, -2 );
			}

			$remap_setting_data[ $setting['name'] ] = $setting;
		}
	}

	$setting_fields = apply_filters( 'user_registration_form_settings_save', ur_admin_form_settings_fields( $form_id ), $form_id );

	foreach ( $setting_fields as $field_data ) {
		if ( isset( $field_data['id'] ) && isset( $remap_setting_data[ $field_data['id'] ] ) ) {

			if ( isset( $remap_setting_data[ $field_data['id'] ]['value'] ) ) {

				// Check if any settings value contains array
				if ( is_array( $remap_setting_data[ $field_data['id'] ]['value'] ) ) {
					$remap_setting_data[ $field_data['id'] ]['value'] = array_map( 'sanitize_text_field', $remap_setting_data[ $field_data['id'] ]['value'] );
					$remap_setting_data[ $field_data['id'] ]['value'] = maybe_serialize( $remap_setting_data[ $field_data['id'] ]['value'] );
				} else {
					$remap_setting_data[ $field_data['id'] ]['value'] = sanitize_text_field( $remap_setting_data[ $field_data['id'] ]['value'] );
				}

				update_post_meta( $form_id, $field_data['id'], $remap_setting_data[ $field_data['id'] ]['value'] );
			}
		} else {
				// Update post meta if any setting value is not set for field data id.
				update_post_meta( $form_id, $field_data['id'], '' );
		}
	}
}

/**
 * Format settings data for same name. e.g. multiselect
 * Encloses all values in array for same name in settings.
 *
 * @param   array $setting_data unformatted settings data.
 * @return  array $settings     formatted settings data.
 */
function ur_format_setting_data( $setting_data ) {

	$key_value = array();
	foreach ( $setting_data as $value ) {

		if ( array_key_exists( $value['name'], $key_value ) ) {
			$value_array = array();

			if ( is_array( $key_value[ $value['name'] ] ) ) {

				$value_array                 = $key_value[ $value['name'] ];
				$value_array[]               = $value['value'];
				$key_value[ $value['name'] ] = $value_array;
			} else {
				$value_array[]               = $key_value[ $value['name'] ];
				$value_array[]               = $value['value'];
				$key_value[ $value['name'] ] = $value_array;
			}
		} else {
			$key_value[ $value['name'] ] = $value['value'];
		}
	}

	$settings = array();
	foreach ( $key_value as $key => $value ) {
		$settings[] = array(
			'name'  => $key,
			'value' => $value,
		);
	}

	return $settings;
}

/**
 * Check for plugin activation date.
 *
 * True if user registration has been installed 30 days ago.
 *
 * @since 1.5.8
 *
 * @return bool
 */
function ur_check_activation_date() {

	// Plugin Activation Time.
	$activation_date = get_option( 'user_registration_activated' );
	$last_month      = strtotime( 'now' ) - MONTH_IN_SECONDS;
	$last_month      = date_i18n( 'Y-m-d', $last_month );

	if ( ! empty( $activation_date ) ) {
		if ( $activation_date < $last_month ) {
			return true;
		}
	}

	return false;
}
