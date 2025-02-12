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
									'desc'     => sprintf( __( 'Select the redirection page which opens from the membership listing shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_groups' ) ), //phpcs:ignore
									'id'       => 'user_registration_member_registration_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => 'min-width:350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Thank You Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains the membership thank you shortcode: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_membership_thank_you' ) ), //phpcs:ignore
									'id'       => 'user_registration_thank_you_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => 'min-width:350px;',
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
		 * Get Bank Global Settings.
		 *
		 * @param array $settings settings.
		 */
		public function get_bank_settings( $settings ) {
			$default_text = '<p>Please transfer the amount to the following bank detail.</p><p>Bank Name: XYZ</p><p>Bank Acc.No: ##############</p>';
			$bank_settings = array(
				'title'    => __( 'Bank Transfer Settings', 'user-registration' ),
				'type'     => 'card',
				'desc'     => '',
				'settings' => array(
					array(
						'title'    => __( 'Enter your details', 'user-registration' ),
						'desc'     => __( 'Field to add necessary bank details which will be shown to users after successful payment using the bank option during checkout.', 'user-registration' ),
						'id'       => 'user_registration_global_bank_details',
						'type'     => 'tinymce',
						'default'  => get_option( 'user_registration_global_bank_details' ),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true
					),
				),
			);

			$settings['sections']['bank_options'] = $bank_settings;

			return $settings;
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
