<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;

defined( 'ABSPATH' ) || exit;

/**
 * Edit Password Module class.
 *
 * @since xx.xx.xx
 */
class EditPassword extends BuilderAbstract {
	/**
	 * Edit Password Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-edit-password';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Edit Password';

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
					'main_content' => esc_html__( 'Edit Password', 'user-registration' ),
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

			'preview_state'          => array(
				'label'            => esc_html__( 'Preview', 'user-registration' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => array(
					'on'  => __( 'On', 'user-registration' ),
					'off' => __( 'Off', 'user-registration' ),
				),
				'default'          => 'on',
				'computed_affects' => array(
					'__render_edit_password',
				),
			),
			'__render_edit_password' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\EditPassword::render_module',
				'computed_depends_on' => array(
					'preview_state',
				),
				'computed_minimum'    => array(
					'preview_state',
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

		$parameters = array();
		return \UR_Shortcodes::edit_password(
			$parameters
		);
	}
}
