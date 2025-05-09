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

if ( ! class_exists( 'UR_Settings_Successfully_Registered_Email', false ) ) :

	/**
	 * UR_Settings_Successfully_Registered_Email Class.
	 */
	class UR_Settings_Successfully_Registered_Email {
		/**
		 * UR_Settings_Successfully_Registered_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Successfully_Registered_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Successfully_Registered_Email Description.
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
			$this->id          = 'successfully_registered_email';
			$this->title       = __( 'Registration Success', 'user-registration' );
			$this->description = __( 'Confirms successful registration to the user.', 'user-registration' );
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
				'user_registration_successfully_registered_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'successfully_registered_email' => array(
							'title'        => __( 'Registration Success', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to the user after successful user registration.', 'user-registration' ),
									'id'       => 'user_registration_enable_successfully_registered_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_successfully_registered_email_subject',
									'type'     => 'text',
									'default'  => __( 'Registration Successful – Welcome to {{blog_info}}!', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_successfully_registered_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_successfully_registered_email(),
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
		 * @return string $message Message content for successfully registered email.
		 */
		public function ur_get_successfully_registered_email() {

			/**
			 * Filter to modify the message content for successfully registered email.
			 *
			 * @param string Message content for successfully registered email to be overridden.
			 */
			$message = apply_filters(
				'user_registration_get_successfully_registered_email',
				sprintf(
					__(
						'Hi {{username}}, <br/>
						Congratulations! You have successfully completed your registration on <a href="{{home_url}}">{{blog_info}}</a>. <br/>

						{{membership_plan_details}}

						Please visit \'<b>My Account</b>\' page to edit your account details and create your user profile. <br/>

						Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Successfully_Registered_Email();
