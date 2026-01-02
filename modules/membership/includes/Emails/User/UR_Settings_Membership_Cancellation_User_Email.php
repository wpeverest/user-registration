<?php

namespace WPEverest\URMembership\Emails\User;

/**
 * Membership_Cancellation_Email.php
 *
 * @class    Membership_Cancellation_Email.php
 * @date     4/24/2025 : 2:19 PM
 */
class UR_Settings_Membership_Cancellation_User_Email {
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
		$this->id          = 'membership_cancellation_user_email';
		$this->title       = __( 'Membership Cancellation Confirmation', 'user-registration' );
		$this->description = __( 'Confirms membership cancellation to the user, providing any relevant account status or next steps.', 'user-registration' );
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
			'user_registration_membership_cancellation_user_email',
			array(
				'title'    => __( 'Membership Cancellation Confirmation Email', 'user-registration' ),
				'sections' => array(
					'completion_email' => array(
						'title'        => __( 'Membership Cancellation Confirmation Email', 'user-registration' ),
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
								'desc'     => __( 'Enable this email to notify the admin after user cancel their membership.', 'user-registration' ),
								'id'       => 'user_registration_enable_membership_cancellation_user_email',
								'default'  => 'yes',
								'type'     => 'toggle',
								'autoload' => false,
							),
							array(
								'title'    => __( 'Email Subject', 'user-registration' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration' ),
								'id'       => 'user_registration_membership_cancellation_user_email_subject',
								'type'     => 'text',
								'default'  => __( 'Membership Cancelled', 'user-registration' ),
								'css'      => '',
								'desc_tip' => true,
							),
							array(
								'title'                  => __( 'Email Content', 'user-registration' ),
								'desc'                   => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'                     => 'user_registration_membership_cancellation_user_email',
								'type'                   => 'tinymce',
								'default'                => $this->user_registration_get_membership_cancellation_user_email(),
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
	public function user_registration_get_membership_cancellation_user_email() {
		$body_content = __(
			'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Hi {{username}},</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Your {{membership_plan_name}} membership has been cancelled as requested.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				You\'ll continue to have access to your member benefits until {{membership_end_date}}.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				If you change your mind, you can reactivate your membership anytime from your user dashboard.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				We\'re sorry to see you go, but you\'re always welcome back.
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

		// Allow filtering of the HTML body for this membership cancellation email.
		$message = apply_filters( 'user_registration_membership_cancellation_user_email_message', $body_content );

		return $message;
	}
}
