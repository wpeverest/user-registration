<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Email_Confirmation
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Email_Confirmation', false ) ) :

/**
 * UR_Settings_Email_Confirmation Class.
 */
class UR_Settings_Email_Confirmation{

	public function __construct() {
		$this->id             = 'email_confirmation';
		$this->title          = __( 'Email Confirmation', 'user-registration' );
		$this->description    = __( 'Email sent to the user with a verification link when email confirmation to register option is choosen', 'user-registration' );
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings() {

	?><h2><?php echo esc_html__('Email Confirmation','user-registration'); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

	<?php
		$settings = apply_filters(
			'user_registration_email_confirmation', array(

				array(
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'email_confirmation',
				),
				array(
					'title'    => __( 'Email Subject', 'user-registration' ),
					'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
					'id'       => 'user_registration_email_confirmation_subject',
	 				'type'     => 'text',
	 				'default'  => __('Please confirm your registration on {{blog_info}}', 'user-registration'),
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

				array(
					'type' => 'sectionend',
					'id'   => 'email_confirmation',
				),

			)
		);

		return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
	}

	public function ur_get_email_confirmation() {

		$message = apply_filters( 'user_registration_get_email_confirmation', sprintf( __(
				'Hi {{username}},

 				You have registered on <a href="{{home_url}}">{{blog_info}}</a>.

 				Please click on this verification link {{home_url}}/wp-login.php?ur_token={{email_token}} to confirm registration.

 				Thank You!', 'user-registration' ) ) );
		return $message;
	}
}
endif;

return new UR_Settings_Email_Confirmation();
