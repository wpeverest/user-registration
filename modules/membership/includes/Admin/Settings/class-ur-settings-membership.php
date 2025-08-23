<?php
/**
 * UserRegistration Membership Settings
 *
 * @class    UR_Settings_Membership
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Membership ' ) ) :

	/**
	 * UR_Settings_Captcha Class
	 */
	class UR_Settings_Membership extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'membership';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'membership';
			$this->label = __( 'Membership', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'user_registration_payment_settings', array( $this, 'get_bank_settings' ) );
			add_filter( 'urm_validate_bank_payment_section_before_update', array(
				$this,
				'validate_bank_section'
			) );
			add_action( 'urm_save_bank_payment_section', array( $this, 'save_section_settings' ), 10, 1 );

		}

		public function get_raw_settings() {
			return array(
				'id'           => 'bank',
				'title'        => __( 'Bank Transfer Settings', 'user-registration' ),
				'type'         => 'accordian',
				'desc'         => '',
				'is_connected' => get_option( 'urm_bank_connection_status', false ),
				'settings'     => array(
					array(
						'title'    => __( 'Enter your details', 'user-registration' ),
						'desc'     => __( 'Field to add necessary bank details which will be shown to users after successful payment using the bank option during checkout.', 'user-registration' ),
						'id'       => 'user_registration_global_bank_details',
						'type'     => 'tinymce',
						'default'  => get_option( 'user_registration_global_bank_details' ),
						'css'      => '',
						'desc_tip' => true
					),
					array(
						'title' => __( 'Save', 'user-registration' ),
						'id'    => 'user_registration_bank_save_settings',
						'type'  => 'button',
						'class' => 'payment-settings-btn'
					),
				),
			);
		}
		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_membership_settings',
				array(
					'title'    => '',
					'sections' => array(
						'membership_settings' => array(
							'title'    => __( 'Membership', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Member Registration Form Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the redirection page which opens from the membership listing shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_groups' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_member_registration_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Thank You Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains the membership thank you shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_membership_thank_you' ) ),
									//phpcs:ignore
									'id'       => 'user_registration_thank_you_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Renewal Behaviour', 'user-registration' ),
									'desc'     => __( 'Choose how membership subscriptions are renewed, automatically through the payment provider or manually by the user', 'user-registration' ),
									'id'       => 'user_registration_renewal_behaviour',
									'type'     => 'select',
									'default'  => 'automatic',
									'class'    => 'ur-enhanced-select',
									'css'      => '',
									'options'  => array(
										'automatic' => __('Renew Automatically', 'user-registration'),
										'manual' => __('Renew Manually', 'user-registration')
									),
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Membership Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_membership_settings_' . $this->id, $settings );
		}

		/**
		 * validate_stripe_section
		 *
		 * @param $form_data
		 *
		 * @return true[]
		 * @throws \Stripe\Exception\ApiErrorException
		 */
		public function validate_bank_section( $form_data ) {
			$response = array(
				'status' => true,
			);

			if ( empty( $form_data['user_registration_global_bank_details'] ) ) {
				$response['status']  = false;
				$response['message'] = 'Bank details cannot be empty';
				return $response;
			}

			return $response;
		}
		/**
		 * Get Bank Global Settings.
		 *
		 * @param array $settings settings.
		 */
		public function get_bank_settings( $settings ) {
//			$default_text  = '<p>Please transfer the amount to the following bank detail.</p><p>Bank Name: XYZ</p><p>Bank Acc.No: ##############</p>';

			$bank_settings = $this->get_raw_settings();

			$settings['sections']['bank_options'] = $bank_settings;

			return $settings;
		}
		/**
		 * save_section_settings
		 *
		 * @param $form_data
		 *
		 * @return void
		 */
		public function save_section_settings( $form_data ) {
			$section = $this->get_raw_settings();

			ur_save_settings_options( $section, $form_data );
		}
		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}

	}

endif;

return new UR_Settings_Membership();
