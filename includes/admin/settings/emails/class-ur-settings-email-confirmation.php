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
									'default'  => __( 'Email Address Confirmation – Verify Your Registration on {{blog_info}}', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),

								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_email_confirmation',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_email_confirmation(),
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
			$message = apply_filters(
				'user_registration_get_email_confirmation',
				sprintf(
					__(
						'Hi {{username}}, <br/>
						Thank you for registering on <a href="{{home_url}}">{{blog_info}}</a>!
						Please click on the link below to confirm your registration: <br/>
						Click here to confirm your registration: <a href="{{home_url}}/{{ur_login}}?ur_token={{email_token}}">Click Here</a><br/>

						Thank You!',
						'user-registration'
					)
				)
			);
			return $message;
		}
	}
endif;

return new UR_Settings_Email_Confirmation();
