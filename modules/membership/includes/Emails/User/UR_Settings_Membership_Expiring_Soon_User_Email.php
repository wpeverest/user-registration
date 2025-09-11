<?php

namespace WPEverest\URMembership\Emails\User;

/**
 * Membership_Expiring_Soon_Email.php
 *
 * @class    Membership_Expiring_Soon_Email.php
 * @date     4/29/2025 : 2:19 PM
 */
class UR_Settings_Membership_Expiring_Soon_User_Email {
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
		$this->id          = 'membership_expiring_soon_user_email';
		$this->title       = __( 'Membership Expiring Soon', 'user-registration' );
		$this->description = __( 'An email sent to notify users about an upcoming membership expiration.', 'user-registration' );
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
			'user_registration_membership_expiring_soon_user_email',
			array(

				'title'    => __( 'Membership Will Expire Soon – Renew Now', 'user-registration' ),
				'sections' => array(
					'schedule_setting' => array(
						'title'    => __( 'Membership Will Expire Soon', 'user-registration' ),
						'type'     => 'card',
						'desc'     => 'Schedule the time when the email should be dispatched before the expiry date.',
						'settings' => array(
							array(
								'title'   => __( 'Select Period', 'user-registration' ),
								'desc'    => __( '', 'user-registration' ),
								'id'      => 'user_registration_membership_expiring_soon_period',
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
								'id'       => 'user_registration_membership_expiring_soon_days_before',
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
								'desc'     => __( 'Enable this email to notify the user that their membership will expire soon if they do not renew manually.', 'user-registration' ),
								'id'       => 'user_registration_membership_enable_expiring_soon_user_email',
								'default'  => 'yes',
								'type'     => 'toggle',
								'autoload' => false,
							),
							array(
								'title'    => __( 'Email Subject', 'user-registration' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration' ),
								'id'       => 'user_registration_membership_expiring_soon_user_email_subject',
								'type'     => 'text',
								'default'  => __( 'Membership Will Expire Soon – Renew Now', 'user-registration' ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'Customize the content of the membership expiring soon email to admin.', 'user-registration' ),
								'id'       => 'user_registration_membership_expiring_soon_user_email_message',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_membership_expiring_soon_user_email(),
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
	public function user_registration_get_membership_expiring_soon_user_email() {
		$message = apply_filters(
			'user_registration_membership_expiring_soon_user_email_message',
			sprintf(
				__(
					'
					Hi {{username}}, <br>
					Just a reminder — your membership is set to expire on {{membership_end_date}}. <br>
					To avoid any interruption in access to your member benefits, please make sure to renew before the expiration date. <br>
					Otherwise, you can manually renew in just a few clicks.<br>
					({renewal_link})<br>
					Stay connected and keep enjoying everything your membership offers!<br>
					Best regards, <br>
					{{blog_info}}',
					'user-registration'
				)
			)
		);

		return $message;
	}
}
