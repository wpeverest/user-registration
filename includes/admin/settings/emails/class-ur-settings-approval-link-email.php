<?php

/**
 * Configure Email
 *
 * @package  UR_Settings_Approval_Link_Email
 * @extends  UR_Settings_Email
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('UR_Settings_Approval_Link_Email', false)) :

	/**
	 * UR_Settings_Approval_Link_Email Class.
	 */
	class UR_Settings_Approval_Link_Email
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$this->id          = 'approval_link_email';
			$this->title       = __('Approval Link Email', 'user-registration');
			$this->description = __('Email sent to the admin with user approval link url when a new user registers', 'user-registration');
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */

		public function get_settings()
		{

			$settings = apply_filters(
				'user_registration_approval_link_email',
				array(
					'title'    => __('Emails', 'user-registration'),
					'sections' => array(
						'admin_email' => array(
							'title'        => __('Approval Link Email', 'user-registration'),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link(__('Return to emails', 'user-registration'), admin_url('admin.php?page=user-registration-settings&tab=email')),
							'preview_link' => ur_email_preview_link(
								__('Preview', 'user-registration'),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __('Enable this email', 'user-registration'),
									'desc'     => __('Enable this email to send approval link for user registration.', 'user-registration'),
									'id'       => 'user_registration_enable_approval_link_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __('Email Receipents', 'user-registration'),
									'desc'     => __('Use comma to send emails to multiple receipents.', 'user-registration'),
									'id'       => 'user_registration_approval_link_email_receipents',
									'default'  => get_option('admin_email'),
									'type'     => 'text',
									'css'      => 'min-width: 350px;',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __('Email Subject', 'user-registration'),
									'desc'     => __('The email subject you want to customize.', 'user-registration'),
									'id'       => 'user_registration_approval_link_email_subject',
									'type'     => 'text',
									'default'  => __('Approval Link For New User Registration', 'user-registration'),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __('Email Content', 'user-registration'),
									'desc'     => __('The email content you want to customize.', 'user-registration'),
									'id'       => 'user_registration_approval_link_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_approval_link_email(),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			return apply_filters('user_registration_get_settings_' . $this->id, $settings);
		}

		/**
		 * Email format.
		 *
		 * @param bool $email_approval_enabled Email Approval Enabled.
		 */
		public function ur_get_approval_link_email()
		{
			$approval_msg = sprintf(
				__(
					'Hi Admin, <br/>

					A new user {{username}} - {{email}} has successfully registered to your site <a href="{{home_url}}">{{blog_info}}</a>. <br/>

					Please review the user role and details at \'<b>Users</b>\' menu in your WP dashboard. <br/><br />

					Click on this link to approve this user directly :  {{approval_link}} <br /><br />
					Thank You!',
					'user-registration'
				)
			);

			$approval_msg = apply_filters('user_registration_admin_email_message', $approval_msg);

			return $approval_msg;
		}
	}
endif;

return new UR_Settings_Approval_Link_Email();
