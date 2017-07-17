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

/**
 * @param array $module_array
 *
 * @return int
 * @throws Exception
 */
function ur_register_modules( $module_array = array() ) {

	global $ur_modules;

	$count = 0;

	foreach ( $module_array as $module_name => $module_data ) {

		if ( isset( $ur_modules[ $module_name ] ) ) {

			throw new Exception( $module_name . ' module already registered.' );
		}

		$file_name = isset( $module_data['file'] ) ? $module_data['file'] : '';

		$file_name = substr( $file_name, 0, ( strlen( $file_name ) ) - ( strlen( strrchr( $file_name, '.' ) ) ) ) . '.php';

		$module_file_path = UR_MODULE_PATH . $module_name . DIRECTORY_SEPARATOR . $file_name;

		if ( ! file_exists( $module_file_path ) ) {

			throw new Exception( $module_name . ' module file or directory not exists.' );

		}
		$description = isset( $module_data['description'] ) ? $module_data['description'] : '';

		$icon = isset( $module_data['icon'] ) ? $module_data['icon'] : '';

		$title = isset( $module_data['title'] ) ? $module_data['title'] : '';

		$class = isset( $module_data['class'] ) ? $module_data['class'] : '';


		$ur_modules[ $module_name ] = array(

			'file'        => $file_name,
			'description' => $description,
			'icon'        => $icon,
			'title'       => $title,
			'class'       => $class,
		);

		$count ++;
	}

	return $count;


}

/**
 * @param $module
 *
 * @return bool
 */
function ur_get_is_register_module( $module ) {

	global $ur_modules;

	if ( ! isset( $ur_modules[ $module ] ) ) {

		return false;

	}

	return true;

}


/**
 * @param $module
 *
 * @return bool
 */
function ur_get_module( $module ) {

	global $ur_modules;

	if ( ! isset( $ur_modules[ $module ] ) ) {

		return false;

	}

	return $ur_modules[ $module ];
}

/**
 * @param $module
 */
function ur_install_module( $module ) {

	if ( ! ur_get_is_register_module( $module ) ) {


		return false;
	}

	global $wpdb;


	$status = $wpdb->query( $wpdb->prepare( "

		INSERT INTO  {$wpdb->prefix}user_registration_modules

		(module_name,installation_date,is_installed,user_id)

		VALUES

		(%s,CURRENT_TIMESTAMP ,%d,%d);",

		$module, 1, get_current_user_id()

	) );

	return $status;


}

/**
 * @param $module
 */
function ur_uninstall_module( $module ) {

	if ( ! ur_get_is_register_module( $module ) ) {


		return false;
	}

	global $wpdb;

	$status = $wpdb->query( $wpdb->prepare( "

		DELETE FROM {$wpdb->prefix}user_registration_modules

		WHERE module_name=%s

		;",

		$module

	) );

	return $status;
}

/**
 * @param $module
 */
function ur_is_module_already_install( $module ) {

	global $wpdb;

	$module_data = $wpdb->get_results( $wpdb->prepare( "

SELECT *  FROM {$wpdb->prefix}user_registration_modules WHERE module_name=%s;", $module

	) );

	return count( $module_data ) == 0 ? false : true;

}

/**
 * @return string
 */
function ur_get_all_installed_modules() {

	global $wpdb;

	$registered_modules = ur_get_all_registered_modules();

	$registered_modules_names = array_keys( $registered_modules );

	$query = "SELECT *  FROM {$wpdb->prefix}user_registration_modules WHERE is_installed = 1 and  module_name in (";


	foreach ( $registered_modules as $module_name => $module_data ) {

		$query .= '%s,';
	}

	$query = rtrim( $query, ',' );

	$query .= ')';

	$data = $wpdb->get_results( $wpdb->prepare( $query, $registered_modules_names ) );

	return $data;
}

/**
 * @return mixed
 */
function ur_get_all_registered_modules() {

	global $ur_modules;

	return is_array( $ur_modules ) ? $ur_modules : array();
}


/**
 * @return mixed
 */
function ur_get_modules_admin_view() {

	$all_modules = ur_get_all_registered_modules();

	$module_node = '<div class="ur-admin-module-block">';

	if ( count( $all_modules ) == 0 ) {

		$module_node . '<h1>' . __( 'Registered modules not found.', 'user-registration' ) . '</h1></div>';

		return $module_node;
	}

	$module_node .= '<h1>Module Lists</h1>';

	$module_node .= '<ul class="ur-module-list">';

	foreach ( $all_modules as $module_name => $module_data ) {

		$button_label = __( 'Install', 'user-registration' );

		$button_class = 'button-primary ur-module-install';

		$is_module_already_installed = ur_is_module_already_install( $module_name );

		if ( $is_module_already_installed ) {

			$button_class = 'button-disabled ur-module-already-install';

			$button_label = __( 'Installed', 'user-registration' );

		}

		$list = '<li class="ur-module-list ' . $module_name . '-module" data-module-name="' . $module_name . '" data-module-file="' . $module_data['file'] . '">';

		$list .= '<div class="ur-single-module">';

		$list .= '<h2 class="ur-single-module-title">' . $module_data['title'] . '<span class="ur-module-icon dashicons ' . $module_data['icon'] . '"></span></h2>';

		$list .= '<div class="ur-module-content" data-nonce="' . wp_create_nonce( 'module-nonce-' . $module_name ) . '">';

		$list .= '<p class="ur-module-description">' . $module_data['description'] . '</p>';

		$list .= '<button data-installed="' . __( 'Install', 'user-registration' ) . '" data-already-installed="' . __( 'Installed', 'user-registration' ) . '" type="button" class="button button-large ' . $button_class . '">' . $button_label . '</button>';

		$list .= '<div style="clear:both"></div>';

		$list .= '</div>';

		$list .= '</div>';

		$list .= '</li>';

		$module_node .= $list;
	}

	$module_node .= '</ul>';

	$module_node .= '<div style="clear:both"></div>';

	$module_node .= '</div>';

	return $module_node;

}
