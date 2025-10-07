<?php
/**
 * UR_Form_Field_Membership.
 *
 * @package  URMembership
 */

use WPEverest\URMembership\Admin\Services\ {
	MembershipGroupService,
	MembershipService
};

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
			'label' => __( 'Membership', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-membership-field',
		);
		$membership_group_service       = new MembershipGroupService();
		$default_group_id               = $membership_group_service->get_default_group_id();
		$default_membership_field_name  = get_option( 'ur_membership_default_membership_field_name', true );

		$this->field_defaults = array(
			'default_label'                     => __( 'Membership Field', 'user-registration' ),
			'default_field_name'                => ! empty( $default_membership_field_name ) && str_contains( $default_membership_field_name, 'membership_field_' ) ? $default_membership_field_name : 'membership_field_' . ur_get_random_number(),
			'default_group'                     => ! empty( $default_group_id ) ? $default_group_id : 0,
			'default_membership_listing_option' => 'all',
		);
		// override the general settings to add membership group setting.
		add_filter( 'user_registration_field_options_general_settings', array( $this, 'settings_override' ), 10, 2 );
		add_filter( 'user_registration_form_field_args', array( $this, 'set_args_for_membership' ), 10, 3 );
		add_filter( 'user_registration_form_field_membership', array( $this, 'set_membership_field' ), 10, 5 );
	}

	public function settings_override( $settings, $id ) {
		if ( 'user_registration_membership' !== $id ) {
			return $settings;
		}
		$membership_group_service = new MembershipGroupService();

		$membership_settings = array(
			'membership_listing_option' => array(
				'setting_id'  => 'membership_listing_option',
				'name'        => 'membership_listing_option',
				'type'        => 'select',
				'label'       => __( 'Membership Display Options', 'user-registration' ),
				'placeholder' => __( 'Select an option', 'user-registration' ),
				'required'    => 1,
				'tip'         => __( 'Choose how you want the memberships to be listed in the form.', 'user-registration' ),
				'default'     => 'all',
				'options'     => array(
					'all'   => 'Show all Memberships.',
					'group' => 'Select a group',
				),
			),
			'membership_group'          => array(
				'setting_id'  => 'membership_group',
				'name'        => 'membership_group',
				'type'        => 'select',
				'label'       => __( 'Select Membership Group', 'user-registration' ),
				'placeholder' => __( 'Select any membership group.', 'user-registration' ),
				'required'    => 1,
				'tip'         => __( "Choose an existing membership group from the dropdown, or create a new one <a href='?page=user-registration-membership&action=add_groups'>here</a>.", 'user-registration' ),
				'options'     => array( 0 => 'Select a Membership Group.' ) + $membership_group_service->get_membership_groups(),
			),

		);

		$settings = $settings + $membership_settings;
		unset( $settings['placeholder'] );
		unset( $settings['required'] );

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

	public function set_args_for_membership( $args, $key, $value ) {
		if ( isset( $args['field_key'] ) && 'membership' == $args['field_key'] ) {
			$args['type'] = 'membership'; // maybe update this in future to be dynamic.
			if ( isset( $args['membership_listing_option'] ) && 'group' === $args['membership_listing_option'] ) {
				$membership_group_service = new MembershipGroupService();
				$memberships              = $membership_group_service->get_group_memberships( $args['membership_group'] );
			} else {
				$membership_service = new MembershipService();
				$memberships        = $membership_service->list_active_memberships();
			}
			$args['options'] = $memberships;
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
			'value'   => $value,
		);
		return \WPEverest\URMembership\ShortCodes::member_registration_form( $attributes + $args );
	}
}

return UR_Form_Field_Membership::get_instance();
