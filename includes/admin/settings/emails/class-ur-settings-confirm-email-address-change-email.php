<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Confirm_Email_Address_Change_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Confirm_Email_Address_Change_Email', false ) ) :

	/**
	 * UR_Settings_Confirm_Email_Address_Change_Email Class.
	 */
	class UR_Settings_Confirm_Email_Address_Change_Email {
		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Confirm_Email_Address_Change_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'confirm_email_address_change_email';
			$this->title       = __( 'Confirm Email Address Change', 'user-registration' );
			$this->description = __( 'Email sent to the user to confirm the email address changed.', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_confirm_email_address_change',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'confirm_email_address_change_email' => array(
							'title'        => __( 'Confirm Email Address Changed Email', 'user-registration' ),
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
									'desc'     => __( 'Email sent to the user to confirm the email address changed.', 'user-registration' ),
									'id'       => 'user_registration_enable_confirm_email_address_change_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_confirm_email_address_change_email_subject',
									'type'     => 'text',
									'default'  => __( 'Confirm email address changed', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_confirm_email_address_change_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_confirm_email_address_change_email(),
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
		public function ur_get_confirm_email_address_change_email() {

			$message = apply_filters(
				'user_registration_get_confirm_email_address_email',
				sprintf(
					wp_kses_post(
						__(
							'Dear {{display_name}},
							<p>You recently requested to change your email address associated with your account to {{updated_new_user_email}} . </p>
							<p>To confirm this change, please click on the following link: {{email_change_confirmation_link}}
							This link will only be active for 24 hours.If you did not request this change, please ignore this email or contact us for assistance.</p>
				<p>Best regards,<br/>
				 {{blog_info}}</p>',
							'user-registration'
						)
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Confirm_Email_Address_Change_Email();
