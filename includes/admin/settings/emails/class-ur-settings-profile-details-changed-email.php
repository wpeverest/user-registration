<?php
/**
 * Configure Email
 *
 * @package UR_Settings_Profile_Details_Changed_Email Class
 * @since 1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Profile_Details_Changed_Email', false ) ) :

	/**
	 * UR_Settings_Profile_Details_Changed_Email Class.
	 */
	class UR_Settings_Profile_Details_Changed_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'profile_details_changed_email';
			$this->title       = __( 'Profile Details Changed Email', 'user-registration' );
			$this->description = __( 'Email sent to the admin when a user changed profile information', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			?><h2><?php echo esc_html__( 'Profile Details Changed Email', 'user-registration' ); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

			<?php
			$settings = apply_filters(
				'user_registration_profile_details_changed_email',
				array(
					array(
						'type' => 'title',
						'desc' => '',
						'id'   => 'profile_details_changed_email',
					),
					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent to the admin when a user changed profile information.', 'user-registration' ),
						'id'       => 'user_registration_enable_profile_details_changed_email',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Email Receipents', 'user-registration' ),
						'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
						'id'       => 'user_registration_edit_profile_email_receipents',
						'default'  => get_option( 'admin_email' ),
						'type'     => 'text',
						'css'      => 'min-width: 350px;',
						'autoload' => false,
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Email Subject', 'user-registration' ),
						'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_profile_details_changed_email_subject',
						'type'     => 'text',
						'default'  => __( 'Profile Details Changed Email: {{blog_info}}', 'user-registration' ),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Email Content', 'user-registration' ),
						'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_profile_details_changed_email',
						'type'     => 'tinymce',
						'default'  => $this->ur_get_profile_details_changed_email(),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'profile_details_changed_email',
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Message to be send to admin while profile detail changed.
		 *
		 * @return string message
		 */
		public function ur_get_profile_details_changed_email() {

			$message = apply_filters(
				'user_registration_profile_details_changed_email_message',
				sprintf(
					__(
						'<pre>User has changed profile information for the following account:

SiteName: {{blog_info}}
Username: {{username}}

{{all_fields}}

Thank You! </pre>',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Profile_Details_Changed_Email();
