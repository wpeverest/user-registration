<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Frontend
 * @version  1.0.0
 * @package  UserRegistration/Frontend
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Frontend Class
 */
class UR_Frontend {

	/**
	 * Hook in tabs.
	 */
	private static $_instance;

	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
	}

	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function includes() {
		include_once( UR_ABSPATH . 'includes' . UR_DS . 'frontend' . UR_DS . 'class-ur-frontend-form-handler.php' );
	}

	/**
	 * Includes any classes we need within admin.
	 */
	public function user_registration_frontend_form( $field_object, $form_id ) {

		$class_name = ur_load_form_field_class($field_object->field_key);
		$instance = $class_name::get_instance();
/*
		$class_path = UR_FORM_PATH . 'class-ur-' . trim( str_replace( '_', '-', $field_object->field_key ) ) . '.php';

		$class_name = 'UR_' . join( '_', array_map( 'ucfirst', explode( '_', $field_object->field_key ) ) );

		if ( ! class_exists( $class_name ) ) {

			$instance = include( $class_path );

		} else {

			$instance = $class_name::get_instance();

		}*/

 		$setting['general_setting'] = $field_object->general_setting;

		$setting['advance_setting'] = $field_object->advance_setting;

		$field_type                 = ur_get_field_type( $field_object->field_key );

		$instance->frontend_includes( $setting, $form_id,$field_type,$field_object->field_key );
	}
}

return new UR_Frontend();
