<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Reset_Password_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Reset_Password_Email', false ) ) :

	/**
	 * UR_Settings_Reset_Password_Email Class.
	 */
	class UR_Settings_Reset_Password_Email {
		/**
		 * UR_Settings_Reset_Password_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Reset_Password_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Reset_Password_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Approval_Link_Email Receiver.
		 *
		 * @var string
		 */

		public $receiver;
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'reset_password_email';
			$this->title       = __( 'Reset Password', 'user-registration' );
			$this->description = __( 'Sends a secure password reset link to the user who requested a reset.', 'user-registration' );
			$this->receiver    = 'User';
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
				'user_registration_reset_password_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'reset_password_email' => array(
							'title'        => __( 'Reset Password Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this to send an email to the user when they request for a password reset.', 'user-registration' ),
									'id'       => 'user_registration_enable_reset_password_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_reset_password_email_subject',
									'type'     => 'text',
									'default'  => __( 'Password Reset Request', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_reset_password_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_reset_password_email(),
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

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 *
		 * @return string $message Message content for reset password email.
		 */
		public function ur_get_reset_password_email() {

			/**
			 * Filter to overwrite the reset password email.
			 *
			 * @param string Message content to overwrite the existing email content.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{username}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				We received a password reset request for your account at <a href="{{home_url}}" style="color: #4A90E2; text-decoration: none;">{{blog_info}}</a>
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					If you did not request this, you can safely ignore this email.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					If you like to proceed, click the link below to reset your password:
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					<a href="{{home_url}}/{{ur_reset_pass_slug}}?action=rp&key={{key}}&login={{username}}" style="color: #4A90E2; text-decoration: none;" rel="noreferrer noopener" target="_blank">Click Here</a>
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					 This link is valid for 24 hours.
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
			 * Filter to modify the message content for reset password email.
			 *
			 * @param string $body_content Message content for reset password email to be overridden.
			 */
			$message = apply_filters( 'user_registration_reset_password_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Reset_Password_Email();
