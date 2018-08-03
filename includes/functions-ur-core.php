<?php
/**
 * UserRegistration Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @package UserRegistration/Functions
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Include core functions (available in both admin and frontend).
include( UR_ABSPATH . 'includes/functions-ur-page.php' );
include( UR_ABSPATH . 'includes/functions-ur-account.php' );
include( UR_ABSPATH . 'includes/functions-ur-deprecated.php' );

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param string $value Value.
 */
function ur_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

if ( ! function_exists( 'is_ur_endpoint_url' ) ) {

	/**
	 * is_ur_endpoint_url - Check if an endpoint is showing.
	 *
	 * @param  string $endpoint
	 *
	 * @return bool
	 */
	function is_ur_endpoint_url( $endpoint = false ) {
		global $wp;

		$ur_endpoints = UR()->query->get_query_vars();

		if ( false !== $endpoint ) {
			if ( ! isset( $ur_endpoints[ $endpoint ] ) ) {
				return false;
			} else {
				$endpoint_var = $ur_endpoints[ $endpoint ];
			}

			return isset( $wp->query_vars[ $endpoint_var ] );
		} else {
			foreach ( $ur_endpoints as $key => $value ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					return true;
				}
			}

			return false;
		}
	}
}

if ( ! function_exists( 'is_ur_account_page' ) ) {

	/**
	 * is_ur_account_page - Returns true when viewing an account page.
	 *
	 * @return bool
	 */
	function is_ur_account_page() {
		return is_page( ur_get_page_id( 'myaccount' ) ) || ur_post_content_has_shortcode( 'user_registration_my_account' ) || apply_filters( 'user_registration_is_account_page', false );
	}
}

if ( ! function_exists( 'is_ur_edit_account_page' ) ) {

	/**
	 * Check for edit account page.
	 * Returns true when viewing the edit account page.
	 *
	 * @return bool
	 */
	function is_ur_edit_account_page() {
		global $wp;

		return ( is_ur_account_page() && isset( $wp->query_vars['edit-password'] ) );
	}
}

if ( ! function_exists( 'is_ur_lost_password_page' ) ) {

	/**
	 * is_ur_lost_password_page - Returns true when viewing the lost password page.
	 *
	 * @return bool
	 */
	function is_ur_lost_password_page() {
		global $wp;

		return ( is_ur_account_page() && isset( $wp->query_vars['lost-password'] ) );
	}
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param  string|array $var
 *
 * @return string|array
 */
function ur_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'ur_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @since  1.0.0  Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param  string $var
 *
 * @return string
 */
function ur_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
	) ) );
}

/**
 * Get other templates (e.g. my account) passing attributes and including the file.
 *
 * @param string $template_name
 * @param array  $args          (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 */
function ur_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = ur_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'ur_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'user_registration_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'user_registration_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return string
 */
function ur_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = UR()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = UR()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/
	if ( ! $template || UR_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'user_registration_locate_template', $template, $template_name, $template_path );
}

/**
 * Display a UserRegistration help tip.
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 */
function ur_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = ur_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="user-registration-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Checks whether the content passed contains a specific short code.
 *
 * @param  string $tag Shortcode tag to check.
 *
 * @return bool
 */
function ur_post_content_has_shortcode( $tag = '' ) {
	global $post;

	return ( is_singular() || is_front_page() ) && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $tag );
}

/**
 * Wrapper for ur_doing_it_wrong.
 *
 * @since  1.0.0
 *
 * @param  string $function
 * @param  string $version
 * @param  string $replacement
 */
function ur_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( is_ajax() ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @param  string  $name   Name of the cookie being set.
 * @param  string  $value  Value of the cookie.
 * @param  integer $expire Expiry of the cookie.
 * @param  string  $secure Whether the cookie should be served only over https.
 */
function ur_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}

/**
 * Read in UserRegistration headers when reading plugin headers.
 *
 * @since  1.1.0
 *
 * @param  array $headers
 *
 * @return array $headers
 */
