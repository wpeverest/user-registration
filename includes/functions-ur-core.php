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
	 * Check if an endpoint is showing.
	 *
	 * @param string $endpoint User registration myaccount endpoints.
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
	 * Returns true when viewing an account page.
	 *
	 * @return bool
	 */
	function is_ur_account_page() {
		return is_page( ur_get_page_id( 'myaccount' ) ) || ur_post_content_has_shortcode( 'user_registration_my_account' ) || apply_filters( 'user_registration_is_account_page', false );
	}
}

if ( ! function_exists( 'is_ur_login_page' ) ) {

	/**
	 * Returns true when viewing an login page.
	 *
	 * @return bool
	 */
	function is_ur_login_page() {
		return is_page( ur_get_page_id( 'login' ) ) || ur_post_content_has_shortcode( 'user_registration_login' ) || apply_filters( 'user_registration_is_login_page', false );
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
	 * Returns true when viewing the lost password page.
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
 * @param  string|array $var Variable.
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
 * @param  string $var Value to sanitize.
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
 * @param  array $unit Unit, defaults to 'px'.
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
	return is_bool( $string ) ? $string : ( ( 'yes' === $string || 'on' === $string || 1 === $string || 'true' === $string || '1' === $string || 'today' === $string || 'range' === $string ) ? true : ( null === $string ? '0' : false ) );
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
 * @param string $template_name Template Name.
 * @param array  $args Extra arguments(default: array()).
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path  Default path of template provided(default: '').
 */
function ur_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // phpcs:ignore
	}

	$located = ur_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'ur_get_template', $located, $template_name, $args, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), '1.0' );

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
 * @param string $template_name Template Name.
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path  Default path of template provided(default: '').
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

	// Get default template.
	if ( ! $template || UR_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'user_registration_locate_template', $template, $template_name, $template_path );
}

/**
 * Display a UserRegistration help tip.
 *
 * @param  string $tip        Help tip text.
 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
 * @param string $classname Classname.
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
	$new_shortcode = '';
	$wp_version    = '5.0';
	if ( version_compare( $GLOBALS['wp_version'], $wp_version, '>=' ) ) {
		if ( is_object( $post ) ) {
			$blocks = parse_blocks( $post->post_content );
			foreach ( $blocks as $block ) {

				if ( ( 'core/shortcode' === $block['blockName'] || 'core/paragraph' === $block['blockName'] ) && isset( $block['innerHTML'] ) ) {
					$new_shortcode = $block['innerHTML'];
				} elseif ( 'user-registration/form-selector' === $block['blockName'] && isset( $block['attrs']['shortcode'] ) ) {
					$new_shortcode = '[' . $block['attrs']['shortcode'] . ']';
				}
			}
		}
		return ( is_singular() || is_front_page() ) && is_a( $post, 'WP_Post' ) && has_shortcode( $new_shortcode, $tag );
	} else {
		return ( is_singular() || is_front_page() ) && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $tag );
	}
}

/**
 * Wrapper for ur_doing_it_wrong.
 *
 * @since  1.0.0
 *
 * @param  string $function Callback function name.
 * @param  string $message Message to display.
 * @param  string $version Version of the plugin.
 */
function ur_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( defined( 'DOING_AJAX' ) ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( esc_html( $function ), esc_html( $message ), esc_html( $version ) );
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
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); //phpcs:ignore
	}
}

