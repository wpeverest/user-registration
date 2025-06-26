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
			add_filter( 'urm_validate_stripe_payment_section_before_update', array(
				$this,
				'validate_stripe_section'
			) );
			add_action( 'urm_save_stripe_payment_section', array( $this, 'save_section_settings' ), 10, 1 );
		}
	}

	/**
	 * raw_settings
	 *
	 * @return array
	 */
	public function raw_settings() {
		return array(
			'id'           => 'stripe',
			'title'        => __( 'Stripe Settings', 'user-registration' ),
			'type'         => 'accordian',
			'show_status'  => false,
			'desc'         => '',
			'is_connected' => get_option( 'urm_stripe_connection_status', false ),
			'settings'     => array(
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
				array(
					'title' => __( 'Save', 'user-registration' ),
					'id'    => 'user_registration_stripe_save_settings',
					'type'  => 'button',
					'class' => 'payment-settings-btn'
				),
			),
		);
	}

	/**
	 * Get Stripe Global Settings.
	 *
	 * @param array $settings settings.
	 */
	public function get_stripe_settings( $settings ) {
		$stripe_settings                        = $this->raw_settings();
		$settings['sections']['stripe_options'] = $stripe_settings;

		return $settings;
	}

	/**
	 * validate_stripe_section
	 *
	 * @param $form_data
	 *
	 * @return true[]
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function validate_stripe_section( $form_data ) {
		$changed  = false;
		$response = array(
			'status' => true,
			'connected' => true,
		);

		foreach ( $form_data as $k => $data ) {
			$last_data = get_option( $k );
			if ( $last_data !== $data ) {
				$changed = true;
				break;
			}
		}

		if ( $changed ) {
			$mode            = isset( $form_data['user_registration_stripe_test_mode'] ) ? ( ( true === $form_data['user_registration_stripe_test_mode'] ) ? 'test' : 'live' ) : 'test';
			$publishable_key = $form_data[ sprintf( 'user_registration_stripe_%s_publishable_key', $mode ) ];
			$secret_key      = $form_data[ sprintf( 'user_registration_stripe_%s_secret_key', $mode ) ];


			\Stripe\Stripe::setApiKey( $secret_key ); // Replace with your actual key

			try {
				$customers = \Stripe\Customer::all( [ 'limit' => 1 ] );
			} catch ( \Stripe\Exception\AuthenticationException $e ) {
				$response['status']  = false;
				$response['connected']  = false;
				$response['message'] = 'Invalid stripe credentials';
			}
		}

		return $response;


	}

	/**
	 * save_section_settings
	 *
	 * @param $form_data
	 *
	 * @return void
	 */
	public function save_section_settings( $form_data ) {
		$section = $this->raw_settings();
		ur_save_settings_options( $section, $form_data );
	}
}

new User_Registration_Stripe_Module();
