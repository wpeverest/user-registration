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

if ( ! class_exists( 'UR_Settings_Email_Configure', false ) ) :

/**
 * UR_Settings_Email_Configure Class.
 */
class UR_Settings_Email_Configure extends UR_Settings_Page {

	
	public function __construct() {
		$this->id             = 'email_configure';
		$this->title          = __( 'Configure Emails', 'user-registration' );
		add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 30 );
		add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
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
						'id'       => 'user_registration_enable_this_email',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),

					array(
						'title'    => __( 'Email Content', 'user-registration' ),
						'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
						'id'       => 'user_registration_email_configuration',
		 				'type'     => 'tinymce',
		 				'default'  => ur_get_admin_email(),
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

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}

}
endif;

return new UR_Settings_Email_Configure();
