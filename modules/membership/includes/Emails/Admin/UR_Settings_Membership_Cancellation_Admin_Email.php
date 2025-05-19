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
		$this->receiver    = __( 'Admin', 'user-registration-profile-completeness' );
	}

	/**
	 * Get settings
	 *
	 * @return array
	 * @since 1.0.0
	 *
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
								'default'  => __( 'Membership Cancelled!', 'user-registration' ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'Customize the content of the membership cancellation email to admin.', 'user-registration' ),
								'id'       => 'user_registration_membership_cancellation_admin_email_message',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_membership_cancellation_admin_email(),
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
	 *
	 */
	public function user_registration_get_membership_cancellation_admin_email() {
		$message = apply_filters(
			'user_registration_membership_cancellation_admin_email_message',
			sprintf(
				__( '
					Hi Admin, <br>
					The user {{username}} has cancelled their membership on {{blog_info}}. <br>

					Thank you!',
					'user-registration' )
			)
		);

		return $message;
	}
}