function ur_enable_ur_plugin_headers( $headers ) {
	if ( ! class_exists( 'UR_Plugin_Updates', false ) ) {
		include_once( dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updates.php' );
	}

	$headers['URRequires'] = UR_Plugin_Updates::VERSION_REQUIRED_HEADER;
	$headers['URTested']   = UR_Plugin_Updates::VERSION_TESTED_HEADER;

	return $headers;
}

add_filter( 'extra_plugin_headers', 'ur_enable_ur_plugin_headers' );

/**
 * Set field type for all registrered field keys
 * @param  string $field_key field's field key
 * @return string $field_type
 */
function ur_get_field_type( $field_key ) {

	$fields = ur_get_registered_form_fields();

	$field_type = 'text';

	if ( in_array( $field_key, $fields ) ) {

		switch ( $field_key ) {

			case 'user_email':
			case 'email':
				$field_type = 'email';
				break;
			case 'user_confirm_password':
			case 'password':
			case 'user_pass':
				$field_type = 'password';
				break;
			case 'user_login':
			case 'nickname':
			case 'first_name':
			case 'last_name':
			case 'display_name':
			case 'text':
				$field_type = 'text';
				break;
			case 'user_url':
				$field_type = 'url';
				break;
			case 'description':
			case 'textarea':
				$field_type = 'textarea';
				break;
			case 'select':
			case 'country':
				$field_type = 'select';
				break;
			case 'file':
				$field_type = 'file';
				break;
			case 'privacy_policy':
			case 'mailchimp':
			case 'checkbox':
				$field_type = 'checkbox';
				break;
			case 'number':
				$field_type = 'number';
				break;
			case 'date':
				$field_type = 'date';
				break;
			case 'radio':
				$field_type = 'radio';
				break;
		}
	}

	return apply_filters( 'user_registration_field_keys', $field_type, $field_key );
}

/**
 * Get user table fields.
 *
 * @return array
 */
function ur_get_user_table_fields() {
	return apply_filters( 'user_registration_user_table_fields', array(
		'user_email',
		'user_pass',
		'user_login',
		'user_url',
		'display_name',
	) );
}

/**
 * Get required fields.
 *
 * @return array
 */
function ur_get_required_fields() {
	return apply_filters( 'user_registration_required_form_fields', array(
		'user_email',
		'user_pass',
	) );
}

/**
 * Get one time draggable fields fields.
 *
 * @return array
 */
function ur_get_one_time_draggable_fields() {
	$form_fields = ur_get_user_field_only();
	return apply_filters( 'user_registration_one_time_draggable_form_fields', $form_fields );
}

/**
 * Get fields excluding in profile tab
 *
 * @return array
 */
function ur_exclude_profile_details_fields() {
	return apply_filters( 'user_registration_exclude_profile_fields', array(
		'user_pass',
		'user_confirm_password',
	) );
}

/**
 * @deprecated 1.4.1
 * @return void
 */
function ur_get_account_details_fields() {
	ur_deprecated_function( 'ur_get_account_details_fields', '1.4.1', 'ur_exclude_profile_details_fields' );
}

/**
 * Get all fields appearing in profile tab.
 *
 * @return array
 */
function ur_get_user_profile_field_only() {
	$user_fields = array_diff( ur_get_registered_form_fields(), ur_exclude_profile_details_fields() );
	return apply_filters( 'user_registration_user_profile_field_only', $user_fields );
}

/*
* All fields to update without adding prefix
* @returns array
*/
function ur_get_fields_without_prefix() {
	$fields = ur_get_user_field_only();
	return apply_filters( 'user_registration_fields_without_prefix', $fields );

}

/**
 * Get all default fields by wordpress.
 *
 * @return array
*/
function ur_get_user_field_only() {
	return apply_filters( 'user_registration_user_form_fields', array(
		'user_email',
		'user_pass',
		'user_confirm_password',
		'user_login',
		'nickname',
		'first_name',
		'last_name',
		'user_url',
		'display_name',
		'description',
	) );
}

/**
 * Get all extra form fields
 *
 * @return array
*/
function ur_get_other_form_fields() {
	$registered  = ur_get_registered_form_fields();
	$user_fields = ur_get_user_field_only();
	$result      = array_diff( $registered, $user_fields );

	return apply_filters( 'user_registration_other_form_fields', $result );
}

/**
 * All default fields storing in usermeta table
 * @return mixed|array
 */
function ur_get_registered_user_meta_fields() {
	return apply_filters( 'user_registration_registered_user_meta_fields', array(
		'nickname',
		'first_name',
		'last_name',
		'description'
	) );
}

/**
 * All registered form fields
 * @return mixed|array
 */
function ur_get_registered_form_fields() {
	return apply_filters( 'user_registration_registered_form_fields', array(
		'user_email',
		'user_pass',
		'user_confirm_password',
		'user_login',
		'nickname',
		'first_name',
		'last_name',
		'user_url',
		'display_name',
		'description',
		'text',
		'password',
		'email',
		'select',
		'country',
		'textarea',
		'number',
		'date',
		'checkbox',
		'privacy_policy',
		'radio',
	) );
}

/**
 * General settings for each fields
 * @param string $id id for each field 
 * @return mixed|array
 */
function ur_get_general_settings( $id ) {

	$general_settings = array(
		'label'      => array(
			'type'        => 'text',
			'label'       => __( 'Label', 'user-registration' ),
			'name'        => 'ur_general_setting[label]',
			'placeholder' => __( 'Label', 'user-registration' ),
			'required'    => true,
		),
		'description'      => array(
			'type'        => 'textarea',
			'label'       => __( 'Description', 'user-registration' ),
			'name'        => 'ur_general_setting[description]',
			'placeholder' => __( 'Description', 'user-registration' ),
			'required'    => true,
		),
		'field_name' => array(
			'type'        => 'text',
			'label'       => __( 'Field Name', 'user-registration' ),
			'name'        => 'ur_general_setting[field_name]',
			'placeholder' => __( 'Field Name', 'user-registration' ),
			'required'    => true,
		),

		'placeholder' => array(
			'type'        => 'text',
			'label'       => __( 'Placeholder', 'user-registration' ),
			'name'        => 'ur_general_setting[placeholder]',
			'placeholder' => __( 'Placeholder', 'user-registration' ),
			'required'    => true,
		),
		'required'    => array(
			'type'        => 'select',
			'label'       => __( 'Required', 'user-registration' ),
			'name'        => 'ur_general_setting[required]',
			'placeholder' => '',
			'required'    => true,
			'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
		),
		'hide_label'    => array(
			'type'        => 'select',
			'label'       => __( 'Hide Label', 'user-registration' ),
			'name'        => 'ur_general_setting[hide_label]',
			'placeholder' => '',
			'required'    => true,
			'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
		),
	);

	$exclude_placeholder = apply_filters( 'user_registration_exclude_placeholder',
		array(
			'checkbox',
			'country',
			'date',
			'privacy_policy',
			'radio',
			'select',
			'file',
			'mailchimp'
		)
	);
	$strip_id = substr( $id, 18 );

	if( in_array( $strip_id, $exclude_placeholder ) ) {
		unset( $general_settings['placeholder'] );
	}
	return apply_filters( 'user_registration_field_options_general_settings', $general_settings, $id );
}

/**
 * Load form field class.
 *
 * @param $class_key
 */
function ur_load_form_field_class( $class_key ) {
	$exploded_class = explode( '_', $class_key );
	$class_path     = UR_FORM_PATH . 'class-ur-' . join( '-', array_map( 'strtolower', $exploded_class ) ) . '.php';
	$class_name     = 'UR_Form_Field_' . join( '_', array_map( 'ucwords', $exploded_class ) );
	$class_path     = apply_filters( 'user_registration_form_field_' . $class_key . '_path', $class_path );
	/* Backward Compat since 1.4.0 */
	if ( file_exists( $class_path ) ) {
		$class_name     = 'UR_' . join( '_', array_map( 'ucwords', $exploded_class ) );
		if ( ! class_exists( $class_name ) ) {
			include_once( $class_path );
		}
	}
	/* Backward compat end*/

	return $class_name;
}

/**
 * List of all roles 
 * @return array $all_roles
 */
function ur_get_default_admin_roles() {
	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles = isset( $wp_roles->roles ) ? $wp_roles->roles : array();
	$all_roles = array();

	foreach ( $roles as $role_key => $role ) {
		$all_roles[ $role_key ] = $role['name'];
	}

	return apply_filters( 'user_registration_user_default_roles', $all_roles );
}


/**
 * Random number generated by time()
 * @return int
 */
function ur_get_random_number() {
	$time = time();

	return $time;
}

/**
 * Form settings
 * @param $form_id
 *
 * @since 1.0.1
 */
function ur_admin_form_settings_fields( $form_id ) {

	$all_roles = ur_get_default_admin_roles();

	$arguments = array(
		'form_id' => $form_id,

		'setting_data' => array(
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
				'label'             => __( 'Enable strong password', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_strong_password',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'yes' => __( 'Yes', 'user-registration' ),
					'no'  => __( 'No', 'user-registration' )
				),
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_label', 'Submit' ),
			),
			array(
				'type'              => 'select',
				'label'             => sprintf( __( 'Enable %1$s %2$s reCaptcha %3$s support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=integration" target="_blank">', '</a>' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_recaptcha_support',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'yes' => __( 'Yes', 'user-registration' ),
					'no'  => __( 'No', 'user-registration' )
				),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_recaptcha_support', 'no' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Template', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_template',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'Default'  => __( 'Default', 'user-registration' ),
					'Bordered' => __( 'Bordered', 'user-registration' ),
					'Flat'     => __( 'Flat', 'user-registration' ),
					'Rounded'  => __( 'Rounded', 'user-registration' ),
					'Rounded Edge'=> __( 'Rounded Edge', 'user-registration' ),
				),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_template', 'default' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Custom CSS class', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_custom_class',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'			=> ur_get_single_post_meta( $form_id, 'user_registration_form_custom_class' ),
			),
		)
	);

	$arguments = apply_filters( 'user_registration_get_form_settings', $arguments );

	return $arguments['setting_data'];

}

