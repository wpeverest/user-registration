<?php

namespace WPEverest\URMembership\Emails\User;

/**
 * Membership_Ended_User_Email.php
 *
 * @class    Membership_Ended_User_Email.php
 * @date     4/29/2025 : 2:19 PM
 */
class UR_Settings_Membership_Ended_User_Email {
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
		$this->id          = 'membership_ended_user_email';
		$this->title       = __( 'Membership Ended ', 'user-registration' );
		$this->description = __( 'An email sent to notify users about the expiration of their membership.', 'user-registration' );
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
			'user_registration_membership_ended_user_email',
			array(
				'title'    => __( 'Membership Has Ended ', 'user-registration' ),
				'sections' => array(
					'membership_ended_email' => array(
						'title'        => __( 'Membership Has Ended Mail Settings', 'user-registration' ),
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
								'desc'     => __( 'Enable this email to notify the user that their membership has expired.', 'user-registration' ),
								'id'       => 'user_registration_membership_enable_membership_ended_user_email',
								'default'  => 'yes',
								'type'     => 'toggle',
								'autoload' => false,
							),
							array(
								'title'    => __( 'Email Subject', 'user-registration' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration' ),
								'id'       => 'user_registration_membership_ended_user_email_subject',
								'type'     => 'text',
								'default'  => __( 'Your Membership Has Ended – Rejoin to Regain Access ', 'user-registration' ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration' ),
								'desc'     => __( 'Customize the content of the membership has ended email to user.', 'user-registration' ),
								'id'       => 'user_registration_membership_ended_user_email_message',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_membership_ended_user_email(),
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
	public function user_registration_get_membership_ended_user_email() {
		$message = apply_filters(
			'user_registration_membership_ended_user_email_message',
			sprintf(
				__(
					'
					Hi {{username}}, <br>
					Your membership expired on {{membership_end_date}}, and access to your member benefits has ended.<br>
					To continue enjoying everything {{site_name}} offers, you’ll need to re-purchase the membership.<br>
					Otherwise, you can manually renew in just a few clicks.<br>
					It only takes a minute to rejoin<br>
					{{membership_renewal_link}}<br>
					We’d love to have you back! <br>
					Best regards, <br>
					{{blog_info}}',
					'user-registration'
				)
			)
		);

		return $message;
	}
}
