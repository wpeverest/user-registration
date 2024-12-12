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
 * UR_Form_Field_Membership Class
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
			'default_group'      => 0,
		);
		//override the general settings to add membership group setting.
		add_filter( "user_registration_field_options_general_settings", array( $this, 'settings_override' ), 10, 2 );
		add_filter( "user_registration_form_field_args", array( $this, 'set_args_for_membership' ), 10, 3 );
		add_filter( 'user_registration_payment_fields', array( $this, 'remove_payment_fields' ), 11, 1 );
	}

	public function settings_override( $settings, $id ) {
		if ( "user_registration_membership" !== $id ) {
			return $settings;
		}
		$membership_group_service = new MembershipGroupService();
		$membership_settings      = array(
			'membership_group' => array(
				'setting_id'  => 'membership_group',
				'name'        => 'membership_group',
				'type'        => 'select',
				'label'       => __( 'Membership Group', 'user-registration' ),
				'placeholder' => __( 'Select any membership group.', 'user-registration' ),
				'required'    => 1,
				'tip'         => __( "Select a membership group from the dropdown.", 'user-registration' ),
				'options'     => array( 0 => 'Select a Membership Group.' ) + $membership_group_service->get_membership_groups()
			)
		);

		$settings = array_merge( $membership_settings, $settings );

		unset( $settings['placeholder'] );
		unset( $settings['required'] );

		return $settings;

	}

	public function remove_payment_fields( $fields ) {
		if ( ! isset( $_GET['edit-registration'] ) ) {
			return $fields;
		}
		$has_membership_field = check_membership_field_in_form();
		if ( ! $has_membership_field ) {
			return $fields;
		}

		return array();
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

	public function set_args_for_membership( $args, $key, $value ) {
		if ( isset( $args['field_key'] ) && "membership" == $args['field_key'] ) {
			$args['type']             = 'membership'; //maybe update this in future to be dynamic.
			$membership_group_service = new MembershipGroupService();
			$args['options']          = $membership_group_service->get_group_memberships( $args['membership_group'] );
		}

		return $args;
	}

	/**
	 * set_membership_field
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 * @param $current_row
	 *
	 * @return string
	 */
	public function set_membership_field( $field, $key, $args, $value, $current_row ) {
		$attributes = array(
			'preview' => true,
			'key'     => $key,
			'value'   => $value
		);

		return \WPEverest\URMembership\ShortCodes::member_registration_form( $attributes + $args );
	}
}

return UR_Form_Field_Membership::get_instance();