/**
 * User Login Option
 * @return mixed
 */
function ur_login_option() {

	return apply_filters( 'user_registration_login_options', array(
			'default'        => __( 'Manual login after registration', 'user-registration' ),
			'email_confirmation' => __('Email confirmation to login', 'user-registration'),
			'auto_login'     => __( 'Auto login after registration', 'user-registration' ),
			'admin_approval' => __( 'Admin approval after registration', 'user-registration' )
		)
	);
}

/**
 * @param      $post_id
 * @param      $meta_key
 * @param null $default
 *
 * @since 1.0.1
 *
 * @return null
 */
function ur_get_single_post_meta( $post_id, $meta_key, $default = null ) {

	$post_meta = get_post_meta( $post_id, $meta_key );

	if ( isset( $post_meta[0] ) ) {
		return $post_meta[0];
	}

	return $default;
}

/**
 * @param $form_id
 * @param $meta_key
 *
 * @since 1.0.1
 */
function ur_get_form_setting_by_key( $form_id, $meta_key, $default = '' ) {

	$fields = ur_admin_form_settings_fields( $form_id );
	$value = '';

	foreach ( $fields as $field ) {

		if ( isset( $field['id'] ) && $meta_key == $field['id'] ) {
			$value = isset( $field['default'] ) ? $field['default'] : $default;
			break;
		}
	}

	return $value;
}

