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
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Email' ) ) :

	/**
	 * UR_Settings_Email Class
	 */
	class UR_Settings_Email extends UR_Settings_Page {

		/**
		 * Email notification classes.
		 *
		 * @var array
		 */
		public $emails = array();

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'email';
			$this->label = __( 'Emails', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'user_registration_admin_field_email_notification', array( $this, 'email_notification_setting' ), 10, 2 );

			$this->emails['UR_Settings_Admin_Email']                   = include 'emails/class-ur-settings-admin-email.php';
			$this->emails['UR_Settings_Awaiting_Admin_Approval_Email'] = include 'emails/class-ur-settings-awaiting-admin-approval-email.php';

			$this->emails['UR_Settings_Email_Confirmation'] = include 'emails/class-ur-settings-email-confirmation.php';

			$this->emails['UR_Settings_Registration_Approved_Email'] = include 'emails/class-ur-settings-registration-approved-email.php';

			$this->emails['UR_Settings_Registration_Denied_Email'] = include 'emails/class-ur-settings-registration-denied-email.php';

			$this->emails['UR_Settings_Registration_Pending_Email'] = include 'emails/class-ur-settings-registration-pending-email.php';

			$this->emails['UR_Settings_Successfully_Registered_Email'] = include 'emails/class-ur-settings-successfully-registered-email.php';

			$this->emails['UR_Settings_Reset_Password_Email'] = include 'emails/class-ur-settings-reset-password-email.php';

			$this->emails['UR_Settings_Profile_Details_Changed_Email'] = include 'emails/class-ur-settings-profile-details-changed-email.php';

			$this->emails = apply_filters( 'user_registration_email_classes', $this->emails );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = apply_filters(
				'user_registration_email_settings',
				array(
					'title' => __( 'Emails', 'user-registration' ),
					'sections' => array (
						'email_notification_settings' => array(
							'title' => __( 'Email notifications', 'user-registration' ),
							'type'  => 'card',
							'desc'  => __( 'Email notifications sent from user registration are listed below. Click on an email to configure it.', 'user-registration'),
							'settings' => array(
								ur_get_user_login_option(),

								array( 'type' => 'email_notification' ),
							),
						),
						'sender_option' => array(
							'title' => __( 'Email Sender Options', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
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
									'desc'              => __( 'How the sender email appears in outgoing user registration emails.', 'user-registration' ),
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
							),
						),
					),

					array(
						'title' => __( 'Send a Test Email', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'send_test_email',
					),

					array(
						'title'             => __( 'Send To', 'user-registration' ),
						'desc'              => __( 'Enter email address where test email will be sent.', 'user-registration' ),
						'id'                => 'user_registration_email_send_to',
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
						'title'             => __( 'Send Email', 'user-registration' ),
						'desc'              => __( 'Click to send test email.', 'user-registration' ),
						'id'                => 'user_registration_email_test',
						'type'              => 'link',
						'css'               => 'min-width:300px;',
						'buttons'  => array(
							array (
								'title' => __( 'Send Email', 'user-registration' ),
								'href'  => '#',
								'class' => 'button user_registration_send_email_test',
							),
						),
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'send_test_email',
					),

				)
			);

			return apply_filters( 'user_registration_get_email_settings_' . $this->id, $settings );
		}

		public function get_emails() {
			return $this->emails;
		}

		public function email_notification_setting( $settings, $value ) {
			$settings .= '<tr valign="top">';
			$settings .= '<td class="ur_emails_wrapper" colspan="2">';
			$settings .= '<table class="ur_emails widefat" cellspacing="0">';
			$settings .= '<thead>';
			$settings .= '<tr>';

			$columns = apply_filters(
						'user_registration_email_setting_columns',
						array(
							'name'    => __( 'Email', 'user-registration' ),
							'actions' => __( 'Configure', 'user-registration' ),
						)
					);

			foreach ( $columns as $key => $column ) {
				$settings .= '<th style="padding-left:15px" class="ur-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
			}
			$settings .= '</tr>';
			$settings .= '</thead>';
			$settings .= '<tbody>';

			$emails = $this->get_emails();
			foreach ( $emails as $email ) {
				$settings .= '<tr><td class="ur-email-settings-table">';
				$settings .= '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_' . $email->id . '' ) .
												'">' . __( $email->title, 'user-registration' ) . '</a>';
				$settings .=  ur_help_tip( __( $email->description, 'user-registration' ) );
				$settings .= '</td>';
				$settings .= '<td class="ur-email-settings-table">';
				$settings .= '<a class="button tips" data-tip="' . esc_attr__( 'Configure', 'user-registration' ) . '" href="' . admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_' . $email->id . '' ) . '"><span class="dashicons dashicons-admin-generic"></span> </a>';
				$settings .= '</td>';
				$settings .= '</tr>';
			}

			$settings .= '</tbody>';
			$settings .= '</table>';
			$settings .= '</td>';
			$settings .= '</tr>';

			return $settings;
		}

		public function save() {
			global $current_section;
			$emails = $this->get_emails();

			foreach ( $emails as $email ) {
				if ( $current_section == 'ur_settings_' . $email->id . '' ) {
					$settings = new $email();
					$settings = $settings->get_settings();
				}
			}

			$settings = isset( $settings ) ? $settings : $this->get_settings();

			UR_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;
			$emails = $this->get_emails();

			foreach ( $emails as $email ) {
				if ( $current_section == 'ur_settings_' . $email->id . '' ) {
					$settings = new $email();
					$settings = $settings->get_settings();
				}
			}

			$settings = isset( $settings ) ? $settings : $this->get_settings();

			UR_Admin_Settings::output_fields( $settings );

			if ( ! empty( $current_section ) ) {
				?>
				<div id ="smart-tags">
					<a href="https://docs.wpeverest.com/docs/user-registration/email-settings/smart-tags/"><?php echo esc_html__( 'Smart Tags Used', 'user-registration' ); ?></a>
				</div>
				<?php
			}
		}
	}

endif;

return new UR_Settings_Email();
