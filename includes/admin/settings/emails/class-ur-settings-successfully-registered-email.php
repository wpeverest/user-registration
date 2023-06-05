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
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'successfully_registered_email';
			$this->title       = __( 'Successfully Registered Email', 'user-registration' );
			$this->description = __( 'Email sent to the user after successful registration', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_successfully_registered_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'successfully_registered_email' => array(
							'title'     => __( 'Successfully Registered Email', 'user-registration' ),
							'type'      => 'card',
							'desc'      => '',
							'back_link' => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
							'settings'  => array(
								array(
									'type'     => 'link',
									'css'      => 'min-width:70px;',
									'buttons'  => array(
										array(
											'title' => __( 'Preview', 'user-registration' ),
											'href'  => add_query_arg(
												array(
													'ur_email_preview' => $this->id,
												),
												home_url()
											),
											'class'  => 'user_registration_email_preview',
											'target' => '_blank',
										),
									),
								),
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
									'default'  => __( 'Congratulations! Registration Complete on {{blog_info}}', 'user-registration' ),
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

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 */
		public function ur_get_successfully_registered_email() {

			$message = apply_filters(
				'user_registration_get_successfully_registered_email',
				sprintf(
					__(
						'Hi {{username}}, <br/>

You have successfully completed user registration on <a href="{{home_url}}">{{blog_info}}</a>. <br/>

Please visit \'<b>My Account</b>\' page to edit your account details and create your user profile on <a href="{{home_url}}">{{blog_info}}</a>. <br/>

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
