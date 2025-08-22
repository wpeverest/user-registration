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
								'default'  => __( 'Membership Cancellation Confirmed – {{membership_plan_name}}', 'user-registration' ),
								'css'      => '',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'       => 'user_registration_membership_cancellation_user_email',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_membership_cancellation_user_email(),
								'css'      => '',
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
	public function user_registration_get_membership_cancellation_user_email() {
		$message = apply_filters(
			'user_registration_membership_cancellation_user_email_message',
			sprintf(
				__(
					'
					Hi {{username}}, <br>
					We\'re sorry to see you go. Your request to cancel the {{membership_plan_name}} membership has been successfully processed. <br>
					If you change your mind in the future, we\'ll be here to welcome you back.<br>
					Thank you for being a part of {{blog_info}}.<br><br>

					Goodbye and take care!',
					'user-registration'
				)
			)
		);

		return $message;
	}
}
