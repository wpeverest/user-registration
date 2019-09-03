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
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'admin_email';
			$this->title       = __( 'Admin Email', 'user-registration' );
			$this->description = __( 'Email sent to the admin when a new user registers', 'user-registration' );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			?><h2><?php echo esc_html__( 'Admin Email', 'user-registration' ); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

			<?php
			$settings = apply_filters(
				'user_registration_admin_email',
				array(
					array(
						'type' => 'title',
						'desc' => '',
						'id'   => 'admin_email',
					),
					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent to admin after successful user registration.', 'user-registration' ),
						'id'       => 'user_registration_enable_admin_email',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Email Receipents', 'user-registration' ),
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
						'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
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
					array(
						'type' => 'sectionend',
						'id'   => 'admin_email',
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email format.
		 */
		public function ur_get_admin_email() {

			$message = apply_filters(
				'user_registration_admin_email_message',
				sprintf(
					__(
						'Hi Admin, <br/>

A new user {{username}} - {{email}} has successfully registered to your site <a href="{{home_url}}">{{blog_info}}</a>. <br/>

Please review the user role and details at \'<b>Users</b>\' menu in your WP dashboard. <br/>

Thank You!',
						'user-registration'
					)
				)
			);

			return $message;
		}
	}
endif;

return new UR_Settings_Admin_Email();
