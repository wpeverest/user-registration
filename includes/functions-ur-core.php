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
require UR_ABSPATH . 'includes/functions-ur-page.php';
require UR_ABSPATH . 'includes/functions-ur-account.php';
require UR_ABSPATH . 'includes/functions-ur-deprecated.php';

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

		return ( is_ur_account_page() && isset( $wp->query_vars['ur-lost-password'] ) );
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
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $var ),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}

/**
 * Format dimensions for display.
 *
 * @since  1.7.0
 * @param  array $dimensions Array of dimensions.
 * @param  array $suffix     Suffix, defaults to 'px'.
 * @return string
 */
function ur_sanitize_dimension_unit( $dimensions = array(), $unit = 'px' ) {
	return ur_array_to_string( ur_suffix_array( $dimensions, $unit ) );
}

/**
 * Add a suffix into an array.
 *
 * @since  1.7.0
 * @param  array  $array  Raw array data.
 * @param  string $suffix Suffix to be added.
 * @return array Modified array with suffix added.
 */
function ur_suffix_array( $array = array(), $suffix = '' ) {
	return preg_filter( '/$/', $suffix, $array );
}
/**
 * Implode an array into a string by $glue and remove empty values.
 *
 * @since  1.7.0
 * @param  array  $array Array to convert.
 * @param  string $glue  Glue, defaults to ' '.
 * @return string
 */
function ur_array_to_string( $array = array(), $glue = ' ' ) {
	return is_string( $array ) ? $array : implode( $glue, array_filter( $array ) );
}
/**
 * Explode a string into an array by $delimiter and remove empty values.
 *
 * @since  1.7.0
 * @param  string $string    String to convert.
 * @param  string $delimiter Delimiter, defaults to ','.
 * @return array
 */
function ur_string_to_array( $string, $delimiter = ',' ) {
	return is_array( $string ) ? $string : array_filter( explode( $delimiter, $string ) );
}

/**
 * Converts a string (e.g. 'yes' or 'no') to a bool.
 *
 * @param string $string String to convert.
 * @return bool
 */
function ur_string_to_bool( $string ) {
	return is_bool( $string ) ? $string : ( 'yes' === $string || 1 === $string || 'true' === $string || '1' === $string );
}

/**
 * Converts a bool to a 'yes' or 'no'.
 *
 * @param bool $bool String to convert.
 * @return string
 */
