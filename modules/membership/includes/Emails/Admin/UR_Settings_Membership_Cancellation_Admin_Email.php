<?php

namespace WPEverest\URMembership\Emails\Admin;

/**
 * Membership_Cancellation_Email.php
 *
 * @class    Membership_Cancellation_Email.php
 * @date     4/24/2025 : 2:19 PM
 */
class UR_Settings_Membership_Cancellation_Admin_Email {
	/**
	 * @var string
	 */
	public $id = '';

	/**
	 * Email Title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Email description.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * Email receiver.
	 *
	 * @var string
	 */
	public $receiver = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id          = 'membership_cancellation_admin_email';
		$this->title       = __( 'Membership Cancellation Notification', 'user-registration' );
		$this->description = __( 'Notifies membership cancellation to the admin.', 'user-registration' );
		$this->receiver    = 'Admin';
	}

	/**
	 * Get settings
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings() {

		$settings = apply_filters(
			'user_registration_membership_cancellation_admin_email',
			array(
				'title'    => __( 'Membership Cancellation Notification Email', 'user-registration' ),
				'sections' => array(
					'completion_email' => array(
						'title'        => __( 'Membership Cancellation Notification Email', 'user-registration' ),
						'type'         => 'card',
						'desc'         => '',
						'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-admin' ) ),
						'preview_link' => ur_email_preview_link(
							__( 'Preview', 'user-registration' ),
							$this->id
						),
						'settings'     => array(
							array(
								'title'    => __( 'Enable this email', 'user-registration' ),
								'desc'     => __( 'Enable this email to notify the admin after user cancel their membership.', 'user-registration' ),
								'id'       => 'user_registration_enable_membership_cancellation_admin_email',
								'default'  => 'yes',
								'type'     => 'toggle',
								'autoload' => false,
							),
							array(
								'title'    => __( 'Email Subject', 'user-registration' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration' ),
								'id'       => 'user_registration_membership_cancellation_admin_email_subject',
								'type'     => 'text',
								'default'  => __( 'Membership Cancelled: {{username}}', 'user-registration' ),
								'css'      => '',
								'desc_tip' => true,
							),
							array(
								'title'                  => __( 'Email Content', 'user-registration' ),
								'desc'                   => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'                     => 'user_registration_membership_cancellation_admin_email',
								'type'                   => 'tinymce',
								'default'                => $this->user_registration_get_membership_cancellation_admin_email(),
								'css'                    => '',
								'desc_tip'               => true,
								'show-ur-registration-form-button' => false,
								'show-smart-tags-button' => true,
								'show-reset-content-button' => true,
							),
						),
					),
				),
			)
		);

		return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
	}

	/**
	 * Notification sent to admin when member cancel their membership.
	 */
	public function user_registration_get_membership_cancellation_admin_email() {
		$body_content = __(
			'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Hi Admin,
			</p>
			<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				A member has cancelled their membership.
			</p>
			<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				<strong>Member Details:</strong>
				<ul>
				<li style="margin-bottom: 10px;">
					<strong>Name</strong>: {{username}}
				</li>
				<li style="margin-bottom: 10px;">
					<strong>Email</strong>: {{email}}
				</li>
				<li style="margin-bottom: 10px;">
					<strong>Membership Plan</strong>: {{membership_plan_name}}
				</li>
				<li style="margin-bottom: 10px;">
					<strong>Cancellation Date</strong>: {{membership_cancellation_date}}
				</li>
				<li style="margin-bottom: 10px;">
					<strong>Access Expires</strong>: {{membership_end_date}}
				</li>
				</ul>
			</p>
			<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
			Thanks
			</p>
			',
			'user-registration'
		);

		$body_content = ur_wrap_email_body_content( $body_content );

		// Wrap with the pro email template if UR Pro is active.
		if ( UR_PRO_ACTIVE && function_exists( 'ur_get_email_template_wrapper' ) ) {
			$body_content = ur_get_email_template_wrapper( $body_content, false );
		}

		/**
		 * Filter to modify the admin email message content.
		 *
		 * @param string $general_msg Message to be overridden for admin email.
		 */
		$message = apply_filters( 'user_registration_membership_cancellation_admin_email_message', $body_content );

		return $message;
	}
}
