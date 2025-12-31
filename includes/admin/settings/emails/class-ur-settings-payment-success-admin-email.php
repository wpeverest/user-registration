<?php
/**
 * Configure Email
 *
 * @category Class
 * @author   WPEverest
 * @since   1.3.3
 * @package UserRegistrationPayments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Payment_Success_Admin_Email', false ) ) :

	/**
	 * UR_Settings_Payment_Success_Admin_Email Class.
	 */
	class UR_Settings_Payment_Success_Admin_Email {
		/**
		 * UR_Settings_Payment_Success_Admin_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Payment_Success_Admin_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Payment_Success_Admin_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Payment_Success_Admin_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'payment_success_admin_email';
			$this->title       = esc_html__( 'Payment Success', 'user-registration' );
			$this->description = esc_html__( 'Notifies admins that a user’s payment was successful.', 'user-registration' );
			$this->receiver    = 'Admin';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_payment_success_admin_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'payment_success_email' => array(
							'title'        => esc_html__( 'Payment Success Admin Email', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-admin' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this email sent to the admin after succesful payment from user.', 'user-registration' ),
									'id'       => 'user_registration_enable_payment_success_admin_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Receipents', 'user-registration' ),
									'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
									'id'       => 'user_registration_payments_admin_email_receipents',
									'default'  => get_option( 'admin_email' ),
									'type'     => 'text',
									'css'      => '',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_payment_success_admin_email_subject',
									'type'     => 'text',
									'default'  => __( 'Payment Received from {{username}}', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_payment_success_admin_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_payment_success_admin_email(),
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
		public static function ur_get_payment_success_admin_email() {

			/**
			 * Filter to overwrite the payment success admin email.
			 *
			 * @param string Message content to overwrite the existing email content.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi Admin,
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					You have received a payment.
				</p>
				<p>
					<b>Payment Details:</b>
					<ul>
					<li style="margin-bottom:10px;">
						<b>Member:</b> {{username}}
					</li>
					<li style="margin-bottom:10px;">
						<b>Amount:</b> {{payment_amount}}
					</li>
					<li style="margin-bottom:10px;">
						<b>Payment Date:</b> {{payment_date}}
					</li>
					</ul>
					</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					View full payment details: <br>
					<a href="{{home_url}}/wp-admin/user-edit.php?user_id={{user_id}}" style="color: #4A90E2; text-decoration: none;">Click Here</a>
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Thanks
</p>
				',
				'user-registration'
			);
			$body_content = ur_wrap_email_body_content( $body_content );

			if ( UR_PRO_ACTIVE && function_exists( 'ur_get_email_template_wrapper' ) ) {
				$body_content = ur_get_email_template_wrapper( $body_content, false );
			}

			/**
			 * Filter to modify the payment success admin email message content.
			 *
			 * @param string $body_content Message content for payment success admin email to be overridden.
			 */
			$message = apply_filters( 'user_registration_payment_admin_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Payment_Success_Admin_Email();
