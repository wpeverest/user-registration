<?php
/**
 * UserRegistration Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend).
include( UR_ABSPATH . 'includes/functions-ur-page.php' );
include( UR_ABSPATH . 'includes/functions-ur-account.php' );

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

		return ( is_ur_account_page() && isset( $wp->query_vars['edit-account'] ) );
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
 * @param  array $headers
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
 * Get user table fields.
 *
 * @return array
 */
function ur_get_user_table_fields() {
	return apply_filters( 'user_registration_user_table_fields', array(
		'user_email',
		'user_password',
		'user_username',
		'user_url',
		'user_display_name',
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
		'user_password',
		'user_username',
	) );
}

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
			case 'user_password':
				$field_type = 'password';
				break;
			case 'user_username':
			case 'user_nickname':
			case 'user_first_name':
			case 'user_last_name':
			case 'user_display_name':
			case 'text':
				$field_type = 'text';
				break;
			case 'user_url':
				$field_type = 'url';
				break;
			case 'user_description':
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
		}
	}

	return $field_type;
}

function ur_get_one_time_draggable_fields() {
	$form_fields = ur_get_user_field_only();

	return apply_filters( 'user_registration_one_time_draggable_form_fields', $form_fields );
}

function ur_get_account_details_fields() {

	return apply_filters( 'user_registration_registered_account_fields', array(
		'user_email',
		'user_password',
		'user_confirm_password',
		'user_username',
		'user_first_name',
		'user_last_name',

	) );


}

function ur_get_user_profile_field_only() {

	$user_fields = array_diff( ur_get_registered_form_fields(), ur_get_account_details_fields() );

	return $user_fields;
}

function ur_get_user_field_only() {
	$user_fields = array();

	foreach ( ur_get_registered_form_fields() as $field ) {
		if ( substr( $field, 0, 5 ) == 'user_' ) {
			array_push( $user_fields, $field );
		}
	}

	return $user_fields;
}

function ur_get_other_form_fields() {
	$registered  = ur_get_registered_form_fields();
	$user_fields = ur_get_user_field_only();
	$result      = array_diff( $registered, $user_fields );

	return $result;
}


/**
 * @return mixed|array
 */
function ur_get_registered_user_meta_fields() {
	return apply_filters( 'user_registration_registered_user_meta_fields', array(
		'user_nickname',
		'user_first_name',
		'user_last_name',
		'user_description'

	) );
}

/**
 * @return mixed|array
 */
function ur_get_registered_form_fields() {
	return apply_filters( 'user_registration_registered_form_fields', array(
		'user_email',
		'user_password',
		'user_confirm_password',
		'user_username',
		'user_nickname',
		'user_first_name',
		'user_last_name',
		'user_url',
		'user_display_name',
		'user_description',
		'text',
		'password',
		'email',
		'select',
		'country',
		'textarea',
	) );
}

/**
 * @return mixed|array
 */
function ur_get_general_settings() {
	$general_settings = array(
		'label'      => array(
			'type'        => 'text',
			'label'       => __( 'Label', 'user-registration' ),
			'name'        => 'ur_general_setting[label]',
			'id'          => 'ur_general_setting_label',
			'placeholder' => __( 'Label', 'user-registration' ),
			'required'    => true,
		),
		'field_name' => array(
			'type'        => 'text',
			'label'       => __( 'Field Name', 'user-registration' ),
			'name'        => 'ur_general_setting[field_name]',
			'id'          => 'ur_general_setting_field_name',
			'placeholder' => __( 'Field Name', 'user-registration' ),
			'required'    => true,
		),

		'placeholder' => array(
			'type'        => 'text',
			'label'       => __( 'Placeholder', 'user-registration' ),
			'name'        => 'ur_general_setting[placeholder]',
			'id'          => 'ur_general_setting_placeholder',
			'placeholder' => __( 'Placeholder', 'user-registration' ),
			'required'    => true,
		),
		'required'    => array(
			'type'        => 'select',
			'label'       => __( 'Required', 'user-registration' ),
			'name'        => 'ur_general_setting[required]',
			'id'          => 'ur_general_setting_required',
			'placeholder' => '',
			'required'    => true,
			'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
		),
	);

	return apply_filters( 'user_registration_field_options_general_settings', $general_settings );
}

/**
 * Load form field class.
 *
 * @param $class_key
 */
function ur_load_form_field_class( $class_key ) {
	$exploded_class = explode( '_', $class_key );
	$class_path     = UR_FORM_PATH . 'class-ur-' . join( '-', array_map( 'strtolower', $exploded_class ) ) . '.php';
	$class_name     = 'UR_' . join( '_', array_map( 'ucwords', $exploded_class ) );
	$class_path     = apply_filters( 'user_registration_form_field_' . $class_key . '_path', $class_path );

	if ( ! class_exists( $class_name ) ) {
		if ( file_exists( $class_path ) ) {

			include_once( $class_path );
		}
	}

	return $class_name;
}

function ur_get_default_admin_roles() {
	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	$roles = array();
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	$roles = $wp_roles->roles;

	$all_roles = array();

	foreach ( $roles as $role_key => $role ) {

		$all_roles[ $role_key ] = $role['name'];

	}

	return apply_filters( 'user_registration_user_default_roles', $all_roles );

}

/**
 * @return int
 */
function get_random_number() {

	$time = time();

	return $time;

}

/**
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
				'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_form_submit_label', 'Register' ),


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
			)

		)
	);
	$arguments = apply_filters( 'user_registration_get_form_settings', $arguments );


	return $arguments['setting_data'];

}

/**
 * @return mixed
 */
function ur_login_option() {

	return apply_filters( 'user_registration_login_options', array(

			'default'        => __( 'Manual login after registration', 'user-registration' ),
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
 * @param $user_id
 *
 *
 */
function ur_get_user_approval_status( $user_id ) {

	$login_option = get_option( 'user_registration_general_setting_login_options', '' );

	if ( 'admin_approval' === $login_option ) {

		$user_status = get_user_meta( $user_id, 'ur_user_status', true );

		if ( $user_status == 0 || $user_status == - 1 ) {

			return $user_status;
		}

		return true;


	}

	return true;

}
