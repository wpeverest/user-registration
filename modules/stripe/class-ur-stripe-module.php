<?php
/**
 * User_Registration_Stripe_Module
 *
 * @package  User_Registration_Stripe_Module
 * @since  4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class User_Registration_Stripe_Module
 */
class User_Registration_Stripe_Module {

	/**
	 * User_Registration_Stripe_Module Constructor
	 */
	public function __construct() {

		// Stripe Settings Hooks.
		if ( is_admin() ) {
			// Filter for global settings.
			add_filter( 'user_registration_payment_settings', array( $this, 'get_stripe_settings' ) );
		}
	}

	/**
	 * Get Stripe Global Settings.
	 *
	 * @param array $settings settings.
	 */
	public function get_stripe_settings( $settings ) {
		$stripe_settings = array(
			'title'    => __( 'Stripe Settings', 'user-registration' ),
			'type'     => 'card',
			'desc'     => '',
			'settings' => array(
				array(
					'title'    => __( 'Test Publishable key', 'user-registration' ),
					'desc'     => __( 'Stripe test publishable  key.', 'user-registration' ),
					'id'       => 'user_registration_stripe_test_publishable_key',
					'type'     => 'text',
					'css'      => 'min-width: 350px',
					'desc_tip' => true,
					'default'  => '',
				),
				array(
					'title'    => __( 'Test Secret key', 'user-registration' ),
					'desc'     => __( 'Stripe test secret key.', 'user-registration' ),
					'id'       => 'user_registration_stripe_test_secret_key',
					'type'     => 'text',
					'css'      => 'min-width: 350px',
					'desc_tip' => true,
					'default'  => '',
				),
				array(
					'type'     => 'toggle',
					'title'    => __( 'Enable Test Mode', 'user-registration' ),
					'desc'     => __( 'Check if using test mode.', 'user-registration' ),
					'id'       => 'user_registration_stripe_test_mode',
					'desc_tip' => true,
					'default'  => '',
				),
				array(
					'title'    => __( 'Live Publishable Key', 'user-registration' ),
					'desc'     => __( 'Stripe live publishable key.', 'user-registration' ),
					'id'       => 'user_registration_stripe_live_publishable_key',
					'type'     => 'text',
					'css'      => 'min-width: 350px',
					'desc_tip' => true,
					'default'  => '',
				),
				array(
					'title'    => __( 'Live Secret key', 'user-registration' ),
					'desc'     => __( 'Stripe live secret key.', 'user-registration' ),
					'id'       => 'user_registration_stripe_live_secret_key',
					'type'     => 'text',
					'css'      => 'min-width: 350px',
					'desc_tip' => true,
					'default'  => '',
				),
			),
		);

		$settings['sections']['stripe_options'] = $stripe_settings;
		return $settings;
	}
}

new User_Registration_Stripe_Module();
