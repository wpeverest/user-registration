<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Section_Divider
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Section_Divider Class
 */
class UR_Section_Divider extends UR_Form_Field {

	private static $_instance;


	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_section_divider';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Section Divider','user-registration' ),

			'icon' => 'dashicons dashicons-minus',
		);

		$this->field_defaults = array(

			'default_label' => __( 'Section Divider','user-registration' ),

			'default_field_name' => 'section_divider_' . ur_get_random_number(),

		);
	}


	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// TODO: Implement validation() method.
	}
}

return UR_Section_Divider::get_instance();
