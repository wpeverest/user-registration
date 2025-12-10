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
			$has_membership_group = true;
			$membership_group     = $s->general_setting->membership_group;
		}
		if ( $has_membership_group ) {
			update_post_meta( $post['form_id'], 'urm_form_group_' . $membership_group, true );
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
		wp_register_script( 'user-registration-membership-groups', UR_MEMBERSHIP_JS_ASSETS_URL . '/admin/membership-groups' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
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

		// Get active/configured payment gateways
		$active_payment_gateways = urm_get_all_active_payment_gateways( 'paid' );

		// Map payment gateway keys to their image filenames
		$gateway_images = array(
			'paypal'        => 'paypal.png',
			'stripe'        => 'stripe.png',
			'bank'          => 'bank.png',
			'authorize'     => 'authorize-net.png',
			'authorize_net' => 'authorize-net.png',
			'mollie'        => 'mollie.png',
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