/**
 * Read in UserRegistration headers when reading plugin headers.
 *
 * @since  1.1.0
 *
 * @param  array $headers header.
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
 * @param  string $field_key field's field key.
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
	if ( ur_post_content_has_shortcode( 'user_registration_my_account' ) || ur_post_content_has_shortcode( 'user_registration_edit_profile' ) ) {
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
 * Get profile detail fields.
 *
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

if ( ! function_exists( 'ur_get_field_name_with_prefix_usermeta' ) ) {
	/**
	 * Returns user registration meta fields with prefix before registration.
	 *
	 * @param string $field_name Field name.
	 *
	 * @return string
	 */
	function ur_get_field_name_with_prefix_usermeta( $field_name ) {
		$default_fields = array_merge_recursive( ur_get_user_table_fields(), ur_get_registered_user_meta_fields() );
		if ( ! in_array( $field_name, $default_fields ) ) {
			$field_name = 'user_registration_' . $field_name;
		}
		return $field_name;
	}
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
			'hidden'                => __( 'Hidden', 'user-registration' ),
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
			'setting_id'  => 'label',
			'type'        => 'text',
			'label'       => __( 'Label', 'user-registration' ),
			'name'        => 'ur_general_setting[label]',
			'placeholder' => __( 'Label', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter text for the form field label. This is recommended and can be hidden in the Advanced Settings.', 'user-registration' ),
		),
		'description' => array(
			'setting_id'  => 'description',
			'type'        => 'textarea',
			'label'       => __( 'Description', 'user-registration' ),
			'name'        => 'ur_general_setting[description]',
			'placeholder' => __( 'Description', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter text for the form field description.', 'user-registration' ),
		),
		'field_name'  => array(
			'setting_id'  => 'field-name',
			'type'        => 'text',
			'label'       => __( 'Field Name', 'user-registration' ),
			'name'        => 'ur_general_setting[field_name]',
			'placeholder' => __( 'Field Name', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Unique key for the field.', 'user-registration' ),
		),

		'placeholder' => array(
			'setting_id'  => 'placeholder',
			'type'        => 'text',
			'label'       => __( 'Placeholder', 'user-registration' ),
			'name'        => 'ur_general_setting[placeholder]',
			'placeholder' => __( 'Placeholder', 'user-registration' ),
			'required'    => true,
			'tip'         => __( 'Enter placeholder for the field.', 'user-registration' ),
		),
		'required'    => array(
			'setting_id'  => 'required',
			'type'        => 'toggle',
			'label'       => __( 'Required', 'user-registration' ),
			'name'        => 'ur_general_setting[required]',
			'placeholder' => '',
			'required'    => true,
			'default'     => 'false',
			'tip'         => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'user-registration' ),
		),
		'hide_label'  => array(
			'setting_id'  => 'hide-label',
			'type'        => 'toggle',
			'label'       => __( 'Hide Label', 'user-registration' ),
			'name'        => 'ur_general_setting[hide_label]',
			'placeholder' => '',
			'required'    => true,
			'default'     => 'false',
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
			'hidden',
		)
	);
	$strip_id            = str_replace( 'user_registration_', '', $id );

	if ( in_array( $strip_id, $exclude_placeholder, true ) ) {
		unset( $general_settings['placeholder'] );
	}

	$choices_fields = array( 'radio', 'select', 'checkbox' );

	if ( in_array( $strip_id, $choices_fields, true ) ) {

		$settings['options'] = array(
			'setting_id'  => 'options',
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
	if ( 'privacy_policy' === $strip_id || 'user_confirm_email' === $strip_id || 'user_confirm_password' === $strip_id || in_array( $strip_id, ur_get_required_fields() ) ) {
		$general_settings['required'] = array(
			'setting_id'  => '',
			'type'        => 'hidden',
			'label'       => '',
			'name'        => 'ur_general_setting[required]',
			'placeholder' => '',
			'default'     => true,
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
	if ( null != $class_path && file_exists( $class_path ) ) {
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
				'label'             => __( 'User Approval And Login Option', 'user-registration' ),
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
				'label'       => __( 'Send User Approval Link in Email', 'user-registration' ),
				'description' => '',
				'id'          => 'user_registration_form_setting_enable_email_approval',
				'type'        => 'toggle',
				'tip'         => __( 'Check to receive a link with token in email to approve the users directly.', 'user-registration' ),
				'css'         => 'min-width: 350px;',
				'default'     => ur_get_approval_default( $form_id ),
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_default_user_role', get_option( 'user_registration_form_setting_default_user_role', 'subscriber' ) ),
				'tip'               => __( 'Default role for the users registered through this form.', 'user-registration' ),
			),
			array(
				'type'              => 'toggle',
				'label'             => __( 'Enable Strong Password', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_strong_password',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password', ur_string_to_bool( get_option( 'user_registration_form_setting_enable_strong_password', 1 ) ) ),
				'tip'               => __( 'Make strong password compulsary.', 'user-registration' ),
			),
			array(
				'type'              => 'radio-group',
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength', get_option( 'user_registration_form_setting_minimum_password_strength', '3' ) ),
				'tip'               => __( 'Set minimum required password strength.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Submit Button Class', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_form_submit_class',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_class', '' ),
				'tip'               => __( 'Enter CSS class names for the Submit Button. Multiple class names should be separated with spaces.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Submit Button Text', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_form_submit_label',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_label', 'Submit' ),
				'tip'               => __( 'Enter desired text for the Submit Button.', 'user-registration' ),
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
				'type'              => 'toggle',

				/* translators: 1: Link tag open 2:: Link content 3:: Link tag close */
				'label'             => sprintf( __( 'Enable &nbsp; %1$s %2$s Captcha %3$s &nbsp; Support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=captcha" target="_blank">', '</a>' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_enable_recaptcha_support',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_recaptcha_support', false ) ),
				'tip'               => __( 'Enable Captcha for strong security from spams and bots.', 'user-registration' ),
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_template', ucwords( str_replace( '_', ' ', get_option( 'user_registration_form_template', 'default' ) ) ) ),
				'tip'               => __( 'Choose form template to use.', 'user-registration' ),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Form Class', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_custom_class',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_custom_class' ),
				'tip'               => __( 'Enter CSS class names for the Form Wrapper. Multiple class names should be separated with spaces.', 'user-registration' ),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Redirect After Registration', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_redirect_after_registration',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => apply_filters(
					'user_registration_redirect_after_registration_options',
					array(
						'no-redirection' => __( 'No Redirection', 'user-registration' ),
						'internal-page'  => __( 'Internal Page', 'user-registration' ),
						'external-url'   => __( 'External URL', 'user-registration' ),
					)
				),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_after_registration', 'no-redirection' ),
				'tip'               => __( 'Choose where to redirect the user after successful registration.', 'user-registration' ),
				'custom_attributes' => array(),
			),
			array(
				'type'              => 'select',
				'label'             => __( 'Custom Page', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_redirect_page',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'options'           => ur_get_all_pages(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_page', '' ),
				'tip'               => __( 'Choose the custom page to redirect after registration', 'user-registration' ),
				'custom_attributes' => array(),
			),
			array(
				'type'              => 'text',
				'label'             => __( 'Redirect URL', 'user-registration' ),
				'id'                => 'user_registration_form_setting_redirect_options',
				'class'             => array( 'ur-enhanced-select' ),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_options', get_option( 'user_registration_general_setting_redirect_options', '' ) ),  // Getting redirect options from global settings for backward compatibility.
				'tip'               => __( 'This option lets you enter redirect path after successful user registration.', 'user-registration' ),
			),
			array(
				'type'              => 'number',
				'label'             => __( 'Waiting Period Before Redirection ( In seconds )', 'user-registration' ),
				'description'       => '',
				'required'          => false,
				'id'                => 'user_registration_form_setting_redirect_after',
				'class'             => array(),
				'input_class'       => array(),
				'custom_attributes' => array(),
				'min'               => '0',
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_after', '2' ),
				'tip'               => __( 'Time to wait after registration before redirecting user to another page.', 'user-registration' ),
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
			'default'            => __( 'Auto approval and manual login', 'user-registration' ),
			'auto_login'         => __( 'Auto approval and auto login ', 'user-registration' ),
			'admin_approval'     => __( 'Admin approval', 'user-registration' ),
			'email_confirmation' => __( 'Auto approval after email confirmation', 'user-registration' ),
		)
	);
}

/**
 * User Login Option
 *
 * @return array
 */
function ur_login_option_with() {

	return apply_filters(
		'user_registration_login_options_with',
		array(
			'default'  => __( 'Username or Email', 'user-registration' ),
			'username' => __( 'Username', 'user-registration' ),
			'email'    => __( 'Email', 'user-registration' ),
		)
	);
}

/**
 * Get Default value for Enable Email Approval Checkbox
 *
 * @param int $form_id Form ID.
 */
function ur_get_approval_default( $form_id ) {
	if ( isset( $form_id ) && 0 != absint( $form_id ) ) {
		$value = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_email_approval' );
	} else {
		$value = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_email_approval', get_option( 'user_registration_login_option_enable_email_approval', false ) );
	}
	$value = ur_string_to_bool( $value ) ? true : false;

	return $value;
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
		|| 'user_registration_pdf_submission_to_admin' === $meta_key || 'user_registration_pdf_submission_to_user' === $meta_key || 'user_registration_form_setting_enable_assign_user_role_conditionally' === $meta_key ) {
			$post_meta[0] = ur_string_to_bool( $post_meta[0] );
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
			$value = isset( $field['default'] ) ? sanitize_text_field( $field['default'] ) : $default;
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

				$field_key = isset( $field_data->field_key ) && null !== $field_data->field_key ? $field_data->field_key : '';

				if ( ! empty( $field_key ) ) {
					$field_name = isset( $field_data->general_setting->field_name ) && null !== $field_data->general_setting->field_name ? $field_data->general_setting->field_name : '';

					if ( null === $key ) {

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
 * @param array $handlers Log handlers.
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
					/* translators: %s: Class */
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
			esc_url_raw( $api_endpoint ),
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
			'type'     => 'toggle',
			'autoload' => false,
		);
	} else {
		update_option( 'user_registration_email_setting_disable_email', false );
	}
}

/**
 * Get the node to display google reCaptcha
 *
 * @param string $context Recaptcha context.
 * @param string $recaptcha_enabled Is Recaptcha enabled.
 * @return string
 */
function ur_get_recaptcha_node( $context, $recaptcha_enabled = false ) {

	$recaptcha_type      = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
	$invisible_recaptcha = ur_option_checked( 'user_registration_captcha_setting_invisible_recaptcha_v2', false );
	$theme_mod           = '';

	if ( 'v2' === $recaptcha_type && ! $invisible_recaptcha ) {
		$recaptcha_site_key    = get_option( 'user_registration_captcha_setting_recaptcha_site_key' );
		$recaptcha_site_secret = get_option( 'user_registration_captcha_setting_recaptcha_site_secret' );
		$enqueue_script        = 'ur-google-recaptcha';
	} elseif ( 'v2' === $recaptcha_type && $invisible_recaptcha ) {
		$recaptcha_site_key    = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key' );
		$recaptcha_site_secret = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret' );
		$enqueue_script        = 'ur-google-recaptcha';
	} elseif ( 'v3' === $recaptcha_type ) {
		$recaptcha_site_key    = get_option( 'user_registration_captcha_setting_recaptcha_site_key_v3' );
		$recaptcha_site_secret = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3' );
		$enqueue_script        = 'ur-google-recaptcha-v3';
	} elseif ( 'hCaptcha' === $recaptcha_type ) {
		$recaptcha_site_key    = get_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha' );
		$recaptcha_site_secret = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha' );
		$enqueue_script        = 'ur-recaptcha-hcaptcha';
	} elseif ( 'cloudflare' === $recaptcha_type ) {
		$recaptcha_site_key    = get_option( 'user_registration_captcha_setting_recaptcha_site_key_cloudflare' );
		$theme_mod             = get_option( 'user_registration_captcha_setting_recaptcha_cloudflare_theme' );
		$enqueue_script        = 'ur-recaptcha-cloudflare';
	}
	static $rc_counter = 0;

	if ( $recaptcha_enabled ) {

		if ( 0 === $rc_counter ) {
			wp_enqueue_script( 'ur-recaptcha' );
			wp_enqueue_script( $enqueue_script );

			$ur_google_recaptcha_code = array(
				'site_key'          => $recaptcha_site_key,
				'is_captcha_enable' => true,
				'version'           => $recaptcha_type,
				'is_invisible'      => $invisible_recaptcha,
				'theme_mode'        => $theme_mod,
			);

			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				?>
				<script id="<?php echo esc_attr( $enqueue_script ); ?>">
					const ur_recaptcha_code = <?php echo wp_json_encode( $ur_google_recaptcha_code ); ?>
				</script>
				<?php
			} else {
				wp_localize_script( $enqueue_script, 'ur_recaptcha_code', $ur_google_recaptcha_code );
			}
			$rc_counter++;
		}

		if ( 'v3' === $recaptcha_type ) {
			if ( 'login' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha-v3" style="display:none"><textarea id="g-recaptcha-response" name="g-recaptcha-response" ></textarea></div>';
			} elseif ( 'register' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha-v3" style="display:none"><textarea id="g-recaptcha-response" name="g-recaptcha-response" ></textarea></div>';
			} elseif ( 'lost_password' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_lost_password" class="g-recaptcha-v3" style="display:none"><textarea id="g-recaptcha-response" name="g-recaptcha-response" ></textarea></div>';
			} else {
				$recaptcha_node = '';
			}
		} elseif ( 'hCaptcha' === $recaptcha_type ) {

			if ( 'login' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha-hcaptcha"></div>';

			} elseif ( 'register' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha-hcaptcha"></div>';
			} elseif ( 'lost_password' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_lost_password" class="g-recaptcha-hcaptcha"></div>';
			} else {
				$recaptcha_node = '';
			}
		} elseif ( 'cloudflare' === $recaptcha_type ) {

			if ( 'login' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_login" class="cf-turnstile"></div>';

			} elseif ( 'register' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_register" class="cf-turnstile"></div>';
			} elseif ( 'lost_password' === $context ) {
				$recaptcha_node = '<div id="node_recaptcha_lost_password" class="cf-turnstile"></div>';
			} else {
				$recaptcha_node = '';
			}
		} else {
			if ( 'v2' === $recaptcha_type && $invisible_recaptcha ) {
				if ( 'login' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha" data-size="invisible"></div>';
				} elseif ( 'register' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha" data-size="invisible"></div>';
				} elseif ( 'lost_password' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_lost_password" class="g-recaptcha" data-size="invisible"></div>';
				} else {
					$recaptcha_node = '';
				}
			} else {
				if ( 'login' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_login" class="g-recaptcha"></div>';

				} elseif ( 'register' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_register" class="g-recaptcha"></div>';
				} elseif ( 'lost_password' === $context ) {
					$recaptcha_node = '<div id="node_recaptcha_lost_password" class="g-recaptcha"></div>';
				} else {
					$recaptcha_node = '';
				}
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
	$name_value = array();

	$admin_profile = new UR_Admin_Profile();
	$extra_data    = $admin_profile->get_user_meta_by_form_fields( $user_id );
	$form_fields   = isset( array_column( $extra_data, 'fields' )[0] ) ? array_column( $extra_data, 'fields' )[0] : array(); //phpcs:ignore
	if ( ! empty( $form_fields ) ) {
		foreach ( $form_fields as $field_key => $field_data ) {
			$value     = get_user_meta( $user_id, $field_key, true );
			$field_key = str_replace( 'user_registration_', '', $field_key );

			if ( is_serialized( $value ) ) {
				$value = unserialize( $value, array( 'allowed_classes' => false ) ); //phpcs:ignore
				$value = implode( ',', $value );
			}

			$name_value[ $field_key ] = $value;

		}
	}

	return apply_filters( 'user_registration_user_extra_fields', $name_value, $user_id );
}

/**
 * Get User status like approved, pending.
 *
 * @param  string $user_status Admin approval status of user.
 * @param  string $user_email_status Email confirmation status of user.
 */
function ur_get_user_status( $user_status, $user_email_status ) {
	$status = array();
	if ( '0' === $user_status || '0' === $user_email_status ) {
		array_push( $status, 'Pending' );
	} elseif ( '-1' === $user_status || '-1' === $user_email_status ) {
		array_push( $status, 'Denied' );
	} else {
		if ( $user_email_status ) {
			array_push( $status, 'Verified' );
		} else {
			array_push( $status, 'Approved' );
		}
	}
	return $status;
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
 * @param  string $str String to check.
 * @since  1.4.2
 * @return mixed
 */
function ur_is_json( $str ) {
	if ( ! is_string( $str ) ) {
		return false;
	}

	$json = json_decode( $str );
	return $json && $str != $json && json_last_error() == JSON_ERROR_NONE;
}

/**
 * Checks if the form contains a date field or not.
 *
 * @param  int $form_id     Form ID.
 * @since  1.5.3
 * @return boolean
 */
function ur_has_date_field( $form_id ) {

	$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

	if ( ! empty( $post_content_array ) ) {
		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					if ( isset( $field->field_key ) && 'date' === $field->field_key ) {
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
 * @param  string $content     Shortcode content.
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

			// Loop the result array and add the missing shortcode attribute key.
			foreach ( $result as $key => $value ) {

				// Loop the shortcode attribute key.
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
 * Print js script by properly sanitizing and escaping.
 *
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
		 * User Registration js filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo wp_kses( apply_filters( 'user_registration_queued_js', $js ), array( 'script' => array( 'type' => true ) ) );

		unset( $ur_queued_js );
	}
}
/**
 * Enqueue UR js.
 *
 * @since 1.1.2
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code to enqueue.
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
		icl_register_string( isset( $form_id ) && 0 !== $form_id ? 'user_registration_' . absint( $form_id ) : 'user-registration', isset( $field_id ) ? $field_id : '', $variable );
	}
	if ( function_exists( 'icl_t' ) ) {
		$variable = icl_t( isset( $form_id ) && 0 !== $form_id ? 'user_registration_' . absint( $form_id ) : 'user-registration', isset( $field_id ) ? $field_id : '', $variable );
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
 * @param int $user_id User ID.
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
 * @param string      $target_date Target date.
 * @param string|null $start_date Start date.
 * @param string|null $end_date End date.
 *
 * @return bool
 */
function ur_falls_in_date_range( $target_date, $start_date = null, $end_date = null ) {
	$start_ts       = strtotime( $start_date );
	$end_ts         = strtotime( $end_date . ' +1 Day' );
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
 * @param int    $form_id Form Id.
 * @param object $settings Settings for specific email.
 * @param string $message Message to be sent in email body.
 * @param string $subject Subject of the email.
 *
 * @return array
 */
function user_registration_email_content_overrider( $form_id, $settings, $message, $subject ) {
	// Check if email templates addon is active.
	if ( class_exists( 'User_Registration_Email_Templates' ) ) {
		$email_content_override = ur_get_single_post_meta( $form_id, 'user_registration_email_content_override', '' );

		// Check if the post meta exists and have contents.
		if ( $email_content_override ) {

			$auto_password_template_overrider = isset( $email_content_override[ $settings->id ] ) ? $email_content_override[ $settings->id ] : '';

			// Check if the email override is enabled.
			if ( '' !== $auto_password_template_overrider && ur_string_to_bool( $auto_password_template_overrider['override'] ) ) {
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
 * @param string $post_key Post Key.
 * @param array  $profile Form Data.
 * @param mixed  $value Value.
 */
function ur_get_valid_form_data_format( $new_string, $post_key, $profile, $value ) {
	$valid_form_data = array();
	if ( isset( $profile[ $post_key ] ) ) {
		$field_type = $profile[ $post_key ]['type'];

		switch ( $field_type ) {
			case 'checkbox':
			case 'multi_select2':
				if ( ! is_array( $value ) && ! empty( $value ) ) {
					$value = ur_maybe_unserialize( $value );
				}
				break;
			case 'file':
				$files = is_array( $value ) ? $value : explode( ',', $value );

				if ( is_array( $files ) && isset( $files[0] ) ) {
					$attachment_ids = '';

					foreach ( $files as $key => $file ) {
						$seperator = 0 < $key ? ',' : '';

						if ( wp_http_validate_url( $file ) ) {

							$attachment_ids = $attachment_ids . '' . $seperator . '' . attachment_url_to_postid( $file );
						}
					}
					$value = ! empty( $attachment_ids ) ? $attachment_ids : $value;
				} else {

					if ( wp_http_validate_url( $value ) ) {
						$value = attachment_url_to_postid( $value );
					}
				}
				break;
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
			'field_key' => $new_string,
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
function ur_resolve_conflicting_shortcodes_with_aioseo( $conflict_shortcodes ) {
	$ur_shortcodes = array(
		'User Registration My Account' => '[user_registration_my_account]',
		'User Registration Login'      => '[user_registration_login]',
	);

	$conflict_shortcodes = array_merge( $conflict_shortcodes, $ur_shortcodes );
	return $conflict_shortcodes;
}

add_filter( 'aioseo_conflicting_shortcodes', 'ur_resolve_conflicting_shortcodes_with_aioseo' );

/**
 * Parse name values and smart tags
 *
 * @param  int   $user_id User ID.
 * @param  int   $form_id Form ID.
 * @param  array $valid_form_data Form filled data.
 *
 * @since 1.9.6
 *
 * @return array
 */
function ur_parse_name_values_for_smart_tags( $user_id, $form_id, $valid_form_data ) {

	$name_value = array();
	$data_html  = '<table class="user-registration-email__entries" cellpadding="0" cellspacing="0"><tbody>';

	// Generate $data_html string to replace for {{all_fields}} smart tag.
	foreach ( $valid_form_data as $field_meta => $form_data ) {

		if ( 'user_confirm_password' === $field_meta || 'user_pass' === $field_meta || preg_match( '/password_/', $field_meta ) ) {
			continue;
		}

		// Donot include privacy policy value.
		if ( isset( $form_data->extra_params['field_key'] ) && 'privacy_policy' === $form_data->extra_params['field_key'] ) {
			continue;
		}

		if ( isset( $form_data->extra_params['field_key'] ) && 'country' === $form_data->extra_params['field_key'] && '' !== $form_data->value ) {
			$country_class    = ur_load_form_field_class( $form_data->extra_params['field_key'] );
			$countries        = $country_class::get_instance()->get_country();
			$form_data->value = isset( $countries[ $form_data->value ] ) ? $countries[ $form_data->value ] : $form_data->value;
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
	$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, $valid_form_data, $form_id, $user_id );

	return array( $name_value, $data_html );
}

/**
 * Get field data by field_name.
 *
 * @param int    $form_id Form Id.
 * @param string $field_name Field Name.
 *
 * @return array
 */
function ur_get_field_data_by_field_name( $form_id, $field_name ) {
	$field_data = array();

	$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

	foreach ( $post_content_array as $post_content_row ) {
		foreach ( $post_content_row as $post_content_grid ) {
			if ( is_array( $post_content_grid ) || is_object( $post_content_grid ) ) {
				foreach ( $post_content_grid as $field ) {
					if ( isset( $field->field_key ) && isset( $field->general_setting->field_name ) && $field->general_setting->field_name === $field_name ) {
						$field_data = array(
							'field_key'       => $field->field_key,
							'general_setting' => $field->general_setting,
							'advance_setting' => $field->advance_setting,
						);
					}
				}
			}
		}
	}
	return $field_data;
}

if ( ! function_exists( 'user_registration_pro_get_conditional_fields_by_form_id' ) ) {
	/**
	 * Get form fields by form id
	 *
	 * @param int    $form_id Form ID.
	 * @param string $selected_field_key Field Key.
	 */
	function user_registration_pro_get_conditional_fields_by_form_id( $form_id, $selected_field_key ) {
		$args          = array(
			'post_type'   => 'user_registration',
			'post_status' => 'publish',
			'post__in'    => array( $form_id ),
		);
			$post_data = get_posts( $args );
		// wrap all fields in array.
		$fields = array();
		if ( isset( $post_data[0]->post_content ) ) {
			$post_content_array = json_decode( $post_data[0]->post_content );

			if ( ! is_null( $post_content_array ) ) {
				foreach ( $post_content_array as $data ) {
					foreach ( $data as $single_data ) {
						foreach ( $single_data as $field_data ) {
							if ( isset( $field_data->general_setting->field_name )
								&& isset( $field_data->general_setting->label ) ) {

								$strip_fields = array(
									'section_title',
									'html',
									'wysiwyg',
									'billing_address_title',
									'shipping_address_title',
									'stripe_gateway',
									'profile_picture',
									'file',
								);

								if ( in_array( $field_data->field_key, $strip_fields, true ) ) {
									continue;
								}

								$fields[ $field_data->general_setting->field_name ] = array(
									'label'     => $field_data->general_setting->label,
									'field_key' => $field_data->field_key,
								);
							}
						}
					}
				}
			}
		}
		// Unset selected meta key.
		unset( $fields[ $selected_field_key ] );
		return $fields;
	}
}

if ( ! function_exists( 'user_registration_pro_render_conditional_logic' ) ) {
	/**
	 * Render Conditional Logic in form settings of form builder.
	 *
	 * @param array  $connection Connection Data.
	 * @param string $integration Integration.
	 * @param int    $form_id Form ID.
	 * @return string
	 */
	function user_registration_pro_render_conditional_logic( $connection, $integration, $form_id ) {
		$output  = '<div class="ur_conditional_logic_container">';
		$output .= '<h4>' . esc_html__( 'Conditional Logic', 'user-registration' ) . '</h4>';
		$output .= '<div class="ur_use_conditional_logic_wrapper ur-check">';
		$checked = '';

		if ( isset( $connection['enable_conditional_logic'] ) && ur_string_to_bool( $connection['enable_conditional_logic'] ) ) {

			$checked = 'checked=checked';
		}
		$output .= '<div class="ur-toggle-section ur-form-builder-toggle">';
		$output .= '<span class="user-registration-toggle-form">';
		$output .= '<input class="ur-use-conditional-logic" type="checkbox" name="ur_use_conditional_logic" id="ur_use_conditional_logic" ' . $checked . '>';
		$output .= '<span class="slider round">';
		$output .= '</span>';
		$output .= '</span>';
		$output .= '<label>' . esc_html__( 'Use conditional logics', 'user-registration' ) . '</label>';
		$output .= '</div>';
		$output .= '</div>';

		$output                .= '<div class="ur_conditional_logic_wrapper" data-source="' . esc_attr( $integration ) . '">';
		$output                .= '<h4>' . esc_html__( 'Conditional Rules', 'user-registration' ) . '</h4>';
		$output                .= '<div class="ur-logic"><p>' . esc_html__( 'Send data only if the following matches.', 'user-registration' ) . '</p></div>';
		$output                .= '<div class="ur-conditional-wrapper">';
		$output                .= '<select class="ur_conditional_field" name="ur_conditional_field">';
		$get_all_fields         = user_registration_pro_get_conditional_fields_by_form_id( $form_id, '' );
		$selected_ur_field_type = '';

		if ( isset( $get_all_fields ) ) {

			foreach ( $get_all_fields as $key => $field ) {
				$selected_attr = '';

				if ( isset( $connection['conditional_logic_data']['conditional_field'] ) && $connection['conditional_logic_data']['conditional_field'] === $key ) {
					$selected_attr          = 'selected=selected';
					$selected_ur_field_type = $field['field_key'];
				}
				$output .= '<option data-type="' . esc_attr( $field['field_key'] ) . '" data-label="' . esc_attr( $field['label'] ) . '" value="' . esc_attr( $key ) . '" ' . $selected_attr . '>' . esc_html( $field['label'] ) . '</option>';
			}
		}
		$output .= '</select>';
		$output .= '<select class="ur-conditional-condition" name="ur-conditional-condition">';
		$output .= '<option value="is" ' . ( isset( $connection['conditional_logic_data']['conditional_operator'] ) && 'is' === $connection['conditional_logic_data']['conditional_operator'] ? 'selected' : '' ) . '> is </option>';
		$output .= '<option value="is_not" ' . ( isset( $connection['conditional_logic_data']['conditional_operator'] ) && 'is_not' === $connection['conditional_logic_data']['conditional_operator'] ? 'selected' : '' ) . '> is not </option>';
		$output .= '</select>';

		if ( 'checkbox' == $selected_ur_field_type || 'radio' == $selected_ur_field_type || 'select' == $selected_ur_field_type || 'country' == $selected_ur_field_type || 'billing_country' == $selected_ur_field_type || 'shipping_country' == $selected_ur_field_type || 'select2' == $selected_ur_field_type || 'multi_select2' == $selected_ur_field_type ) {
			$choices = user_registration_pro_get_checkbox_choices( $form_id, $connection['conditional_logic_data']['conditional_field'] );
			$output .= '<select name="ur-conditional-input" class="ur-conditional-input">';

			if ( is_array( $choices ) && array_filter( $choices ) ) {
				$output .= '<option>--select--</option>';

				foreach ( $choices as $key => $choice ) {
					$key           = 'country' == $selected_ur_field_type ? $key : $choice;
					$selectedvalue = isset( $connection['conditional_logic_data']['conditional_value'] ) && $connection['conditional_logic_data']['conditional_value'] == $key ? 'selected="selected"' : '';
					$output       .= '<option ' . $selectedvalue . ' value="' . esc_attr( $key ) . '">' . esc_html( $choice ) . '</option>';
				}
			} else {
				$selected = isset( $connection['conditional_logic_data']['conditional_value'] ) ? $connection['conditional_logic_data']['conditional_value'] : 0;
				$output  .= '<option value="1" ' . ( ur_string_to_bool( $selected ) ? 'selected="selected"' : '' ) . ' >' . esc_html__( 'Checked', 'user-registration' ) . '</option>';
			}
			$output .= '</select>';
		} else {
			$value   = isset( $connection['conditional_logic_data']['conditional_value'] ) ? $connection['conditional_logic_data']['conditional_value'] : '';
			$output .= '<input class="ur-conditional-input" type="text" name="ur-conditional-input" value="' . esc_attr( $value ) . '">';
		}
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}


if ( ! function_exists( 'user_registration_pro_get_checkbox_choices' ) ) {
	/**
	 * Get Select and Checkbox Fields Choices
	 *
	 * @param int    $form_id Form ID.
	 * @param string $field_name Field Name.
	 * @return array $choices
	 */
	function user_registration_pro_get_checkbox_choices( $form_id, $field_name ) {

		$form_data = (object) user_registration_pro_get_field_data( $form_id, $field_name );
		/* Backward Compatibility. Modified since 1.5.7. To be removed later. */
			$advance_setting_choices = isset( $form_data->advance_setting->choices ) ? $form_data->advance_setting->choices : '';
			$advance_setting_options = isset( $form_data->advance_setting->options ) ? $form_data->advance_setting->options : '';
		/* Bacward Compatibility end.*/

		$choices = isset( $form_data->general_setting->options ) ? $form_data->general_setting->options : '';

		/* Backward Compatibility. Modified since 1.5.7. To be removed later. */
		if ( ! empty( $advance_setting_choices ) ) {
			$choices = explode( ',', $advance_setting_choices );
		} elseif ( ! empty( $advance_setting_options ) ) {
			$choices = explode( ',', $advance_setting_options );
			/* Backward Compatibility end. */

		} elseif ( 'country' === $form_data->field_key ) {
			$country = new UR_Form_Field_Country();
			$country->get_country();
			$choices = $country->get_country();
		}

		return $choices;
	}
}

if ( ! function_exists( 'user_registration_pro_get_field_data' ) ) {
	/**
	 * Get all fields data
	 *
	 * @param  int    $form_id    Form ID.
	 * @param  string $field_name Field Name.
	 * @return array    $field_data.
	 */
	function user_registration_pro_get_field_data( $form_id, $field_name ) {
		$args      = array(
			'post_type'   => 'user_registration',
			'post_status' => 'publish',
			'post__in'    => array( $form_id ),
		);
		$post_data = get_posts( $args );

		if ( isset( $post_data[0]->post_content ) ) {
			$post_content_array = json_decode( $post_data[0]->post_content );

			foreach ( $post_content_array as $data ) {
				foreach ( $data as $single_data ) {
					foreach ( $single_data as $field_data ) {
						isset( $field_data->general_setting->field_name ) ? $field_data->general_setting->field_name : '';
						if ( $field_data->general_setting->field_name === $field_name ) {
								return $field_data;
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'ur_install_extensions' ) ) {
	/**
	 * This function return boolean according to string to avoid colision of 1, true, yes.
	 *
	 * @param [string] $name Name of the extension.
	 * @param [string] $slug Slug of the extension.
	 * @throws Exception Extension Download and activation unsuccessful message.
	 */
	function ur_install_extensions( $name, $slug ) {
		try {

			$plugin = 'user-registration-pro' === $slug ? plugin_basename( sanitize_text_field( wp_unslash( $slug . '/user-registration.php' ) ) ) : plugin_basename( sanitize_text_field( wp_unslash( $slug . '/' . $slug . '.php' ) ) );
			$status = array(
				'install' => 'plugin',
				'slug'    => sanitize_key( wp_unslash( $slug ) ),
			);

			if ( ! current_user_can( 'install_plugins' ) ) {
				$status['errorMessage'] = esc_html__( 'Sorry, you are not allowed to install plugins on this site.', 'user-registration' );

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
				$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$status['plugin']     = $plugin;
				$status['pluginName'] = $plugin_data['Name'];

				if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
					$result = activate_plugin( $plugin );

					if ( is_wp_error( $result ) ) {
						$status['errorCode']    = $result->get_error_code();
						$status['errorMessage'] = $result->get_error_message();

						/* translators: %1$s: Activation error message */
						throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
					}

					$status['success'] = true;
					$status['message'] = $name . ' has been installed and activated successfully';

					return $status;
				}
			}

			$api = json_decode(
				UR_Updater_Key_API::version(
					array(
						'license'   => get_option( 'user-registration_license_key' ),
						'item_name' => $name,
					)
				)
			);

			if ( is_wp_error( $api ) ) {
				$status['errorMessage'] = $api->get_error_message();

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			$status['pluginName'] = $api->name;
			$api->version         = isset( $api->new_version ) ? $api->new_version : '1.0.0';

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$status['debug'] = $skin->get_upgrade_messages();
			}

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( is_wp_error( $skin->result ) ) {
				$status['errorCode']    = $skin->result->get_error_code();
				$status['errorMessage'] = $skin->result->get_error_message();

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( $skin->get_errors()->get_error_code() ) {
				$status['errorMessage'] = $skin->get_error_messages();

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;

				$status['errorCode']    = 'unable_to_connect_to_filesystem';
				$status['errorMessage'] = esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'user-registration' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				/* translators: %1$s: Activation error message */
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			$install_status = install_plugin_install_status( $api );

			if ( current_user_can( 'activate_plugin', $install_status['file'] ) ) {
				if ( is_plugin_inactive( $install_status['file'] ) ) {
					$status['activateUrl'] =
					esc_url_raw(
						add_query_arg(
							array(
								'action'   => 'activate',
								'plugin'   => $install_status['file'],
								'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $install_status['file'] ),
							),
							admin_url( 'admin.php?page=user-registration-addons' )
						)
					);
				} else {
					$status['deActivateUrl'] =
					esc_url_raw(
						add_query_arg(
							array(
								'action'   => 'deactivate',
								'plugin'   => $install_status['file'],
								'_wpnonce' => wp_create_nonce( 'deactivate-plugin_' . $install_status['file'] ),
							),
							admin_url( 'admin.php?page=user-registration-addons' )
						)
					);
				}
			}

			$status['success'] = true;
			$status['message'] = $name . ' has been installed and activated successfully';

			return $status;

		} catch ( Exception $e ) {

			$message           = $e->getMessage();
			$status['success'] = false;
			$status['message'] = $message;

			return $status;
		}
	}
}

add_action( 'user_registration_init', 'ur_profile_picture_migration_script' );

if ( ! function_exists( 'ur_profile_picture_migration_script' ) ) {

	/**
	 * Update usermeta from profile_pic_url to attachemnt id and move files to new directory.
	 *
	 * @since 1.5.0.
	 */
	function ur_profile_picture_migration_script() {

		if ( ! get_option( 'ur_profile_picture_migrated', false ) ) {

			$users = get_users(
				array(
					'meta_key' => 'user_registration_profile_pic_url',
				)
			);

			foreach ( $users as $user ) {
				$user_registration_profile_pic_url = get_user_meta( $user->ID, 'user_registration_profile_pic_url', true );

				if ( ! is_numeric( $user_registration_profile_pic_url ) ) {
					$user_registration_profile_pic_attachment = attachment_url_to_postid( $user_registration_profile_pic_url );
					if ( 0 != $user_registration_profile_pic_attachment ) {
						update_user_meta( $user->ID, 'user_registration_profile_pic_url', absint( $user_registration_profile_pic_attachment ) );
					}
				}
			}

			update_option( 'ur_profile_picture_migrated', true );
		}
	}
}

add_action( 'delete_user', 'ur_delete_user_files_on_user_delete', 10, 3 );

if ( ! function_exists( 'ur_delete_user_files_on_user_delete' ) ) {

	/**
	 * Delete user uploaded files when user is deleted.
	 *
	 * @param [type] $user_id User Id.
	 * @param [type] $reassign  Reassign to another user ( admin ).
	 * @param [type] $user User Data.
	 */
	function ur_delete_user_files_on_user_delete( $user_id, $reassign, $user ) {

		// Return if reassign is set.
		if ( null !== $reassign ) {
			return;
		}

		// Delete user uploaded file when user is deleted.
		if ( class_exists( 'URFU_Uploaded_Data' ) ) {
			$post = get_post( ur_get_form_id_by_userid( $user_id ) );

			$form_data_object = json_decode( $post->post_content );

			$file_fields = URFU_Uploaded_Data::get_file_field( $form_data_object );

			foreach ( $file_fields as $field ) {

				$meta_key = isset( $field['key'] ) ? $field['key'] : '';

				$attachment_ids = explode( ',', get_user_meta( $user->ID, 'user_registration_' . $meta_key, true ) );

				foreach ( $attachment_ids as $attachment_id ) {
					$file_path = get_attached_file( $attachment_id );

					if ( file_exists( $file_path ) ) {
						unlink( $file_path );
					}
				}
			}
		}

		// Delete user uploaded profile image when user is deleted.
		$profile_pic_attachment_id = get_user_meta( $user_id, 'user_registration_profile_pic_url', true );

		$pic_path = get_attached_file( $profile_pic_attachment_id );

		if ( file_exists( $pic_path ) ) {
			unlink( $pic_path );
		}
	}
}

if ( ! function_exists( 'ur_format_field_values' ) ) {

	/**
	 * Get field type by meta key
	 *
	 * @param int    $field_meta_key Field key or meta key.
	 * @param string $field_value Field's value .
	 */
	function ur_format_field_values( $field_meta_key, $field_value ) {
		if ( strpos( $field_meta_key, 'user_registration_' ) ) {
			$field_meta_key = substr( $field_meta_key, 0, strpos( $field_meta_key, 'user_registration_' ) );
		}

		$user_id = isset( $_GET['user'] ) ? sanitize_text_field( wp_unslash( $_GET['user'] ) ) : get_current_user_id();
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : $user_id;
		$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : ur_get_form_id_by_userid( $user_id ); //phpcs:ignore

		$field_name = ur_get_field_data_by_field_name( $form_id, $field_meta_key );
		$field_key  = isset( $field_name['field_key'] ) ? $field_name['field_key'] : '';

		switch ( $field_key ) {
			case 'checkbox':
			case 'multi_select2':
				$field_value = ( is_array( $field_value ) && ! empty( $field_value ) ) ? implode( ', ', $field_value ) : $field_value;
				break;
			case 'country':
				$countries = UR_Form_Field_Country::get_instance()->get_country();
				if ( ! isset( $countries[ $field_value ] ) ) {
					$key = array_search( $field_value, $countries, true );
					if ( $key ) {
						$field_value = $key;
					}
				}
				$field_value = isset( $countries[ $field_value ] ) ? $countries[ $field_value ] : '';
				break;
			case 'file':
				$attachment_ids = is_array( $field_value ) ? $field_value : explode( ',', $field_value );
				$links          = array();

				foreach ( $attachment_ids as $attachment_id ) {
					if ( is_numeric( $attachment_id ) ) {
						$attachment_url = '<a href="' . wp_get_attachment_url( $attachment_id ) . '">' . basename( get_attached_file( $attachment_id ) ) . '</a>';
						array_push( $links, $attachment_url );
					} elseif ( ur_is_valid_url( $attachment_id ) ) {
						$attachment_url = '<a href="' . $attachment_id . '">' . $attachment_id . '</a>';
						array_push( $links, $attachment_url );
					} else {
						array_push( $links, $attachment_id );
					}
				}

				$field_value = implode( ', ', $links );

				break;
			case 'privacy_policy':
				if ( ur_string_to_bool( $field_value ) ) {
					$field_value = 'Checked';
				} else {
					$field_value = 'Not Checked';
				}
				break;
			case 'wysiwyg':
				$field_value = html_entity_decode( $field_value );
				break;
			case 'profile_picture':
				$field_value = '<img class="profile-preview" alt="Profile Picture" width="50px" height="50px" src="' . ( is_numeric( $field_value ) ? esc_url( wp_get_attachment_url( $field_value ) ) : esc_url( $field_value ) ) . '" />';
				$field_value = wp_kses_post( $field_value );
				break;
			default:
				$field_value = $field_value;
				break;
		}

		return $field_value;
	}
}

add_action( 'admin_init', 'user_registration_install_pages_notice' );

if ( ! function_exists( 'user_registration_install_pages_notice' ) ) {
	/**
	 * Display install pages notice if the user has skipped getting started.
	 *
	 * @since 2.2.3
	 */
	function user_registration_install_pages_notice() {

		if ( get_option( 'user_registration_onboarding_skipped', false ) ) {
			UR_Admin_Notices::add_notice( 'install' );
		}

		if ( isset( $_POST['user_registration_myaccount_page_id'] ) ) { //phpcs:ignore
			$my_account_page = $_POST['user_registration_myaccount_page_id']; //phpcs:ignore
		} else {
			$my_account_page = get_option( 'user_registration_myaccount_page_id', 0 );
		}

		$matched        = 0;
		$myaccount_page = array();

		if ( $my_account_page ) {
			$myaccount_page = get_post( $my_account_page );
		}

		if ( ! empty( $myaccount_page ) ) {
			$matched    = ur_find_my_account_in_page( $myaccount_page->ID );
		}

		if ( 0 === $matched ) {
			$my_account_setting_link = admin_url() . 'admin.php?page=user-registration-settings#user_registration_myaccount_page_id';

			$message = sprintf(
				/* translators: %1$s - My account Link. */
				__( 'Please choose a <strong title="A page with [user_registration_my_account] shortcode">My Account</strong> page in <a href="%1$s" style="text-decoration:none;">General Settings</a>. <br/><strong>Got Stuck? Read</strong> <a href="https://docs.wpuserregistration.com/docs/how-to-show-account-profile/" style="text-decoration:none;" target="_blank">How to setup My Account page</a>.', 'user-registration' ),
				$my_account_setting_link
			);
			UR_Admin_Notices::add_custom_notice( 'select_my_account', $message );
		} else {
			UR_Admin_Notices::remove_notice( 'select_my_account' );
		}
	}
}

if ( ! function_exists( 'ur_find_my_account_in_page' ) ) {

	/**
	 * Find My Account Shortcode.
	 *
	 * @param int $login_page_id Login Page ID.
	 * @return int If matched then 1 else 0.
	 * @since  2.2.7
	 */
	function ur_find_my_account_in_page( $login_page_id ) {
		global $wpdb;
		$post_table = $wpdb->prefix . 'posts';
		$post_meta_table = $wpdb->prefix . 'postmeta';

		$matched = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$post_table} WHERE ID = '{$login_page_id}' AND ( post_content LIKE '%[user_registration_login%' OR post_content LIKE '%[user_registration_my_account%' OR post_content LIKE '%[woocommerce_my_account%' )" ) //phpcs:ignore
		);

		if ( $matched <= 0 ) {
			$matched = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$post_meta_table} WHERE post_id = '{$login_page_id}' AND ( meta_value LIKE '%[user_registration_login%' OR meta_value LIKE '%[user_registration_my_account%' OR meta_value LIKE '%[woocommerce_my_account%' )" ) //phpcs:ignore
			);
		}
		return $matched;
	}
}

if ( ! function_exists( 'ur_get_license_plan' ) ) {

	/**
	 * Get a license plan.
	 *
	 * @return bool|string Plan on success, false on failure.
	 * @since  2.2.4
	 */
	function ur_get_license_plan() {
		$license_key = get_option( 'user-registration_license_key' );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( $license_key && is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			$license_data = get_transient( 'ur_pro_license_plan' );

			if ( false === $license_data ) {
				$license_data = json_decode(
					UR_Updater_Key_API::check(
						array(
							'license' => $license_key,
						)
					)
				);

				if ( ! empty( $license_data->item_name ) ) {
					$license_data->item_plan = strtolower( str_replace( 'LifeTime', '', str_replace( 'User Registration', '', $license_data->item_name ) ) );
					set_transient( 'ur_pro_license_plan', $license_data, WEEK_IN_SECONDS );
				}
			}

			return isset( $license_data ) ? $license_data : false;
		}

		return false;
	}
}

if ( ! function_exists( 'ur_get_json_file_contents' ) ) {

	/**
	 * UR Get json file contents.
	 *
	 * @param mixed $file File path.
	 * @param mixed $to_array Returned data in array.
	 * @since  2.2.4
	 */
	function ur_get_json_file_contents( $file, $to_array = false ) {
		if ( $to_array ) {
			return json_decode( ur_file_get_contents( $file ), true );
		}
		return json_decode( ur_file_get_contents( $file ) );
	}
}

if ( ! function_exists( 'ur_is_valid_url' ) ) {

	/**
	 * UR file get contents.
	 *
	 * @param mixed $url URL.
	 */
	function ur_is_valid_url( $url ) {
		// Must start with http:// or https://.
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			return false;
		}

		// Must pass validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'ur_file_get_contents' ) ) {

	/**
	 * UR file get contents.
	 *
	 * @param mixed $file File path.
	 * @since  2.2.4
	 */
	function ur_file_get_contents( $file ) {

		if ( $file ) {
			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$local_file = preg_replace( '/\\\\|\/\//', '/', plugin_dir_path( UR_PLUGIN_FILE ) . $file );

			if ( $wp_filesystem->exists( $local_file ) ) {
				$response = $wp_filesystem->get_contents( $local_file );
				return $response;
			}
		}
		return;
	}
}

if ( ! function_exists( 'crypt_the_string' ) ) {
	/**
	 * Encrypt/Decrypt the provided string.
	 * Encrypt while setting token and updating to database, decrypt while comparing the stored token.
	 *
	 * @param  string $string String to encrypt/decrypt.
	 * @param  string $action Encrypt/decrypt action. 'e' for encrypt and 'd' for decrypt.
	 * @return string Encrypted/Decrypted string.
	 */
	function crypt_the_string( $string, $action = 'e' ) {
		$secret_key = get_option( 'ur_secret_key' );
		$secret_iv  = get_option( 'ur_secret_iv' );

		if ( empty( $secret_key ) || empty( $secret_iv ) ) {
			$secret_key = ur_generate_random_key();
			$secret_iv  = ur_generate_random_key();
			update_option( 'ur_secret_key', $secret_key );
			update_option( 'ur_secret_iv', $secret_iv );
		}

		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( 'e' == $action ) {
			if ( function_exists( 'openssl_encrypt' ) ) {
				$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
			} else {
				$output = base64_encode( $string );
			}
		} elseif ( 'd' == $action ) {
			if ( function_exists( 'openssl_decrypt' ) ) {
				$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
			} else {
				$output = base64_decode( $string );
			}
		}

		return $output;
	}
} //phpcs:ignore

if ( ! function_exists( 'ur_generate_random_key' ) ) {
	/**
	 * Function to generate the random key.
	 *
	 * @since 3.0.2.1
	 */
	function ur_generate_random_key() {
		$length              = 32;
		$allow_special_chars = true;
		$key                 = wp_generate_password( $length, $allow_special_chars );
		return $key;
	}
}

if ( ! function_exists( 'ur_clean_tmp_files' ) ) {
	/**
	 * Clean up the tmp folder - remove all old files every day (filterable interval).
	 */
	function ur_clean_tmp_files() {
		$files = glob( trailingslashit( ur_get_tmp_dir() ) . '*' );

		if ( ! is_array( $files ) || empty( $files ) ) {
			return;
		}

		$lifespan = (int) apply_filters( 'user_registration_clean_tmp_files_lifespan', DAY_IN_SECONDS );

		foreach ( $files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			// In some cases filemtime() can return false, in that case - pretend this is a new file and do nothing.
			$modified = (int) filemtime( $file );
			if ( empty( $modified ) ) {
				$modified = time();
			}

			if ( ( time() - $modified ) >= $lifespan ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}
}

if ( ! function_exists( 'ur_get_tmp_dir' ) ) {
	/**
	 * Get tmp dir for files.
	 *
	 * @return string
	 */
	function ur_get_tmp_dir() {
		$tmp_root = UR_UPLOAD_PATH . 'temp-uploads';

		if ( ! file_exists( $tmp_root ) || ! wp_is_writable( $tmp_root ) ) {
			wp_mkdir_p( $tmp_root );
		}

		$index = trailingslashit( $tmp_root ) . 'index.html';

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		return $tmp_root;
	}
}

if ( ! function_exists( 'ur_get_user_roles' ) ) {
	/**
	 * Returns an array of all roles associated with the user.
	 *
	 * @param [int] $user_id User Id.
	 *
	 * @returns array
	 */
	function ur_get_user_roles( $user_id ) {
		$roles = array();

		if ( $user_id ) {
			$user_meta = get_userdata( $user_id );
			$roles     = isset( $user_meta->roles ) ? $user_meta->roles : array();
		}

		$user_roles = array_map( 'ucfirst', $roles );

		return $user_roles;
	}
}

if ( ! function_exists( 'ur_upload_profile_pic' ) ) {
	/**
	 * Upload Profile Picture
	 *
	 * @param [array] $valid_form_data Valid Form Data.
	 * @param [int]   $user_id User Id.
	 */
	function ur_upload_profile_pic( $valid_form_data, $user_id ) {
		$attachment_id = array();
		$upload_path   = UR_UPLOAD_PATH . 'profile-pictures'; /*Get path of upload dir of User Registration for profile pictures*/

		// Checks if the upload directory exists and create one if not.
		if ( ! file_exists( $upload_path ) ) {
			wp_mkdir_p( $upload_path );
		}
		$valid_extensions = array( 'image/jpeg', 'image/jpg', 'image/gif', 'image/png' );
		$upload_file      = $valid_form_data['profile_pic_url']->value;
		$valid_ext        = array();

		foreach ( $valid_extensions as $key => $value ) {
			$image_extension   = explode( '/', $value );
			$valid_ext[ $key ] = isset( $image_extension[1] ) ? $image_extension[1] : '';

			if ( 'jpeg' === $valid_ext[ $key ] ) {
				$index               = count( $valid_extensions );
				$valid_ext[ $index ] = 'jpg';
			}
		}

		if ( ! is_numeric( $upload_file ) ) {
			$upload = ur_maybe_unserialize( crypt_the_string( $upload_file, 'd' ) );
			if ( function_exists( 'mime_content_type' ) ) {
				$upload_file_type = isset( $upload['file_path'] ) ? mime_content_type( $upload['file_path'] ) : '';
			} else {
				$upload_file_info = isset( $upload['file_path'] ) ? wp_check_filetype( $upload['file_path'] ) : '';
				$upload_file_type = ! empty( $upload_file_info ) ? $upload_file_info['type'] : '';
			}

			if ( isset( $upload['file_name'] ) && isset( $upload['file_path'] ) && isset( $upload['file_extension'] ) && in_array( $upload_file_type, $valid_extensions ) && in_array( $upload['file_extension'], $valid_ext ) ) {
				$upload_path = $upload_path . '/';
				$file_name   = wp_unique_filename( $upload_path, $upload['file_name'] );
				$file_path   = $upload_path . sanitize_file_name( $file_name );
				// Check the type of file. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $file_name ), null );
				$moved    = '';

				if ( basename( $upload['file_path'] ) === $upload['file_name'] ) {
					$moved = rename( $upload['file_path'], $file_path );
				}

				if ( $moved ) {
					$attachment_id = wp_insert_attachment(
						array(
							'guid'           => $file_path,
							'post_mime_type' => $filetype['type'],
							'post_title'     => preg_replace( '/\.[^.]+$/', '', sanitize_file_name( $file_name ) ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						),
						$file_path
					);

					if ( ! is_wp_error( $attachment_id ) ) {
						include_once ABSPATH . 'wp-admin/includes/image.php';

						// Generate and save the attachment metas into the database.
						wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );
					}
				}
			}
		} else {
			$attachment_id = $upload_file;
		}
		$attachment_id = ! empty( $attachment_id ) ? $attachment_id : '';
		update_user_meta( $user_id, 'user_registration_profile_pic_url', $attachment_id );
	}
}

/**
 * Check given string is valid url or not.
 */
if ( ! function_exists( 'ur_is_valid_url' ) ) {
	/**
	 * Checks if url is valid.
	 *
	 * @param [string] $url URL.
	 * @return bool
	 */
	function ur_is_valid_url( $url ) {

		// Must start with http:// or https://.
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			return false;
		}

		// Must pass validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'ur_option_checked' ) ) {
	/**
	 * Returns whether a setting checkbox or toggle is enabled.
	 *
	 * @param string $option_name Option Name.
	 * @param string $default Default Value.
	 * @return boolean
	 */
	function ur_option_checked( $option_name = '', $default = '' ) {

		if ( empty( $option_name ) ) {
			return false;
		}

		$option_value = get_option( $option_name, $default );

		// Handling Backward Compatibility.
		if ( 'yes' === $option_value ) {
			return true;
		} elseif ( 'no' === $option_value ) {
			return false;
		}

		return ur_string_to_bool( $option_value );
	}
}

if ( ! function_exists( 'ur_check_captch_keys' ) ) {
	/**
	 * Check the site key and secret key for the selected captcha type, are valid or not.
	 *
	 * @return bool
	 */
	function ur_check_captch_keys() {
		$recaptcha_type      = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
		$invisible_recaptcha = ur_option_checked( 'user_registration_captcha_setting_invisible_recaptcha_v2', false );

		$site_key   = '';
		$secret_key = '';

		if ( 'v2' === $recaptcha_type ) {
			if ( $invisible_recaptcha ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret' );
			} else {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret' );
			}
		} elseif ( 'v3' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_v3' );
			$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3' );
		} elseif ( 'hCaptcha' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha' );
			$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha' );
		} elseif ( 'cloudflare' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_cloudflare' );
			$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_cloudflare' );
		}

		if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
			return true;
		}

		return false;

	}
}


if ( ! function_exists( 'ur_premium_settings_tab' ) ) {

	/**
	 * Settings tab list to display as premium tabs.
	 *
	 * @since 3.0
	 */
	function ur_premium_settings_tab() {

		$premium_tabs = array(
			'woocommerce'                            => array(
				'label'  => esc_html__( 'WooCommerce', 'user-registration' ),
				'plugin' => 'user-registration-woocommerce',
				'plan'   => array( 'personal', 'plus', 'professional' ),
				'name'   => esc_html__( 'User Registration - WooCommerce', 'user-registration' ),
			),
			'content_restriction'                    => array(
				'label'  => esc_html__( 'Content Restriction', 'user-registration' ),
				'plugin' => 'user-registration-content-restriction',
				'plan'   => array( 'personal', 'plus', 'professional' ),
				'name'   => esc_html__( 'User Registration - Content Restriction', 'user-registration' ),
			),
			'file_upload'                            => array(
				'label'  => esc_html__( 'File Uploads', 'user-registration' ),
				'plugin' => 'user-registration-file-upload',
				'plan'   => array( 'personal', 'plus', 'professional' ),
				'name'   => esc_html__( 'User Registration - File Upload', 'user-registration' ),
			),
			'user-registration-customize-my-account' => array(
				'label'  => esc_html__( 'Customize My Account', 'user-registration' ),
				'plugin' => 'user-registration-customize-my-account',
				'plan'   => array( 'plus', 'professional' ),
				'name'   => esc_html__( 'User Registration customize my account', 'user-registration' ),
			),
		);

		return apply_filters( 'user_registration_premium_settings_tab', $premium_tabs );
	}
}

add_action( 'user_registration_settings_tabs', 'ur_display_premium_settings_tab' );

if ( ! function_exists( 'ur_display_premium_settings_tab' ) ) {

	/**
	 * Method to display premium settings tabs.
	 *
	 * @since 3.0
	 */
	function ur_display_premium_settings_tab() {
		$license_data    = ur_get_license_plan();
		$license_plan    = ! empty( $license_data->item_plan ) ? $license_data->item_plan : false;
		$premium_tabs    = ur_premium_settings_tab();
		$tabs_to_display = array();
		$tab_html        = '';

		foreach ( $premium_tabs as $tab => $detail ) {
			$tooltip_html = '';
			$button       = '';
			if ( 'woocommerce' === $tab && ! is_plugin_active( $detail['plugin'] . '/' . $detail['plugin'] . '.php' ) ) {
				continue;
			}

			if ( ! empty( $license_plan ) ) {
				if ( ! in_array( $license_plan, $detail['plan'], true ) ) {
					if ( is_plugin_active( $detail['plugin'] . '/' . $detail['plugin'] . '.php' ) ) {
						continue;
					}

					/* translators: %s: License Plan Name. */
					$tooltip_html = sprintf( __( 'You have been subscribed to %s plan. Please upgrade to higher plans to use this feature.', 'user-registration' ), ucfirst( $license_plan ) );
					$button       = '<a target="_blank" href="https://wpuserregistration.com/pricing/?utm_source=pro-fields&utm_medium=popup-button&utm_campaign=ur-upgrade-to-pro">' . esc_html__( 'Upgrade Plan', 'user-registration' ) . '</a>';
					array_push( $tabs_to_display, $tab );
				} else {
					$plugin_name = $detail['name'];
					$action      = '';

					if ( file_exists( WP_PLUGIN_DIR . '/' . $detail['plugin'] ) ) {
						if ( ! is_plugin_active( $detail['plugin'] . '/' . $detail['plugin'] . '.php' ) ) {
							$action = 'Activate';
						} else {
							continue;
						}
					} else {
						$action = 'Install';
					}

					/* translators: %s: Addon Name. */
					$tooltip_html = sprintf( __( 'Please %1$s %2$s addon to use this feature.', 'user-registration' ), $action, ucwords( str_replace( '-', ' ', $detail['plugin'] ) ) );

					/* translators: %s: Action Name. */
					$button = '<a href="#" class="user-registration-settings-addon-' . strtolower( $action ) . '" data-slug="' . $detail['plugin'] . '" data-name="' . $plugin_name . '">' . sprintf( esc_html__( '%s Addon', 'user-registration' ), $action ) . '</a>';
					array_push( $tabs_to_display, $tab );
				}
			} else {

				if ( is_plugin_active( $detail['plugin'] . '/' . $detail['plugin'] . '.php' ) ) {
					continue;
				}

				$tooltip_html = __( 'You are currently using the free version of our plugin. Please upgrade to premium version to use this feature.', 'user-registration' );
				$button       = '<a target="_blank" href="https://wpuserregistration.com/pricing/?utm_source=pro-fields&utm_medium=popup-button&utm_campaign=ur-upgrade-to-pro">' . esc_html__( 'Upgrade to Pro', 'user-registration' ) . '</a>';
				array_push( $tabs_to_display, $tab );
			}

			if ( in_array( $tab, $tabs_to_display, true ) ) {
				$tab_html .= '<button class="nav-tab ur-nav__link ur-nav-premium" disabled>';
				$tab_html .= '<span class="ur-tooltip">' . esc_html( $tooltip_html ) . wp_kses_post( $button ) . '</span>';
				$tab_html .= '<span class="ur-nav__link-icon">';
				$tab_html .= ur_file_get_contents( '/assets/images/settings-icons/' . $tab . '.svg' );
				$tab_html .= '</span>';
				$tab_html .= '<span class="ur-nav__link-label">';
				$tab_html .= '<p>' . esc_html( $detail['label'] ) . '</p>';
				$tab_html .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="19" height="19" rx="2.5" fill="#5462FF" stroke="#5462FF"/><path d="M10 5L13 13H7L10 5Z" fill="#EFEFEF"/><path fill-rule="evenodd" clip-rule="evenodd" d="M5 7L5.71429 13H14.2857L15 7L10 11.125L5 7ZM14.2857 13.5714H5.71427V15H14.2857V13.5714Z" fill="white"/></svg>';
				$tab_html .= '</span>';
				$tab_html .= '</button>';
			}
		}

		echo $tab_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'ur_is_ajax_login_enabled' ) ) {
	/**
	 * Check whether the ajax login is enabled or not.
	 *
	 * @return bool
	 */
	function ur_is_ajax_login_enabled() {
		return ur_option_checked( 'ur_login_ajax_submission', false );
	}
}

if ( ! function_exists( 'ur_process_login' ) ) {
	/**
	 * Process the login form.
	 *
	 * @param string $nonce_value Nonce.
	 * @throws Exception Login errors.
	 *
	 * @since 3.0
	 */
	function ur_process_login( $nonce_value ) {
		try {
			// Custom error messages.
			$messages = array(
				'empty_username'   => get_option( 'user_registration_message_username_required', esc_html__( 'Username is required.', 'user-registration' ) ),
				'empty_password'   => get_option( 'user_registration_message_empty_password', null ),
				'invalid_username' => get_option( 'user_registration_message_invalid_username', null ),
				'unknown_email'    => get_option( 'user_registration_message_unknown_email', esc_html__( 'A user could not be found with this email address.', 'user-registration' ) ),
				'pending_approval' => get_option( 'user_registration_message_pending_approval', null ),
				'denied_access'    => get_option( 'user_registration_message_denied_account', null ),
			);

			$post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification

			$recaptcha_value     = isset( $post['g-recaptcha-response'] ) ? ur_clean( wp_unslash( $post['g-recaptcha-response'] ) ) : '';
			$captcha_response    = isset( $post['CaptchaResponse'] ) ? $post['CaptchaResponse'] : ''; //phpcs:ignore
			$recaptcha_enabled   = ur_option_checked( 'user_registration_login_options_enable_recaptcha', false );
			$recaptcha_type      = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
			$invisible_recaptcha = ur_option_checked( 'user_registration_captcha_setting_invisible_recaptcha_v2', false );

			$login_data = array(
				'user_password' => isset( $post['password'] ) ? wp_unslash( $post['password'] ) : '', //phpcs:ignore;
				'remember'      => isset( $post['rememberme'] ),
			);

			$username = isset( $post['username'] ) ? trim( sanitize_user( wp_unslash( $post['username'] ) ) ) : '';

			if ( 'v2' === $recaptcha_type && ! $invisible_recaptcha ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret' );
			} elseif ( 'v2' === $recaptcha_type && $invisible_recaptcha ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret' );
			} elseif ( 'v3' === $recaptcha_type ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_v3' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3' );
			} elseif ( 'hCaptcha' === $recaptcha_type ) {
				$recaptcha_value = isset( $post['h-captcha-response'] ) ? ur_clean( wp_unslash( $post['h-captcha-response'] ) ) : '';
				$site_key        = get_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha' );
				$secret_key      = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha' );
			} elseif ( 'cloudflare' === $recaptcha_type ) {
				$recaptcha_value = isset( $post['cf-turnstile-response'] ) ? ur_clean( wp_unslash( $post['cf-turnstile-response'] ) ) : '';
				$site_key        = get_option( 'user_registration_captcha_setting_recaptcha_site_key_cloudflare' );
				$secret_key      = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_cloudflare' );
			}

			if ( ur_is_ajax_login_enabled() ) {
				$recaptcha_value  = $captcha_response;
			}

			if ( $recaptcha_enabled && ! empty( $site_key ) && ! empty( $secret_key ) ) {
				if ( ! empty( $recaptcha_value ) ) {
					if ( 'hCaptcha' === $recaptcha_type ) {
						$data = wp_remote_get( 'https://hcaptcha.com/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
						$data = json_decode( wp_remote_retrieve_body( $data ) );

						if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_hcaptcha_threshold', 0.5 ) ) ) {
							throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . esc_html__( 'Error on hCaptcha. Contact your site administrator.', 'user-registration' ) );
						}
					} elseif ( 'cloudflare' === $recaptcha_type ) {
						$url          = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
						$params       = array(
							'method' => 'POST',
							'body'   => array(
								'secret'   => $secret_key,
								'response' => $recaptcha_value,
							),
						);
						$data = wp_safe_remote_post( $url, $params );
						$data = json_decode( wp_remote_retrieve_body( $data ) );

						if ( empty( $data->success ) ) {
							throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . esc_html__( 'Error on Cloudflare. Contact your site administrator.', 'user-registration' ) );
						}
					} else {
						$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
						$data = json_decode( wp_remote_retrieve_body( $data ) );
						if ( empty( $data->success ) || ( isset( $data->score ) && $data->score <= get_option( 'user_registration_captcha_setting_recaptcha_threshold_score_v3', apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) ) {
							throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . esc_html__( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ) );
						}
					}
				} else {
					throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . get_option( 'user_registration_form_submission_error_message_recaptcha', esc_html__( 'Captcha code error, please try again.', 'user-registration' ) ) );
				}
			}

			do_action( 'user_registration_login_process_before_username_validation', $post, $username, $nonce_value, $messages );

			$validation_error = new WP_Error();
			$validation_error = apply_filters( 'user_registration_process_login_errors', $validation_error, sanitize_user( wp_unslash( $post['username'] ) ), sanitize_user( wp_unslash( $post['password'] ) ) );

			if ( $validation_error->get_error_code() ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . $validation_error->get_error_message() );
			}

			if ( empty( $username ) ) {
				throw new Exception( '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong>' . $messages['empty_username'] );
			}

			if ( is_email( $username ) && apply_filters( 'user_registration_get_username_from_email', true ) ) {
				$user = get_user_by( 'email', $username );

				if ( isset( $user->user_login ) ) {
					$login_data['user_login'] = $user->user_login;
				} else {
					if ( empty( $messages['unknown_email'] ) ) {
						$messages['unknown_email'] = esc_html__( 'A user could not be found with this email address.', 'user-registration' );
					}

					throw new Exception( '<strong>' . esc_html__( 'ERROR: ', 'user-registration' ) . '</strong>' . $messages['unknown_email'] );
				}
			} else {
				$login_data['user_login'] = $username;
			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( 'login', $username );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// To check the specific login.
			if ( 'email' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
				$user_data                = get_user_by( 'email', $username );
				$login_data['user_login'] = isset( $user_data->user_email ) ? $user_data->user_email : is_email( $username );
			} elseif ( 'username' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
				$user_data                = get_user_by( 'login', $username );
				$login_data['user_login'] = isset( $user_data->user_login ) ? $user_data->user_login : ! is_email( $username );
			} else {
				$login_data['user_login'] = $username;
			}

			// Perform the login.
			$user = wp_signon( apply_filters( 'user_registration_login_credentials', $login_data ), is_ssl() );

			if ( is_wp_error( $user ) ) {
				// Set custom error messages.
				if ( ! empty( $user->errors['empty_username'] ) && ! empty( $messages['empty_username'] ) ) {
					$user->errors['empty_username'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['empty_username'] );
				}
				if ( ! empty( $user->errors['empty_password'] ) && ! empty( $messages['empty_password'] ) ) {
					$user->errors['empty_password'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['empty_password'] );
				}
				if ( ! empty( $user->errors['invalid_username'] ) && ! empty( $messages['invalid_username'] ) ) {
					$user->errors['invalid_username'][0] = $messages['invalid_username'];
				}
				if ( ! empty( $user->errors['pending_approval'] ) && ! empty( $messages['pending_approval'] ) ) {
					$user->errors['pending_approval'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['pending_approval'] );
				}
				if ( ! empty( $user->errors['denied_access'] ) && ! empty( $messages['denied_access'] ) ) {
					$user->errors['denied_access'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['denied_access'] );
				}

				$message = $user->get_error_message();
				$message = str_replace( '<strong>' . esc_html( $login_data['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
				throw new Exception( $message );
			} else {
				if ( in_array( 'administrator', $user->roles, true ) && ur_option_checked( 'user_registration_login_options_prevent_core_login', true ) ) {
					$redirect = admin_url();
				} else {
					if ( ! empty( $post['redirect'] ) ) {
						$redirect = esc_url_raw( wp_unslash( $post['redirect'] ) );
					} elseif ( wp_get_raw_referer() ) {
						$redirect = wp_get_raw_referer();
					} else {
						$redirect = get_home_url();
					}
				}

				$redirect = apply_filters( 'user_registration_login_redirect', $redirect, $user );

				if ( ur_is_ajax_login_enabled() ) {
					wp_send_json_success( array( 'message' => $redirect ) );
					wp_send_json( $user );
				} else {
					wp_redirect( wp_validate_redirect( $redirect, $redirect ) );
					exit;
				}

				if ( ur_is_ajax_login_enabled() ) {
					wp_send_json( $user );
				}
			}
		} catch ( Exception $e ) {
			$status_code = $e->getCode();
			$message     = $e->getMessage();

			if ( $status_code >= 200 && $status_code < 300 ) {
				if ( ur_is_ajax_login_enabled() ) {
					wp_send_json_success(
						array(
							'message' => $message,
							'status'  => true,
						)
					);
				}

				ur_add_notice( $message, 'success' );
			} else {

				if ( ur_is_ajax_login_enabled() ) {
					wp_send_json_error(
						array(
							'message' => $message,
						)
					);
				}

				ur_add_notice( apply_filters( 'login_errors', $message ), 'error' );
				do_action( 'user_registration_login_failed' );
			}
		}
	}
}

if ( ! function_exists( 'ur_generate_onetime_token' ) ) {
	/**
	 * Generate a one-time token for the given user ID and action.
	 *
	 * @param int    $user_id The ID of the user for whom to generate the token.
	 * @param string $action The action for which to generate the token.
	 * @param int    $key_length The length of the random key to be generated. Defaults to 32.
	 * @param int    $expiration_time The duration of the token's validity in minutes. Defaults to 60.
	 * @return string The generated one-time token.
	 */
	function ur_generate_onetime_token( $user_id = 0, $action = '', $key_length = 32, $expiration_time = 60 ) {
		$time = time();
		$key  = wp_generate_password( $key_length, false );

		// Concatenate the key, action, and current time to form the token string.
		$string = $key . $action . $time;

		// Generate the token hash.
		$token = wp_hash( $string );

		// Set the token expiration time in seconds.
		$expiration = apply_filters( $action . '_onetime_token_expiration', $expiration_time * 60 );

		// Set the user meta values for the token and expiration time.
		update_user_meta( $user_id, $action . '_token' . $user_id, $token );
		update_user_meta( $user_id, $action . '_token_expiration' . $user_id, $time + $expiration );

		return $token;
	}
}

if ( ! function_exists( 'ur_get_current_page_url' ) ) {
	/**
	 * Get the current page URL.
	 *
	 * @return string The URL of the current page.
	 */
	function ur_get_current_page_url() {
		$page_url = '';

		if ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
			$page_url .= 'https://';
		} else {
			$page_url .= 'http://';
		}

		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$page_url .= sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$page_url .= sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		return $page_url;
	}
}

if ( ! function_exists( 'ur_is_passwordless_login_enabled' ) ) {
	/**
	 * Check whether the passwordless login is enabled or not.
	 *
	 * @return bool
	 */
	function ur_is_passwordless_login_enabled() {
		return ur_option_checked( 'user_registration_pro_passwordless_login', false );
	}
}

if ( ! function_exists( 'ur_get_ip_address' ) ) {

	/**
	 * Get current user IP Address.
	 *
	 * @return string
	 */
	function ur_get_ip_address() {
		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) { // WPCS: input var ok, CSRF ok.
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );  // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { // WPCS: input var ok, CSRF ok.
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) rest_is_ip_address( trim( current( preg_split( '/[,:]/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) ); // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) { // @codingStandardsIgnoreLine
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ); // @codingStandardsIgnoreLine
		}
		return '';
	}
}

if ( ! function_exists( 'ur_get_all_pages' ) ) {
	/**
	 * Returns map of published pages as id->title format.
	 *
	 * @return array
	 */
	function ur_get_all_pages() {
		$pages = get_pages();

		$pages_array = array();

		foreach ( $pages as $page ) {
			$pages_array[ $page->ID ] = $page->post_title;
		}

		return $pages_array;
	}
}


if ( ! function_exists( 'user_registration_process_email_content' ) ) {
	/**
	 * Returns email content wrapped in email template.
	 *
	 * @param string $email_content Email Content.
	 * @param string $template Email Template id.
	 */
	function user_registration_process_email_content( $email_content, $template = '' ) {
		// Check if email template is selected.
		if ( '' !== $template && 'none' !== $template ) {
			$email_content = apply_filters( 'user_registration_email_template_message', $email_content, $template );
		} else {
			ob_start();
			?>
			<div class="user-registration-email-body" style="padding: 100px 0; background-color: #ebebeb;">
				<table class="user-registration-email" border="0" cellpadding="0" cellspacing="0" style="width: 40%; margin: 0 auto; background: #ffffff; padding: 30px 30px 26px; border: 0.4px solid #d3d3d3; border-radius: 11px; font-family: 'Segoe UI', sans-serif; ">
					<tbody>
						<tr>
							<td colspan="2" style="text-align: left;">
								<?php echo wp_kses_post( $email_content ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			$email_content = wp_kses_post( ob_get_clean() );
		}

		return $email_content;
	}
}

if ( ! function_exists( 'ur_email_preview_link' ) ) {

	/**
	 * Get link for preview email button used on email settings.
	 *
	 * @param  string $label Label.
	 * @param  string $email_id Email id.
	 */
	function ur_email_preview_link( $label, $email_id ) {
		$url = add_query_arg(
			array(
				'ur_email_preview' => $email_id,
			),
			home_url()
		);

		return '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '" class="button user-registration-email-preview " style="min-width:70px;">' . esc_html( $label ) . '</a>';
	}
}

add_action( 'user_registration_after_user_meta_update', 'ur_parse_and_update_hidden_field', 10, 3 );

if ( ! function_exists( 'ur_parse_and_update_hidden_field' ) ) {
	/**
	 * Parse the hidden field value and update.
	 *
	 * @param array $form_data form data.
	 * @param int   $form_id form id.
	 * @param int   $user_id user id.
	 */
	function ur_parse_and_update_hidden_field( $form_data, $form_id, $user_id ) {
		$values = array(
			'form_id'      => $form_id,
			'process_type' => 'ur_parse_after_meta_update',
		);

		foreach ( $form_data as $key => $value ) {
			if ( 'user_email' === $value->field_name ) {
				$values['email'] = ur_format_field_values( $value->field_name, $value->value );
			}

			$values[ $value->field_name ] = ur_format_field_values( $value->field_name, $value->value );
		}

		foreach ( $form_data as $key => $value ) {
			if ( isset( $value->extra_params['field_key'] ) && 'hidden' === $value->extra_params['field_key'] ) {
				$content    = $value->value;
				$field_name = 'user_registration_' . $value->field_name;
				if ( '' !== $content ) {
					$content = apply_filters( 'user_registration_process_smart_tags', $content, $values );
					update_user_meta( $user_id, $field_name, $content );
				}
			}
		}
	}
}

if ( ! function_exists( 'ur_maybe_unserialize' ) ) {
	/**
	 * UR Unserialize data.
	 *
	 * @param string $data Data that might be unserialized.
	 * @param array  $options Options.
	 *
	 * @return mixed Unserialized data can be any type.
	 *
	 * @since 3.0.2
	 */
	function ur_maybe_unserialize( $data, $options = array() ) {

		if ( is_serialized( $data ) ) {
			if ( version_compare( PHP_VERSION, '7.1.0', '>=' ) ) {
				$options = wp_parse_args( $options, array( 'allowed_classes' => false ) );
				return @unserialize( trim( $data ), $options ); //phpcs:ignore
			}
			return @unserialize( trim( $data ) );
		}

		return $data;
	}
}

if ( ! function_exists( 'user_registration_conditional_user_meta_filter' ) ) {
	/**
	 * Filter user meta field when conditinal logic applied.
	 *
	 * @param array $valid_form_data Form Data.
	 * @param int   $user_id User Id.
	 * @param int   $form_id Form Id.
	 * @return array array of form data.
	 *
	 * @since 3.0.4
	 */
	function user_registration_conditional_user_meta_filter( $valid_form_data, $user_id, $form_id ) {
		if ( $user_id <= 0 ) {
			return $valid_form_data;
		}

		$field_name   = '';
		$hidden_field = isset( $_POST['urcl_hide_fields'] ) ? ur_clean( $_POST['urcl_hide_fields'] ) : array(); //phpcs:ignore

		if ( empty( $hidden_field ) ) {
			return $valid_form_data;
		}

		$hidden_array_field = json_decode( stripslashes( $hidden_field ) );

		if ( isset( $_POST['action'] ) && 'user_registration_user_form_submit' ===  $_POST['action'] ) { //phpcs:ignore
			foreach ( $hidden_array_field as $field ) {
				$field_name = $field;
				if ( in_array( $field_name, array_keys( $valid_form_data ) ) ) {
					unset( $valid_form_data[ $field_name ] );
				}
			}
		} else {
			foreach ( $hidden_array_field as $field ) {
				$field_name = 'user_registration_' . $field;
				if ( in_array( $field_name, array_keys( $valid_form_data ) ) ) {
					unset( $valid_form_data[ $field_name ] );
				}
			}
		}

		return $valid_form_data;
	}
}

add_filter( 'user_registration_before_user_meta_update', 'user_registration_conditional_user_meta_filter', 10, 3 );
add_filter( 'user_registration_before_save_profile_details', 'user_registration_conditional_user_meta_filter', 10, 3 );
