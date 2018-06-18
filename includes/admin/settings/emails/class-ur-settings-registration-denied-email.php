<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Registration_Denied_Email
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Registration_Denied_Email', false ) ) :

/**
 * UR_Settings_Registration_Denied_Email Class.
 */
class UR_Settings_Registration_Denied_Email{

	
	public function __construct() {
		$this->id             = 'registration_denied_email';
		$this->title          = __( 'Registration Denied Email', 'user-registration' );
		$this->description    = __( 'Email sent to the user notifying the registration is denied by the admin', 'user-registration' );
	}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

		?><h2><?php echo esc_html__('Registration Denied Email','user-registration'); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

		<?php
			$settings = apply_filters(
				'user_registration_registration_denied_email', array(
					array(
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'registration_denied_email',
					),
					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent to admin after successfull user registration.', 'user-registration' ),
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
		 				'default'  => __('Sorry! Registration denied on {{blog_info}}', 'user-registration'),
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
					array(
						'type' => 'sectionend',
						'id'   => 'registration_denied_email',
					),

				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

	public function ur_get_registration_denied_email() {
		
		$message = apply_filters( 'user_registration_get_registration_denied_email', sprintf( __( 
				'Hi {{username}},

				You have registered on <a href="{{home_url}}">{{blog_info}}</a>.
 				
 				Unfortunately your registration is denied. Sorry for the inconvenience.
 				
 				Thank You!', 'user-registration' ) ) );
		
		return $message;
	}
}
endif;

return new UR_Settings_Registration_Denied_Email();
