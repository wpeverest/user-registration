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
		add_action( 'admin_menu', array( $this, 'add_urm_menu' ), 15 );
		add_action( 'admin_init', array( $this, 'actions' ) );
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
		add_filter( 'wp_editor_settings', array( $this, 'remove_media_buttons' ) );
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
		wp_register_script( 'user-registration-membership', UR_MEMBERSHIP_JS_ASSETS_URL . '/admin/user-registration-membership-admin' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
		wp_enqueue_script( 'ur-snackbar' );
		wp_enqueue_script( 'sweetalert2' );
		wp_enqueue_script( 'user-registration-membership' );
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
			wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), '1.0.0' );
		}
		wp_enqueue_style( 'ur-snackbar' );
		wp_enqueue_style( 'sweetalert2' );
		wp_register_style( 'ur-membership-admin-style', UR_MEMBERSHIP_CSS_ASSETS_URL . '/user-registration-membership-admin.css', array(), UR_MEMBERSHIP_VERSION );
		wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_MEMBERSHIP_VERSION );
		wp_enqueue_style( 'ur-core-builder-style' );
		wp_enqueue_style( 'ur-membership-admin-style' );
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

	//todo might need to remove later if none of the bulk actions are used

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
//				$this->bulk_trash( $delete_list );
				break;
			case 'untrash':
//				$this->bulk_untrash( $membership_list );
				break;
			case 'delete':
//				$this->bulk_trash( $delete_list, true, $delete_membership );
				break;
			default:
				break;
		}
	}

	/**
	 * Bulk trash/delete.
	 *
	 * @param array $membership_lists Membership List post id.
	 * @param bool $delete Delete action.
	 */
	private function bulk_trash( $membership_lists, $delete = false, $is_membership = true ) {
		$membership_group_service = new MembershipGroupService();
		foreach ( $membership_lists as $membership_id ) {
			$form_id = $membership_group_service->get_group_form_id( $membership_id );
			if ( $delete ) {
				if ( ! $is_membership && ( "" != $form_id ) ) {
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
			__( 'Membership', 'user-registration' ), // page title
			__( 'Membership', 'user-registration' ), // menu title
			'edit_posts', // capability
			'user-registration-membership', // slug
			array(
				$this,
				'render_membership_page',
			)
		);
		add_action( 'load-' . $rules_page, array( $this, 'membership_initialization' ) );

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], ['user-registration-membership', 'user-registration-membership-groups', 'user-registration-members'] ) ) {

			add_submenu_page(
				'user-registration',
				__( 'All Plans', 'user-registration' ),
				'↳ ' . __( 'All Plans', 'user-registration' ),
				'edit_posts',
				'user-registration-membership',
				array(
					$this,
					'render_membership_page',
				),
				16
			);

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
				17
			);

			$members = new Members();
			add_submenu_page(
				'user-registration',
				__( 'Membership Members', 'user-registration' ),
				'↳ ' . __( 'Members', 'user-registration' ),
				'manage_user_registration',
				'user-registration-members',
				array( $members, 'render_members_page'),
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
				$membership_groups->render_membership_groups_list_table( $menu_items );
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
		$enable_membership_button = false;
		$roles                    = wp_roles()->role_names;
		$membership_service       = new MembershipService();
		$memberships = $membership_service->list_active_memberships();

		include __DIR__ . '/../Views/membership-create.php';

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
				'_nonce'              => wp_create_nonce( 'ur_membership' ),
				'membership_id'       => $membership_id,
				'membership_content'  => $membership_content,
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'wp_roles'            => ur_membership_get_all_roles(),
				'posts'               => $posts,
				'labels'              => $this->get_i18_labels(),
				'membership_page_url' => admin_url( 'admin.php?page=user-registration-membership' ),
				'delete_icon'         => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE )
			)
		);
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function remove_media_buttons( $settings ) {
		//return tinymce as default
		add_filter( 'wp_default_editor', function () {
				return 'tinymce';
		} );
		if ( ( isset( $_GET['page'] ) && 'user-registration-settings' === $_GET['page'] ) && ( isset( $_GET["tab"] ) && "payment" === $_GET["tab"] ) ) {
			$settings['media_buttons'] = false;
		}

		return $settings;
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