function ur_bool_to_string( $bool ) {
	if ( ! is_bool( $bool ) ) {
		$bool = ur_string_to_bool( $bool );
	}
	return true === $bool ? 'yes' : 'no';
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

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'ur_get_template', $located, $template_name, $args, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );

		return;
	}

	do_action( 'user_registration_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

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
function ur_help_tip( $tip, $allow_html = false, $classname = 'user-registration-help-tip' ) {
	if ( $allow_html ) {
		$tip = ur_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return sprintf( '<span class="%s" data-tip="%s"></span>', $classname, $tip );
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
 * @param  array $headers header,
 *
 * @return array $headers
 */
function ur_enable_ur_plugin_headers( $headers ) {
	if ( ! class_exists( 'UR_Plugin_Updates', false ) ) {
		include_once dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updates.php';
	}

	$headers['URRequires'] = UR_Plugin_Updates::VERSION_REQUIRED_HEADER;
	$headers['URTested']   = UR_Plugin_Updates::VERSION_TESTED_HEADER;

	return $headers;
}

add_filter( 'extra_plugin_headers', 'ur_enable_ur_plugin_headers' );

/**
 * Set field type for all registrered field keys
 *
 * @param  string $field_key field's field key
 * @return string $field_type
 */
function ur_get_field_type( $field_key ) {

	$fields = ur_get_registered_form_fields();

	$field_type = 'text';

	if ( in_array( $field_key, $fields ) ) {

		switch ( $field_key ) {

			case 'user_email':
			case 'user_confirm_email':
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
			case 'mailerlite':
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
	return apply_filters(
		'user_registration_user_table_fields',
		array(
			'user_email',
			'user_pass',
			'user_login',
			'user_url',
			'display_name',
		)
	);
}

/**
 * Get required fields.
 *
 * @return array
 */
function ur_get_required_fields() {
	return apply_filters(
		'user_registration_required_form_fields',
		array(
			'user_email',
			'user_pass',
		)
	);
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

	$fields_to_exclude = array(
		'user_pass',
		'user_confirm_password',
		'user_confirm_email',
		'invite_code',
		'learndash_course',
	);

	// Check if the my account page contains [user_registration_my_account] shortcode.
	if ( ur_post_content_has_shortcode( 'user_registration_my_account' ) ) {
		// Push profile_picture field to fields_to_exclude array.
		array_push( $fields_to_exclude, 'profile_picture' );
	}

	return apply_filters(
		'user_registration_exclude_profile_fields',
		$fields_to_exclude
	);
}

/**
 * Get readonly fields in profile tab
 *
 * @return array
 */
function ur_readonly_profile_details_fields() {
	return apply_filters(
		'user_registration_readonly_profile_fields',
		array(
			'user_login'            => array(
				'message' => __( 'Username can not be changed.', 'user-registration' ),
			),
			'user_pass'             => array(
				'value'   => 'password',
				'message' => __( 'Passowrd can not be changed.', 'user-registration' ),
			),
			'user_confirm_password' => array(
				'value'   => 'password',
				'message' => __( 'Confirm password can not be changed.', 'user-registration' ),
			),
			'user_confirm_email'    => array(
				'message' => __( 'Confirm email can not be changed.', 'user-registration' ),
			),
		)
	);
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

/**
 * All fields to update without adding prefix.
 *
 * @return array
 */
function ur_get_fields_without_prefix() {
	$fields = ur_get_user_field_only();
	return apply_filters( 'user_registration_fields_without_prefix', $fields );

}

/**
 * Get all default fields by WordPress.
 *
 * @return array
 */
function ur_get_user_field_only() {
	return apply_filters(
		'user_registration_user_form_fields',
		array(
			'user_email',
			'user_confirm_email',
			'user_pass',
			'user_confirm_password',
			'user_login',
			'nickname',
			'first_name',
			'last_name',
			'user_url',
			'display_name',
			'description',
		)
	);
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
 *
 * @return mixed|array
 */
function ur_get_registered_user_meta_fields() {
	return apply_filters(
		'user_registration_registered_user_meta_fields',
		array(
			'nickname',
			'first_name',
			'last_name',
			'description',
		)
	);
}

/**
 * All registered form fields
 *
 * @return mixed|array
 */
function ur_get_registered_form_fields() {
	return apply_filters(
		'user_registration_registered_form_fields',
		array(
			'user_email',
			'user_confirm_email',
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
		)
	);
}

/**
 * All registered form fields with default labels
 *
 * @return mixed|array
 */
function ur_get_registered_form_fields_with_default_labels() {
	return apply_filters(
		'user_registration_registered_form_fields_with_default_labels',
		array(
			'user_email'            => __( 'User Email', 'user-registration' ),
			'user_confirm_email'    => __( 'User Confirm Email', 'user-registration' ),
			'user_pass'             => __( 'User Pass', 'user-registration' ),
			'user_confirm_password' => __( 'User Confirm Password', 'user-registration' ),
			'user_login'            => __( 'User Login', 'user-registration' ),
			'nickname'              => __( 'Nickname', 'user-registration' ),
			'first_name'            => __( 'First Name', 'user-registration' ),
			'last_name'             => __( 'Last Name', 'user-registration' ),
			'user_url'              => __( 'User URL', 'user-registration' ),
			'display_name'          => __( 'Display Name', 'user-registration' ),
			'description'           => __( 'Description', 'user-registration' ),
			'text'                  => __( 'Text', 'user-registration' ),
			'password'              => __( 'Password', 'user-registration' ),
			'email'                 => __( 'Secondary Email', 'user-registration' ),
			'select'                => __( 'Select', 'user-registration' ),
			'country'               => __( 'Country', 'user-registration' ),
			'textarea'              => __( 'Textarea', 'user-registration' ),
			'number'                => __( 'Number', 'user-registration' ),
			'date'                  => __( 'Date', 'user-registration' ),
			'checkbox'              => __( 'Checkbox', 'user-registration' ),
			'privacy_policy'        => __( 'Privacy Policy', 'user-registration' ),
			'radio'                 => __( 'Radio', 'user-registration' ),
		)
	);
}

/**
 * General settings for each fields
 *
 * @param string $id id for each field.
 * @return mixed|array
 */
function ur_get_general_settings( $id ) {

	$general_settings = array(
		'label'       => array(
			'type'        => 'text',
			'label'       => __( 'Label', 'user-registration' ),
			'name'        => 'ur_general_setting[label]',
			'placeholder' => __( 'Label', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter text for the form field label. This is recommended and can be hidden in the Advanced Settings.', 'user-registration' ),
		),
		'description' => array(
			'type'        => 'textarea',
			'label'       => __( 'Description', 'user-registration' ),
			'name'        => 'ur_general_setting[description]',
			'placeholder' => __( 'Description', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter text for the form field description.', 'user-registration' ),
		),
		'field_name'  => array(
			'type'        => 'text',
			'label'       => __( 'Field Name', 'user-registration' ),
			'name'        => 'ur_general_setting[field_name]',
			'placeholder' => __( 'Field Name', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Unique key for the field.', 'user-registration' ),
		),

		'placeholder' => array(
			'type'        => 'text',
			'label'       => __( 'Placeholder', 'user-registration' ),
			'name'        => 'ur_general_setting[placeholder]',
			'placeholder' => __( 'Placeholder', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter placeholder for the field.', 'user-registration' ),
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
			'tip'         => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'user-registration' ),
		),
		'hide_label'  => array(
			'type'        => 'select',
			'label'       => __( 'Hide Label', 'user-registration' ),
			'name'        => 'ur_general_setting[hide_label]',
			'placeholder' => '',
			'required'    => true,
			'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
			'tip'         => __( 'Check this option to hide the label of this field.', 'user-registration' ),
		),
	);

	$exclude_placeholder = apply_filters(
		'user_registration_exclude_placeholder',
		array(
			'checkbox',
			'privacy_policy',
			'radio',
			'file',
			'mailchimp',
		)
	);
	$strip_id            = str_replace( 'user_registration_', '', $id );

	if ( in_array( $strip_id, $exclude_placeholder, true ) ) {
		unset( $general_settings['placeholder'] );
	}

	$choices_fields = array( 'radio', 'select', 'checkbox' );

	if ( in_array( $strip_id, $choices_fields, true ) ) {

		$settings['options'] = array(
			'type'        => 'checkbox' === $strip_id ? 'checkbox' : 'radio',
			'label'       => __( 'Options', 'user-registration' ),
			'name'        => 'ur_general_setting[options]',
			'placeholder' => '',
			'required'    => true,
			'options'     => array(
				__( 'First Choice', 'user-registration' ),
				__( 'Second Choice', 'user-registration' ),
				__( 'Third Choice', 'user-registration' ),
			),
		);

		$general_settings = ur_insert_after_helper( $general_settings, $settings, 'field_name' );
	}

	if ( 'privacy_policy' === $strip_id ) {
		$general_settings['required'] = array(
			'type'        => 'hidden',
			'label'       => '',
			'name'        => 'ur_general_setting[required]',
			'placeholder' => '',
			'default'     => 'yes',
			'required'    => true,
		);
	}
	return apply_filters( 'user_registration_field_options_general_settings', $general_settings, $id );
}

/**
 * Insert in between the indexes in multidimensional array.
 *
 * @since  1.5.7
 * @param  array  $items      An array of items.
 * @param  array  $new_items  New items to insert inbetween.
 * @param  string $after      Index to insert after.
 *
 * @return array              Ordered array of items.
 */
function ur_insert_after_helper( $items, $new_items, $after ) {

	// Search for the item position and +1 since is after the selected item key.
	$position = array_search( $after, array_keys( $items ), true ) + 1;

	// Insert the new item.
	$return_items  = array_slice( $items, 0, $position, true );
	$return_items += $new_items;
	$return_items += array_slice( $items, $position, count( $items ) - $position, true );

	return $return_items;
}

/**
 * Load form field class.
 *
 * @param string $class_key Class Key.
 */
function ur_load_form_field_class( $class_key ) {
	$exploded_class = explode( '_', $class_key );
	$class_path     = UR_FORM_PATH . 'class-ur-' . join( '-', array_map( 'strtolower', $exploded_class ) ) . '.php';
	$class_name     = 'UR_Form_Field_' . join( '_', array_map( 'ucwords', $exploded_class ) );
	$class_path     = apply_filters( 'user_registration_form_field_' . $class_key . '_path', $class_path );
	/* Backward Compat since 1.4.0 */
	if ( file_exists( $class_path ) ) {
		$class_name = 'UR_' . join( '_', array_map( 'ucwords', $exploded_class ) );
		if ( ! class_exists( $class_name ) ) {
			include_once $class_path;
		}
	}
	/* Backward compat end*/
	return $class_name;
}

/**
 * List of all roles
 *
 * @return array $all_roles
 */
function ur_get_default_admin_roles() {
	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
	}

	$roles     = isset( $wp_roles->roles ) ? $wp_roles->roles : array();
	$all_roles = array();

	foreach ( $roles as $role_key => $role ) {
		$all_roles[ $role_key ] = $role['name'];
	}

	return apply_filters( 'user_registration_user_default_roles', $all_roles );
}


/**
 * Random number generated by time()
 *
 * @return int
 */
function ur_get_random_number() {
	return time();
}

/**
 * General Form settings
 *
 * @param int $form_id  Form ID.
 *
 * @since 1.0.1
 *
 * @return array Form settings.
 */
function ur_admin_form_settings_fields( $form_id ) {

	$all_roles = ur_get_default_admin_roles();

	$arguments = array(
		'form_id'      => $form_id,

		'setting_data' => array(
			array(
				'label'             => __( 'User login option', 'user-registration' ),
				'description'       => __( 'This option lets you choose login option after user registration.', 'user-registration' ),
				'id'                => 'user_registration_form_setting_login_options',
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options' ) ),
				'type'              => 'select',
				'class'             => array( 'ur-enhanced-select' ),
				'custom_attributes' => array(),
				'input_class'       => array(),
				'required'          => false,
				'options'           => ur_login_option(),
				'tip'               => __( 'Login method that should be used by the users registered through this form.', 'user-registration' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Default User Role', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_default_user_role',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => $all_roles,
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_default_user_role', 'subscriber' ),
				'tip'               => __( 'Default role for the users registered through this form.', 'user-registration' ),
			),
			array(
				'type'              => 'checkbox',
				'label'             => __( 'Enable Strong Password', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_strong_password',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password', 'yes' ),
				'tip'               => __( 'Make strong password compulsary.', 'user-registration' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Minimum Password Strength', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_minimum_password_strength',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'0' => __( 'Very Weak', 'user-registration' ),
					'1' => __( 'Weak', 'user-registration' ),
					'2' => __( 'Medium', 'user-registration' ),
					'3' => __( 'Strong', 'user-registration' ),
				),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength', '3' ),
				'tip'               => __( 'Set minimum required password strength.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Redirect URL', 'user-registration' ),
				'id'                => 'user_registration_form_setting_redirect_options',
				'description'       => __( 'This option lets you enter redirect path after successful user registration.', 'user-registration' ),
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_options', get_option( 'user_registration_general_setting_redirect_options', '' ) ),  // Getting redirect options from global settings for backward compatibility.
				'tip'               => __( 'URL to redirect to after registration.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Form Submit Button Custom Class', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_form_submit_class',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_class', '' ),
				'tip'               => __( 'Custom css class to embed in the submit button. You can enter multiple classes seperated with space.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Form Submit Button Label', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_form_submit_label',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_label', 'Submit' ),
				'tip'               => __( 'Set label for the submit button.', 'user-registration' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Success message position', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_success_message_position',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'0' => __( 'Top', 'user-registration' ),
					'1' => __( 'Bottom', 'user-registration' ),
				),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_success_message_position', '1' ),
				'tip'               => __( 'Display success message either at the top or bottom after successful registration.', 'user-registration' ),
			),
			array(
				'type'              => 'checkbox',
				'label'             => sprintf( __( 'Enable %1$s %2$s reCaptcha %3$s Support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=integration" target="_blank">', '</a>' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_recaptcha_support',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_recaptcha_support', 'no' ),
				'tip'               => __( 'Enable reCaptcha for strong security from spams and bots.', 'user-registration' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Form Template', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_template',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => array(
					'Default'      => __( 'Default', 'user-registration' ),
					'Bordered'     => __( 'Bordered', 'user-registration' ),
					'Flat'         => __( 'Flat', 'user-registration' ),
					'Rounded'      => __( 'Rounded', 'user-registration' ),
					'Rounded Edge' => __( 'Rounded Edge', 'user-registration' ),
				),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_template', 'default' ),
				'tip'               => __( 'Choose form template to use.', 'user-registration' ),
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_custom_class' ),
				'tip'               => __( 'Custom css class to embed in the registration form. You can enter multiple classes seperated with space.', 'user-registration' ),
			),
		),
	);

	$arguments = apply_filters( 'user_registration_get_form_settings', $arguments );

	return $arguments['setting_data'];
}

/**
 * User Login Option
 *
 * @return array
 */
function ur_login_option() {

	return apply_filters(
		'user_registration_login_options',
		array(
			'default'            => __( 'Manual login after registration', 'user-registration' ),
			'email_confirmation' => __( 'Email confirmation to login', 'user-registration' ),
			'auto_login'         => __( 'Auto login after registration', 'user-registration' ),
			'admin_approval'     => __( 'Admin approval after registration', 'user-registration' ),
		)
	);
}

/**
 * Get Post meta value by meta key.
 *
 * @param int    $post_id Post ID.
 * @param string $meta_key Meta Key.
 * @param mixed  $default Default Value.
 *
 * @since 1.0.1
 *
 * @return mixed
 */
function ur_get_single_post_meta( $post_id, $meta_key, $default = null ) {

	$post_meta = get_post_meta( $post_id, $meta_key );

	if ( isset( $post_meta[0] ) ) {
		if ( 'user_registration_form_setting_enable_recaptcha_support' === $meta_key || 'user_registration_form_setting_enable_strong_password' === $meta_key
		|| 'user_registration_pdf_submission_to_admin' === $meta_key || 'user_registration_pdf_submission_to_user' === $meta_key || 'user_registration_form_setting_enable_assign_user_role_conditionally' === $meta_key) {
			if ( 'yes' === $post_meta[0] ) {
				$post_meta[0] = 1;
			}
		}
		return $post_meta[0];
	}

	return $default;
}

/**
 * Get general form settings by meta key (settings id).
 *
 * @param int    $form_id Form ID.
 * @param string $meta_key Meta Key.
 * @param mixed  $default Default Value.
 *
 * @since 1.0.1
 *
 * @return mixed
 */
function ur_get_form_setting_by_key( $form_id, $meta_key, $default = '' ) {

	$fields = ur_admin_form_settings_fields( $form_id );
	$value  = '';

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
 *
 * @param int $user_id User ID.
 * @return int
 */
function ur_get_user_approval_status( $user_id ) {

	$user_status = 1;

	$form_id = ur_get_form_id_by_userid( $user_id );

	$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

	if ( 'admin_approval' === $login_option ) {

		$user_status = get_user_meta( $user_id, 'ur_user_status', true );
	}

	return $user_status;
}

/**
 * Get form data by field key.
 *
 * @param array  $form_data Form Data.
 * @param string $key Field Key.
 *
 * @return array
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
				$logger = new $class();
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
 *
 * @param string $file Plugin File.
 * @param int    $item_id Item ID.
 * @param string $addon_version Addon Version.
 * @param bool   $beta Is beta version.
 *
 * @since 1.1.0
 */
function ur_addon_updater( $file, $item_id, $addon_version, $beta = false ) {
	$api_endpoint = 'https://wpeverest.com/edd-sl-api/';
	$license_key  = trim( get_option( 'user-registration_license_key' ) );
	if ( class_exists( 'UR_AddOn_Updater' ) ) {
		new UR_AddOn_Updater(
			$api_endpoint,
			$file,
			array(
				'version' => $addon_version,
				'license' => $license_key,
				'item_id' => $item_id,
				'author'  => 'WPEverest',
				'url'     => home_url(),
				'beta'    => $beta,
			)
		);
	}
}

/**
 * Check if username already exists in case of optional username
 * And while stripping through email address and incremet last number by 1.
 *
 * @param  string $username Username.
 * @return string
 */
function check_username( $username ) {

	if ( username_exists( $username ) ) {
		preg_match_all( '/\d+$/m', $username, $matches );

		if ( isset( $matches[0][0] ) ) {
			$last_char       = $matches[0][0];
			$strip_last_char = substr( $username, 0, -( strlen( (string) $last_char ) ) );
			$last_char++;
			$username = $strip_last_char . $last_char;
			$username = check_username( $username );

			return $username;
		} else {
			$username = $username . '_1';
			$username = check_username( $username );

			return $username;
		}
	}

	return $username;
}

/**
 * Get all user registration forms title with respective id.
 *
 * @param int $post_count Post Count.
 * @return array
 */
function ur_get_all_user_registration_form( $post_count = -1 ) {
	$args        = array(
		'status'      => 'publish',
		'numberposts' => $post_count,
		'order'       => 'ASC',
	);
	$posts_array = UR()->form->get_form( '', $args );
	$all_forms   = array();

	foreach ( $posts_array as $post ) {
		$all_forms[ $post->ID ] = $post->post_title;
	}

	return $all_forms;
}

/**
 * Checks user login option, if not email confirmation force not disable emails.
 */
function ur_get_user_login_option() {

	if ( 'email_confirmation' !== get_option( 'user_registration_general_setting_login_options' ) ) {
		return array(
			'title'    => __( 'Disable emails', 'user-registration' ),
			'desc'     => __( 'Disable all emails sent after registration.', 'user-registration' ),
			'id'       => 'user_registration_email_setting_disable_email',
			'default'  => 'no',
			'type'     => 'checkbox',
			'autoload' => false,
		);
	} else {
		update_option( 'user_registration_email_setting_disable_email', 'no' );
	}
}

/**
 * Get the node to display google reCaptcha
 *
 * @param string $recaptcha_enabled Is Recaptcha enabled.
 * @param string $context Recaptcha context.
 * @return string
 */
function ur_get_recaptcha_node( $recaptcha_enabled = 'no', $context ) {

	$recaptcha_version     = get_option( 'user_registration_integration_setting_recaptcha_version' );
	$recaptcha_site_key    = 'v3' === $recaptcha_version ? get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' ) : get_option( 'user_registration_integration_setting_recaptcha_site_key' );
	$recaptcha_site_secret = 'v3' === $recaptcha_version ? get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' ) : get_option( 'user_registration_integration_setting_recaptcha_site_secret' );

	static $rc_counter = 0;

	if ( ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) && ! empty( $recaptcha_site_key ) && ! empty( $recaptcha_site_secret ) ) {

		if ( 0 === $rc_counter ) {
			$enqueue_script = 'v3' === $recaptcha_version ? 'ur-google-recaptcha-v3' : 'ur-google-recaptcha';
			wp_enqueue_script( $enqueue_script );
			wp_localize_script(
				$enqueue_script,
				'ur_google_recaptcha_code',
				array(
					'site_key'          => $recaptcha_site_key,
					'is_captcha_enable' => true,
					'version'           => $recaptcha_version,
				)
			);

			$rc_counter++;
		}

		if ( 'v3' === $recaptcha_version ) {
			if ( 'login' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha-v3" style="display:none"><textarea id="g-recaptcha-response" name="g-recaptcha-response" ></textarea></div>';
			} elseif ( 'register' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha-v3" style="display:none"><textarea id="g-recaptcha-response" name="g-recaptcha-response" ></textarea></div>';
			} else {
				$recaptcha_node = '';
			}
		} else {

			if ( 'login' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha"></div>';

			} elseif ( 'register' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha"></div>';
			} else {
				$recaptcha_node = '';
			}
		}
	} else {
		$recaptcha_node = '';
	}

	return $recaptcha_node;
}

/**
 * Get meta key label pair by form id
 *
 * @param  int $form_id Form ID.
 * @since  1.5.0
 * @return array
 */
function ur_get_meta_key_label( $form_id ) {

	$key_label = array();

	$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

	foreach ( $post_content_array as $post_content_row ) {
		foreach ( $post_content_row as $post_content_grid ) {
			foreach ( $post_content_grid as $field ) {
				if ( isset( $field->field_key ) && isset( $field->general_setting->field_name ) ) {
					$key_label[ $field->general_setting->field_name ] = $field->general_setting->label;
				}
			}
		}
	}

	return apply_filters( 'user_registration_meta_key_label', $key_label, $form_id, $post_content_array );
}

/**
 * Get all user registration fields of the user by querying to database.
 *
 * @param  int $user_id    User ID.
 * @since  1.5.0
 * @return array
 */
function ur_get_user_extra_fields( $user_id ) {

	global $wpdb;
	$name_value        = array();
	$user_extra_fields = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE 'user_registration\_%' AND user_id = " . $user_id . ' ;' );

	foreach ( $user_extra_fields as $extra_field ) {

		// Get meta key remove user_registration_ from the beginning
		$key   = isset( $extra_field->meta_key ) ? substr( $extra_field->meta_key, 18 ) : '';
		$value = isset( $extra_field->meta_value ) ? $extra_field->meta_value : '';

		if ( is_serialized( $value ) ) {
			$value = unserialize( $value );
			$value = implode( ',', $value );
		}
			$name_value[ $key ] = $value;
	}

	return apply_filters( 'user_registration_user_extra_fields', $name_value, $user_id );
}

/**
 * Get link for back button used on email settings.
 *
 * @param  string $label Label.
 * @param  string $url URL.
 */
function ur_back_link( $label, $url ) {
	return '<small class="ur-admin-breadcrumb"><a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '">&#x2934;</a></small>';
}

/**
 * The function wp_doing ajax() is introduced in core @since 4.7,
 */
if ( ! function_exists( 'wp_doing_ajax' ) ) {
	/**
	 * Filters whether the current request is a WordPress Ajax request.
	 */
	function wp_doing_ajax() {
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
}

/**
 * Checks if the string is json or not
 *
 * @param  string $str
 * @since  1.4.2
 * @return mixed
 */
function ur_is_json( $str ) {
	$json = json_decode( $str );
	return $json && $str !== $json;
}

/**
 * Checks if the form contains a date field or not.
 *
 * @param  int $form_id     Form ID
 * @since  1.5.3
 * @return boolean
 */
function ur_has_date_field( $form_id ) {

	$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

	if ( ! empty( $post_content_array ) ) {
		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					if ( isset( $field->field_key ) && $field->field_key === 'date' ) {
						return true;
					}
				}
			}
		}
	}

	return false;
}

/**
 * Get attributes from the shortcode content.
 *
 * @param  $content     Shortcode content.
 * @return array        Array of attributes within the shortcode.
 *
 * @since  1.6.0
 */
function ur_get_shortcode_attr( $content ) {
	$pattern = get_shortcode_regex();

	$keys   = array();
	$result = array();

	if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) ) {

		foreach ( $matches[0] as $key => $value ) {

			// $matches[ 3 ] return the shortcode attribute as string.
			// replace space with '&' for parse_str() function.
			$get = str_replace( ' ', '&', $matches[3][ $key ] );
			parse_str( $get, $output );

			// Get all shortcode attribute keys.
			$keys     = array_unique( array_merge( $keys, array_keys( $output ) ) );
			$result[] = $output;
		}

		if ( $keys && $result ) {

			// Loop the result array and add the missing shortcode attribute key
			foreach ( $result as $key => $value ) {

				// Loop the shortcode attribute key
				foreach ( $keys as $attr_key ) {
					$result[ $key ][ $attr_key ] = isset( $result[ $key ][ $attr_key ] ) ? $result[ $key ][ $attr_key ] : null;
				}

				// Sort the array key.
				ksort( $result[ $key ] );
			}
		}
	}

	return $result;
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

	$sql  = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
		AND b.option_value < %d";
	$rows = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) ); // WPCS: unprepared SQL ok.

	$sql   = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 17 ) )
		AND b.option_value < %d";
	$rows2 = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_site_transient_' ) . '%', $wpdb->esc_like( '_site_transient_timeout_' ) . '%', time() ) ); // WPCS: unprepared SQL ok.

	return absint( $rows + $rows2 );
}
add_action( 'user_registration_installed', 'ur_delete_expired_transients' );

/**
 * String translation function.
 *
 * @since 1.7.3
 *
 * @param int    $form_id Form ID.
 * @param string $field_id Field ID.
 * @param mixed  $variable To be translated for WPML compatibility.
 */
function ur_string_translation( $form_id, $field_id, $variable ) {
	if ( function_exists( 'icl_register_string' ) ) {
		icl_register_string( isset( $form_id ) ? 'user_registration_' . absint( $form_id ) : 'user-registration', isset( $field_id ) ? $field_id : '', $variable );
	}
	if ( function_exists( 'icl_t' ) ) {
		$variable = icl_t( isset( $form_id ) ? 'user_registration_' . absint( $form_id ) : 'user-registration', isset( $field_id ) ? $field_id : '', $variable );
	}
	return $variable;
}

/**
 * Get Form ID from User ID.
 *
 * @param int $user_id User ID.
 *
 * @return int $form_id Form ID.
 */
function ur_get_form_id_by_userid( $user_id ) {
	$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
	$form_id       = 0;

	if ( isset( $form_id_array[0] ) ) {
		$form_id = $form_id_array[0];
	}
	return $form_id;
}

/**
 * Get source ID through which the given user was supposedly registered.
 *
 * @since 1.9.0
 *
 * @param int $user_id
 *
 * @return mixed
 */
function ur_get_registration_source_id( $user_id ) {
	$user_metas = get_user_meta( $user_id );

	if ( isset( $user_metas['user_registration_social_connect_bypass_current_password'] ) ) {
		$networks = array( 'facebook', 'linkedin', 'google', 'twitter' );

		foreach ( $networks as $network ) {

			if ( isset( $user_metas[ 'user_registration_social_connect_' . $network . '_username' ] ) ) {
				return $network;
			}
		}
	} elseif ( isset( $user_metas['ur_form_id'] ) ) {
		return $user_metas['ur_form_id'][0];
	} else {
		return null;
	}
}

/**
 * Check if a datetime falls in a range of time.
 *
 * @since 1.9.0
 *
 * @param string      $target_date
 * @param string|null $start_date
 * @param string|null $end_date
 *
 * @return bool
 */
function ur_falls_in_date_range( $target_date, $start_date = null, $end_date = null ) {
	$start_ts       = strtotime( $start_date );
	$end_ts         = strtotime( $end_date );
	$target_date_ts = strtotime( $target_date );

	// If the starting and the ending date are set as same.
	if ( $start_ts === $end_ts ) {
		$datetime = new DateTime();
		$datetime->setTimestamp( $end_ts );

		date_add( $datetime, date_interval_create_from_date_string( '23 hours 59 mins 59 secs' ) );
		$end_ts = $datetime->getTimestamp();
	}

	if ( $start_date && $end_date ) {
		return ( $start_ts <= $target_date_ts ) && ( $target_date_ts <= $end_ts );
	} elseif ( $start_date ) {
		return ( $start_ts <= $target_date_ts );
	} elseif ( $end_date ) {
		return ( $target_date_ts <= $end_ts );
	} else {
		return false;
	}
}

/**
 * Get Post Content By Form ID.
 *
 * @param int $form_id Form Id.
 *
 * @return array|mixed|null|object
 */
function ur_get_post_content( $form_id ) {

	$args      = array(
		'post_type'   => 'user_registration',

		'post_status' => 'publish',

		'post__in'    => array( $form_id ),
	);
	$post_data = get_posts( $args );

	if ( isset( $post_data[0]->post_content ) ) {

		return json_decode( $post_data[0]->post_content );

	} else {

		return array();
	}
}

/**
 * A wp_parse_args() for multi-dimensional array.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_parse_args/
 *
 * @since 1.9.0
 *
 * @param array $args       Value to merge with $defaults.
 * @param array $defaults   Array that serves as the defaults.
 *
 * @return array    Merged user defined values with defaults.
 */
function ur_parse_args( &$args, $defaults ) {
	$args     = (array) $args;
	$defaults = (array) $defaults;
	$result   = $defaults;
	foreach ( $args as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = ur_parse_args( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}
	return $result;
}

/**
 * Override email content for specific form.
 *
 * @param int $form_id Form Id.
 * @param object $settings Settings for specific email.
 * @param string $message Message to be sent in email body.
 * @param string $subject Subject of the email.
 *
 * @return array
 */
function user_registration_email_content_overrider($form_id, $settings, $message, $subject) {
	// Check if email templates addon is active.
	if( class_exists( 'User_Registration_Email_Templates')) {
		$email_content_override = ur_get_single_post_meta( $form_id, 'user_registration_email_content_override', '' );

		// Check if the post meta exists and have contents.
		if( $email_content_override ) {

			$auto_password_template_overrider = isset( $email_content_override[$settings->id] ) ?  $email_content_override[$settings->id] : '';

			// Check if the email override is enabled.
			if( '' !== $auto_password_template_overrider && '1' === $auto_password_template_overrider['override']) {
				$message = $auto_password_template_overrider['content'];
				$subject = $auto_password_template_overrider['subject'];
			}
		}

	}
	return array( $message, $subject );
}

/** Get User Data in particular array format.
 *
 * @param string $new_string Field Key.
 * @param string $post_key Post Key
 * @param array $profile Form Data.
 * @param mixed $value Value.
 */
function ur_get_valid_form_data_format( $new_string, $post_key, $profile, $value ) {
	$valid_form_data = array();
	if ( isset( $profile[ $post_key ] ) ) {
		$field_type = $profile[ $post_key ]['type'];
		if ( 'checkbox' === $field_type || 'multi_select2' === $field_type ) {
			if ( ! is_array( $value ) && ! empty( $value ) ) {
				$value = maybe_unserialize( $value );
			}
		}
		$valid_form_data[ $new_string ]               = new stdClass();
		$valid_form_data[ $new_string ]->field_name   = $new_string;
		$valid_form_data[ $new_string ]->value        = $value;
		$valid_form_data[ $new_string ]->field_type   = $profile[ $post_key ]['type'];
		$valid_form_data[ $new_string ]->label        = $profile[ $post_key ]['label'];
		$valid_form_data[ $new_string ]->extra_params = array(
			'field_key' => $profile[ $post_key ]['field_key'],
			'label'     => $profile[ $post_key ]['label'],
		);
	} else {
		$valid_form_data[ $new_string ]               = new stdClass();
		$valid_form_data[ $new_string ]->field_name   = $new_string;
		$valid_form_data[ $new_string ]->value        = $value;
		$valid_form_data[ $new_string ]->extra_params = array(
			'field_key' => $new_string
		);
	}
	return $valid_form_data;
}

/**
 * Add our login and my account shortcodes to conflicting shortcodes filter of All In One Seo plugin to resolve the conflict
 *
 * @param array $conflict_shortcodes Array of shortcodes that All in one Seo is conflicting with.
 *
 * @since 1.9.4
 */
function ur_resolve_conflicting_shortcodes_with_aioseo( $conflict_shortcodes ){
	$ur_shortcodes = array(
		'User Registration My Account' => '[user_registration_my_account]',
		'User Registration Login' => '[user_registration_login]'
		);

	$conflict_shortcodes  = array_merge( $conflict_shortcodes, $ur_shortcodes);
	return $conflict_shortcodes;
}
add_filter( 'aioseo_conflicting_shortcodes', 'ur_resolve_conflicting_shortcodes_with_aioseo' );

/**
 * Parse name values and smart tags
 *
 * @param  array $valid_form_data Form filled data.
 * @param  int   $form_id Form ID.
 * @param  int   $user_id User ID.
 *
 * @since 1.9.6
 *
 * @return array
 */
function ur_parse_name_values_for_smart_tags( $user_id, $form_id, $valid_form_data ) {

	$name_value = array();
	$data_html       = '<table class="user-registration-email__entries" cellpadding="0" cellspacing="0"><tbody>';

	// Generate $data_html string to replace for {{all_fields}} smart tag.
	foreach ( $valid_form_data as $field_meta => $form_data ) {
		if ( 'user_confirm_password' === $field_meta ) {
			continue;
		}

		// Donot include privacy policy value.
		if ( isset( $form_data->extra_params['field_key'] ) && 'privacy_policy' === $form_data->extra_params['field_key'] ) {
			continue;
		}

		// Process for file upload.
		if ( isset( $form_data->extra_params['field_key'] ) && 'file' === $form_data->extra_params['field_key'] ) {
			$form_data->value = isset( $form_data->value ) ? wp_get_attachment_url( $form_data->value ) : '';
		}

		$label      = isset( $form_data->extra_params['label'] ) ? $form_data->extra_params['label'] : '';
		$field_name = isset( $form_data->field_name ) ? $form_data->field_name : '';
		$value      = isset( $form_data->value ) ? $form_data->value : '';

		if ( 'user_pass' === $field_meta ) {
			$value = __( 'Chosen Password', 'user-registration' );
		}

		// Check if value contains array.
		if ( is_array( $value ) ) {
			$value = implode( ',', $value );
		}

		$data_html .= '<tr><td>' . $label . ' : </td><td>' . $value . '</td></tr>';

		$name_value[ $field_name ] = $value;
	}

	$data_html .= '</tbody></table>';

	// Smart tag process for extra fields.
	$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, $form_data, $form_id, $user_id );

	return array( $name_value, $data_html );
}
