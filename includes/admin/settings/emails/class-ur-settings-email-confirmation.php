<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Email_Confirmation
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Email_Confirmation', false ) ) :

	/**
	 * UR_Settings_Email_Confirmation Class.
	 */
	class UR_Settings_Email_Confirmation {
		/**
		 * UR_Settings_Email_Confirmation Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Email_Confirmation Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Email_Confirmation Description.
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
			$this->id          = 'email_confirmation';
			$this->title       = __( 'Email Address Confirmation', 'user-registration' );
			$this->description = __( 'Requests the user to confirm their email address by clicking a verification link.', 'user-registration' );
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
				'user_registration_email_confirmation',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'email_confirmation' => array(
							'title'        => __( 'Email Address Confirmation Email', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-user' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_email_confirmation_subject',
									'type'     => 'text',
									'default'  => __( 'Confirm Your Email Address', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),

								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_email_confirmation',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_email_confirmation(),
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
		 * @return string $message Message content for email confirmation.
		 */
		public function ur_get_email_confirmation() {

			/**
			 * Filter to overwrite email confirmation message content.
			 *
			 * @param string Message content for email confirmation to be overwritten.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{username}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Thank you for registering at <a href="{{home_url}}" style="color: #4A90E2; text-decoration: none;">{{blog_info}}</a>!
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					To complete your registration, please confirm your email address by clicking the link below:
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					<a href="{{home_url}}/{{ur_login}}?ur_token={{email_token}}" style="color: #4A90E2; text-decoration: none;">Click Here</a>
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				This verification link is valid for 24 hours. If you need assistance, we\'re here to help.
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

			$message = apply_filters( 'user_registration_get_email_confirmation', $body_content );
			return $message;
		}
	}
endif;

return new UR_Settings_Email_Confirmation();
