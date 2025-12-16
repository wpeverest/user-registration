<?php
/**
 * Class UR_Settings_Payment
 *
 * Handles the payment related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * 
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Payment' ) ) {
	/**
	 * UR_Settings_Payment Class
	 */
	class UR_Settings_Payment extends UR_Settings_Page {
        private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {            
			$this->id    = 'payment';
			$this->label = __( 'Payment', 'user-registration' );
            parent::__construct();
            $this->handle_hooks();
		}
        public static function get_instance() {
            if ( null === self::$_instance ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        /**
         * Register hooks for submenus and section UI.
         * @return void
         */
        public function handle_hooks() {
            add_filter( "user_registration_get_sections_{$this->id}",  array( $this, 'get_sections_callback' ), 1, 1 );
            add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
        }
        /**
         * Filter to provide sections submenu for payment settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'payment-method' ] = __( 'Payment Method', 'user-registration' );
            $sections[ 'store' ] = __( 'Store', 'user-registration' );
            $sections[ 'tax-vat' ] = __( 'Tax & VAT', 'user-registration' );
            $sections[ 'invoices' ] = __( 'Invoices', 'user-registration' );
            $sections[ 'payment-retry' ] = __( 'Payment Retry & Dunning', 'user-registration' );
            return $sections;
        }
        /**
         * Filter to provide sections UI for payment settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            $paypal_settings = $this->get_paypal_settings();
            $stripe_settings = $this->get_stripe_settings();
            $bank_transfer_settings = $this->get_bank_transfer_settings();
            if( 'payment-method' === $current_section ) {
                add_filter( 'user_registration_settings_hide_save_button', true );
                $settings = array(
                    'title' => '',
                    'sections' => array(
                        'paypal_options' => $paypal_settings,
                        'stripe_options' => $stripe_settings,
                        'bank_transfer_options' => $bank_transfer_settings,
                    ),
                );
            } else {
                $settings = $this->upgrade_to_pro_setting();
            }
            return $settings;
        }
        public function get_paypal_settings() {
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
        public function get_stripe_settings() {
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
        public function get_bank_transfer_settings() {
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
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Payment', 'get_instance' ) ? UR_Settings_Payment::get_instance() : new UR_Settings_Payment();
