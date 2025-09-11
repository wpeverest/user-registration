<?php
/**
 * UR_Payment_Setting Class
 * Settings for Payments
 *
 * @version   1.0.0
 * @package UserRegistrationPayments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Payment_Setting' ) ) :

	/**
	 * UR PDF Setting.
	 */
	class UR_Payment_Setting extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'payment';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'payment';
			$this->label = esc_html__( 'Payments', 'user-registration' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'urm_save_payment-settings_payment_section', array( $this, 'save_section_settings' ), 10, 1 );
		}

		/**
		 * Function to get Global Settings
		 */
		public function get_settings() {
			$currencies      = ur_payment_integration_get_currencies();
			$currencies_list = array();

			// Break and concatenate the currency symbol and code.
			foreach ( $currencies as $code => $currency ) {
				$currencies_list[ $code ] = $currency['name'] . ' ( ' . $code . ' ' . $currency['symbol'] . ' )';
			}

			$settings = array(
				'title'    => __( 'Payments', 'user-registration' ),
				'sections' => array(
					'payment_settings' => array(
						'id'          => 'payment-settings',
						'title'       => esc_html__( 'Payment Settings', 'user-registration' ),
						'type'        => 'card',
						'desc'        => '',
						'show_status' => false,
						'show_logo'   => false,
						'settings'    => array(
							array(
								'title'    => __( 'Currency', 'user-registration' ),
								'desc'     => __( 'This option lets you choose currency for payments.', 'user-registration' ),
								'id'       => 'user_registration_payment_currency',
								'default'  => 'USD',
								'type'     => 'select',
								'class'    => 'ur-enhanced-select',
								'css'      => '',
								'desc_tip' => true,
								'options'  => $currencies_list,
							),
							array(
								'title' => __( 'Save', 'user-registration' ),
								'id'    => 'user_registration_payment_save_settings',
								'type'  => 'button',
								'class' => 'payment-settings-btn'
							),
						),
					),
				),
			);

			return apply_filters( 'user_registration_payment_settings', $settings );
		}

		/**
		 * Get output of global payment settings.
		 */
		public function output() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

			// $saved_currency = get_option( 'user_registration_payment_currency', 'USD' );

			// if ( ! in_array( $saved_currency, paypal_supported_currencies_list() ) ) {
			// 	$currency_url = 'https://developer.paypal.com/docs/reports/reference/paypal-supported-currencies/';
			// 	echo '<div id="ur-currency-error" class="notice notice-warning is-dismissible"><p><strong>' . esc_html__( 'CURRENCY_NOT_SUPPORTED Currency Code :', 'user-registration' ) . '</strong> ' . esc_html( $saved_currency ) . esc_html__( ' is not currently supported by Paypal. Please Refer', 'user-registration' ) . ' <a href="' . esc_url( $currency_url ) . '" rel="noreferrer noopener" target="_blank">' . esc_html__( 'Paypal supported currencies', 'user-registration' ) . '</a></p></div>';
			// }

			UR_Admin_Settings::output_fields( $settings );
			$GLOBALS['hide_save_button'] = true;
		}

		/**
		 * save_section_settings
		 *
		 * @param $form_data
		 *
		 * @return void
		 */
		public function save_section_settings( $form_data ) {
			$settings = $this->get_settings();
			ur_save_settings_options( $settings['sections']['payment_settings'], $form_data );
		}
	}

endif;

return new UR_Payment_Setting();
