<?php
/**
 * UserRegistration Email Settings
 *
 * @class    UR_Settings_Email
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Email' ) ) :

	/**
	 * UR_Settings_Email Class
	 */
	class UR_Settings_Email extends UR_Settings_Page {


		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'email';
			$this->label = __( 'Emails', 'user-registration' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );

			add_action( 'user_registration_admin_field_email_notification', array( $this, 'email_notification_setting' ) );

			include_once( UR_ABSPATH . 'includes/admin/settings/emails/class-ur-settings-admin-email.php' );
			include_once( UR_ABSPATH . 'includes/admin/settings/emails/class-ur-settings-email-confirmation.php' );
			include_once( UR_ABSPATH . 'includes/admin/settings/emails/class-ur-settings-successfully-registered-email.php' );

			parent::__construct();
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings(){
			$settings = apply_filters( 'user_registration_email_settings', array(				
					array(
						'title' => __( 'General Email Settings', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_email_setting',
					),

					ur_get_user_login_option(),
					
					array(
						'type' => 'sectionend',
						'id'   => 'general_email_setting',
					),

					array( 'title' => __( 'Email notifications', 'user-registration' ),  'desc' => __( 'Email notifications sent from user registration are listed below. Click on an email to configure it.', 'user-registration' ), 'type' => 'title', 'id' => 'email_notification_settings' ),

					array( 'type' => 'email_notification' ),

					array( 'type' => 'sectionend', 'id' => 'email_notification_settings' ),

					array( 'type' => 'sectionend', 'id' => 'email_recipient_options' ),

					array(
						'title' => __( 'Email Sender Options', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'sender_option',
					),

					array(
						'title'    => __( '"From" name', 'user-registration' ),
						'desc'     => __( 'How the sender name appears in outgoing user registration emails.', 'user-registration' ),
						'id'       => 'user_registration_email_from_name',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => esc_attr( get_bloginfo( 'name', 'display' ) ),
						'autoload' => false,
						'desc_tip' => true,
					),

					array(
						'title'             => __( '"From" address', 'user-registration' ),
						'desc'              => __( 'How the sender email appears in outgoing user registration emails.', 'online-restaurant-reservation' ),
						'id'                => 'user_registration_email_from_address',
						'type'              => 'email',
						'custom_attributes' => array(
							'multiple' => 'multiple',
						),
						'css'               => 'min-width:300px;',
						'default'           => get_option( 'admin_email' ),
						'autoload'          => false,
						'desc_tip'          => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'sender_option',
					),
				)
			);
				return apply_filters( 'user_registration_get_email_settings_' . $this->id, $settings );
		}

		public function email_notification_setting() {
		?>
			<tr valign="top">
			    <td class="ur_emails_wrapper" colspan="2">
					<table class="ur_emails widefat" cellspacing="0">
						<thead>
							<tr>
								<?php
									$columns = apply_filters( 'user_registration_email_setting_columns', array(
										'name'       => __( 'Email', 'user-registrtion' ),
										'actions'    => __( 'Configure', 'user-registration' ),
									) );
									foreach ( $columns as $key => $column ) {
										echo '<th class="ur-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
									}
								?>
							</tr>
						</thead>
						<tbody>
							<?php echo '<tr><td class="ur-email-settings-table-admin-email">
													<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_admin_email' ) . 
													'">'. __('Admin Email', 'user-registration') .'</a>' . ur_help_tip( __('Customize the email sent to admin when a new user register','user-registration' ) ) . '
										</td>
										
										<td class="ur-email-settings-table-admin-email">
													<a class="button alignright tips" data-tip="'. esc_attr__( 'Configure','user-registration' ) .'" href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_admin_email' ) . '">' . esc_html__( 'Configure', 'user-registration' ) . ' </a>
										</td></tr>
										<tr>
										<td class="ur-email-settings-table-email-confirmation">
													<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_email_confirmation' ) . 
													'">'. __('Email Confirmation', 'user-registration') .'</a>' . ur_help_tip( __('Customize the email sent to user when email confimation login option is active','user-registration' ) ) . '
										</td>
										<td class="ur-email-settings-table-email-confirmation">
													<a class="button alignright tips" data-tip="'. esc_attr__( 'Configure','user-registration' ) .'" href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_email_confirmation' ) . '">' . esc_html__( 'Configure', 'user-registration' ) . ' </a>
										</td></tr>
										<tr>
										<td class="ur-email-settings-table-successfully-registered-email">
													<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_successfully_registered_email' ) . 
													'">'. __('Successfully Registered Email', 'user-registration') .'</a>' . ur_help_tip( __('Customize the email sent to user when the registration is complete','user-registration' ) ) . '
										</td>
										<td class="ur-email-settings-table-email-confirmation">
													<a class="button alignright tips" data-tip="'. esc_attr__( 'Configure','user-registration' ) .'" href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_successfully_registered_email' ) . '">' . esc_html__( 'Configure', 'user-registration' ) . ' </a>
										</td>
										</tr>

										';
							?>
						</tbody>
					</table>
				</td>
			</tr>
		<?php
		}
	
		public function save() {
			global $current_section;

			switch ( $current_section ) {
			 	case 'ur_settings_admin_email':
			 		$settings = new UR_Settings_Admin_Email();
			 		$settings = $settings->get_settings();
			 	break;

			 	case 'ur_settings_email_confirmation':
			 		$settings = new UR_Settings_Email_Confirmation();
			 		$settings = $settings->get_settings();
			 	break;

			 	case 'ur_settings_successfully_registered_email':
			 		$settings = new UR_Settings_Successfully_Registered_Email();
			 		$settings = $settings->get_settings();
			 	break;

			 	default:
			 		$settings = $this->get_settings();
			 }
			 	UR_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			switch ( $current_section ) {
				case 'ur_settings_admin_email':
					$settings = new UR_Settings_Admin_Email;
					$settings = $settings->get_settings();
				break;

				case 'ur_settings_email_confirmation':
			 		$settings = new UR_Settings_Email_Confirmation();
			 		$settings = $settings->get_settings();
			 	break;

			 	case 'ur_settings_successfully_registered_email':
			 		$settings = new UR_Settings_Successfully_Registered_Email();
			 		$settings = $settings->get_settings();
			 	break;

			 	default:
			 		$settings = $this->get_settings();
			 }
			 	UR_Admin_Settings::output_fields( $settings );

		}
	}

endif;

return new UR_Settings_Email();