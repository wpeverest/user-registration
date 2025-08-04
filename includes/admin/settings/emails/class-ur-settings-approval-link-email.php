<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Approval_Link_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Approval_Link_Email', false ) ) :

	/**
	 * UR_Settings_Approval_Link_Email Class.
	 */
	class UR_Settings_Approval_Link_Email {
		/**
		 * UR_Settings_Approval_Link_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Approval_Link_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Approval_Link_Email Description.
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
			$this->id          = 'approval_link_email';
			$this->title       = __( 'Admin Approval Request', 'user-registration' );
			$this->description = __( 'Requests admin approval for a user registration approval, with a direct link to approve or deny.', 'user-registration' );
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
				'user_registration_approval_link_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'admin_email' => array(
							'title'        => __( 'Admin Approval Request Email with Approval Link', 'user-registration' ),
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
									'desc'     => __( 'Enable this email to send approval link for user registration.', 'user-registration' ),
									'id'       => 'user_registration_enable_approval_link_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Receipents', 'user-registration' ),
									'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
									'id'       => 'user_registration_approval_link_email_receipents',
									'default'  => get_option( 'admin_email' ),
									'type'     => 'text',
									'css'      => '',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_approval_link_email_subject',
									'type'     => 'text',
									'default'  => __( 'Action Required: Review & Approve New User Registration', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_approval_link_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_approval_link_email(),
									'css'      => '',
									'desc_tip' => true,
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
		 * Email format for approval link in email.
		 *
		 * @return string $approval_msg Message content for approval link in email.
		 */
		public function ur_get_approval_link_email() {
			$approval_msg = sprintf(
				__(
					'Hi Admin, <br/>

					A new user {{username}} - {{email}} has successfully registered to your site <a href="{{home_url}}">{{blog_info}}</a>. <br/>

					Please review their details and assigned role in the \'<b>Users</b>\' menu of your WordPress dashboard.  <br/>

					[Approve User: {{approval_link}}] - Link <br>
					[Deny User: {{denial_link}}] - Link <br>

					Thank You!',
					'user-registration'
				)
			);

			/**
			 * Filter to modify the approval email message email content.
			 *
			 * @param string $approval_msg Message content to be overridden for admin approval email.
			 */
			$approval_msg = apply_filters( 'user_registration_admin_approval_email_message', $approval_msg );

			return $approval_msg;
		}
	}
endif;

return new UR_Settings_Approval_Link_Email();
