<?php
/**
 * Configure Email
 *
 * @category Class
 * @author   WPEverest
 * @since   1.0.0
 * @package UserRegistrationPayments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Payment_Retry_Failed_Email', false ) ) :

	/**
	 * UR_Settings_Payment_Retry_Failed_Email Class.
	 */
	class UR_Settings_Payment_Retry_Failed_Email {
		/**
		 * UR_Settings_Payment_Retry_Failed_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Payment_Retry_Failed_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Payment_Retry_Failed_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Payment_Retry_Failed_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'payment_retry_failed_email';
			$this->title       = esc_html__( 'Payment Retry - Failed Attempt Email', 'user-registration' );
			$this->description = esc_html__( 'Notifies user on failed attempt to retry payment on subscription payments.', 'user-registration' );
			$this->receiver    = 'User';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_payment_retry_failed_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'payment_retry_failed_email' => array(
							'title'        => esc_html__( 'Payment Retry - Failed Attempt', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-user' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this email sent to the user after succesful payment.', 'user-registration' ),
									'id'       => 'user_registration_enable_payment_success_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_payment_success_email_subject',
									'type'     => 'text',
									'default'  => __( 'Payment Success – Registration Payment Complete on {{blog_info}}', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
								'id'       => 'user_registration_payment_success_email',
								'type'     => 'tinymce',
								'default'  => $this->ur_get_payment_retry_failed_email(),
								'css'      => '',
								'desc_tip' => true,
								'show-ur-registration-form-button' => false,
								'show-smart-tags-button' => true,
								'show-reset-content-button' => true,
							),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Get payment success email.
		 */
		public static function ur_get_payment_retry_failed_email() {

			$message = apply_filters(
				'user_registration_payment_email_message',
				sprintf(
					__(
						'Hi {{username}}, <br/><br/>
						We attempted to process your payment but the retry attempt was unsuccessful. We may try again automatically. To avoid interruption, please update your payment details or try again. You can view the payment details here:<br/><br/>
						{{payment_invoice}}<br/><br/>
						Thank you.',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Payment_Retry_Failed_Email();