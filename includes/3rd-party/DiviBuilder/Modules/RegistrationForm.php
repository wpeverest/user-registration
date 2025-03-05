<?php
namespace WPEverest\URMembership\DiviBuilder\Modules;

use WPEverest\URMembership\DiviBuilder\BuilderAbstract;

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
	public $title = 'Registration Form';

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
				'label'            => esc_html__( '-- Select Form --', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => $forms,
				'default'          => '5',
				'computed_affects' => array(
					'__render_registration_form',
				),
			),
			'__render_registration_form' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URMembership\DiviBuilder\Modules\RegistrationForm::render_module',
				'computed_depends_on' => array(
					'form_id',
				),
				'computed_minimum'    => array(
					'form_id',
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
		$form_id = isset( $props['form_id'] ) ? $props['form_id'] : '0';

		// // Check if we are in the Divi Visual Builder
		// if (et_fb_enabled() ) {
		// return "<div class='user-registration-divi-preview'>" . esc_html__( 'Registration Form Preview', 'user-registration' ) . '</div>';
		// }

		if ( '0' === $form_id ) {
			return esc_html__( 'Please Select the registration form', 'user-registration' );
		}

		// Render the form via shortcode in the frontend.
		$divi_shortcode = sprintf( "[user_registration_form id='%s']", $form_id );

		$output = do_shortcode( $divi_shortcode );

		return $output;
	}
}
