<?php
/**
 * UR_Form_Field_Membership.
 *
 * @package  URMembership
 */

use WPEverest\URMembership\Admin\Services\MembershipGroupService;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Text Class
 */
class UR_Form_Field_Membership extends UR_Form_Field {

	/**
	 * Instance Variable.
	 *
	 * @var [mixed]
	 */
	private static $_instance;

	/**
	 * Get Instance of class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id                       = 'user_registration_membership';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Membership Field', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-membership-field',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Membership Field', 'user-registration' ),
			'default_field_name' => 'membership_field_' . ur_get_random_number(),
			'default_group' => 0,
		);
		//override the general settings to add membership group setting.
		add_filter( "user_registration_field_options_general_settings", array( $this, 'settings_override' ), 10, 2 );
	}

	public function settings_override( $settings, $id ) {
		if ( "user_registration_membership" !== $id ) {
			return $settings;
		}
		$membership_group_service = new MembershipGroupService();

		$settings['membership_group']      = array(
			'setting_id'  => 'membership_group',
			'type'        => 'select',
			'label'       => __( 'Membership Group', 'user-registration' ),
			'placeholder' => __( 'Select any membership group.', 'user-registration' ),
			'required'    => 1,
			'tip'         => __( "Select a membership group from the dropdown.", 'user-registration' ),
			'options'     => array( 0 => 'Select a Membership Group.' ) + $membership_group_service->get_membership_groups()
		);

		return $settings;

	}

	/**
	 * Get Registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}

	/**
	 * Validate field.
	 *
	 * @param [object] $single_form_field Field Data.
	 * @param [object] $form_data Form Data.
	 * @param [string] $filter_hook Hook.
	 * @param [int]    $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

	}
}

return UR_Form_Field_Membership::get_instance();
