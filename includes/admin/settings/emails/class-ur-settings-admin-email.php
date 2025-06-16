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
									'css'      => 'min-width: 350px;',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'A New Member Registered.', 'user-registration' ),
									'id'       => 'user_registration_admin_email_subject',
									'type'     => 'text',
									'default'  => __( 'A New User Registered', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_admin_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_admin_email(),
									'css'      => 'min-width: 350px;',
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
		 * Email format.
		 *
		 * @return string $message Message content to be overridden for admin email.
		 */
		public function ur_get_admin_email() {

			$general_msg = sprintf(
				__(
					'Hi Admin, <br/>

					A new user {{username}} - {{email}} has successfully registered to your site <a href="{{home_url}}">{{blog_info}}</a>. <br/>
					{{membership_plan_details}} <br/>
					You can review their details and manage their role from the \'<b>Users</b>\' section in your WordPress dashboard.<br/><br />
					Thank You!',
					'user-registration'
				)
			);

			/**
			 * Filter to modify the admin email message content.
			 *
			 * @param string $general_msg Message to be overridden for admin email.
			 */
			$message = apply_filters( 'user_registration_admin_email_message', $general_msg );

			return $message;
		}
	}
endif;

return new UR_Settings_Admin_Email();
