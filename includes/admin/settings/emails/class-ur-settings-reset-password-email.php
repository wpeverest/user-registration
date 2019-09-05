<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Reset_Password_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Reset_Password_Email', false ) ) :

	/**
	 * UR_Settings_Reset_Password_Email Class.
	 */
	class UR_Settings_Reset_Password_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'reset_password_email';
			$this->title       = __( 'Reset Password Email', 'user-registration' );
			$this->description = __( 'Email sent to the user when a user requests for reset password', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			?><h2><?php echo esc_html__( 'Reset Password Email', 'user-registration' ); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

			<?php
			$settings = apply_filters(
				'user_registration_reset_password_email',
				array(
					array(
						'type' => 'title',
						'desc' => '',
						'id'   => 'reset_password_email',
					),
					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent to the user when a user requests for reset password.', 'user-registration' ),
						'id'       => 'user_registration_enable_reset_password_email',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Email Subject', 'user-registration' ),
						'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_reset_password_email_subject',
						'type'     => 'text',
						'default'  => __( 'Password Reset Email: {{blog_info}}', 'user-registration' ),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Email Content', 'user-registration' ),
						'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_reset_password_email',
						'type'     => 'tinymce',
						'default'  => $this->ur_get_reset_password_email(),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'reset_password_email',
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 */
		public function ur_get_reset_password_email() {

			$message = apply_filters(
				'user_registration_reset_password_email_message',
				sprintf(
					__(
						'Someone has requested a password reset for the following account: <br/>

SiteName: {{blog_info}} <br/>
Username: {{username}} <br/>

If this was a mistake, just ignore this email and nothing will happen. <br/>

To reset your password, visit the following address: <br/>
{{home_url}}/{{ur_login}}?action=rp&key={{key}}&login={{username}} <br/>

Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Reset_Password_Email();
