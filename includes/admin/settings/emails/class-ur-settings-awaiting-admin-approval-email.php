<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Awaiting_Admin_Approval_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Awaiting_Admin_Approval_Email', false ) ) :

	/**
	 * UR_Settings_Awaiting_Admin_Approval_Email Class.
	 */
	class UR_Settings_Awaiting_Admin_Approval_Email {
		/**
		 * UR_Settings_Awaiting_Admin_Approval_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Awaiting_Admin_Approval_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Awaiting_Admin_Approval_Email Description.
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
			$this->id          = 'awaiting_admin_approval_email';
			$this->title       = __( 'Awaiting Admin Approval', 'user-registration' );
			$this->description = __( 'Lets the user know their registration is pending and they must wait for admin approval.', 'user-registration' );
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
				'user_registration_awaiting_admin_approval',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'awaiting_admin_approval_email' => array(
							'title'        => __( 'Awaiting Admin Approval Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to user notifying the registration is awaiting admin approval.', 'user-registration' ),
									'id'       => 'user_registration_enable_awaiting_admin_approval_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_awaiting_admin_approval_email_subject',
									'type'     => 'text',
									'default'  => __( 'Your Registration is Pending Approval', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_awaiting_admin_approval_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_awaiting_admin_approval_email(),
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
		 * @return string $message Message content for admin approval email.
		 */
		public function ur_get_awaiting_admin_approval_email() {

			/**
			 * Filter to overwrite the admin approval email.
			 *
			 * @param string Message content to overwrite the existing email content.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{username}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Thank you for registering at <a href="{{home_url}}" style="color: #4A90E2; text-decoration: none;">{{blog_info}}</a>!
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Your registration is currently awaiting approval from our team. We will send you an email as soon as your account is approved.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				If you have questions, feel free to reach out.</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Thanks</p>
				',
				'user-registration'
			);
			$body_content = ur_wrap_email_body_content( $body_content );

			if ( UR_PRO_ACTIVE && function_exists( 'ur_get_email_template_wrapper' ) ) {
				$body_content = ur_get_email_template_wrapper( $body_content, false );
			}

			/**
			 * Filter to modify the approval email message email content.
			 *
			 * @param string $body_content Message content to be overridden for admin approval email.
			 */
			$message = apply_filters( 'user_registration_get_awaiting_admin_approval_email', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Awaiting_Admin_Approval_Email();
