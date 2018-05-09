<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Registration_Pending_Email
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Registration_Pending_Email', false ) ) :

/**
 * UR_Settings_Registration_Pending_Email Class.
 */
class UR_Settings_Registration_Pending_Email{

	public function __construct() {
		$this->id             = 'registration_pending_email';
		$this->title          = __( 'Registration Pending Email', 'user-registration' );
		$this->description    = __( 'Email sent to the user notifying the registration is pending', 'user-registration' );
	}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

		?><h2><?php echo esc_html__('Registration Pending Email','user-registration'); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

		<?php
			$settings = apply_filters(
				'user_registration_registration_pending_email', array(

					array(
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'registration_pending_email',
					),

					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent to admin after successfull user registration.', 'user-registration' ),
						'id'       => 'user_registration_enable_registration_pending_email',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),

					array(
						'title'    => __( 'Email Subject', 'user-registration' ),
						'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_registration_pending_email_subject',
		 				'type'     => 'text',
		 				'default'  => __('Sorry! Registration changed to pending on {{blog_info}}', 'user-registration'),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Email Content', 'user-registration' ),
						'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_registration_pending_email',
		 				'type'     => 'tinymce',
		 				'default'  => $this->ur_get_registration_pending_email(),
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'registration_pending_email',
					),

				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

	public function ur_get_registration_pending_email() {
	
		$message = apply_filters( 'user_registration_get_registration_pending_email', __( sprintf(

			'Hi {{username}},
					<br/>
           <br/>
					Your registration on <a href="{{home_url}}">{{blog_info}}</a> has been changed to pending.
					<br/>
					Sorry for the inconvenience.
					<br/>
           <br/>
					You will be notified after it is approved.
					<br/>
					<br/>
					Thank You!'), 'user-registration' ) );

		return $message;
	}
}
endif;

return new UR_Settings_Registration_Pending_Email();
