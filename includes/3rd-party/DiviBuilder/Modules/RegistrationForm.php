<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;

defined( 'ABSPATH' ) || exit;

/**
 * Registration Form Module class.
 *
 * @since xx.xx.xx
 */
class RegistrationForm extends BuilderAbstract {
	/**
	 * Registration Form Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-registration-form';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Registration Form';

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
					'main_content' => esc_html__( 'Forms', 'user-registration' ),
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

		$forms        = ur_get_all_user_registration_form();
		$default_form = array( esc_html__( '-- Select Form --', 'user-registration' ) );
		$forms        = $default_form + $forms;

		$fields = array(
			'form_id'                    => array(
				'label'            => esc_html__( 'Registration Form', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => $forms,
				'default'          => '',
				'computed_affects' => array(
					'__render_registration_form',
				),
			),
			'user_state'                 => array(
				'label'            => esc_html__( 'User State', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => array(
					''           => __( '-- Select User State --', 'user-registration' ),
					'logged_in'  => __( 'Logged In', 'user-registration' ),
					'logged_out' => __( 'Logged Out', 'user-registration' ),
				),
				'default'          => '',
				'computed_affects' => array(
					'__render_registration_form',
				),
			),
			'__render_registration_form' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\RegistrationForm::render_module',
				'computed_depends_on' => array(
					'form_id',
					'user_state',
				),
				'computed_minimum'    => array(
					'form_id',
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
		$form_id    = isset( $props['form_id'] ) ? absint( $props['form_id'] ) : 0;
		$user_state = isset( $props['user_state'] ) ? sanitize_text_field( $props['user_state'] ) : '';

		if ( 0 === $form_id || ! $form_id ) {
			return sprintf( '<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>', esc_html__( 'Please Select the registration form', 'user-registration' ) );
		}

		// Render the form via shortcode in the frontend.
		$output = \UR_Shortcodes::form(
			array(
				'id'        => $form_id,
				'userState' => $user_state,
			)
		);

		return $output;
	}
}
