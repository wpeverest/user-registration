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
								'css'      => 'min-width: 350px;',
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
								'default'  => __( 'Reminder: Automatic Renewal for Your Membership is Coming Up', 'user-registration' ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'       => 'user_registration_membership_renewal_reminder_user_email_message',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_membership_renewal_reminder_user_email(),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
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
		$message = apply_filters(
			'user_registration_membership_renewal_reminder_user_email_message',
			sprintf(
				__(
					'
					Hi {{username}}, <br><br>
					This is a friendly reminder that your {{membership_plan_name}} membership on {{blog_info}} is due for renewal soon. <br>
					Don\'t worry – your membership will be automatically renewed, and the payment will be processed shortly. <br>
					If you need to update your payment details or have any questions, feel free to reach out to us.<br>
					Thank you for being a valued member!<br><br>
					Best regards,<br>
					{{blog_info}}',
					'user-registration'
				)
			)
		);

		return $message;
	}
}
