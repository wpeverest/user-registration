<?php
namespace WPEverest\URM\DiviBuilder\Modules;

use WPEverest\URM\DiviBuilder\BuilderAbstract;

defined( 'ABSPATH' ) || exit;

/**
 * Content Restriction Module class.
 *
 * @since xx.xx.xx
 */
class ContentRestriction extends BuilderAbstract {
	/**
	 * Content Restriction Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = 'urm-content-restriction';

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = 'URM Content Restriction';

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
					'main_content' => esc_html__( 'Content Restriction', 'user-registration' ),
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

		$all_roles = wp_roles()->roles;
		$role_list = array(
			'all_logged_in_users' => __( 'All logged users', 'user-registration' ),
			'guest'               => __( 'Guest User', 'user-registration' ),
		);
		foreach ( $all_roles as $key => $role ) {
			$role_list[ $key ] = $role['name'];
		}

		$fields = array(
			'user_role'                    => array(
				'label'            => esc_html__( 'User Role', 'user-registration' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'options'          => $role_list,
				'default'          => 'subscriber',
				'computed_affects' => array(
					'__render_content_restriction',
				),
			),
			'restrict_content'             => array(
				'label'            => esc_html__( 'Content', 'user-registration' ),
				'type'             => 'tiny_mce',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'main_content',
				'default'          => '',
				'computed_affects' => array(
					'__render_content_restriction',
				),
			),
			'__render_content_restriction' => array(
				'type'                => 'computed',
				'computed_callback'   => 'WPEverest\URM\DiviBuilder\Modules\ContentRestriction::render_module',
				'computed_depends_on' => array(
					'user_role',
					'restrict_content',
				),
				'computed_minimum'    => array(
					'user_role',
					'restrict_content',
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

		if ( ! ur_check_module_activation( 'content-restriction' ) ) {
			return sprintf( '<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>', esc_html__( 'Please active content restriction.', 'user-registration' ) );
		}
		// Getting current post id because global $post not working in the divi.
		$post_id = isset( $_POST['current_page']['id'] ) ? absint( $_POST['current_page']['id'] ) : 0;

		$output = sprintf(
			'[urcr_restrict access_role="%s" post_id="%s"]%s[/urcr_restrict]',
			$props['user_role'],
			$post_id,
			isset( $props['restrict_content'] ) ? $props['restrict_content'] : '',
		);

		$output = do_shortcode( $output );

		return $output;
	}
}