/**
 * Get user status in case of admin approval login option
 * @param $user_id
 * @return int $user_status
 */
function ur_get_user_approval_status( $user_id ) {

    $user_status = 1;

	$login_option = get_option( 'user_registration_general_setting_login_options', '' );

	if ( 'admin_approval' === $login_option ) {

		$user_status = get_user_meta( $user_id, 'ur_user_status', true );
	}

	return $user_status;
}

/**
 * @param $form_data
 */
function ur_get_form_data_by_key( $form_data, $key = null ) {

	$form_data_array = array();

	foreach ( $form_data as $data ) {

		foreach ( $data as $single_data ) {

			foreach ( $single_data as $field_data ) {

				$field_key = isset( $field_data->field_key ) && $field_data->field_key !== null ? $field_data->field_key : '';

				if ( ! empty( $field_key ) ) {
					$field_name = isset( $field_data->general_setting->field_name ) && $field_data->general_setting->field_name !== null ? $field_data->general_setting->field_name : '';

					if ( $key === null ) {

						if ( ! empty( $field_name ) ) {
							$form_data_array[ $field_name ] = $field_data;
						} else {
							$form_data_array[] = $field_data;
						}
					} else {

						if ( $field_key === $key ) {

							if ( ! empty( $field_name ) ) {
								$form_data_array[ $field_name ] = $field_data;
							} else {
								$form_data_array[] = $field_data;
							}
						}
					}
				}
			}
		}
	}
		return $form_data_array;
}

