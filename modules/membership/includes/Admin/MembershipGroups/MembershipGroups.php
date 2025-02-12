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

class MembershipGroups {
	public function __construct() {
		$this->init();
	}

	public function init() {
		add_filter( 'update_group_ids_before_deleting', array( $this, 'remove_group_ids_having_form' ) , 10 , 1 );
		$this->enqueue_scripts();
	}

	public function remove_group_ids_having_form( $ids  ) {

	}
	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'user-registration-membership' !== $_GET['page'] && ! in_array( $_GET['page'], array(
				'add_groups',
				'list_groups'
			) ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_register_script( 'user-registration-membership-groups', UR_MEMBERSHIP_JS_ASSETS_URL . '/admin/membership-groups' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'user-registration-membership-groups' );
		$this->localize_scripts();
	}

	public function localize_scripts() {
		$membership_group_id = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : null;

		wp_localize_script(
			'user-registration-membership-groups',
			'urmg_localized_data',
			array(
				'_nonce'              => wp_create_nonce( 'ur_membership_group' ),
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'membership_group_id' => $membership_group_id,
				'labels'              => $this->get_i18_labels(),
				'membership_group_url' => admin_url( 'admin.php?page=user-registration-membership&action=list_groups' ),
				'delete_icon'         => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE )
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
			'i18n_prompt_no_membership_selected' => __( 'Please select at least one group.', 'user-registration' )
		);
	}

	public function render_membership_groups_list_table( $menu_items ) {
		$membership_table_list = new MembershipGroupsListTable();
		$enable_members_button = true;
		require __DIR__ . '/../Views/Partials/header.php';
		$membership_table_list->display_page();
	}

	public function render_membership_group_creator( $menu_items ) {
		$membership_service       = new MembershipService();
		$memberships              = $membership_service->list_active_memberships();
		$membership_group_service = new MembershipGroupService();

		if ( isset( $_GET['post_id'] ) && ! empty( $_GET['post_id'] ) ) {
			$group_id         = absint( $_GET['post_id'] );
			$membership_group = $membership_group_service->get_membership_group_by_id( $group_id );
		}
		include __DIR__ . '/../Views/membership-groups-create.php';
	}

}
