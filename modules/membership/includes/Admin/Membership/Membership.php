<?php
/**
 * URMembership Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  URMembership/Admin
 * @category Admin
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Membership;

use WPEverest\URMembership\Admin\Members\Members;
use WPEverest\URMembership\Admin\Membership\ListTable;
use WPEverest\URMembership\Admin\MembershipGroups\MembershipGroups;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Membership {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'user_registration_screen_ids', array( $this, 'ur_membership_add_screen_id' ) );
		// add_action( 'admin_menu', array( $this, 'add_urm_menu' ), 15 );
		add_action( 'admin_init', array( $this, 'actions' ) );
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
		add_filter( 'user_registration_login_options', array( $this, 'add_payment_login_option' ) );
		add_action( 'admin_head', array( $this, 'fix_menu_highlighting' ) );
	}

	/**
	 * Fix menu highlighting for frontend listing edit pages.
	 */
	public function fix_menu_highlighting() {
		global $submenu_file, $parent_file;

		if ( isset( $_GET['page'] ) && 'user-registration-members' === $_GET['page'] ) {
			$parent_file  = 'user-registration';
			$submenu_file = 'user-registration-membership';
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		global $wp_scripts;
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';

		if ( empty( $_GET['page'] ) || 'user-registration-membership' !== $_GET['page'] ) {
			return;
		}

		// Enqueue jQuery UI Sortable for drag-and-drop functionality
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_register_script(
			'user-registration-membership',
			UR()->plugin_url() . '/assets/js/modules/membership/admin/user-registration-membership-admin' . $suffix . '.js',
			array(
				'jquery',
				'jquery-ui-sortable',
			),
			UR_VERSION,
			true
		);
		wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), UR_VERSION, true );
		wp_enqueue_script( 'ur-snackbar' );
		wp_enqueue_script( 'sweetalert2' );
		wp_enqueue_script( 'user-registration-membership' );

		// Enqueue membership access rules script if content restriction module is enabled
		$membership_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

		// Enqueue jQuery UI for sortable if needed
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Enqueue membership access rules script (always use non-minified for now)
		$script_path = UR()->plugin_url() . '/assets/js/modules/content-restriction/admin/urcr-membership-access-rules.js';

		wp_enqueue_script(
			'urcr-membership-access-rules',
			$script_path,
			array( 'jquery', 'user-registration-membership' ),
			UR()->version,
			true
		);

		// Localize script with necessary data
		$localized_data = array();
		if ( class_exists( '\URCR_Admin_Assets' ) ) {
			$localized_data = \URCR_Admin_Assets::get_localized_data();
		}
		$localized_data['membership_id']      = $membership_id;
		$localized_data['ajax_url']           = admin_url( 'admin-ajax.php' );
		$localized_data['nonce']              = wp_create_nonce( 'urcr_manage_content_access_rule' );
		$localized_data['today']              = date( 'Y-m-d' );
		$localized_data['drip_content_label'] = array(
			'drip_this_content' => __( 'Drip This Content', 'user-registration' ),
			'fixed_date'        => __( 'Fixed Date', 'user-registration' ),
			'days_after'        => __( 'Days After', 'user-registration' ),
		);

		wp_localize_script(
			'urcr-membership-access-rules',
			'urcr_membership_access_data',
			$localized_data
		);

		$this->localize_scripts();
	}

	/**
	 * Enqueue styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		if ( empty( $_GET['page'] ) || 'user-registration-membership' !== $_GET['page'] ) {
			return;
		}
		if ( ! wp_style_is( 'ur-snackbar', 'registered' ) ) {
			wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), UR_VERSION );
		}
		wp_enqueue_style( 'ur-snackbar' );
		wp_enqueue_style( 'sweetalert2' );
		wp_register_style( 'ur-membership-admin-style', UR()->plugin_url() . '/assets/css/modules/membership/user-registration-membership-admin.css', array(), UR_VERSION );
		wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		wp_enqueue_style( 'ur-core-builder-style' );
		wp_enqueue_style( 'ur-membership-admin-style' );

		// Enqueue shared content restriction styles if content restriction module is enabled
		wp_register_style(
			'urcr-shared',
			UR()->plugin_url() . '/assets/css/urcr-shared.css',
			array(),
			UR()->version
		);
		wp_enqueue_style( 'urcr-shared' );

		wp_register_style(
			'urcr-content-access-restriction',
			UR()->plugin_url() . '/assets/css/urcr-content-access-restriction.css',
			array( 'urcr-shared' ),
			UR()->version
		);
		wp_enqueue_style( 'urcr-content-access-restriction' );
	}

	/**
	 * Membership Listing admin actions.
	 */
	public function actions() {
		if ( isset( $_GET['page'] ) && 'user-registration-membership' === $_GET['page'] ) {

			// Bulk actions.
			if ( isset( $_REQUEST['action'] ) && ( isset( $_REQUEST['membership'] ) || isset( $_REQUEST['membership_group_id'] ) ) ) {
				$this->bulk_actions();
			}

			// Empty trash.
			if ( isset( $_GET['empty_trash'] ) ) {
				$this->empty_trash();
			}
		}
	}

	// todo might need to remove later if none of the bulk actions are used

	/**
	 * Bulk actions.
	 */
	private function bulk_actions() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permissions to edit user registration membership lists!', 'user-registration' ) );
		}
		$delete_membership = true;
		$membership_list   = array_map( 'absint', ! empty( $_REQUEST['membership'] ) ? (array) $_REQUEST['membership'] : array() );

		if ( empty( $membership_list ) ) {
			$delete_membership = false;
			$membership_list   = array_map( 'absint', ! empty( $_REQUEST['membership_group_id'] ) ? (array) $_REQUEST['membership_group_id'] : array() );
		}

		$delete_list = $membership_list;
		$action      = isset( $_REQUEST['action'] ) ? wp_unslash( $_REQUEST['action'] ) : array();

		switch ( $action ) {
			case 'trash':
				// $this->bulk_trash( $delete_list );
				break;
			case 'untrash':
				// $this->bulk_untrash( $membership_list );
				break;
			case 'delete':
				// $this->bulk_trash( $delete_list, true, $delete_membership );
				break;
			default:
				break;
		}
	}

	/**
	 * Bulk trash/delete.
	 *
	 * @param array $membership_lists Membership List post id.
	 * @param bool  $delete Delete action.
	 */
	private function bulk_trash( $membership_lists, $delete = false, $is_membership = true ) {
		$membership_group_service = new MembershipGroupService();
		foreach ( $membership_lists as $membership_id ) {
			$form_id = $membership_group_service->get_group_form_id( $membership_id );
			if ( $delete ) {
				if ( ! $is_membership && ( '' != $form_id ) ) {
					break;
				}
				wp_delete_post( $membership_id, true );
			} else {
				wp_trash_post( $membership_id );
			}
		}

		$type   = ! EMPTY_TRASH_DAYS || $delete ? 'deleted' : 'trashed';
		$qty    = count( $membership_lists );
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$redirect_args = array(
			'page' => 'user-registration-membership',
		);

		if ( ! $is_membership ) {
			$redirect_args['action'] = 'list_groups';
		}

		if ( $status ) {
			$redirect_args['status'] = $status;
		}

		$redirect_args[ $type ] = $qty;

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit();
	}

	/**
	 * Bulk untrash.
	 *
	 * @param array $membership_lists Membership List post id.
	 */
	private function bulk_untrash( $membership_lists ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permissions to trash Memberships.!', 'user-registration' ) );
		}
		foreach ( $membership_lists as $membership_id ) {
			wp_untrash_post( $membership_lists );
		}
		$qty = count( $membership_lists );
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=user-registration-membership&status=trashed&untrashed=' . $qty ) ) );
		exit();
	}

	/**
	 * Empty Trash.
	 */
	private function empty_trash() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'empty_trash' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permissions to delete Memberships!', 'user-registration' ) );
		}

		$membership_lists = get_posts(
			array(
				'post_type'           => 'ur_membership',
				'ignore_sticky_posts' => true,
				'nopaging'            => true,
				'post_status'         => 'trash',
				'fields'              => 'ids',
			)
		);

		foreach ( $membership_lists as $membership ) {
			wp_delete_post( $membership, true );
		}

		$qty = count( $membership_lists );
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=user-registration-membership&deleted=' . $qty ) ) );
		exit();
	}

	/**
	 * Remove Notices.
	 */
	public static function hide_unrelated_notices() {
		// Return on other than access rule creator page.
		if ( empty( $_REQUEST['page'] ) || 'user-registration-membership' !== $_REQUEST['page'] ) {
			return;
		}

		ur_membership_remove_unrelated_notices();
	}

	/**
	 * Add Membership addons screen_ids to the pool of user registration screen ids.
	 *
	 * @param array $screen_ids Screens ids of user registration and addons.
	 *
	 * @return array
	 */
	public function ur_membership_add_screen_id( $screen_ids ) {

		$urm_screen_ids = array(
			'user-registration-membership_page_user-registration-membership',
			'ur_membership',
		);

		return array_merge( $screen_ids, $urm_screen_ids );
	}

	/**
	 * Add User Membership Menu
	 *
	 * @return void
	 */
	public function add_urm_menu() {
		$rules_page = add_submenu_page(
			'user-registration',
			__( 'Memberships', 'user-registration' ), // page title
			__( 'Memberships', 'user-registration' ), // menu title
			'edit_posts', // capability
			'user-registration-membership', // slug
			array(
				$this,
				'render_membership_page',
			),
			2
		);
		add_action( 'load-' . $rules_page, array( $this, 'membership_initialization' ) );

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'user-registration-membership', 'user-registration-membership-groups', 'user-registration-members', 'user-registration-coupons', 'user-registration-content-restriction', 'member-payment-history', 'user-registration-team' ) ) ) {
			add_submenu_page(
				'user-registration',
				__( 'Membership Groups', 'user-registration' ),
				'↳ ' . __( 'Groups', 'user-registration' ),
				'manage_user_registration',
				'user-registration-membership&action=list_groups',
				array(
					$this,
					'render_membership_page',
				),
				3
			);

			$members = new Members();
			add_submenu_page(
				'user-registration',
				__( 'Membership Members', 'user-registration' ),
				'↳ ' . __( 'Members', 'user-registration' ),
				'manage_user_registration',
				'user-registration-members',
				array( $members, 'render_members_page' ),
				18
			);
		}
	}

	/**
	 * Init membership before loading the page
	 *
	 * @since 1.0.0
	 */
	public function membership_initialization() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				'<h1>' . esc_html__( 'You need a higher level of permission.', 'user-registration' ) . '</h1>' .
				'<p>' . esc_html__( 'Sorry, you are not allowed to list users.', 'user-registration' ) . '</p>',
				403
			);
		}
		if ( isset( $_GET['page'] ) && 'user-registration-membership' === $_GET['page'] ) {

			$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			switch ( $action_page ) {
				case 'add_new_membership':
					break;
				default:
					global $membership_table_list;
					require_once __DIR__ . '/ListTable.php';
					$membership_table_list = new ListTable();
					$membership_table_list->process_actions();
					break;
			}
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function render_membership_page() {

		$action_page        = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$post_id            = isset( $_GET['post_id'] ) ? sanitize_text_field( $_GET['post_id'] ) : '';
		$membership_details = array();
		$membership         = array();
		$menu_items         = get_membership_menus();
		$membership_groups  = new MembershipGroups();

		switch ( $action_page ) {
			case 'add_new_membership':
				if ( $post_id ) {
					$membership             = get_post( $post_id );
					$membership_details     = json_decode( wp_unslash( get_post_meta( $post_id, 'ur_membership', true ) ), true );
					$membership_description = get_post_meta( $post_id, 'ur_membership_description', true );

					$membership_details['description'] = $membership_description;
				}

				$this->render_membership_creator( $membership, $membership_details, $menu_items );
				break;
			case 'list_groups':
				if ( ur_check_module_activation( 'membership-groups' ) ) {
					$membership_groups->render_membership_groups_list_table( $menu_items );
				} else {
					$this->render_membership_viewer( $menu_items );
				}
				break;
			case 'add_groups':
				$membership_groups->render_membership_group_creator( $menu_items );
				break;
			default:
				$this->render_membership_viewer( $menu_items );
		}
	}

	/**
	 * render_membership_viewer
	 *
	 * @return void
	 */
	public function render_membership_viewer( $menu_items ) {
		global $membership_table_list;
		if ( ! $membership_table_list ) {
			return;
		}
		$enable_members_button = true;
		require __DIR__ . '/../Views/Partials/header.php';
		$membership_table_list->display_page();
	}

	/**
	 * Render Membership Creator
	 *
	 * @param $membership
	 * @param $membership_details
	 * @param $menu_items
	 *
	 * @return void
	 */
	public function render_membership_creator( $membership = null, $membership_details = null, $menu_items = null ) {
		$enable_membership_button    = false;
		$roles                       = wp_roles()->role_names;
		$membership_service          = new MembershipService();
		$membership_group_service    = new MembershipGroupService();
		$membership_group_repository = new MembershipGroupRepository();

		$memberships = $membership_service->list_active_memberships();

		$group_id = 0;

		if ( isset( $_GET['post_id'] ) && ! empty( $_GET['post_id'] ) ) {
			$membership_id    = absint( $_GET['post_id'] );
			$membership_group = $membership_group_repository->get_membership_group_by_membership_id( $membership_id );
			$group_id         = $membership_group['ID'] ?? 0;
		}

		foreach ( $memberships as $key => $_membership ) {
			$current_membership_group = $membership_group_repository->get_membership_group_by_membership_id( $_membership['ID'] );

			if ( ! empty( $current_membership_group ) && absint( $current_membership_group['ID'] ) !== $group_id ) {
				unset( $memberships[ $key ] );
			}
		}

		// Get membership rule data if membership exists
		$membership_rule_data         = null;
		$membership_condition_options = array();
		$membership_localized_data    = array();

		// Get condition options and localized data
		if ( class_exists( '\URCR_Admin_Assets' ) ) {
			$membership_localized_data    = \URCR_Admin_Assets::get_localized_data();
			$membership_condition_options = isset( $membership_localized_data['condition_options'] ) ? $membership_localized_data['condition_options'] : array();

			// Filter for free users - show membership, roles, and user_state
			// For pro users, show all conditions
			if ( ! isset( $membership_localized_data['is_pro'] ) || ! $membership_localized_data['is_pro'] ) {
				$membership_condition_options = array_filter(
					$membership_condition_options,
					function ( $option ) {
						return isset( $option['value'] ) && ( $option['value'] === 'membership' || $option['value'] === 'roles' || $option['value'] === 'user_state' );
					}
				);
			}
		}

		if ( $membership && isset( $membership->ID ) ) {
			$membership_id = $membership->ID;

			// Get membership rule data using reusable function
			if ( function_exists( 'urcr_get_membership_rule_data' ) ) {
				$membership_rule_data = urcr_get_membership_rule_data( $membership_id );
			}
		}

		include __DIR__ . '/../Views/membership-create.php';
	}

	/**
	 * Get membership create page tabs configuration
	 *
	 * @return array Array of tab configurations with keys: id, label, step, partial, icon_svg
	 */
	public function get_membership_create_tabs() {
		// Helper function to load SVG icon from file
		$load_svg_icon = function ( $icon_name ) {
			if ( function_exists( 'UR' ) && method_exists( UR(), 'plugin_path' ) ) {
				$icon_path = UR()->plugin_path() . '/assets/images/icons/' . $icon_name . '.svg';
				if ( file_exists( $icon_path ) ) {
					return file_get_contents( $icon_path );
				}
			}

			return '';
		};

		$tabs = array(
			array(
				'id'       => 'ur-basic-tab',
				'label'    => __( 'Basics', 'user-registration' ),
				'step'     => 0,
				'partial'  => 'membership-create-basics-tab.php',
				'icon_svg' => $load_svg_icon( 'membership-basics-icon' ),
			),
			array(
				'id'       => 'ur-access-tab',
				'label'    => __( 'Access', 'user-registration' ),
				'step'     => 1,
				'partial'  => 'membership-create-access-tab.php',
				'icon_svg' => $load_svg_icon( 'membership-access-icon' ),
			),
			array(
				'id'       => 'ur-advanced-tab',
				'label'    => __( 'Advanced', 'user-registration' ),
				'step'     => 2,
				'partial'  => 'membership-create-advanced-tab.php',
				// 'icon_svg' => $load_svg_icon( 'membership-advanced-icon' ),
				'icon_svg' => $load_svg_icon( 'advanced' ),
			),
		);

		/**
		 * Filter membership create page tabs
		 *
		 * @param array $tabs Array of tab configurations
		 *
		 * @return array Modified tabs array
		 */
		return apply_filters( 'ur_membership_create_tabs', $tabs );
	}

	/**
	 * Render condition row HTML for membership access rules
	 *
	 * @param array $condition Condition data.
	 * @param array $condition_options Available condition options.
	 * @param array $localized_data Localized data for labels and options.
	 * @param bool  $is_locked Whether the condition is locked (non-editable).
	 *
	 * @return string HTML for condition row.
	 */
	private function render_condition_row( $condition, $condition_options, $localized_data, $is_locked = false ) {
		$condition_id = isset( $condition['id'] ) ? esc_attr( $condition['id'] ) : 'x' . time() . '_' . wp_rand();
		$type         = isset( $condition['type'] ) ? sanitize_text_field( $condition['type'] ) : 'roles';
		$value        = isset( $condition['value'] ) ? $condition['value'] : '';

		// Find condition option
		$selected_option = null;
		foreach ( $condition_options as $option ) {
			if ( $option['value'] === $type ) {
				$selected_option = $option;
				break;
			}
		}

		if ( ! $selected_option ) {
			// Check if condition_options is not empty before accessing index 0
			if ( ! empty( $condition_options ) && isset( $condition_options[0] ) ) {
				$selected_option = $condition_options[0];
				$type            = isset( $selected_option['value'] ) ? $selected_option['value'] : $type;
			} else {
				// Fallback if condition_options is empty
				$selected_option = array(
					'value' => $type,
					'label' => $type,
					'type'  => 'multiselect',
				);
			}
		}

		$input_type = isset( $selected_option['type'] ) ? $selected_option['type'] : 'multiselect';
		$label      = isset( $selected_option['label'] ) ? $selected_option['label'] : $type;

		// Build condition field select
		$disabled_attr = $is_locked ? ' disabled' : '';
		$field_select  = '<select class="urcr-condition-field-select urcr-condition-value-input"' . $disabled_attr . '>';
		foreach ( $condition_options as $option ) {
			$selected      = ( $option['value'] === $type ) ? 'selected' : '';
			$field_select .= '<option value="' . esc_attr( $option['value'] ) . '" ' . $selected . '>' . esc_html( $option['label'] ) . '</option>';
		}
		$field_select .= '</select>';

		// Build value input
		$value_input = $this->render_condition_value_input( $condition_id, $input_type, $type, $value, $localized_data, $is_locked );

		// Remove button - hide if locked
		$remove_button = '';
		if ( ! $is_locked ) {
			$remove_button = '<button type="button" class="button button-link-delete urcr-condition-remove" aria-label="' . esc_attr__( 'Remove condition', 'user-registration' ) . '">' .
							'<span class="dashicons dashicons-no-alt"></span>' .
							'</button>';
		}

		$operator_text = esc_html__( 'is', 'user-registration' );

		return '<div class="urcr-condition-wrapper" data-condition-id="' . esc_attr( $condition_id ) . '">' .
				'<div class="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">' .
				'<div class="urcr-condition-only ur-d-flex ur-align-items-start">' .
				'<div class="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">' .
				'<div class="urcr-condition-field-name">' . $field_select . '</div>' .
				'<div class="urcr-condition-operator"><span>' . $operator_text . '</span></div>' .
				'<div class="urcr-condition-value">' . $value_input . '</div>' .
				'</div>' .
				'</div>' .
				'</div>' .
				$remove_button .
				'</div>';
	}

	/**
	 * Render condition value input HTML
	 *
	 * @param string $condition_id Condition ID.
	 * @param string $input_type Input type (multiselect, checkbox, date, period, number, text).
	 * @param string $field_type Field type.
	 * @param mixed  $value Current value.
	 * @param array  $localized_data Localized data.
	 * @param bool   $is_locked Whether the input is locked (non-editable).
	 *
	 * @return string HTML for value input.
	 */
	private function render_condition_value_input( $condition_id, $input_type, $field_type, $value, $localized_data, $is_locked = false ) {
		$html = '';

		$disabled_attr = $is_locked ? ' disabled' : '';

		if ( $field_type === 'ur_form_field' ) {
			$form_id     = '';
			$form_fields = array();
			if ( is_array( $value ) && isset( $value['form_id'] ) ) {
				$form_id = sanitize_text_field( $value['form_id'] );
			}
			if ( is_array( $value ) && isset( $value['form_fields'] ) && is_array( $value['form_fields'] ) ) {
				$form_fields = $value['form_fields'];
			}
			$value_attr = ' data-value="' . esc_attr( wp_json_encode( $value ) ) . '"';

			$ur_forms = isset( $localized_data['ur_forms'] ) ? $localized_data['ur_forms'] : array();

			$html  = '<div class="urcr-ur-form-field-condition" data-condition-id="' . esc_attr( $condition_id ) . '"' . $value_attr . '>';
			$html .= '<div class="urcr-form-selection ur-d-flex ur-align-items-center ur-g-4 ur-mb-2">';
			$html .= '<select class="urcr-form-select components-select-control__input urcr-condition-value-input"' . $disabled_attr . '>';
			$html .= '<option value="">' . esc_html__( 'Select a form', 'user-registration' ) . '</option>';
			foreach ( $ur_forms as $id => $title ) {
				$selected = ( (string) $id === (string) $form_id ) ? 'selected' : '';
				$html    .= '<option value="' . esc_attr( $id ) . '" ' . $selected . '>' . esc_html( $title ) . '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';
			$html .= '<div class="urcr-form-fields-list"></div>';
			$html .= '</div>';
		} elseif ( $input_type === 'multiselect' ) {
			// Add data attribute for values to be set by JavaScript
			$value_attr = '';
			if ( is_array( $value ) && ! empty( $value ) ) {
				$value_attr = ' data-value="' . esc_attr( wp_json_encode( $value ) ) . '"';
			} elseif ( ! empty( $value ) ) {
				$value_attr = ' data-value="' . esc_attr( wp_json_encode( array( $value ) ) ) . '"';
			}
			$html = '<select class="urcr-enhanced-select2 urcr-condition-value-input" multiple data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '"' . $value_attr . $disabled_attr . '></select>';
		} elseif ( $input_type === 'checkbox' ) {
			// User state - radio buttons
			$checked_logged_in  = ( $value === 'logged-in' || $value === 'logged_in' || $value === '' ) ? 'checked' : '';
			$checked_logged_out = ( $value === 'logged-out' || $value === 'logged_out' ) ? 'checked' : '';
			$logged_in_label    = isset( $localized_data['labels']['logged_in'] ) ? $localized_data['labels']['logged_in'] : __( 'Logged In', 'user-registration' );
			$logged_out_label   = isset( $localized_data['labels']['logged_out'] ) ? $localized_data['labels']['logged_out'] : __( 'Logged Out', 'user-registration' );

			$html = '<div class="urcr-checkbox-radio-input">' .
					'<label><input type="radio" name="condition_' . esc_attr( $condition_id ) . '_user_state" value="logged-in" ' . $checked_logged_in . $disabled_attr . '> ' . esc_html( $logged_in_label ) . '</label>' .
					'<label><input type="radio" name="condition_' . esc_attr( $condition_id ) . '_user_state" value="logged-out" ' . $checked_logged_out . $disabled_attr . '> ' . esc_html( $logged_out_label ) . '</label>' .
					'</div>';
		} elseif ( $input_type === 'date' ) {
			$html = '<input type="date" class="urcr-condition-value-input" data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '" value="' . esc_attr( $value ) . '"' . $disabled_attr . '>';
		} elseif ( $input_type === 'period' ) {
			$period_select = 'During';
			$period_input  = '';
			if ( is_array( $value ) ) {
				$period_select = isset( $value['select'] ) ? sanitize_text_field( $value['select'] ) : 'During';
				$period_input  = isset( $value['input'] ) ? absint( $value['input'] ) : '';
			}

			$during_text      = esc_html__( 'During', 'user-registration' );
			$after_text       = esc_html__( 'After', 'user-registration' );
			$days_placeholder = esc_attr__( 'Days', 'user-registration' );

			$html = '<div class="urcr-period-input-group ur-d-flex ur-align-items-center" style="gap: 8px;">' .
					'<select class="urcr-period-select urcr-condition-value-input" data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '" data-period-part="select"' . $disabled_attr . '>' .
					'<option value="During" ' . ( $period_select === 'During' ? 'selected' : '' ) . '>' . $during_text . '</option>' .
					'<option value="After" ' . ( $period_select === 'After' ? 'selected' : '' ) . '>' . $after_text . '</option>' .
					'</select>' .
					'<input type="number" class="urcr-period-number urcr-condition-value-input" data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '" data-period-part="input" value="' . esc_attr( $period_input ) . '" min="0" placeholder="' . $days_placeholder . '"' . $disabled_attr . '>' .
					'</div>';
		} elseif ( $input_type === 'number' ) {
			$html = '<input type="number" class="urcr-condition-value-input" data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '" value="' . esc_attr( $value ) . '"' . $disabled_attr . '>';
		} else {
			$html = '<input type="text" class="urcr-condition-value-input" data-condition-id="' . esc_attr( $condition_id ) . '" data-field-type="' . esc_attr( $field_type ) . '" value="' . esc_attr( $value ) . '"' . $disabled_attr . '>';
		}

		return $html;
	}

	/**
	 * Render content target HTML
	 *
	 * @param array $target Target data.
	 * @param array $localized_data Localized data for labels.
	 *
	 * @return string HTML for content target.
	 */
	private function render_content_target( $target, $localized_data ) {
		$target_id = isset( $target['id'] ) ? esc_attr( $target['id'] ) : 'x' . time() . '_' . wp_rand();
		$type      = isset( $target['type'] ) ? sanitize_text_field( $target['type'] ) : 'pages';
		$value     = isset( $target['value'] ) ? $target['value'] : '';

		if ( $type === 'wp_pages' ) {
			$type = 'pages';
		} elseif ( $type === 'wp_posts' ) {
			$type = 'posts';
		}

		$show_masteriyo_course = 'masteriyo_courses' === $type && ! ur_check_module_activation( 'masteriyo-course-integration' );

		$type_labels = apply_filters(
			'urcr_type_labels',
			array(
				'pages'             => isset( $localized_data['labels']['pages'] ) ? $localized_data['labels']['pages'] : __( 'Pages', 'user-registration' ),
				'posts'             => isset( $localized_data['labels']['posts'] ) ? $localized_data['labels']['posts'] : __( 'Posts', 'user-registration' ),
				'post_types'        => isset( $localized_data['labels']['post_types'] ) ? $localized_data['labels']['post_types'] : __( 'Post Types', 'user-registration' ),
				'taxonomy'          => isset( $localized_data['labels']['taxonomy'] ) ? $localized_data['labels']['taxonomy'] : __( 'Taxonomy', 'user-registration' ),
				'whole_site'        => isset( $localized_data['labels']['whole_site'] ) ? $localized_data['labels']['whole_site'] : __( 'Whole Site', 'user-registration' ),
				'masteriyo_courses' => isset( $localized_data['labels']['masteriyo_courses'] ) ? $localized_data['labels']['masteriyo_courses'] : __( 'Masteriyo Courses', 'user-registration' ),
			),
			$localized_data
		);

		$type_label = isset( $type_labels[ $type ] ) ? $type_labels[ $type ] : $type;

		$html = '<div style="' . ( $show_masteriyo_course ? 'display:none !important;' : '' ) . '" class="urcr-target-item ur-d-flex ur-align-items-center ur-mt-2" data-target-id="' . $target_id . '">';

		$display_label = ( $type === 'whole_site' ) ? __( 'Includes', 'user-registration' ) : $type_label;
		$html         .= '<span class="urcr-target-type-label">' . esc_html( $display_label ) . ':</span>';

		if ( $type === 'whole_site' ) {
			$whole_site_value_label = __( 'Whole Site', 'user-registration' );
			if ( isset( $localized_data['content_type_options'] ) && is_array( $localized_data['content_type_options'] ) ) {
				foreach ( $localized_data['content_type_options'] as $option ) {
					if ( isset( $option['value'] ) && $option['value'] === 'whole_site' && isset( $option['label'] ) ) {
						$whole_site_value_label = $option['label'];
						break;
					}
				}
			}
			$html .= '<span data-content-type="whole_site" data-field-type="whole_site">' . esc_html( $whole_site_value_label ) . '</span>';
		} elseif ( $type === 'taxonomy' ) {
			// Handle taxonomy value structure
			// Target can have: { type: 'taxonomy', taxonomy: 'cat', value: [] }
			// Or value can be: { taxonomy: 'cat', value: [] }
			$taxonomy = '';
			$terms    = array();

			// Check if taxonomy is at target level
			if ( isset( $target['taxonomy'] ) ) {
				$taxonomy = sanitize_text_field( $target['taxonomy'] );
			}

			// Check value structure
			if ( is_array( $value ) ) {
				if ( isset( $value['taxonomy'] ) ) {
					$taxonomy = sanitize_text_field( $value['taxonomy'] );
				}
				if ( isset( $value['value'] ) && is_array( $value['value'] ) ) {
					$terms = $value['value'];
				} elseif ( isset( $value['terms'] ) && is_array( $value['terms'] ) ) {
					$terms = $value['terms'];
				} elseif ( ! isset( $value['taxonomy'] ) && ! isset( $value['value'] ) && ! isset( $value['terms'] ) ) {
					// Value might be the terms array directly
					$terms = $value;
				}
			}

			// Wrap taxonomy selects in a container for proper layout
			$html .= '<div class="urcr-taxonomy-select-group">';
			$html .= '<select class="urcr-taxonomy-select">';
			if ( isset( $localized_data['taxonomies'] ) && is_array( $localized_data['taxonomies'] ) ) {
				foreach ( $localized_data['taxonomies'] as $tax_key => $tax_label ) {
					$selected = ( $tax_key === $taxonomy ) ? 'selected' : '';
					$html    .= '<option value="' . esc_attr( $tax_key ) . '" ' . $selected . '>' . esc_html( $tax_label ) . '</option>';
				}
			}
			$html .= '</select>';

			// Add data-value attribute for terms
			$terms_attr = '';
			if ( ! empty( $terms ) ) {
				$terms_attr = ' data-value="' . esc_attr( wp_json_encode( $terms ) ) . '"';
			}
			$html .= '<select class="urcr-enhanced-select2 urcr-content-target-input" multiple data-target-id="' . $target_id . '" data-content-type="taxonomy" data-field-type="taxonomy"' . $terms_attr . '></select>';
			$html .= '</div>';
		} else {
			$value_attr = '';
			if ( is_array( $value ) && ! empty( $value ) ) {
				$value_attr = ' data-value="' . esc_attr( wp_json_encode( $value ) ) . '"';
			}
			$html .= apply_filters(
				'urcr_default_content_target_html',
				'<select class="urcr-enhanced-select2 urcr-content-target-input" multiple data-target-id="' . $target_id . '" data-content-type="' . esc_attr( $type ) . '" data-field-type="' . esc_attr( $type ) . '"' . $value_attr . '></select>',
				$target_id,
				$type,
				$value
			);
		}
		if ( ! in_array( $type, array( 'whole_site', 'masteriyo_courses', 'menu_items', 'files', 'custom_uri' ), true ) ) {

			$content_drip = ur_check_module_activation( 'content-drip' );

			$drip            = isset( $target['drip'] ) ? $target['drip'] : array(
				'activeType' => 'fixed_date',
				'value'      => array(
					'fixed_date' => array(
						'date' => '',
						'time' => '',
					),
					'days_after' => array( 'days' => 0 ),
				),
			);
			$fixed_date_date = isset( $drip['value']['fixed_date']['date'] ) ? $drip['value']['fixed_date']['date'] : '';
			$fixed_date_time = isset( $drip['value']['fixed_date']['time'] ) ? $drip['value']['fixed_date']['time'] : '';
			$days_after_days = isset( $drip['value']['days_after']['days'] ) ? $drip['value']['days_after']['days'] : '';

			$drip_active_type = isset( $drip['activeType'] ) ? $drip['activeType'] : 'fixed_date';

			$html .= '<div style="' . ( $content_drip && UR_PRO_ACTIVE ? '' : 'display:none;' ) . '" class="urcr-membership-drip" data-active_type="' . esc_attr( $drip_active_type ) . '"
			data-fixed_date_date="' . esc_attr( $fixed_date_date ) . '"
			data-fixed_date_time="' . esc_attr( $fixed_date_time ) . '"
			data-days_after_days="' . esc_attr( $days_after_days ) . '">

			<button type="button" class="urcr-drip__trigger">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
				<path d="M11.09 6.545a.91.91 0 1 1 1.82 0v4.893l3.133 1.567a.91.91 0 0 1-.813 1.626l-3.637-1.818a.91.91 0 0 1-.502-.813V6.545Z"/>
				<path d="M20.182 12a8.182 8.182 0 1 0-16.364 0 8.182 8.182 0 0 0 16.364 0ZM22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Z"/>
				</svg>' . esc_html__( 'Drip This Content', 'user-registration' ) . '
			</button>';

			$html .= '<div class="urcr-drip__popover" style="display:none;">
				<div class="urcr-drip__arrow"></div>

				<div class="urcr-drip__tabs">
					<div class="urcr-drip__tabList">
						<button type="button" class="urcr-drip__tab" data-value="fixed_date">Fixed Date</button>
						<button type="button" class="urcr-drip__tab" data-value="days_after">Days After</button>
					</div>

					<div class="urcr-drip__panels">
						<div class="urcr-drip__panel fixed_date-panel">
							<input type="date" class="urcr-drip__input drip-date" min="' . esc_attr( date( 'Y-m-d' ) ) . '" value="' . esc_attr( $fixed_date_date ) . '" />
							<input type="time" class="urcr-drip__input drip-time" value="' . esc_attr( $fixed_date_time ) . '" />
						</div>

						<div class="urcr-drip__panel days_after-panel" style="display:none;">
							<input type="number" class="urcr-drip__input drip-days" value="' . esc_attr( $days_after_days ) . '" min="0" />
						</div>
					</div>
				</div>
			</div>';

			$html .= '</div>';

		}

		$html .= '<button type="button" class="button button-link-delete urcr-target-remove" aria-label="' . esc_attr__( 'Remove content target', 'user-registration' ) . '">' .
				'<span class="dashicons dashicons-no-alt"></span>' .
				'</button>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * localize Membership data
	 *
	 * @return void
	 */
	public function localize_scripts() {
		$membership_id      = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : null;
		$membership_content = null;
		$title              = esc_html__( 'Untitled', 'user-registration' );

		if ( $membership_id ) {
			$rule_as_wp_post = get_post( $membership_id, ARRAY_A );

			if ( $rule_as_wp_post ) {
				$title              = $rule_as_wp_post['post_title'];
				$membership_content = json_decode( stripslashes( $rule_as_wp_post['post_content'] ), true );
			} else {
				$membership_id = null;
			}

			if ( 'draft' === $rule_as_wp_post['post_status'] ) {
				$is_draft = true;
			} else {
				$GLOBALS['urcr_hide_save_draft_button'] = true;
			}
		}
		$posts = get_posts(
			array(
				'post_status' => 'publish',
				'numberposts' => 100,
			)
		);
		$posts = wp_list_pluck( $posts, 'post_title', 'ID' );
		wp_localize_script(
			'user-registration-membership',
			'ur_membership_localized_data',
			array(
				'_nonce'                          => wp_create_nonce( 'ur_membership' ),
				'membership_id'                   => $membership_id,
				'membership_content'              => $membership_content,
				'ajax_url'                        => admin_url( 'admin-ajax.php' ),
				'wp_roles'                        => ur_membership_get_all_roles(),
				'posts'                           => $posts,
				'labels'                          => $this->get_i18_labels(),
				'membership_page_url'             => admin_url( 'admin.php?page=user-registration-membership' ),
				'delete_icon'                     => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
				'update_order_nonce'              => wp_create_nonce( 'ur_membership_update_order' ),
				'update_order_action'             => 'user_registration_membership_update_membership_order',
				'validate_payment_currency_nonce' => wp_create_nonce( 'validate_payment_currency_nonce' ),
				'local_currency_not_support_msg'  => __( 'Free membership plan does not support local currency.' , 'user-registration' )
			)
		);
	}


	/**
	 * Get i18 Labels
	 *
	 * @return array
	 */
	public function get_i18_labels() {
		return array(
			'network_error'                                => esc_html__( 'Network error', 'user-registration' ),
			'i18n_field_is_required'                       => _x( 'field is required.', 'user registration membership', 'user-registration' ),
			'i18n_valid_url_field_validation'              => _x( 'Please enter a valid url for', 'user registration membership', 'user-registration' ),
			'i18n_valid_price_field_validation'            => _x( 'Invalid Price. The amount must be greater than 0.', 'user registration membership', 'user-registration' ),
			'i18n_valid_amount_field_validation'           => _x( 'Input Field Amount must be greater than 0.', 'user registration membership', 'user-registration' ),
			'i18n_valid_trial_period_field_validation'     => _x( 'Trial period must be less than subscription period.', 'user registration membership', 'user-registration' ),
			'i18n_error'                                   => _x( 'Error', 'user registration membership', 'user-registration' ),
			'i18n_save'                                    => _x( 'Save', 'user registration membership', 'user-registration' ),
			'i18n_prompt_title'                            => __( 'Delete Membership Plan', 'user-registration' ),
			'i18n_prompt_bulk_subtitle'                    => __( 'Are you sure you want to delete these memberships permanently?', 'user-registration' ),
			'i18n_prompt_single_subtitle'                  => __( 'Are you sure you want to delete this membership permanently?', 'user-registration' ),
			'i18n_prompt_ok'                               => __( 'Ok', 'user-registration' ),
			'i18n_prompt_delete'                           => __( 'Delete', 'user-registration' ),
			'i18n_prompt_cancel'                           => __( 'Cancel', 'user-registration' ),
			'i18n_prompt_no_membership_selected'           => __( 'Please select at least one membership.', 'user-registration' ),
			'i18n_pg_validation_error'                     => __( 'Please select at least one payment gateway.', 'user-registration' ),
			'i18n_valid_min_trial_period_field_validation' => _x( 'Trial period must atleast be of 1 day.', 'user registration membership', 'user-registration' ),
			'i18n_valid_min_subs_period_field_validation'  => _x( 'Subscription period must atleast be of 1 day.', 'user registration membership', 'user-registration' ),
			'i18n_paypal'                                  => __( 'Paypal ', 'user-registration' ),
			'i18n_stripe'                                  => __( 'Stripe ', 'user-registration' ),
			'i18n_stripe_setup_error'                      => __( 'Incomplete Stripe Gateway setup please update stripe payment settings before continuing.', 'user-registration' ),
			'i18n_paypal_setup_error'                      => __( 'Incomplete Paypal Gateway setup please update paypal payment settings before continuing.', 'user-registration' ),
			'i18n_bank_setup_error'                        => __( 'Incomplete Bank Transfer setup please update bank transfer payment settings before continuing.', 'user-registration' ),
			'i18n_paypal_client_secret_id_error'           => __( 'Settings for client_id and client_secret is incomplete.', 'user-registration' ),
			'i18n_previous_save_action_ongoing'            => _x( 'Previous save action on going.', 'user registration admin', 'user-registration' ),
			'i18n_update_order'                            => __( 'Update Order', 'user-registration' ),
		);
	}

	/**
	 * Add Payment Before Registration option.
	 *
	 * @param array $options Other login options.
	 *
	 * @return  array
	 */
	public function add_payment_login_option( $options ) {

		if ( ! array_key_exists( 'payment', $options ) ) {
			$options['payment'] = esc_html__( 'Payment before login', 'user-registration' );
		}

		return $options;
	}
}
