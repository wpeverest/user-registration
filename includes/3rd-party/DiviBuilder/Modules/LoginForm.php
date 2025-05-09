<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;

defined( 'ABSPATH' ) || exit;

/**
 * Login Form Module class.
 *
 * @since xx.xx.xx
 */
class LoginForm extends BuilderAbstract {
	/**
	 * Login Form Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-login-form';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Login Form';

	/**
	 * Settings
	 *
	 * @since xx.xx.xx
	 * @return void
	 */
	public function settings_init() {

		$this->settings_modal_toggles = array(
			'general' => array(
				'toggles' => array(
					'main_content' => esc_html__( 'Login Form', 'user-registration' ),
				),
			),
		);
	}
	/**
	 * Displays the module setting fields.
	 *
	 * @since xx.xx.xx
	 * @return array $fields Array of settings fields.
	 */
	public function get_fields() {

		$fields = array(
			'redirect_url'        => array(
				'label'            => esc_html__( 'Redirect URL', 'user-registration' ),
				'type'             => 'text',
				'option_category'  => 'basic_option',
				'description'      => esc_html__( 'This option lets you redirect the page URL after login.', 'user-registration' ),
				'toggle_slug'      => 'main_content',
				'computed_affects' => array(
					'__render_login_form',
				),
			),
			'logout_redirect'     => array(
				'label'            => esc_html__( 'Logout Redirect URL', 'user-registration' ),
				'type'             => 'text',
				'option_category'  => 'basic_option',
				'description'      => esc_html__( 'This option lets you redirect the page URL after logout.', 'user-registration' ),
				'toggle_slug'      => 'main_content',
				'computed_affects' => array(
					'__render_login_form',
				),
			),
			'user_state'          => array(
				'label'            => esc_html__( 'User State', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => array(
					'logged_in'  => __( 'Logged In', 'user-registration' ),
					'logged_out' => __( 'Logged Out', 'user-registration' ),
				),
				'default'          => 'logged_out',
				'computed_affects' => array(
					'__render_login_form',
				),
			),
			'__render_login_form' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\LoginForm::render_module',
				'computed_depends_on' => array(
					'redirect_url',
					'logout_redirect',
					'user_state',
				),
				'computed_minimum'    => array(
					'redirect_url',
					'logout_redirect',
					'user_state',
				),
			),

		);
		return $fields;
	}

	/**
	 * Render content.
	 *
	 * @param array $props The attributes values.
	 * @return void
	 */
	public static function render_module( $props = array() ) {
		$redirect_url    = isset( $props['redirect_url'] ) ? esc_url_raw( $props['redirect_url'] ) : '';
		$logout_redirect = isset( $props['logout_redirect'] ) ? esc_url_raw( $props['logout_redirect'] ) : '';
		$user_state      = isset( $props['user_state'] ) ? sanitize_text_field( $props['user_state'] ) : '';

		$paramter = array();

		// Render the form via shortcode in the frontend.
		if ( ! empty( $redirect_url ) ) {
			$parameters['redirect_url'] = $redirect_url;
		}

		if ( ! empty( $logout_redirect ) ) {
			$parameters['logout_redirect'] = $logout_redirect;
		}

		if ( ! empty( $user_state ) ) {
			$parameters['userState'] = $user_state;
		}

		return \UR_Shortcodes::login(
			$parameters
		);
	}
}
