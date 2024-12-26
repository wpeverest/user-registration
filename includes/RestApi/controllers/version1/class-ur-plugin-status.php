<?php
/**
 * Getting started controller class.
 *
 * @since xx.xx.xx
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
	 * @since xx.xx.xx
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
	 * @since xx.xx.xx
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
	 * @since xx.xx.xx
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
	 * @since xx.xx.xx
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
	 * @since xx.xx.xx
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function plugin_activate( $request ) {
		$module_data = $request->get_param( 'addonData' );

		if ( is_string( $module_data ) ) {
			$module_data = json_decode( $module_data, true );
		}

		if ( ! is_array( $module_data ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Invalid module data format.', 'user-registration' ),
				),
				400
			);
		}

		$addon_slugs   = array();
		$feature_slugs = array();

		foreach ( $module_data as $addon ) {
			if ( isset( $addon['type'] ) && 'addon' === $addon['type'] ) {
				array_push( $addon_slugs, $addon );
			} else {
				$slug                   = $addon['slug'];
				$feature_slugs[ $slug ] = isset( $addon['name'] ) ? $addon['name'] : $slug;
			}
		}

		$failed_modules = array();

		if ( ! empty( $addon_slugs ) ) {
			$failed_modules = array_merge( $failed_modules, self::bulk_install_addons( $addon_slugs ) );
		}

		if ( ! empty( $feature_slugs ) ) {
			$failed_modules = array_merge( $failed_modules, self::bulk_enable_feature( $feature_slugs ) );
		}

		if ( count( $failed_modules ) > 0 ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => sprintf( __( '%1$s activation failed. Please try again later.', 'user-registration' ), implode( ', ', $failed_modules ) ),
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'All of the selected modules have been activated successfully.', 'user-registration' ),
				),
				200
			);
		}
	}

		/**
		 * Handler for installing bulk extension.
		 *
		 * @since 3.0.3
		 *
		 * @param array $addon_data Datas of addons to activate.
		 *
		 * @see Plugin_Upgrader
		 *
		 * @global WP_Filesystem_Base $wp_filesystem Subclass
		 */
	public static function bulk_install_addons( $addon_data ) {
		$failed_addons = array();

		foreach ( $addon_data as $addon ) {
			$slug   = isset( $addon['slug'] ) ? sanitize_key( wp_unslash( $addon['slug'] ) ) : '';
			$name   = isset( $addon['name'] ) ? sanitize_text_field( $addon['name'] ) : '';
			$plugin = plugin_basename( WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php' );
			if ( is_plugin_active( $plugin ) ) {
				continue;
			}
			$status = array(
				'install' => 'plugin',
				'slug'    => $slug,
			);

			$status = UR_Modules::ur_install_individual_addon( $slug, $plugin, $name, $status );

			if ( isset( $status['success'] ) && ! $status['success'] ) {
				$failed_addons[] = array(
					'name'    => $name,
					'message' => $status['errorMessage'],
				);
			}
		}

		return $failed_addons;
	}

		/**
		 * Install individual addon.
		 *
		 * @since 3.0.3
		 *
		 * @param string $slug   Addon slug.
		 * @param string $plugin Addon plugin path.
		 * @param string $name   Addon name.
		 * @param array  $status Status.
		 *
		 * @return array
		 */
	public static function install_individual_addon( $slug, $plugin, $name, $status ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
			$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$status['plugin']     = $plugin;
			$status['pluginName'] = $plugin_data['Name'];

			if ( is_plugin_inactive( $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					$status['errorCode']    = $result->get_error_code();
					$status['errorMessage'] = $result->get_error_message();
					$status['success']      = false;
					return $status;
				}
				$status['success'] = true;
				$status['message'] = __( 'Addons activated successfully', 'user-registration' );
				return $status;
			}
		}

		$api = json_decode(
			UR_Updater_Key_API::version(
				array(
					'license'   => get_option( 'user-registration_license_key' ),
					'item_name' => ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
				)
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['success']      = false;
			$status['errorMessage'] = $api['msg'];
			return $status;
		}

		$status['pluginName'] = $api->name;
		$skin                 = new WP_Ajax_Upgrader_Skin();
		$upgrader             = new Plugin_Upgrader( $skin );
		$result               = $upgrader->install( $api->download_link );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['success']      = false;
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			return $status;
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['success']      = false;
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			return $status;
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['success']      = false;
			$status['errorMessage'] = $skin->get_error_messages();
			return $status;
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;
			$status['success']      = false;
			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'user-registration' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}
			return $status;
		}

		$api->version   = isset( $api->new_version ) ? $api->new_version : '';
		$install_status = install_plugin_install_status( $api );
		activate_plugin( $plugin );
		$status['success'] = true;
		$status['message'] = __( 'Addon installed Successfully', 'user-registration' );
		return $status;
	}
}
