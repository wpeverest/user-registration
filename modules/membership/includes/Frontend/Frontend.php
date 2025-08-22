<?php
/**
 * URMembership Frontend.
 *
 * @class    Frontend
 * @package  URMembership/Frontend
 * @category Frontend
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Frontend;

use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Class
 */
class Frontend {

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
		add_action( 'wp_enqueue_membership_scripts', array( $this, 'load_scripts' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'ur_add_membership_tab_endpoint' ) );
		add_filter( 'user_registration_account_menu_items', array( $this, 'ur_membership_tab' ), 10, 1 );
		add_action(
			'user_registration_account_ur-membership_endpoint',
			array(
				$this,
				'user_registration_membership_tab_endpoint_content',
			)
		);

		add_action( 'template_redirect', array( $this, 'set_thank_you_transient' ) );
		add_action( 'wp_loaded', array( $this, 'clear_upgrade_data' ) );
	}

	/**
	 * Add the item to $items array.
	 *
	 * @param array $items Items.
	 */
	public function ur_membership_tab( $items ) {
		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return $items;
		}
		$new_items                  = array();
		$new_items['ur-membership'] = __( 'Membership', 'user-registration' );
		$items                      = array_merge( $items, $new_items );

		return $this->delete_account_insert_before_helper( $items, $new_items, 'user-logout' );
	}

	/**
	 * Delete Account insert after helper.
	 *
	 * @param mixed $items Items.
	 * @param mixed $new_items New items.
	 * @param mixed $before Before item.
	 */
	public function delete_account_insert_before_helper( $items, $new_items, $before ) {

		// Search for the item position.
		$position = array_search( $before, array_keys( $items ), true );

		// Insert the new item.
		$return_items = array_slice( $items, 0, $position, true );
		$return_items += $new_items;
		$return_items += array_slice( $items, $position, count( $items ) - $position, true );

		return $return_items;
	}

	/**
	 * Membership tab content.
	 */
	public function user_registration_membership_tab_endpoint_content() {
		$user_id = get_current_user_id();
		$this->load_scripts();
		$membership_repositories         = new MembersRepository();
		$members_order_repository        = new MembersOrderRepository();
		$members_subscription_repository = new MembersSubscriptionRepository();
		$orders_repository               = new OrdersRepository();
		$membership                      = $membership_repositories->get_member_membership_by_id( $user_id );
		if( ! empty( $membership['post_content'] ) ) {
			$membership['post_content'] = json_decode( $membership['post_content'], true );
		}
		$membership_service              = new MembershipService();
		$membership_details              = $membership_service->get_membership_details( $membership['post_id'] );
		$active_gateways                 = array();

		if ( ! empty( $membership_details['payment_gateways'] ) ) {
			$active_gateways = array_filter( $membership_details['payment_gateways'], function ( $item, $key ) {
				return "on" == $item["status"] && in_array($key, array('paypal', 'stripe', 'bank'));
			}, ARRAY_FILTER_USE_BOTH );
		}

		$membership['active_gateways'] = $active_gateways;
		$is_upgrading                  = ur_string_to_bool( get_user_meta( $user_id, 'urm_is_upgrading', true ) );
		$last_order                    = $members_order_repository->get_member_orders( $user_id );
		$bank_data                     = array();
		if ( ! empty( $last_order ) && $last_order['status'] == 'pending' && $last_order['payment_method'] === 'bank' ) {
			$bank_data = array(
				'show_bank_notice' => true,
				'bank_data'        => get_option( 'user_registration_global_bank_details', '' ),
				'notice_1'         => apply_filters( 'urm_bank_info_notice_1_filter', __( 'Please complete the payment using the bank details provided by the admin. <br> Once the payment is verified, your upgraded membership will be activated. Kindly wait for the admin\'s confirmation.', 'user-registration' ) ),
				'notice_2'         => apply_filters( 'urm_bank_info_notice_2_filter', __( 'Please complete the payment using the bank details provided by the admin. <br> Your membership will be renewed once the payment is verified. Kindly wait for the admin\'s confirmation.', 'user-registration' ) )
			);
		}
		$subscription_data = $members_subscription_repository->get_member_subscription( $user_id );

		$membership_data = array(
			'user'              => get_user_by( 'id', get_current_user_id() ),
			'membership'        => $membership,
			'is_upgrading'      => $is_upgrading,
			'bank_data'         => $bank_data,
			'renewal_behaviour' => get_option( 'user_registration_renewal_behaviour', 'automatic' ),
			'subscription_data' => $subscription_data
		);

		if ( ! empty( $last_order ) ) {
			$order_meta = $orders_repository->get_order_metas( $last_order['ID'] );
			if ( ! empty( $order_meta ) ) {
				$membership_data['delayed_until'] = $order_meta['meta_value'];
			}
		}

		ur_get_template(
			'myaccount/membership.php',
			$membership_data
		);
	}

	/**
	 * Add Membership tab endpoint.
	 */
	public function ur_add_membership_tab_endpoint() {

		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return;
		}
		$mask = Ur()->query->get_endpoints_mask();

		add_rewrite_endpoint( 'ur-membership', $mask );
		flush_rewrite_rules();
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		// Enqueue frontend scripts here.
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'sweetalert2' );

		wp_register_script( 'user-registration-membership-frontend-script', UR_MEMBERSHIP_JS_ASSETS_URL . '/frontend/user-registration-membership-frontend' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'user-registration-membership-frontend-script' );
		// Enqueue frontend styles here.
		wp_register_style( 'user-registration-membership-frontend-style', UR_MEMBERSHIP_CSS_ASSETS_URL . '/user-registration-membership-frontend.css', array(), UR_MEMBERSHIP_VERSION );
		wp_enqueue_style( 'user-registration-membership-frontend-style' );

		wp_enqueue_script( 'user-registration-membership-stripe-v3', 'https://js.stripe.com/v3/', array() );
		$this->localize_scripts();
	}

	/**
	 * Localize the frontend scripts with necessary data.
	 *
	 * This function uses the wp_localize_script function to add a JavaScript object
	 * to the frontend script. The object contains several properties:
	 * - `_nonce`: A WordPress nonce generated using wp_create_nonce function.
	 * - `ajax_url`: The URL of the admin-ajax.php file generated using admin_url function.
	 * - `login_url`: The URL of the login page generated using wp_login_url function.
	 * - `labels`: An array of internationalized labels generated by the get_i18_labels method.
	 *
	 * @return void
	 */
	public function localize_scripts() {
		$currency             = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies           = ur_payment_integration_get_currencies();
		$symbol               = $currencies[ $currency ]['symbol'];
		$registration_page_id = get_option( 'user_registration_member_registration_page_id' );

		$redirect_page_url = get_permalink( $registration_page_id );

		$thank_you_page  = urm_get_thank_you_page();
		$stripe_settings = \WPEverest\URMembership\Admin\Services\Stripe\StripeService::get_stripe_settings();


		wp_localize_script(
			'user-registration-membership-frontend-script',
			'ur_membership_frontend_localized_data',
			array(
				'_nonce'                           => wp_create_nonce( 'ur_members_frontend' ),
				'upgrade_membership_nonce'         => wp_create_nonce( 'urm_upgrade_membership' ),
				'renew_membership_nonce'           => wp_create_nonce( 'urm_renew_membership' ),
				'_confirm_payment_nonce'           => wp_create_nonce( 'urm_confirm_payment' ),
				'ajax_url'                         => admin_url( 'admin-ajax.php' ),
				'login_url'                        => wp_login_url(),
				'labels'                           => $this->get_i18_labels(),
				'currency_symbol'                  => $symbol,
				'membership_registration_page_url' => $redirect_page_url,
				'thank_you_page_url'               => $thank_you_page,
				'stripe_publishable_key'           => $stripe_settings['publishable_key'],
				'membership_gateways'              => get_option( 'ur_membership_payment_gateways', array() )
			)
		);
	}

	/**
	 * Retrieves an array of internationalized labels used in the frontend.
	 *
	 * @return array An associative array of internationalized labels.
	 */
	public function get_i18_labels() {

		return array(
			'network_error'                                => __( 'Network error', 'user-registration' ),
			'i18n_field_is_required'                       => __( 'field is required.', 'user-registration' ),
			'i18n_field_password_empty_validation'         => __( 'Password cannot be empty', 'user-registration' ),
			'i18n_field_email_field_validation'            => __( 'Please enter a valid email address.', 'user-registration' ),
			'i18n_field_confirm_password_field_validation' => __( 'Password does not match with confirm password.', 'user-registration' ),
			'i18n_field_password_field_length_validation'  => __( 'Password must be at least 8 characters long', 'user-registration' ),
			'i18n_field_password_field_regex_validation'   => __( 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.', 'user-registration' ),
			'i18n_field_payment_gateway_field_validation'  => __( 'Please select a Payment Gateway.', 'user-registration' ),
			'i18n_field_select_payment_gateway'            => __( 'Select a Payment Gateway.', 'user-registration' ),
			'i18n_thank_you'                               => __( 'Thank You', 'user-registration' ),
			'i18n_sign_in'                                 => __( 'Sign In', 'user-registration' ),
			'i18n_order_successful'                        => __( 'Your order has been successfully placed.', 'user-registration' ),
			'i18n_transaction_id'                          => __( 'Please use this transaction ID for future references.', 'user-registration' ),
			'i18n_membership_required'                     => __( 'Membership field is required.', 'user-registration' ),
			'i18n_coupon_invalid_error'                    => __( 'Coupon is Invalid.', 'user-registration' ),
			'i18n_coupon_discount_message'                 => __( 'discount on membership has been applied.', 'user-registration' ),
			'i18n_coupon_empty_error'                      => __( 'Coupon Field is empty.', 'user-registration' ),
			'i18n_coupon_free_membership_error'            => __( 'Invalid membership type (Free).', 'user-registration' ),
			'i18n_incomplete_stripe_setup_error'           => __( 'Stripe Payment stopped. Incomplete Stripe setup.', 'user-registration' ),
			'i18n_bank_details_title'                      => __( 'Bank Details.', 'user-registration' ),
			'i18n_change_membership_title'                 => __( 'Change Membership', 'user-registration' ),
			'i18n_change_renew_title'                      => __( 'Renew Membership', 'user-registration' ),
			'i18n_change_plan_required'                    => __( 'At least one Plan must be selected', 'user-registration' ),
			'i18n_error'                                   => __( 'Error', 'user-registration' ),
			'i18n_empty_card_details'                      => __( 'Your card number is incomplete.', 'user-registration' ),
			'i18n_cancel_membership_text'                  => __( 'Cancel Membership', 'user-registration' ),
			'i18n_close'                                   => __( 'Close', 'user-registration' ),
			'i18n_cancel_membership_subtitle'              => __( 'Are you sure you want to cancel this membership permanently?', 'user-registration' ),
			'i18n_sending_text'                            => __( 'Sending ...', 'user-registration' ),
		);
	}

	public function set_thank_you_transient() {
		if ( ! isset( $_GET['urm_uuid'] ) || ! isset( $_GET['thank_you'] ) ) {
			return;
		}
		$uuid         = $_GET['urm_uuid'] ? sanitize_text_field( $_GET['urm_uuid'] ) : ur_get_random_number();
		$transient_id = "uuid_{$uuid}_thank_you";
		delete_transient( $transient_id );
		$thank_you_page = get_permalink( absint( $_GET['thank_you'] ) );
		set_transient( $transient_id, $thank_you_page, 15 * MINUTE_IN_SECONDS );

	}

	/**
	 * clear_upgrade_data
	 * If Paypal payment fails then clear meta's so user can try again
	 * @return void
	 */
	public function clear_upgrade_data() {
		$user_id              = get_current_user_id();
		$subscription_service = new MembersSubscriptionRepository();
		$user_subscription    = $subscription_service->get_member_subscription( $user_id );
		if ( empty( $user_subscription ) ) {
			return;
		}
		$next_subscription_data = json_decode( get_user_meta( $user_id, 'urm_next_subscription_data', true ), true );

		if ( ! empty( $next_subscription_data ) && empty( $next_subscription_data['delayed_until'] ) && ! empty( $next_subscription_data['payment_method'] ) && ( "paypal" === $next_subscription_data['payment_method'] ) ) {
			if ( $user_subscription['status'] === 'active' ) {
				delete_user_meta( $user_id, 'urm_is_upgrading' );
				delete_user_meta( $user_id, 'urm_is_upgrading_to' );
			}
		}
	}
}
