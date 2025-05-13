<?php
/**
 * Configure User Email
 *
 * @package UR_Settings_Profile_Details_Updated_Email Class
 * @since 3.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Profile_Details_Updated_Email', false ) ) :

	/**
	 * UR_Settings_Profile_Details_Updated_Email Class.
	 */
	class UR_Settings_Profile_Details_Updated_Email {
		/**
		 * UR_Settings_Profile_Details_Updated_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Profile_Details_Updated_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Profile_Details_Updated_Email Description.
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
			$this->id          = 'profile_details_updated_email';
			$this->title       = __( 'Profile Updated', 'user-registration' );
			$this->description = __( 'Confirms to the user that their profile details were successfully updated.', 'user-registration' );
			$this->receiver    = __( 'User', 'user-registration' );
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
				'user_registration_profile_details_updated_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'profile_details_updated_email' => array(
							'title'        => __( 'Profile Updated Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to the user when a user updated their profile information.', 'user-registration' ),
									'id'       => 'user_registration_enable_profile_details_updated_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_profile_details_updated_email_subject',
									'type'     => 'text',
									'default'  => __( 'Profile Updated Successfully on {{blog_info}}', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_profile_details_Updated_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_profile_details_updated_email(),
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
		 * Email Message to be send to admin while profile detail changed.
		 *
		 * @return string message
		 */
		public function ur_get_profile_details_updated_email() {

			/**
			 * Filter to modify the message content for profile details updated.
			 *
			 * @return string $message Message content for profile details updated email to be overridden.
			 */
			$message = apply_filters(
				'user_registration_profile_details_updated_email_message',
				sprintf(
					__(
					'Hi {{username}},<br/>
					Your profile details have been successfully updated on {{blog_info}}.<br/>
					{{all_fields}}<br/>
					Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Profile_Details_Updated_Email();
