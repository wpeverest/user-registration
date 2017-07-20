<?php
/**
 * User Registration Shortcodes.
 *
 * @class    UR_Modules
 * @version  1.4.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Modules Class
 */
class UR_Modules {


	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(

			'ajax_module_install'   => true,
			'ajax_module_uninstall' => true,


		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_module_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_user_registration_module_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function init() {

		self::register_modules();


		self::init_modules();


		self::add_ajax_events();

	}

	private static function register_modules() {


		do_action( 'before_user_registration_modules_registered' );

		$modules =
			apply_filters( 'user_registration_modules', array(

				'captcha' => array(
					'file'        => 'captcha',
					'description' => __( 'This is simple captcha module for user registration plugin', 'user-registration' ),
					'icon'        => 'dashicons-admin-site',
					'title'       => 'Captcha',
					'class'       => 'Captcha'

				),
				'test'    => array(
					'file'        => 'test',
					'description' => __( 'This is simple tests module for user registration plugin', 'user-registration' ),
					'icon'        => 'dashicons-admin-site',
					'title'       => 'Test',
					'class'       => 'Test'


				)


			) );

		$status = ur_register_modules( $modules );


		do_action( 'after_user_registration_modules_registered' );

	}

	public static function ajax_module_install() {

		$module_name = isset( $_POST['module_name'] ) ? $_POST['module_name'] : '';

		check_ajax_referer( 'module-nonce-' . $module_name, 'security' );

		if ( true !== ur_get_is_register_module( $module_name ) ) {

			wp_send_json_error( array(

				'message' => __( 'Not registered.', 'user-registration' )
			) );


			exit;

		}
		if ( ur_is_module_already_install( $module_name ) ) {


			wp_send_json_error( array(

				'message' => __( 'Already installed.', 'user-registration' )
			) );

			exit;
		}


		$user_id = get_current_user_id();

		$is_installed = 1;

		global $wpdb;

		$status = $wpdb->query( $wpdb->prepare(
			"
INSERT INTO  {$wpdb->prefix}user_registration_modules
(module_name,installation_date,is_installed,user_id)
VALUES
(%s,CURRENT_TIMESTAMP ,%d,%d);", $module_name, $is_installed, $user_id
		) );

		if ( $status > 0 ) {
			wp_send_json_success( array(

				'message' => __( 'Successfully installed.', 'user-registration' )
			) );
			exit;
		}

		wp_send_json_error( array(

			'message' => __( 'Could not installed.', 'user-registration' )
		) );

		exit;

	}

	public static function ajax_module_uninstall() {

		$module_name = isset( $_POST['module_name'] ) ? $_POST['module_name'] : '';

		check_ajax_referer( 'module-nonce-' . $module_name, 'security' );

		if ( true !== ur_get_is_register_module( $module_name ) ) {

			wp_send_json_error( array(

				'message' => __( 'Not registered.', 'user-registration' )
			) );


			exit;

		}
		if ( ! ur_is_module_already_install( $module_name ) ) {


			wp_send_json_error( array(

				'message' => __( 'Module not installed.', 'user-registration' )
			) );

			exit;
		}


		$status = ur_uninstall_module( $module_name );


		if ( $status ) {
			wp_send_json_success( array(

				'message' => __( 'Successfully uninstalled.', 'user-registration' )
			) );
			exit;
		}

		wp_send_json_error( array(

			'message' => __( 'Could not uninstall.', 'user-registration' )
		) );

		exit;

	}

	public static function init_modules() {

		$all_installed_modules = ur_get_all_installed_modules();

		foreach ( $all_installed_modules as $module ) {

			$registered_module = ur_get_module( $module->module_name );


			$module_file_path = UR_MODULE_PATH . $module->module_name . UR_DS . $registered_module['file'];

			if ( file_exists( $module_file_path ) ) {

				include_once $module_file_path;
			}

			if ( method_exists( $registered_module['class'], 'init' ) ) {

				$registered_module['class']::init();

			}

		}
	}
}

UR_Modules::init();

