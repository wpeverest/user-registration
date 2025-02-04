<?php
/**
 * User_Registration_Paypal_Module
 *
 * @package  User_Registration_Stripe_Module
 * @since  4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class User_Registration_Paypal_Module
 */
class User_Registration_Paypal_Module {

	/**
	 * User_Registration_Paypal_Module Constructor
	 */
	public function __construct() {

		// Paypal Settings Hooks.
		if ( is_admin() ) {
			// Filter for global settings.
			add_filter( 'user_registration_payment_settings', array( $this, 'get_paypal_settings' ) );
		}
	}

	/**
	 * Get Stripe Global Settings.
	 *
	 * @param array $settings settings.
	 */
	public function get_paypal_settings( $settings ) {
		$paypal_settings = array(
			'title'    => __( 'Paypal Settings', 'user-registration' ),
			'type'     => 'card',
			'id' =>     'paypal_settings_id',
			'desc'     => '',
			'settings' => array(
				array(
					'id'                => 'user_registration_global_paypal_mode',
					'type'              => 'select',
					'title'             => __( 'Mode', 'user-registration' ),
					'desc'               => __( 'Select a mode to run paypal.', 'user-registration' ),
					'desc_tip'       => true,
					'options'           => array(
						'production' => __( 'Production', 'user-registration' ),
						'test'       => __( 'Test/Sandbox', 'user-registration' ),
					),
					'class'             => 'ur-enhanced-select',
					'default'           => get_option( 'user_registration_global_paypal_mode', 'test' ),
				),
				array(
					'type'     => 'text',
					'title'    => __( 'PayPal Email Address', 'user-registration' ),
					'desc'     => __( 'Enter you PayPal email address.', 'user-registration' ),
					'desc_tip' => true,
					'required' => true,
					'id'       => 'user_registration_global_paypal_email_address',
					'default'  => get_option( 'user_registration_global_paypal_email_address' ),
					'placeholder'  => get_option( 'admin_email' ),
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Cancel Url', 'user-registration' ),
					'desc'     =>  __("Endpoint set for handling paypal cancel api.", "user-registration"),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_cancel_url',
					'default'  => get_option( 'user_registration_global_paypal_cancel_url'),
					'placeholder'  => esc_url(home_url()) ,
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Return Url', 'user-registration' ),
					'desc'     => __("Redirect url after the payment process, also used as notify_url for Paypal IPN.", "user-registration"),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_return_url',
					'default'  => get_option( 'user_registration_global_paypal_return_url' ),
					'placeholder'  => esc_url(wp_login_url())
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Client ID', 'user-registration' ),
					'desc'     =>  __("Your client_id, Required for one click subscription cancellation.", "user-registration"),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_client_id',
					'default'  => get_option( 'user_registration_global_paypal_client_id' ),
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Client Secret', 'user-registration' ),
					'desc'     => __("Your client_secret, Required for one click subscription cancellation.", "user-registration"),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_client_secret',
					'default'  => get_option( 'user_registration_global_paypal_client_secret' ),
				),
			),
		);

		$settings['sections']['paypal_options'] = $paypal_settings;

		return $settings;
	}
}

new User_Registration_Paypal_Module();
