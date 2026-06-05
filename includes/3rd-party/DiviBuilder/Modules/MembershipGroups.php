<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;
use WPEverest\URMembership\ShortCodes;

defined( 'ABSPATH' ) || exit;

/**
 * Membership Groups Module class.
 *
 * @since xx.xx.xx
 */
class MembershipGroups extends BuilderAbstract {
	/**
	 * Membership Groups Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-membership-groups';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Membership Groups';

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
					'main_content' => esc_html__( 'Membership Groups', 'user-registration' ),
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

		$groups = get_posts( array( 'post_type' => 'ur_membership_groups' ) );
		$option = array( esc_html__( '-- Select Group ID --', 'user-registration' ) );

		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$option[ $group->ID ] = $group->post_title;
			}
		}

		$fields = array(
			'group_id'                    => array(
				'label'            => esc_html__( 'Membership Groups', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => $option,
				'default'          => '',
				'computed_affects' => array(
					'__render_memebership_groups',
				),
			),
			'button_text'                 => array(
				'label'            => esc_html__( 'Button Text', 'user-registration' ),
				'type'             => 'text',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'default'          => esc_html__( 'Sign Up', 'user-registration' ),
				'computed_affects' => array(
					'__render_memebership_groups',
				),
			),
			'__render_memebership_groups' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\MembershipGroups::render_module',
				'computed_depends_on' => array(
					'group_id',
					'button_text',
				),
				'computed_minimum'    => array(
					'group_id',
					'button_text',
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
		$group_id    = isset( $props['group_id'] ) ? absint( $props['group_id'] ) : 0;
		$button_text = isset( $props['button_text'] ) ? sanitize_text_field( $props['button_text'] ) : '';

		if ( 0 === $group_id || ! $group_id ) {
			return sprintf( '<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>', esc_html__( 'Please Select the membership groups', 'user-registration' ) );
		}

		if ( ! defined( 'UR_VERSION' ) ) {
			return sprintf( '<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>', esc_html__( 'Please Active the membership.', 'user-registration' ) );
		}

		// Render the form via shortcode in the frontend.
		$output = ShortCodes::membership_listing(
			array(
				'id'          => $group_id,
				'button_text' => $button_text,
			),
			'user_registration_groups'
		);

		return $output;
	}
}
