<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Registration_Denied_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Registration_Denied_Email', false ) ) :

	/**
	 * UR_Settings_Registration_Denied_Email Class.
	 */
	class UR_Settings_Registration_Denied_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'registration_denied_email';
			$this->title       = __( 'Registration Denied Email', 'user-registration' );
			$this->description = __( 'Email sent to the user notifying the registration is denied by the admin', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_registration_denied_email',
				array(
					'title' => __( 'Emails', 'user-registration' ),
					'sections' => array (
						'registration_denied_email' => array(
							'title' => __( 'Registration Denied Email', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'back_link' => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
							'settings' => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this email sent to the user notifying the registration is denied by the admin.', 'user-registration' ),
									'id'       => 'user_registration_enable_registration_denied_email',
									'default'  => 'yes',
									'type'     => 'checkbox',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_registration_denied_email_subject',
									'type'     => 'text',
									'default'  => __( 'Sorry! Registration denied on {{blog_info}}', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_registration_denied_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_registration_denied_email(),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
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
		public function ur_get_registration_denied_email() {

			$message = apply_filters(
				'user_registration_get_registration_denied_email',
				sprintf(
					__(
						'Hi {{username}}, <br/>

You have registered on <a href="{{home_url}}">{{blog_info}}</a>. <br/>

Unfortunately your registration is denied. Sorry for the inconvenience. <br/>

Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Registration_Denied_Email();