/**
 * Get a log file path.
 *
 * @since 1.0.5
 *
 * @param string $handle name.
 *
 * @return string the log file path.
 */
function ur_get_log_file_path( $handle ) {
	return UR_Log_Handler_File::get_log_file_path( $handle );
}

/**
 * Registers the default log handler.
 *
 * @since 1.0.5
 *
 * @param array $handlers
 *
 * @return array
 */
function ur_register_default_log_handler( $handlers ) {

	if ( defined( 'UR_LOG_HANDLER' ) && class_exists( UR_LOG_HANDLER ) ) {
		$handler_class   = UR_LOG_HANDLER;
		$default_handler = new $handler_class();
	} else {
		$default_handler = new UR_Log_Handler_File();
	}

	array_push( $handlers, $default_handler );

	return $handlers;
}

add_filter( 'user_registration_register_log_handlers', 'ur_register_default_log_handler' );


/**
 * Get a shared logger instance.
 *
 * Use the user_registration_logging_class filter to change the logging class. You may provide one of the following:
 *     - a class name which will be instantiated as `new $class` with no arguments
 *     - an instance which will be used directly as the logger
 * In either case, the class or instance *must* implement UR_Logger_Interface.
 *
 * @see UR_Logger_Interface
 * @since 1.1.0
 * @return UR_Logger
 */
function ur_get_logger() {
	static $logger = null;
	if ( null === $logger ) {
		$class      = apply_filters( 'user_registration_logging_class', 'UR_Logger' );
		$implements = class_implements( $class );
		if ( is_array( $implements ) && in_array( 'UR_Logger_Interface', $implements ) ) {
			if ( is_object( $class ) ) {
				$logger = $class;
			} else {
				$logger = new $class;
			}
		} else {
			ur_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					__( 'The class <code>%s</code> provided by user_registration_logging_class filter must implement <code>UR_Logger_Interface</code>.', 'user-registration' ),
					esc_html( is_object( $class ) ? get_class( $class ) : $class )
				),
				'1.0.5'
			);
			$logger = new UR_Logger();
		}
	}

	return $logger;
}

/**
 * Handles addon plugin updater.
 * @param      $file
 * @param      $item_id
 * @param      $addon_version
 * @param bool $beta
 *
 * @since 1.1.0
 */
function ur_addon_updater( $file, $item_id, $addon_version, $beta= false ) {
	$api_endpoint = 'https://wpeverest.com/edd-sl-api/';
	$license_key = trim( get_option( 'user-registration_license_key' ) );
	if ( class_exists( 'UR_AddOn_Updater' ) ) {
		$edd_updater = new UR_AddOn_Updater( $api_endpoint, $file, array(
			'version' => $addon_version,
			'license' => $license_key,
			'item_id' => $item_id,
			'author'  => 'WPEverest',
			'url'     => home_url(),
			'beta'    => $beta ,
		) );
	}

}

/**
 * Check if username already exists in case of optional username
 * And while stripping through email address and incremet last number by 1.
 * @param  string $username
 * @return string $username Modified username
 */
