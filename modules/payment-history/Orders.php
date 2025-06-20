<?php

namespace WPEverest\URMembership\Payment;

use WPEverest\URMembership\Payment\Admin\OrdersListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Orders {
	protected $page;

	public function __construct() {
		$this->page = 'member-payment-history';
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_orders_menu' ), 70 );
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'init', array( $this, 'add_payment_gateway_options' ) );
		$this->includes();
	}

	/**
	 * Remove Notices.
	 */
	public static function hide_unrelated_notices() {
		if ( empty( $_REQUEST['page'] ) || 'member-payment-history' !== $_REQUEST['page'] ) {
			return;
		}
		global $wp_filter;
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

	public function add_orders_menu() {

		$orders_page = add_submenu_page(
			'user-registration',
			__( 'Payment History', 'user-registration' ), // page title
			__( 'Payment History', 'user-registration' ), // menu title
			'manage_user_registration', // Capability required to access
			$this->page, // Menu slug
			array(
				$this,
				'render_payment_history_page',
			),
			5
		);
		add_action( 'load-' . $orders_page, array( $this, 'orders_initialization' ) );
	}


	public function orders_initialization() {
		if ( isset( $_GET['page'] ) && $this->page === $_GET['page'] ) {

			$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			switch ( $action_page ) {
				case 'add_new_membership':
					break;
				default:
					global $orders_list_table;
					$orders_list_table = new OrdersListTable();
					$orders_list_table->process_actions();
					break;
			}
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'member-payment-history' !== $_GET['page'] ) {
			return;
		}
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		if ( ! wp_script_is( 'ur-snackbar', 'reqistered' ) ) {
			wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
			wp_enqueue_script( 'ur-snackbar' );
		}
		wp_enqueue_script( 'sweetalert2' );
		wp_register_script( 'payment-history', UR()->plugin_url() . '/assets/js/modules/membership/admin/payment-history' . $suffix . '.js', array( 'jquery', 'ur-enhanced-select' ), '1.0.0', true );
		wp_enqueue_script( 'payment-history' );

		$this->localize_scripts();
	}

	/**
	 * Enqueue Styles
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( empty( $_GET['page'] ) || 'member-payment-history' !== $_GET['page'] ) {
			return;
		}
		wp_register_style( 'payment-history-css', UR()->plugin_url() . '/assets/css/modules/payment-history/user-registration-payment-history.css', array(), UR_VERSION );
		wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		wp_enqueue_style( 'ur-core-builder-style' );
		if ( ! wp_style_is( 'ur-snackbar', 'reqistered' ) ) {
			wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), '1.0.0' );
		}
		wp_enqueue_style( 'payment-history-css' );
		wp_enqueue_style( 'sweetalert2' );
		wp_enqueue_style( 'ur-snackbar' );
		wp_enqueue_style( 'select2', UR()->plugin_url() . '/assets/css/select2/select2.css', array(), UR_VERSION );
	}

	/**
	 * @return void
	 */
	public function render_payment_history_page() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

		switch ( $action ) {
			case 'add_new_payment':
				break;
			default:
				$this->render_payment_history_list();
				break;
		}
	}

	/**
	 * render_payment_history_list
	 *
	 * @return void
	 */
	public function render_payment_history_list() {
		global $orders_list_table;

		if ( ! $orders_list_table ) {
			return;
		}
		$enable_members_button = true;
		?>
		<div class="ur-membership-header ur-d-flex ur-mr-0 ur-p-3 ur-align-items-center" id=""
			style="margin-left: -20px; background:white; gap: 20px; position: sticky; top: 32px; z-index: 700">
			<img style="max-width: 30px"
				src="<?php echo UR()->plugin_url() . '/assets/images/logo.svg'; ?>" alt="">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page ) ); ?>"
				class="<?php echo esc_attr( ( $_GET['page'] == $this->page ) ? 'row-title' : '' ); ?>"
				style="text-decoration: none"
			>
				<?php esc_html_e( 'Payment History', 'user-registration' ); ?>
			</a>
		</div>

		<div id="payment-detail-modal" class="modal">
			<div class="modal-content">
				<div class="modal-header">
					<span class="close-button">&times;</span>
					<h2><?php echo __( 'Transaction Details', 'user-registration' ); ?></h2>
					<hr>
				</div>
				<div class="modal-body">
				</div>
			</div>
		</div>
		<div id="user-registration-list-table-page">
			<div class="user-registration-list-table-heading" id="ur-users-page-topnav">
				<div class="ur-page-title__wrapper">
					<h1>
						<?php esc_html_e( 'Payment History', 'user-registration' ); ?>
					</h1>
				</div>
			</div>
			<div id="user-registration-pro-filters-row" style="align-items: center;">
				<div class="ur-membership-filter-container" style="display: flex;align-items: center; gap: 10px">
					<form method="get" id="user-registration-users-search-form"
							style="display: flex; width: auto; gap: 20px">
						<input type="hidden" name="page" value="<?php echo $this->page; ?>"/>
						<?php
						$orders_list_table->display_advance_filter();
						?>
					</form>
				</div>

			</div>
			<hr>
			<form method="get" id="ur-membership-payment-history-form">
				<input type="hidden" name="page" value="<?php echo $this->page; ?>"/>
				<?php
				$orders_list_table->display_page();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * includes
	 *
	 * @return void
	 */
	public function includes() {
		new AJAX();
	}

	/**
	 * localize_scripts
	 *
	 * @return void
	 */
	public function localize_scripts() {

		$memberships = get_posts(
			array(
				'post_type'   => 'ur_membership',
				'numberposts' => - 1,
			)
		);
		$memberships = array_filter(
			json_decode( json_encode( $memberships ), true ),
			function ( $item ) {
				$content = json_decode( wp_unslash( $item['post_content'] ), true );

				return $content['status'];
			}
		);
		$memberships = wp_list_pluck( $memberships, 'post_title', 'ID' );

		wp_localize_script(
			'payment-history',
			'urm_orders_localized_data',
			array(
				'_nonce'              => wp_create_nonce( 'ur_member_orders' ),
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'labels'              => $this->get_i18_labels(),
				'membership_page_url' => admin_url( 'admin.php?page=user-registration-membership&action=add_new_membership' ),
				'ur_forms'            => ur_get_all_user_registration_form(),
				'memberships'         => $memberships,
				'delete_icon'         => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
			)
		);
	}

	/**
	 * get_i18_labels
	 *
	 * @return array
	 */
	public function get_i18_labels() {
		return array(
			'network_error'                 => esc_html__( 'Network error', 'user-registration' ),
			'i18n_prompt_title'             => __( 'Delete Orders', 'user-registration' ),
			'i18n_prompt_bulk_subtitle'     => __( 'Are you sure you want to delete these orders permanently?', 'user-registration' ),
			'i18n_prompt_single_subtitle'   => __( 'Are you sure you want to delete this order permanently?', 'user-registration' ),
			'i18n_prompt_delete'            => __( 'Delete', 'user-registration' ),
			'i18n_prompt_cancel'            => __( 'Cancel', 'user-registration' ),
			'i18n_payment_completed'        => __( 'Completed', 'user-registration' ),
			'i18n_prompt_no_order_selected' => __( 'Please select at least one order.', 'user-registration' ),
		);
	}

	/**
	 * Adds the membership options to the database.
	 *
	 * This function adds the payment gateways for the membership plugin to the
	 * WordPress options table. The payment gateways are stored in the 'ur_payment_gateways'
	 * option and are an array containing the strings 'Paypal', 'Stripe', and 'Bank'.
	 *
	 * @return void
	 */
	public function add_payment_gateway_options() {
		$payment_gateways = array(
				'paypal'      => __( 'Paypal', 'user-registration' ),
				'stripe'      => __( 'Stripe', 'user-registration' ),
				'credit_card' => __( 'Stripe (Credit Card)', 'user-registration' ),
				'bank'        => __( 'Bank', 'user-registration' ),
		);
		/**
		 * Filters that hold the list of payment gateway for payment orders.
		 *
		 *@param array $payment_gateways
   		*/
		$payment_gateways = apply_filters( 'user_registration_payment_gateways', $payment_gateways );

		update_option( 'ur_payment_gateways', $payment_gateways );
	}

}

new Orders();
