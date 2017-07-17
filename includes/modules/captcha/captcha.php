<?php

/**
 * Class Captcha
 */
class Captcha {


	public static function init() {

		add_filter( 'user_registration_get_settings_pages', array( __CLASS__, 'add_setting_page' ) );
		add_filter( 'user_registration_get_form_settings', array( __CLASS__, 'add_form_setting' ) );


	}

	/**
	 * @param $settings
	 */
	public static function add_setting_page( $settings ) {

		$settings[] = include( 'includes/class-ur-settings-integration.php' );

		return $settings;
	}

	/**
	 * @param $form_settings
	 */
	public static function add_form_setting( $form_settings ) {


		$form_id                      = $form_settings['form_id'];
		$form_settings['setting_data'][] = array(
			'type'              => 'select',
			'label'             => sprintf( __( 'Enable %1$s %2$s reCaptcha %3$s support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=integration" target="_blank">', '</a>' ),
			'id'                => 'user_registration_integration_setting_recaptcha_site_key',
			'description'       => '',
			'required'          => false,
			'id'                => 'user_registration_form_setting_enable_recaptcha_support',
			'class'             => array( 'ur-enhanced-select' ),
			'input_class'       => array(),
			'options'           => array(
				'yes' => __( 'Yes', 'user-registration' ),
				'no'  => __( 'No', 'user-registration' )
			),
			'custom_attributes' => array(),
			'default'           => ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_recaptcha_support', 'no' ),
		);


		return $form_settings;
	}
}


?>
