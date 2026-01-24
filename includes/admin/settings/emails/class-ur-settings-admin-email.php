<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Admin_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Admin_Email', false ) ) :

	/**
	 * UR_Settings_Admin_Email Class.
	 */
	class UR_Settings_Admin_Email {
		/**
		 * UR_Settings_Admin_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Admin_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Admin_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Admin_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'admin_email';
			$this->title       = __( 'New Member Registered', 'user-registration' );
			$this->description = __( 'Notify admins about a new membership signup, including member details.', 'user-registration' );
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
				'user_registration_admin_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'admin_email' => array(
							'title'        => __( 'New User Registered Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to admin after successful user registration.', 'user-registration' ),
									'id'       => 'user_registration_enable_admin_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Recipients', 'user-registration' ),
									'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
									'id'       => 'user_registration_admin_email_receipents',
									'default'  => get_option( 'admin_email' ),
									'type'     => 'text',
									'css'      => '',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'A New Member Registered.', 'user-registration' ),
									'id'       => 'user_registration_admin_email_subject',
									'type'     => 'text',
									'default'  => __( 'A Member registration: {{username}}', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_admin_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_admin_email(),
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
		 * Email format.
		 *
		 * @return string $message Message content to be overridden for admin email.
		 */
		public function ur_get_admin_email() {

			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi Admin,
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					A new member has been registered.
				</p>
				<p>
					<strong>Member Details:</strong>
					<ul>
					<li style="margin-bottom:10px;">
						<b>Name</b>: {{username}}
					</li>
					<li style="margin-bottom:10px;">
						<b>Email</b>: {{email}}
					</li>
					</ul>
					</p>
					<p>
					{{membership_plan_details}}
					</p>
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
			 * Filter to modify the admin email message content.
			 *
			 * @param string $general_msg Message to be overridden for admin email.
			 */
			$message = apply_filters( 'user_registration_admin_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Admin_Email();
