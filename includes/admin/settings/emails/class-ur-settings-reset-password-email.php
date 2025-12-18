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
									'default'  => __( 'Password Reset Request – Reset Your Password for {{blog_info}}', 'user-registration' ),
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
			 * Filter to modify the message content for reset password email.
			 *
			 * @param string Message content for reset password email to be overridden.
			 */
			$message = apply_filters(
				'user_registration_reset_password_email_message',
				__(
					'<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
						<!-- Header -->
						<div style="background-color: #E8D5FF; padding: 40px 30px; position: relative; overflow: hidden;">
							<!-- Decorative Circles -->
							<div style="position: absolute; top: -20px; right: 20px; width: 80px; height: 80px; background-color: rgba(232, 213, 255, 0.6); border-radius: 50%;"></div>
							<div style="position: absolute; top: 10px; right: 100px; width: 50px; height: 50px; background-color: rgba(232, 213, 255, 0.5); border-radius: 50%;"></div>
							<div style="position: absolute; top: 30px; right: 60px; width: 30px; height: 30px; background-color: rgba(232, 213, 255, 0.4); border-radius: 50%;"></div>
							<!-- Logo -->
							<div style="position: relative; z-index: 1;">
								<svg width="48" height="48" viewBox="0 0 48 48" style="display: inline-block;">
									<path d="M12 4C10.9 4 10 4.9 10 6v36c0 1.1.9 2 2 2h12V4H12z" fill="#4A90E2"/>
									<path d="M24 4v40h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H24z" fill="#E94B7E"/>
								</svg>
							</div>
						</div>
						
						<!-- Body Content -->
						<div style="padding: 40px 30px; background-color: #ffffff;">
							<p style="margin: 0 0 20px 0; color: #000000; font-size: 16px; line-height: 1.6;">
								Hi {{username}},
							</p>
							<p style="margin: 0 0 20px 0; color: #000000; font-size: 16px; line-height: 1.6;">
								We received a request to reset the password for your account on {{blog_info}}.
							</p>
							<p style="margin: 0 0 20px 0; color: #000000; font-size: 16px; line-height: 1.6;">
								If this was a mistake, simply ignore this email, and no changes will be made to your account.
							</p>
							<p style="margin: 0 0 20px 0; color: #000000; font-size: 16px; line-height: 1.6;">
								To reset your password, please click the link below:
							</p>
							<p style="margin: 0 0 30px 0;">
								<a href="{{home_url}}/{{ur_reset_pass_slug}}?action=rp&key={{key}}&login={{username}}" rel="noreferrer noopener" target="_blank" style="color: #4A90E2; text-decoration: none; font-weight: 600;">Click Here</a>
							</p>
						</div>
						
						<!-- Footer -->
						<div style="padding: 30px; background-color: #ffffff; border-top: 1px solid #e0e0e0;">
							<p style="margin: 0 0 10px 0; color: #000000; font-size: 16px; line-height: 1.6;">
								Thank You!
							</p>
							<p style="margin: 0; color: #000000; font-size: 16px; line-height: 1.6; font-weight: 600;">
								{{blog_info}} Team
							</p>
						</div>
					</div>',
					'user-registration'
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Reset_Password_Email();
