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
			add_filter(
				'urm_validate_paypal_payment_section_before_update',
				array(
					$this,
					'validate_paypal_section',
				)
			);
			add_action( 'urm_save_paypal_payment_section', array( $this, 'save_section_settings' ), 10, 1 );
		}
	}


	/**
	 * raw_settings
	 *
	 * @return array
	 */
	public function raw_settings() {
		return array(
			'title'        => __( 'Paypal Settings', 'user-registration' ),
			'type'         => 'accordian',
			'id'           => 'paypal',
			'desc'         => '',
			'is_connected' => get_option( 'urm_paypal_connection_status', false ),
			'settings'     => array(
				array(
					'id'       => 'user_registration_global_paypal_mode',
					'type'     => 'select',
					'title'    => __( 'Mode', 'user-registration' ),
					'desc'     => __( 'Select a mode to run paypal.', 'user-registration' ),
					'desc_tip' => true,
					'options'  => array(
						'production' => __( 'Production', 'user-registration' ),
						'test'       => __( 'Test/Sandbox', 'user-registration' ),
					),
					'class'    => 'ur-enhanced-select',
					'default'  => get_option( 'user_registration_global_paypal_mode', 'test' ),
				),
				array(
					'type'        => 'text',
					'title'       => __( 'PayPal Email Address', 'user-registration' ),
					'desc'        => __( 'Enter you PayPal email address.', 'user-registration' ),
					'desc_tip'    => true,
					'required'    => true,
					'id'          => 'user_registration_global_paypal_email_address',
					'default'     => get_option( 'user_registration_global_paypal_email_address' ),
					'placeholder' => get_option( 'admin_email' ),
				),
				array(
					'type'        => 'text',
					'title'       => __( 'Cancel Url', 'user-registration' ),
					'desc'        => __( 'Endpoint set for handling paypal cancel api.', 'user-registration' ),
					'desc_tip'    => true,
					'id'          => 'user_registration_global_paypal_cancel_url',
					'default'     => get_option( 'user_registration_global_paypal_cancel_url' ),
					'placeholder' => esc_url( home_url() ),
				),
				array(
					'type'        => 'text',
					'title'       => __( 'Return Url', 'user-registration' ),
					'desc'        => __( 'Redirect url after the payment process, also used as notify_url for Paypal IPN.', 'user-registration' ),
					'desc_tip'    => true,
					'id'          => 'user_registration_global_paypal_return_url',
					'default'     => get_option( 'user_registration_global_paypal_return_url' ),
					'placeholder' => esc_url( wp_login_url() ),
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Client ID', 'user-registration' ),
					'desc'     => __( 'Your client_id, Required for subscription related operations.', 'user-registration' ),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_client_id',
					'default'  => get_option( 'user_registration_global_paypal_client_id' ),
				),
				array(
					'type'     => 'text',
					'title'    => __( 'Client Secret', 'user-registration' ),
					'desc'     => __( 'Your client_secret, Required for subscription related operations.', 'user-registration' ),
					'desc_tip' => true,
					'id'       => 'user_registration_global_paypal_client_secret',
					'default'  => get_option( 'user_registration_global_paypal_client_secret' ),
				),
				array(
					'title' => __( 'Save', 'user-registration' ),
					'id'    => 'user_registration_paypal_save_settings',
					'type'  => 'button',
					'class' => 'payment-settings-btn',
				),
			),
		);
	}

	/**
	 * Get Stripe Global Settings.
	 *
	 * @param array $settings settings.
	 */
	public function get_paypal_settings( $settings ) {
		$stripe_settings                        = $this->raw_settings();
		$settings['sections']['paypal_options'] = $stripe_settings;

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
	public function validate_paypal_section( $form_data ) {
		$changed  = false;
		$response = array(
			'status' => true,
		);
		//these value should not be empty
		if ( empty( $form_data['user_registration_global_paypal_cancel_url'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field Cancel Url is required.';

			return $response;
		}
		if ( empty( $form_data['user_registration_global_paypal_return_url'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field Return Url is required.';

			return $response;
		}

		if ( ! ur_is_valid_url( $form_data['user_registration_global_paypal_cancel_url'] ) || ! ur_is_valid_url( $form_data['user_registration_global_paypal_return_url'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Cancel/Return url must be a valid url.';

			return $response;
		}
		preg_match( '#^' . preg_quote( site_url(), '#' ) . '#', $form_data['user_registration_global_paypal_cancel_url'], $cancel_url_matches );
		preg_match( '#^' . preg_quote( site_url(), '#' ) . '#', $form_data['user_registration_global_paypal_return_url'], $return_url_matches );

		if ( count( $cancel_url_matches ) < 1 || count( $return_url_matches ) < 1 ) {
			$response['status']  = false;
			$response['message'] = 'Cancel/Return url cannot be an external url.';

			return $response;
		}

		//check if any value has changed
		foreach ( $form_data as $k => $data ) {
			$last_data = get_option( $k );
			if ( $last_data !== $data ) {
				$changed = true;
				break;
			}
		}

//		if client secret is filled then client id is required and vice versa
		if ( ! empty( $form_data['user_registration_global_paypal_client_id'] ) && empty( $form_data['user_registration_global_paypal_client_secret'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field client secret is required with client id';

			return $response;
		}
		if ( ! empty( $form_data['user_registration_global_paypal_client_secret'] ) && empty( $form_data['user_registration_global_paypal_client_id'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field client id is required with client secret';

			return $response;
		}
		if ( ! empty( $form_data['user_registration_global_paypal_client_id'] ) && ! empty( $form_data['user_registration_global_paypal_client_secret'] ) && $changed ) {
			$client_id      = $form_data['user_registration_global_paypal_client_id'];
			$client_secret  = $form_data['user_registration_global_paypal_client_secret'];
			$url            = ( 'production' === $form_data['user_registration_global_paypal_mode'] ) ? 'https://api-m.paypal.com/' : 'https://api-m.sandbox.paypal.com/';
			$paypal_service = new \WPEverest\URMembership\Admin\Services\Paypal\PaypalService();
			$request        = $paypal_service->login_paypal( $url, $client_id, $client_secret );
			if ( 200 !== $request['status_code'] ) {
				$response['status']  = false;
				$response['message'] = 'Invalid Paypal Credentials';

				return $response;
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

new User_Registration_Paypal_Module();
