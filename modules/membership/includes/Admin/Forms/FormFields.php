<?php
/**
 * FormFields.php
 *
 * FormFields.php
 *
 * @class    FormFields.php
 * @author   Yoyal Limbu
 * @date     11/20/2024 : 2:33 PM
 */

namespace WPEverest\URMembership\Admin\Forms;

use WPEverest\URMembership\Admin\Services\MembershipGroupService;

class FormFields {
	public function __construct() {
		$this->init();
	}

	public function init() {
		add_filter( 'user_registration_membership_admin_template', array(
			$this,
			'ur_add_membership_template'
		), 10, 1 );
		add_filter( 'user_registration_one_time_draggable_form_fields', array(
			$this,
			'enable_one_time_drag_for_membership_field'
		), 10, 1 );

		$extend_fields = array( 'user_registration_other_form_fields' => 'membership' ); //use this array to add more fields in future if required related with membership
		$this->load_field_classes( $extend_fields );
		$this->extend_fields( $extend_fields );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'user_registration_after_form_settings_save', array(
			$this,
			'update_post_membership_group_meta_'
		) );
	}

	public function update_post_membership_group_meta_( $post ) {
		$settings             = ur_get_form_data_by_key( json_decode( $post['form_data'] ), 'membership' );
		$has_membership_group = false;
		foreach ( $settings as $s ) {
			if ( ! isset( $s->general_setting ) ) {
				return;
			}
			if ( 'all' === $s->general_setting->membership_listing_option ) {
				$has_membership_group = false;
			} else {
				$has_membership_group = true;
				$membership_group     = $s->general_setting->membership_group;
			}
		}
		$post_id = $post['form_id'];

		$all_meta = get_post_meta( $post_id );

		foreach ( $all_meta as $meta_key => $meta_value ) {
			if ( 0 === strpos( $meta_key, 'urm_form_group_' ) ) {
				delete_post_meta( $post_id, $meta_key );
			}
		}

		if ( $has_membership_group && ! empty( $membership_group ) ) {
			update_post_meta( $post_id, 'urm_form_group_' . $membership_group, true );
		}
	}

	public function enable_one_time_drag_for_membership_field( $fields ) {
		$fields[] = 'membership';

		return $fields;
	}

	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'add-new-registration' !== $_GET['page'] ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_register_script( 'user-registration-membership-groups', UR()->plugin_url(). '/assets/js/modules/membership/admin/membership-groups' . $suffix . '.js', array( 'jquery' ), UR_VERSION, true );
		wp_enqueue_script( 'user-registration-membership-groups' );
		$this->localize_scripts();
	}

	public function localize_scripts() {
		// Get all available payment gateways
		$payment_gateways = get_option(
			'ur_membership_payment_gateways',
			array(
				'paypal' => __( 'PayPal', 'user-registration' ),
				'stripe' => __( 'Stripe', 'user-registration' ),
				'bank'   => __( 'Bank', 'user-registration' ),
			)
		);
		$membership_type = UR_PRO_ACTIVE ? 'subscription' : 'paid';
		// Get active/configured payment gateways
		$active_payment_gateways = urm_get_all_active_payment_gateways( $membership_type );

		// Map payment gateway keys to their image filenames
		$gateway_images = array(
			'paypal'        => 'paypal-logo.png',
			'stripe'        => 'stripe-logo.png',
			'bank'          => 'bank-logo.png',
			'authorize'     => 'authorize-logo.png',
			'mollie'        => 'mollie-logo.png',
		);

		// Get currency symbol
		$currency   = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies = ur_payment_integration_get_currencies();
		$symbol     = isset( $currencies[ $currency ]['symbol'] ) ? $currencies[ $currency ]['symbol'] : '$';

		wp_localize_script(
			'user-registration-membership-groups',
			'urmg_localized_data',
			array(
				'_nonce'                  => wp_create_nonce( 'ur_membership_group' ),
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'labels'                  => $this->get_i18_labels(),
				'plugin_url'              => UR()->plugin_url(),
				'gateway_images'          => $gateway_images,
				'payment_gateways'        => $payment_gateways,
				'active_payment_gateways' => $active_payment_gateways,
				'currency_symbol'         => $symbol,
			)
		);
	}

	public function get_i18_labels() {
		return array(
			'network_error'          => esc_html__( 'Network error', 'user-registration' ),
			'i18n_field_is_required' => _x( 'field is required.', 'user registration membership', 'user-registration' ),
			'i18n_select_payment_gateway'        => __( 'Select Payment Gateway.', 'user-registration' ),
		);
	}

	public function load_field_classes( $extend_fields ) {
		foreach ( $extend_fields as $filter => $field ) {
			$classname = ur_load_form_field_class( $field );
			if ( ! class_exists( $classname ) ) {
				include_once __DIR__ . '/class-ur-form-field-' . $field . '.php';
			}
		}
	}

	public function extend_fields( $extend_fields ) {
		foreach ( $extend_fields as $filter => $field ) {
			add_filter( $filter, function ( $result ) use ( $field ) {
				return array_merge( $result, array( $field ) );
			}, 10, 1 );
		}
	}

	public function ur_add_membership_template() {
		return dirname( __FILE__ ) . '/Views/admin-membership.php';
	}

}
