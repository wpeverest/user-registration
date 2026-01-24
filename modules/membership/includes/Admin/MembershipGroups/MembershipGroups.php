<?php
/**
 * MembershipGroups
 *
 * @class    MembershipGroups
 * @date     11/25/2024 : 9:57 AM
 */

namespace WPEverest\URMembership\Admin\MembershipGroups;

use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;

class MembershipGroups {
	public function __construct() {
		$this->init();
	}

	public function init() {
		add_filter( 'update_group_ids_before_deleting', array( $this, 'remove_group_ids_having_form' ), 10, 1 );
		$this->enqueue_scripts();
	}

	public function remove_group_ids_having_form( $ids ) {
	}

	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'user-registration-membership' !== $_GET['page'] && ! in_array(
			$_GET['page'],
			array(
				'add_groups',
				'list_groups',
			)
		) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_register_script( 'user-registration-membership-groups', UR()->plugin_url(). '/assets/js/modules/membership/admin/membership-groups' . $suffix . '.js', array( 'jquery' ), UR_VERSION, true );
		wp_enqueue_script( 'user-registration-membership-groups' );
		$this->localize_scripts();
	}

	public function localize_scripts() {
		$membership_group_id = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : null;

		wp_localize_script(
			'user-registration-membership-groups',
			'urmg_localized_data',
			array(
				'_nonce'               => wp_create_nonce( 'ur_membership_group' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'membership_group_id'  => $membership_group_id,
				'labels'               => $this->get_i18_labels(),
				'membership_group_url' => admin_url( 'admin.php?page=user-registration-membership&action=list_groups' ),
				'delete_icon'          => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
			)
		);
	}

	public function get_i18_labels() {
		return array(
			'network_error'                      => esc_html__( 'Network error', 'user-registration' ),
			'i18n_field_is_required'             => _x( 'field is required.', 'user registration membership', 'user-registration' ),
			'i18n_error'                         => _x( 'Error', 'user registration membership', 'user-registration' ),
			'i18n_save'                          => _x( 'Save', 'user registration membership', 'user-registration' ),
			'i18n_prompt_title'                  => __( 'Delete Membership Group', 'user-registration' ),
			'i18n_prompt_bulk_subtitle'          => __( 'Are you sure you want to delete these groups permanently?', 'user-registration' ),
			'i18n_prompt_single_subtitle'        => __( 'Are you sure you want to delete this group permanently?', 'user-registration' ),
			'i18n_prompt_delete'                 => __( 'Delete', 'user-registration' ),
			'i18n_prompt_cannot_delete'          => __( 'Sorry, this group is currently being used in a form. To delete this group please remove this group from form ', 'user-registration' ),
			'i18n_prompt_cancel'                 => __( 'Cancel', 'user-registration' ),
			'i18n_prompt_no_membership_selected' => __( 'Please select at least one group.', 'user-registration' ),
			'i18n_select_payment_gateway'        => __( 'Select Payment Gateway.', 'user-registration' ),

		);
	}

	public function render_membership_groups_list_table( $menu_items ) {
		$membership_table_list = new MembershipGroupsListTable();
		$enable_members_button = true;
		require __DIR__ . '/../Views/Partials/header.php';

		self::enable_groups_module( 'list_group' );

		$membership_table_list->display_page();
	}

	public function render_membership_group_creator( $menu_items ) {
		$membership_service          = new MembershipService();
		$membership_group_service    = new MembershipGroupService();
		$membership_group_repository = new MembershipGroupRepository();

		self::enable_groups_module( 'add_group' );

		$memberships = $membership_service->list_active_memberships();
		$group_id    = 0;

		if ( isset( $_GET['post_id'] ) && ! empty( $_GET['post_id'] ) ) {
			$group_id         = absint( $_GET['post_id'] );
			$membership_group = $membership_group_service->get_membership_group_by_id( $group_id );
		}

		foreach ( $memberships as $key => $membership ) {
			$current_membership_group = $membership_group_repository->get_membership_group_by_membership_id( $membership['ID'] );

			if ( ! empty( $current_membership_group ) && absint( $current_membership_group['ID'] ) !== $group_id ) {
				unset( $memberships[ $key ] );
			}
		}

		include __DIR__ . '/../Views/membership-groups-create.php';
	}

	/**
	 * Enable groups module if not already enabled.
	 */
	private function enable_groups_module( $action ) {
		$group_installation_flag = get_option( 'urm_group_module_installation_flag', false );

		if ( ! ur_check_module_activation( 'membership-groups' ) && ( 'add_group' === $action || ! $group_installation_flag ) ) {
			$enabled_features   = get_option( 'user_registration_enabled_features', array() );
			$enabled_features[] = 'user-registration-membership-groups';
			update_option( 'user_registration_enabled_features', $enabled_features );
			update_option( 'urm_group_module_installation_flag', true );
		}
	}
}
