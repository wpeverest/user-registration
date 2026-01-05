<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;
use WPEverest\URMembership\ShortCodes;

defined( 'ABSPATH' ) || exit;

/**
 * Membership Thank You Module class.
 *
 * @since xx.xx.xx
 */
class MembershipThankYou extends BuilderAbstract {
	/**
	 * Membership Thank You Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-membership-thank-you';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Membership Thank You';

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
					'main_content' => esc_html__( 'Membership Thank You', 'user-registration' ),
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
			'preview_state'                 => array(
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
					'__render_membership_thank_you',
				),
			),
			'__render_membership_thank_you' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\MembershipThankYou::render_module',
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

		if ( ! defined( 'UR_VERSION' ) ) {
			return sprintf( '<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>', esc_html__( 'Please Active the membership.', 'user-registration' ) );
		}

		return Shortcodes::thank_you(
			$parameters,
			'user_registration_membership_thank_you'
		);
	}
}
