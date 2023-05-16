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
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'reset_password_email';
			$this->title       = __( 'Reset Password Email', 'user-registration' );
			$this->description = __( 'Email sent to the user when a user requests for reset password', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_reset_password_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'reset_password_email' => array(
							'title'     => __( 'Reset Password Email', 'user-registration' ),
							'type'      => 'card',
							'desc'      => '',
							'back_link' => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
							'settings'  => array(
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
									'default'  => __( 'Password Reset Email: {{blog_info}}', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_reset_password_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_reset_password_email(),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 */
		public function ur_get_reset_password_email() {

			$message = apply_filters(
				'user_registration_reset_password_email_message',
				sprintf(
					__(
						'Someone has requested a password reset for the following account: <br/>

SiteName: {{blog_info}} <br/>
Username: {{username}} <br/>

If this was a mistake, just ignore this email and nothing will happen. <br/>

To reset your password, visit the following address: <br/>
<a href="{{home_url}}/{{ur_login}}?action=rp&key={{key}}&login={{username}} " rel="noreferrer noopener" target="_blank">Click Here</a><br/>

Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Reset_Password_Email();
