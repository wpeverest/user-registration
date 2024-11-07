<?php
/**
 * URMembership Members.
 *
 * @package  URMembership/Members
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Members;

use WPEverest\URMembership\Admin\Members\MembersListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Members' ) ) {
	/**
	 * Members Class
	 */
	class Members {

		/**
		 * Current page.
		 *
		 * @var string
		 */
		protected $page = null;

		/**
		 * Constructor for the class.
		 *
		 * Sets the page property and registers various hooks.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->page = 'user-registration-members';
			add_action( 'admin_menu', array( $this, 'ur_membership_members_menu' ), 60 );
			add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
			add_filter(
				'manage_user-registration_page_user-registration-members_columns',
				array(
					$this,
					'get_column_headers',
				)
			);
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		}

		/**
		 * Enqueue scripts
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			if ( empty($_GET['page']) ||   'user-registration-members' !== $_GET['page'] ) {
				return;
			}
			$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
			wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
			wp_register_script( 'user-registration-members', UR_MEMBERSHIP_ASSETS_URL . '/js/admin/user-registration-members-admin' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'ur-snackbar' );
			wp_enqueue_script( 'user-registration-members' );
			wp_enqueue_script( 'sweetalert2' );
			wp_register_script( 'selectWoo', UR()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '5.0.0', false );
			wp_enqueue_script( 'selectWoo' );
			$this->localize_scripts();
		}


		/**
		 * Enqueue styles
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {
			if ( empty($_GET['page']) ||   'user-registration-members' !== $_GET['page'] ) {
				return;
			}
			wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), '1.0.0' );
			wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
			wp_register_style( 'ur-membership-admin-style', UR_MEMBERSHIP_ASSETS_URL . '/css/user-registration-membership-admin.css', array(), UR_VERSION );
			wp_enqueue_style( 'ur-membership-admin-style' );
			wp_enqueue_style( 'user-registration-pro-admin-style' );
			wp_enqueue_style( 'sweetalert2' );
			wp_enqueue_style( 'ur-core-builder-style' );
			wp_enqueue_style( 'ur-snackbar' );
			wp_enqueue_style( 'select2', UR()->plugin_url() . '/assets/css/select2/select2.css', array(), '4.0.6' );

		}

		/**
		 * Localizes the scripts for the user registration membership plugin.
		 *
		 * Localizes scripts for user registration membership plugin.
		 *
		 * @return void
		 */
		public function localize_scripts() {
			$member_id = ! empty( $_GET['post_id'] ) ? wp_unslash( $_GET['post_id'] ) : null;
			if ( $member_id ) {
				$rule_as_wp_post = get_post( $member_id, ARRAY_A );
			}

			wp_localize_script(
				'user-registration-members',
				'ur_members_localized_data',
				array(
					'_nonce'           => wp_create_nonce( 'ur_members' ),
					'member_id'        => $member_id,
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'wp_roles'         => ur_membership_get_all_roles(),
					'wp_capabilities'  => ur_membership_get_all_capabilities(),
					'labels'           => $this->get_i18_labels(),
					'members_page_url' => admin_url( 'admin.php?page=user-registration-members&action=add_new_member' ),
					'delete_icon'      => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
				)
			);
		}

		/**
		 * Get i18 labels.
		 *
		 * @return array
		 */
		public function get_i18_labels() {
			return array(
				'network_error'                                 => esc_html__( 'Network error', 'user-registration-membership' ),
				'i18n_field_is_required'                        => _x( 'field is required.', 'user registration membership', 'user-registration-membership' ),
				'i18n_field_email_field_validation'             => _x( 'Please enter a valid email address.', 'user registration membership', 'user-registration-membership' ),
				'i18n_field_password_field_validation'          => _x( 'Password does not match with confirm password.', 'user registration membership', 'user-registration-membership' ),
				'i18n_field_subscription_start_date_validation' => _x( 'Start date must be greater than or equal to today.', 'user registration membership', 'user-registration-membership' ),
				'i18n_prompt_title'                             => __( 'Delete Members', 'user-registration-membership' ),
				'i18n_prompt_bulk_subtitle'                     => __( 'Are you sure you want to delete these members permanently?', 'user-registration-membership' ),
				'i18n_prompt_single_subtitle'                   => __( 'Are you sure you want to delete this members permanently?', 'user-registration-membership' ),
				'i18n_prompt_delete'                            => __( 'Delete', 'user-registration-membership' ),
				'i18n_prompt_cancel'                            => __( 'Cancel', 'user-registration-membership' ),
				'i18n_prompt_no_membership_selected'            => __( 'Please select at least one member.', 'user-registration-membership' ),
			);
		}

		/**
		 * Remove Notices.
		 */
		public static function hide_unrelated_notices() {
			global $wp_filter;

			// Return on other than access rule creator page.
			if ( empty( $_REQUEST['page'] ) || 'user-registration-members' !== $_REQUEST['page'] ) {
				return;
			}

			foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
				if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {
					foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
						foreach ( $hooks as $name => $arr ) {
							// Remove all notices except user registration plugins notices.
							if ( ! strstr( $name, 'user_registration_' ) ) {
								unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
							}
						}
					}
				}
			}
		}

		/**
		 * Hook to display the page content.
		 *
		 * @return void
		 */
		public function ur_membership_members_menu() {
			add_submenu_page(
				'user-registration',
				__( 'Membership Members', 'user-registration-membership' ), // page title.
				__( 'Members', 'user-registration-membership' ), // menu title.
				'manage_user_registration', // Capability required to access.
				'user-registration-members', // Menu slug.
				array(
					$this,
					'render_members_page',
				),
				7
			);
		}

		/**
		 * Renders the members create or add new page.
		 *
		 * @return void
		 */
		public function render_members_page() {
			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			switch ( $action ) {
				case 'add_new_member':
					$this->render_members_create_page();
					break;
				default:
					$this->render_members_list_page();
					break;
			}
		}

		/**
		 * Render members list page.
		 *
		 * @return void
		 */
		public function render_members_list_page() {
			if ( ! current_user_can( 'list_users' ) ) {
				wp_die(
					'<h1>' . esc_html__( 'You need a higher level of permission.', 'user-registration-membership' ) . '</h1>' .
					'<p>' . esc_html__( 'Sorry, you are not allowed to list users.', 'user-registration-membership' ) . '</p>',
					403
				);
			}
			$list_table = new MembersListTable();
			$list_table->prepare_items();
			$enable_members_button = false;
			require __DIR__ . '/../Views/Partials/header.php';
			?>

			<div id="user-registration-list-table-page">
				<div class="user-registration-list-table-heading" id="ur-users-page-topnav">
					<div class="ur-page-title__wrapper">
						<h1>
							<?php esc_html_e( 'All Members', 'user-registration-membership' ); ?>
						</h1>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page . '&action=add_new_member' ) ); ?>"
					   id="user-registration-members-add-btn" class="button ur-button-primary">
						+ <?php esc_html_e( 'Add new Member', 'user-registration-membership' ); ?>
					</a>
				</div>
				<div id="user-registration-list-filters-row" style="align-items: center;">
					<div class="ur-membership-filter-container" style="display: flex;align-items: center; gap: 10px">
						<form method="get" id="user-registration-list-search-form"
							  style="display: flex; width: auto; gap: 20px">
							<input type="hidden" name="page" value="user-registration-members"/>
							<?php
							$list_table->display_search_box();
							?>
						</form>
					</div>


				</div>
				<hr>
				<form method="get" id="user-registration-members-list-form">
					<input type="hidden" name="page" value="user-registration-members"/>
					<?php $list_table->display(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Renders the members create page.
		 *
		 * @return void
		 */
		public function render_members_create_page() {
			$members_list_table = new MembersListTable();
			$roles              = $members_list_table->get_roles();
			$memberships        = $members_list_table->get_all_memberships();
			include __DIR__ . '/../Views/member-create.php';
		}

		/**
		 * Returns the list of column headers for Users list table.
		 *
		 * @return array
		 * @since 4.1
		 */
		public function get_column_headers() {
			$column_headers = apply_filters(
				'manage_admin_page_user-registration-members_column_headers', //phpcs:ignore
				array(
					'cb'                  => '<input type="checkbox" />',
					'username'            => __( 'Username', 'user-registration-membership' ),
					'email'               => __( 'Email', 'user-registration-membership' ),
					'membership'          => __( 'Membership', 'user-registration-membership' ),
					'subscription_status' => __( 'Subscription Status', 'user-registration-membership' ),
					'user_registered'     => __( 'User Registered', 'user-registration-membership' ),
				)
			);

			$column_headers['actions'] = esc_html__( 'Actions', 'user-registration-membership' );

			return $column_headers;
		}


	}
}

