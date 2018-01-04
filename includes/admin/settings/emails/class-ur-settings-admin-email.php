<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Email_Configure
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Admin_Email', false ) ) :

/**
 * UR_Settings_Email_Configure Class.
 */
class UR_Settings_Admin_Email{

	
	public function __construct() {
		$this->id             = 'email_configure';
		$this->title          = __( 'Configure Emails', 'user-registration' );
	}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

		?><h2><?php echo esc_html__('Email Configuration','user-registration'); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

		<?php
			$settings = apply_filters(
				'user_registration_email_configuration', array(

					array(
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'email_configuration',
					),

					array(
						'title'    => __( 'Enable this email', 'user-registration' ),
						'desc'     => __( 'Enable this email sent after successful user registration.', 'user-registration' ),
						'id'       => 'user_registration_enable_admin_email',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
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
						'id'   => 'email_configuration',
					),

				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

	public function ur_get_admin_email() {
	
		$message = apply_filters( 'user_registration_admin_email_message', __( sprintf(

				'Hi Admin,
					<br/><br/>
					A new user {{username}} - {{user_email}} has successfully registered to your site <a href="{{blog_info}}">{{blog_info}}</a>.
						<br/>
	               <br/>
						Please review the user role and details at \'<b>Users</b>\' menu in your WP dashboard.
	               <br/>
	               <br/>
						Thank You!'), 'user-registration' ) );

		return $message;
	}
}
endif;

return new UR_Settings_Admin_Email();
