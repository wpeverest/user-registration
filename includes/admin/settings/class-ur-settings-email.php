<?php
/**
 * UserRegistration Email Settings
 *
 * @class    UR_Settings_Email
 * @version  1.0.0
 * @package  UserRegistration/Admin
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
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'email';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'email';
			$this->label = __( 'Emails', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_filter( 'user_registration_admin_field_to_admin_email_notification', array(
				$this,
				'email_notification_setting'
			), 10, 2 );
			add_filter( 'user_registration_admin_field_to_user_email_notification', array(
				$this,
				'email_notification_setting'
			), 10, 2 );
			$this->initialize_email_classes();
		}

		/**
		 * initialize_email_classes
		 *
		 * @return void
		 */
		public function initialize_email_classes() {
			$email_classes = array(
				'UR_Settings_Admin_Email',
				'UR_Settings_Approval_Link_Email',
				'UR_Settings_Awaiting_Admin_Approval_Email',
				'UR_Settings_Email_Confirmation',
				'UR_Settings_Successfully_Registered_Email',
				'UR_Settings_Registration_Denied_Email',
				'UR_Settings_Registration_Pending_Email',
				'UR_Settings_Reset_Password_Email',
				'UR_Settings_Profile_Details_Changed_Email',
				'UR_Settings_Profile_Details_Updated_Email',
				'UR_Settings_Confirm_Email_Address_Change_Email',
			);

			if ( ur_check_module_activation( 'membership' ) || ur_check_module_activation( 'payments' ) || is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) || is_plugin_active( 'user-registration-authorize-net/user-registration-authorize-net.php' ) ) {
				$email_classes = array_merge( $email_classes, array(
					'UR_Settings_Payment_Success_Email',
					'UR_Settings_Payment_Success_Admin_Email',
				) );

			}

			foreach ( $email_classes as $class ) {
				$this->emails[ $class ] = include 'emails/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
			}

			/**
			 * Filter to modify the email classes accordingly.
			 *
			 * @param class Email classes to be included.
			 */
			$this->emails = apply_filters( 'user_registration_email_classes', $this->emails );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_email_settings',
				array(
					'title'    => '',
					'sections' => array(
						'general_options' => array(
							'title'    => __( 'General Options', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Disable emails', 'user-registration' ),
									'desc'     => __( 'Disable all emails sent after registration.', 'user-registration' ),
									'id'       => 'user_registration_email_setting_disable_email',
									'default'  => 'no',
									'type'     => 'toggle',
									'autoload' => false,
								),
							),
						),
						'sender_option'   => array(
							'title'    => __( 'Email Sender Options', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
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
						'send_test_email' => array(
							'title'    => __( 'Send a Test Email', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
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
									'title'    => __( 'Send Email', 'user-registration' ),
									'desc'     => __( 'Click to send test email.', 'user-registration' ),
									'id'       => 'user_registration_email_test',
									'type'     => 'link',
									'css'      => 'min-width:90px;',
									'buttons'  => array(
										array(
											'title' => __( 'Send Email', 'user-registration' ),
											'href'  => '#',
											'class' => 'button user_registration_send_email_test',
										),
									),
									'desc_tip' => true,
								),
							),
						)
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Email Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_email_settings_' . $this->id, $settings );
		}

		public function get_to_admin_email_list_section() {
			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_to_admin_email_list_section_settings',
				array(
					'title'    => '',
					'sections' => array(
						'email_notification_settings' => array(
							'title'    => __( 'To Admin', 'user-registration' ),
							'type'     => 'card',
//							'desc'     => __( 'Email notifications sent from user registration to the admin are listed below. Click on an email to configure it.', 'user-registration' ),
							'settings' => array(
								array(
									'type' => 'to_admin_email_notification',
									'id'   => 'user_registration_to_admin_email_notification_settings',
								),
							),
						)
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Email Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_to_admin_email_list_section_settings_' . $this->id, $settings );
		}

		public function get_to_user_email_list_section() {
			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_to_user_email_list_section_settings',
				array(
					'title'    => '',
					'sections' => array(
						'email_notification_settings' => array(
							'title'    => __( 'To User', 'user-registration' ),
							'type'     => 'card',
//							'desc'     => __( 'Email notifications sent from user registration to the admin are listed below. Click on an email to configure it.', 'user-registration' ),
							'settings' => array(
								array(
									'type' => 'to_user_email_notification',
									'id'   => 'user_registration_to_user_email_notification_settings',
								),
							),
						)
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Email Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_to_user_email_list_section_settings_' . $this->id, $settings );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''         => __( 'General Options', 'user-registration' ),
				'to-admin' => __( 'To Admin', 'user-registration' ),
				'to-user'  => __( 'To User', 'user-registration' ),
			);

			/**
			 * Filter to get the setings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Retrieve Email Data.
		 *
		 * @return array Emails.
		 */
		public function get_emails() {
			return $this->emails;
		}

		/**
		 * Email Notification Settings.
		 *
		 * @param string $settings Settings.
		 * @param mixed $value Value.
		 */
		public function email_notification_setting( $settings, $value ) {
			$type_array = explode( '_email_notification', $value['type'] );
			$type       = ! empty( $type_array ) ? ( str_replace( 'to_', '', $type_array[0] ) ) : '';

			$settings .= '<tr valign="top">';
			$settings .= '<td class="ur_emails_wrapper" colspan="2">';
			$settings .= '<table class="ur_emails widefat" cellspacing="0">';
			$settings .= '<thead>';
			$settings .= '<tr>';

			/**
			 * Filter to modify the user registration email setting columns.
			 *
			 * @param array Settings to be included on column.
			 */
			$columns = apply_filters(
				'user_registration_email_setting_columns',
				array(
					'name'    => __( 'Email', 'user-registration' ),
					'status'  => __( 'Status', 'user-registration' ),
					'preview' => __( 'Preview', 'user-registration' ),
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
				if ( strtolower( $email->receiver ) !== $type ) {
					continue;
				}
				$status = ! ur_string_to_bool( get_option( 'user_registration_email_setting_disable_email', false ) ) ? ur_string_to_bool( get_option( 'user_registration_enable_' . $email->id, true ) ) : false;

				$settings .= '<tr><td class="ur-email-settings-table">';
				$settings .= '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_' . $email->id . '' ) ) .
							 '">' . esc_html( $email->title ) . '</a>';
				$settings .= ur_help_tip( $email->description );
				$settings .= '</td>';
				$settings .= '<td class="ur-email-settings-table">';
				$label    = ( ( 'email_confirmation' === $email->id ) || ( 'passwordless_login_email' === $email->id ) ) ? esc_html__( 'Always Active', 'user-registration' ) : '<div class="ur-toggle-section"><span class="user-registration-toggle-form user-registration-email-status-toggle" ><input type="checkbox" name="email_status" id="' . esc_attr( $email->id ) . '"' . ( $status ? "checked='checked'" : '' ) . '"/><span class="slider round"></span></span></div>';
				$settings .= '<label class="ur-email-status" style="' . ( $status ? 'color:green;font-weight:500;' : 'color:red;font-weight:500;' ) . '">';
				$settings .= $label;
				$settings .= '</label>';
				$settings .= '</td>';
				$settings .= '<td class="ur-email-settings-table">';
				$settings .= '<a class="button tips user-registration-email-preview " rel="noreferrer noopener" target="__blank" data-tip="' . esc_attr__( 'Preview', 'user-registration' ) . '" href="' . esc_url(
						add_query_arg(
							array(
								'ur_email_preview' => $email->id,
							),
							home_url()
						)
					) . '"><span class="dashicons dashicons-visibility"></span></a>';
				$settings .= '</td>';
				$settings .= '<td class="ur-email-settings-table">';
				$settings .= '<a class="button tips" data-tip="' . esc_attr__( 'Configure', 'user-registration' ) . '" href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_' . $email->id . '' ) ) . '"><span class="dashicons dashicons-admin-generic"></span> </a>';
				$settings .= '</td>';
				$settings .= '</tr>';
			}

			$settings .= '</tbody>';
			$settings .= '</table>';
			$settings .= '</td>';
			$settings .= '</tr>';

			return $settings;
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			global $current_section;

			$sections = $this->get_sections();

			// Hide this navbar when editing/configuring email templates
			if ( ! empty( $current_section ) && ! in_array( $current_section, array_keys( $sections ) ) ) {
				return;
			}

			if ( empty( $sections ) ) {
				return;
			}

			echo '<div class="ur-scroll-ui__scroll-nav"><ul class="subsubsub  ur-scroll-ui__items">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . ' ur-scroll-ui__item">' . esc_html( $label ) . '</a></li>';
			}

			echo '</ul></div>';
		}

		/**
		 * Save Email Settings.
		 */
		public function save() {
			global $current_section;
			$emails = $this->get_emails();

			foreach ( $emails as $email ) {
				if ( 'ur_settings_' . $email->id . '' === $current_section ) {
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
				if ( 'ur_settings_' . $email->id === $current_section ) {
					$settings = new $email();
					$settings = $settings->get_settings();
				}
			}

			switch ( $current_section ) {
				case 'to-admin':
					$settings = $this->get_to_admin_email_list_section();
					break;
				case 'to-user':
					$settings = $this->get_to_user_email_list_section();
					break;
				default:
					$settings = ! empty( $settings ) ? $settings : $this->get_settings();
					break;
			}


			UR_Admin_Settings::output_fields( $settings );

			if ( ! empty( $current_section ) ) {
				?>
				<div id="smart-tags">
					<a href="https://docs.wpuserregistration.com/docs/smart-tags/" rel="noreferrer noopener"
					   target="_blank"><?php echo esc_html__( 'Smart Tags Used', 'user-registration' ); ?></a>
				</div>
				<?php
			}
		}
	}

endif;

return new UR_Settings_Email();