function check_username( $username ) {

	if( username_exists( $username ) ) {
		$last_char = substr( $username, -1 );

		if( is_numeric( $last_char ) ) {
			$strip_last_char = substr( $username, 0, -1 );
			$last_char = $last_char+1;
			$username = $strip_last_char.$last_char;
			$username = check_username( $username );

			return $username;
		}
		else {
			$username = $username.'_1';
			$username = check_username( $username );

			return $username;
		}
	}

	return $username;
}

/**
 * Get all user registration forms title with respective id.
 * @return array $all_forms form id as key and form title as value.
 */
function ur_get_all_user_registration_form() {

	$args        = array(
		'post_type' => 'user_registration',
		'status'    => 'publish',
	);

	$posts_array = get_posts( $args );
	$all_forms = array();

	foreach ( $posts_array as $post ) {
		$all_forms[ $post->ID ] = $post->post_title;
	}

	return $all_forms;
}

/**
 * Check user login option, if not email confirmation force not disable emails.
 */
function ur_get_user_login_option() {
	if( 'email_confirmation' !== get_option( 'user_registration_general_setting_login_options' ) ) {
		return array(
			'title'    => __( 'Disable emails', 'user-registration' ),
			'desc'     => __( 'Disable all emails sent after registration.', 'user-registration' ),
			'id'       => 'user_registration_email_setting_disable_email',
			'default'  => 'no',
			'type'     => 'checkbox',
			'autoload' => false,
		);
	}
	else {
		update_option( 'user_registration_email_setting_disable_email' , 'no');
	}
}

/**
 * Get link for back button used on email settings.
 * @param  string $label 
 * @param  string $url ]
 */
function ur_back_link( $label, $url ) {
	echo '<small class="ur-admin-breadcrumb"><a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '">&#x2934;</a></small>';
}

/**
 * wp_doing ajax() is introduced in core @since 4.7,
 * Filters whether the current request is a WordPress Ajax request.
 */
if ( ! function_exists( 'wp_doing_ajax' ) ) {
	function wp_doing_ajax() {
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
}

/**
 * Checks if the string is json or not
 * @param  string  $str
 * @since  1.4.2
 * @return mixed
 */
function ur_is_json( $str ) {
    $json = json_decode( $str );
    return $json && $str != $json;
}

/**
 * @since 1.1.2
 * Output any queued javascript code in the footer.
 */
function ur_print_js() {
	global $ur_queued_js;

	if ( ! empty( $ur_queued_js ) ) {
		// Sanitize.
		$ur_queued_js = wp_check_invalid_utf8( $ur_queued_js );
		$ur_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $ur_queued_js );
		$ur_queued_js = str_replace( "\r", '', $ur_queued_js );

		$js = "<!-- User Registration JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $ur_queued_js });\n</script>\n";

		/**
		 * user_registration_js filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo apply_filters( 'user_registration_queued_js', $js );

		unset( $ur_queued_js );
	}
}
/**
 * @since 1.1.2
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function ur_enqueue_js( $code ) {
	global $ur_queued_js;

	if ( empty( $ur_queued_js ) ) {
		$ur_queued_js = '';
	}

	$ur_queued_js .= "\n" . $code . "\n";
}

/**
 * Delete expired transients.
 *
 * Deletes all expired transients. The multi-table delete syntax is used.
 * to delete the transient record from table a, and the corresponding.
 * transient_timeout record from table b.
 *
 * Based on code inside core's upgrade_network() function.
 *
 * @since  1.2.0
 * @return int Number of transients that were cleared.
 */
function ur_delete_expired_transients() {
	global $wpdb;

	$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
		AND b.option_value < %d";
	$rows = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) ); // WPCS: unprepared SQL ok.

	$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 17 ) )
		AND b.option_value < %d";
	$rows2 = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_site_transient_' ) . '%', $wpdb->esc_like( '_site_transient_timeout_' ) . '%', time() ) ); // WPCS: unprepared SQL ok.

	return absint( $rows + $rows2 );
}
add_action( 'user_registration_installed', 'ur_delete_expired_transients' );