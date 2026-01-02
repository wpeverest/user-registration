<?php

namespace WPEverest\URMembership\Emails\User;

/**
 * Membership_Renewal_Reminder_Email.php
 *
 * @class    Membership_Renewal_Reminder_Email.php
 * @date     4/29/2025 : 2:19 PM
 */
class UR_Settings_Membership_Renewal_Reminder_User_Email {
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
		$this->id          = 'membership_renewal_reminder_user_email';
		$this->title       = __( 'Membership Renewal Reminder', 'user-registration' );
		$this->description = __( 'An email sent to notify users about an upcoming subscription renewal', 'user-registration' );
		$this->receiver    = 'User';
	}

	/**
	 * Get settings
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings() {

		$settings = apply_filters(
			'user_registration_membership_renewal_reminder_user_email',
			array(

				'title'    => __( 'Membership Renewal Reminder', 'user-registration' ),
				'sections' => array(
					'schedule_setting' => array(
						'title'    => __( 'Membership Renewal Reminder Email', 'user-registration' ),
						'type'     => 'card',
						'desc'     => 'Schedule the time when the email should be dispatched before the next billing date.',
						'settings' => array(
							array(
								'title'   => __( 'Select Period', 'user-registration' ),
								'desc'    => __( '', 'user-registration' ),
								'id'      => 'user_registration_membership_renewal_reminder_period',
								'type'    => 'select',
								'default' => 'weeks',
								'options' => array(
									'days'   => __( 'Day(s)', 'user-registration' ),
									'weeks'  => __( 'Week(s)', 'user-registration' ),
									'months' => __( 'Month(s)', 'user-registration' ),
								),

							),
							array(
								'title'    => __( 'Set Value', 'user-registration' ),
								'desc'     => __( '', 'user-registration' ),
								'id'       => 'user_registration_membership_renewal_reminder_days_before',
								'type'     => 'number',
								'min'      => '1',
								'default'  => 7,
								'css'      => '',
								'desc_tip' => true,
							),
						),
					),
					'renewal_email'    => array(
						'title'        => __( 'Schedule Settings', 'user-registration' ),
						'type'         => 'card',
						'desc'         => '',
						'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-user' ) ),
						'preview_link' => ur_email_preview_link(
							__( 'Preview', 'user-registration' ),
							$this->id
						),
						'settings'     => array(
							array(
								'title'    => __( 'Enable this email', 'user-registration' ),
								'desc'     => __( 'Enable this email to notify the user about an upcoming subscription renewal.', 'user-registration' ),
								'id'       => 'user_registration_membership_enable_renewal_reminder_user_email',
								'default'  => 'yes',
								'type'     => 'toggle',
								'autoload' => false,
							),
							array(
								'title'    => __( 'Email Subject', 'user-registration' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration' ),
								'id'       => 'user_registration_membership_renewal_reminder_user_email_subject',
								'type'     => 'text',
								'default'  => __( 'Your Membership Renews Soon', 'user-registration' ),
								'css'      => '',
								'desc_tip' => true,
							),
							array(
								'title'                  => __( 'Email Content', 'user-registration' ),
								'desc'                   => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'                     => 'user_registration_membership_renewal_reminder_user_email_message',
								'type'                   => 'tinymce',
								'default'                => $this->user_registration_get_membership_renewal_reminder_user_email(),
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
	public function user_registration_get_membership_renewal_reminder_user_email() {
		$body_content = __(
			'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">Hi {{username}},</p>
		<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">Your {{membership_plan_name}} membership will renew automatically on {{renewal_date}}.</p>
		<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;"><strong>Renewal Details:</strong>
		<ul>
		<li style="margin-bottom: 10px;">
		<strong>Amount</strong>: {{renewal_amount}}
		</li>
		<li style="margin-bottom: 10px;">
		<strong>Payment Method</strong>: {{payment_method_last}}
		</li>
		</ul>
		</p>
		<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
		<strong>Note:</strong> Automatic renewal does not apply to bank transfer payments. In such cases, manual renewal is required.
		</p>
		<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">To update your payment method or manage your membership:<br>
		{{manage_membership_link}}
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

		$message = apply_filters( 'user_registration_membership_renewal_reminder_user_email_message', $body_content );

		return $message;
	}
}
