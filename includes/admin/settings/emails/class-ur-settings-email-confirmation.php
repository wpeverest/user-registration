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
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'email_confirmation';
			$this->title       = __( 'Email Confirmation', 'user-registration' );
			$this->description = __( 'Email sent to the user with a verification link when email confirmation to register option is choosen', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_email_confirmation',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'email_confirmation' => array(
							'title'     => __( 'Confirmation Email', 'user-registration' ),
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
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_email_confirmation_subject',
									'type'     => 'text',
									'default'  => __( 'Please confirm your registration on {{blog_info}}', 'user-registration' ),
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

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 */
		public function ur_get_email_confirmation() {

			$message = apply_filters(
				'user_registration_get_email_confirmation',
				sprintf(
					__(
						'Hi {{username}}, <br/>

You have registered on <a href="{{home_url}}">{{blog_info}}</a>. <br/>

Please click on this verification link <a href="{{home_url}}/{{ur_login}}?ur_token={{email_token}}">Click here</a> to confirm registration. <br/>

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
