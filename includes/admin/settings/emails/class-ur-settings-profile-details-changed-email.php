<?php
/**
 * Configure Email
 *
 * @package UR_Settings_Profile_Details_Changed_Email Class
 * @since 1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Profile_Details_Changed_Email', false ) ) :

	/**
	 * UR_Settings_Profile_Details_Changed_Email Class.
	 */
	class UR_Settings_Profile_Details_Changed_Email {
		/**
		 * UR_Settings_Profile_Details_Changed_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Profile_Details_Changed_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Profile_Details_Changed_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Profile_Details_Changed_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'profile_details_changed_email';
			$this->title       = __( 'Profile Updated', 'user-registration' );
			$this->description = __( 'Notifies admin that a user’s profile details have been updated or changed.', 'user-registration' );
			$this->receiver    = 'Admin';
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
				'user_registration_profile_details_changed_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'profile_details_changed_email' => array(
							'title'        => __( 'Profile Updated', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this email sent to the admin when a user changed profile information.', 'user-registration' ),
									'id'       => 'user_registration_enable_profile_details_changed_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Receipents', 'user-registration' ),
									'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
									'id'       => 'user_registration_edit_profile_email_receipents',
									'default'  => get_option( 'admin_email' ),
									'type'     => 'text',
									'css'      => '',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_profile_details_changed_email_subject',
									'type'     => 'text',
									'default'  => __( 'Profile Updated: {{username}}', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_profile_details_changed_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_profile_details_changed_email(),
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
		 * Email Message to be send to admin while profile detail changed.
		 *
		 * @return string message
		 */
		public function ur_get_profile_details_changed_email() {

			/**
			 * Filter to overwrite the profile details changed email.
			 *
			 * @param string Message content to overwrite the existing email content.
			 */
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi Admin,
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				A member has updated their profile information.
				</p>
				<ul>
					<li style="margin-bottom:10px">
					<b>Member</b>: {{username}}
					</li>
					<li style="margin-bottom:10px">
					<b>Updated</b>: {{update_date}}
					</li>
					</ul>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				View and manage this member in your <b>User Registration and Membership</b> dashboard under <b>Members</b>.
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
			 * Filter to modify the message content for profile details changed.
			 *
			 * @param string $body_content Message content for profile details changed email to be overridden.
			 */
			$message = apply_filters( 'user_registration_profile_details_changed_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Profile_Details_Changed_Email();
