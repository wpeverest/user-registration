<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Confirm_Email_Address_Change_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Confirm_Email_Address_Change_Email', false ) ) :

	/**
	 * UR_Settings_Confirm_Email_Address_Change_Email Class.
	 */
	class UR_Settings_Confirm_Email_Address_Change_Email {
		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Description.
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
			$this->id          = 'confirm_email_address_change_email';
			$this->title       = __( 'Email Address Change Confirmation', 'user-registration' );
			$this->description = __( 'Asks the user to verify a newly requested email address change with a confirmation link.', 'user-registration' );
			$this->receiver    = 'User';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_confirm_email_address_change',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'confirm_email_address_change_email' => array(
							'title'        => __( 'Email Address Change Confirmation Email', 'user-registration' ),
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
									'desc'     => __( 'Email sent to the user to confirm the email address changed.', 'user-registration' ),
									'id'       => 'user_registration_enable_confirm_email_address_change_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_confirm_email_address_change_email_subject',
									'type'     => 'text',
									'default'  => __( 'Confirm Your New Email Address', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_confirm_email_address_change_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_confirm_email_address_change_email(),
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
		 * Email Format.
		 */
		public function ur_get_confirm_email_address_change_email() {

			/**
			 * Filter to overwrite the confirm email address change email.
			 *
			 * @param string Message content to overwrite the existing email content.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{display_name}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					You recently requested to change the email associated with your account from {{email}} to {{updated_new_user_email}}.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					To confirm this change, please click the link below: {{email_change_confirmation_link}}
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					This link will remain active for 24 hours. If you did not request this change, please contact us immediately at {{admin_email}}.
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
			 * Filter to modify the confirm email address change email message content.
			 *
			 * @param string $body_content Message content for confirm email address change email to be overridden.
			 */
			$message = apply_filters( 'user_registration_get_confirm_email_address_email', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Confirm_Email_Address_Change_Email();
