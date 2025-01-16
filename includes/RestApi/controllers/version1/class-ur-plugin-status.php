<?php
/**
 * Getting started controller class.
 *
 * @since 4.0
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_Getting_Started Class
 */
class UR_Plugin_Status {
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
	protected $rest_base = 'plugin';

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
			'/' . $this->rest_base . '/upgrade',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'plugin_upgrade' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_plugin_status' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/get_plan',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_license_plan' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'plugin_activate' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * plugin Upgrade
	 *
	 * @since 4.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function plugin_upgrade( $request ) {

		$required_plugins = $request->get_param( 'requiredPlugins' );
		$license_key      = get_option( 'user-registration_license_key' );
		$plugin_status    = array();
		$plugin_to_check  = 'user-registration-pro';

		if ( ! empty( $required_plugins ) ) {
			array_push( $required_plugins, 'user-registration-pro' );
		}

		if ( in_array( $plugin_to_check, $required_plugins ) ) {
			if ( $license_key && is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
				$plugin_status = true;
			} else {
				$plugin_status = false;
			}
		}

		return new WP_REST_Response( array( 'plugin_status' => $plugin_status ), 200 );
	}

	/**
	 * Get Plugin Status.
	 *
	 * @since 4.0
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_plugin_status() {
			$extension_data = self::get_addons_data();

			$addons_lists = $extension_data->products;
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
			$installed_plugin_slugs = array_keys( get_plugins() );

		foreach ( $addons_lists as $addon ) {
			$addon_file = $addon->slug . '/' . $addon->slug . '.php';
			if ( in_array( $addon_file, $installed_plugin_slugs, true ) ) {
				$plugin_statuses[ $addon->slug ] = is_plugin_active( $addon_file ) ? 'active' : 'inactive';
			} else {
				$plugin_statuses[ $addon->slug ] = 'not-installed';
			}
		}

			return new WP_REST_Response(
				array(
					'success'       => true,
					'plugin_status' => $plugin_statuses,
				),
				200
			);
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

	/**
	 * Retrieve addons data.
	 *
	 * @since 4.0
	 *
	 * @return object
	 */
	public static function get_addons_data() {
		$addons_data = ur_get_json_file_contents( 'assets/extensions-json/sections/all_extensions.json' );

		$new_product = (object) array(
			'products' => array(
				(object) array(
					'title'          => 'User Registration PRO',
					'slug'           => 'user-registration-pro',
					'name'           => 'User Registration PRO',
					'image'          => '',
					'excerpt'        => '',
					'link'           => '',
					'released_date'  => '',
					'plan'           => array(
						'personal',
						'plus',
						'professional',
						'themegrill agency',
					),
					'setting_url'    => '',
					'demo_video_url' => '',
				),
			),
		);

		if ( isset( $addons_data->products ) ) {

			$existing_products = $addons_data->products;
			$new_products      = $new_product->products;

			$merged_products = array_merge(
				json_decode( json_encode( $existing_products ), true ),
				json_decode( json_encode( $new_products ), true )
			);

			$addons_data->products = json_decode( json_encode( $merged_products ) );
		}
		return apply_filters( 'user_registration_addons_section_data', $addons_data );
	}


	/**
	 * Get License Plan.
	 *
	 * @since 4.0
	 *
	 * @return WP_REST_Response
	 */
	public static function get_license_plan() {
		$license_data = get_option( 'user-registration_license_active', new stdClass() );
		if ( ! empty( $license_data && isset( $license_data->item_name ) ) ) {
			$license_plan = $license_data->item_name;
		} else {
			$license_plan = '';
		}
		return new WP_REST_Response( array( 'license_plan' => $license_plan ), 200 );
	}

	/**
	 * Bulk Activate Addon.
	 *
	 * @since 4.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function plugin_activate( $request ) {
		$addon = $request->get_param( 'addonData' );

		if ( isset( $addon['type'] ) && 'addon' === $addon['type'] ) {
			$slug        = isset( $addon['name'] ) ? sanitize_key( wp_unslash( $addon['name'] ) ) : '';
			$plugin_slug = wp_unslash( $addon['slug'] ) . '/' . wp_unslash( $addon['slug'] ) . '.php'; // phpcs:ignore
			$name        = isset( $addon['name'] ) ? sanitize_text_field( $addon['name'] ) : '';
			$plugin      = plugin_basename( sanitize_text_field( $plugin_slug ) );
			$status      = array(
				'install' => 'plugin',
				'slug'    => $slug,
			);

			$status = UR_Modules::ur_install_individual_addon( $slug, $plugin, $name, $status );
		} else {
			$slug   = $addon['slug'];
			$status = UR_Modules::ur_enable_feature( $slug );
		}

		if ( isset( $status['success'] ) && ! $status['success'] ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => sprintf(
						__( '%1$s cannot be activated at the moment. %2$s', 'user-registration' ),
						$addon['name'],
						$status['errorMessage']
					),
				),
				400,
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => sprintf( __( '%1$s activated successfully.', 'user-registration' ), $addon['name'] ),
				),
				200
			);
		}
	}
}
