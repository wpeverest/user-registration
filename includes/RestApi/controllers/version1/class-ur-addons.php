<?php
/**
 * Addons controller class.
 *
 * @since 3.1.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_AddonsClass
 */
class UR_Addons {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'user-registration/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'addons';

	/**
	 * Register routes.
	 *
	 * @since 2.1.4
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_addons' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_deactivate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_activate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_deactivate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * Get Addons Lists.
	 *
	 * @since 3.1.6
	 *
	 * @return array Addon lists.
	 */
	public static function ur_get_addons() {
		$admin_addons = new UR_Admin_Addons();
		$addons_lists = $admin_addons->get_section_data( 'all_extensions' );

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugin_slugs = array_keys( get_plugins() );

		foreach ( $addons_lists as $key => $addon ) {
			$addon_file = $addon->slug . '/' . $addon->slug . '.php';
			if ( in_array( $addon_file, $installed_plugin_slugs, true ) ) {
				if ( is_plugin_active( $addon_file ) ) {
					$addon->status = 'active';
				} else {
					$addon->status = 'inactive';
				}
			} else {
				$addon->status = 'not-installed';
			}

			if ( in_array( 'personal', $addon->plan ) ) {
				$addon->required_plan = __( 'Personal', 'user-registration' );
			} elseif ( in_array( 'plus', $addon->plan ) ) {
				$addon->required_plan = __( 'Plus', 'user-registration' );
			} else {
				$addon->required_plan = __( 'Professional', 'user-registration' );
			}
			$addons_lists[ $key ] = $addon;
		}

		return new \WP_REST_Response(
			array(
				'success'      => true,
				'addons_lists' => $addons_lists,
			),
			200
		);
	}

	/**
	 * Deactive a addon.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_deactivate_addon( $request ) {
		if ( ! isset( $request['slug'] ) || empty( trim( $request['slug'] ) ) ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Addon slug is a required field', 'user-registration' ),
				),
				400
			);
		}

		$slug = is_array( $request['slug'] ) ? current( $request['slug'] ) : $request['slug'];
		deactivate_plugins( $slug );
		$active_plugins = get_option( 'active_plugins', array() );

		if ( in_array( $slug, $active_plugins, true ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Addon couldn\'t be deactivated. Please try again later.', 'user-registration' ),
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'Addon deactivated successfully', 'user-registration' ),
				),
				200
			);
		}
	}

	/**
	 * Bulk Activate addons.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_bulk_activate_addon( $request ) {

		if ( ! isset( $request['slugs'] ) || empty( $request['slugs'] ) ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Please select addons to activate', 'user-registration' ),
				),
				400
			);
		}

		activate_plugins( $request['slugs'] );

		$active_plugins = get_option( 'active_plugins', array() );

		if ( count( array_diff( $request['slugs'], $active_plugins ) ) > 0 ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'All of the selected addons couldn\'t be activated. Please try again later.', 'user-registration' ),
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'All of the selected addons have been activated successfully.', 'user-registration' ),
				),
				200
			);
		}
	}

	/**
	 * Bulk Deactivate addons.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_bulk_deactivate_addon( $request ) {

		if ( ! isset( $request['slugs'] ) || empty( $request['slugs'] ) ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Please select addons to deactivate', 'user-registration' ),
				),
				400
			);
		}

		deactivate_plugins( $request['slugs'] );

		$active_plugins = get_option( 'active_plugins', array() );

		if ( count( $request['slugs'] ) === count( array_diff( $request['slugs'], $active_plugins ) ) ) {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'All of the selected addons have been deactivated.', 'user-registration' ),
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Some of the selected addons may not have been deactivated. Please try again later', 'user-registration' ),
				),
				400
			);
		}
	}

	/**
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public static function check_admin_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
