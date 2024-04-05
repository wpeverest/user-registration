<?php
/**
 * Addons controller class.
 *
 * @since 3.1.6
 *
 * @package  UserRegistration/Classes
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
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_activation_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_deactivate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_activation_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_activate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_activation_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_deactivate_addon' ),
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_activation_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/install',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_install_addons' ),
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_installation_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-install',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_install_addons' ),
				'permission_callback' => array( __CLASS__, 'check_admin_plugin_installation_permissions' ),
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
		if ( ! isset( $request['slug'] ) || empty( trim( $request['slug'] ) ) ) { //phpcs:ignore

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
	 * Handler for installing a extension.
	 *
	 * @since 1.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @see Plugin_Upgrader
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public static function ur_install_addons( $request ) {
		if ( ! isset( $request['slug'] ) || empty( $request['slug'] ) ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Please select a addon to install', 'user-registration' ),
				),
				400
			);
		}
		$slug        = sanitize_key( wp_unslash( $request['name'] ) );
		$name        = sanitize_text_field( $request['name'] );
		$plugin_slug = wp_unslash( $request['slug'] ); // phpcs:ignore
		$plugin      = plugin_basename( sanitize_text_field( $plugin_slug ) );
		$status      = array(
			'install' => 'plugin',
			'slug'    => $slug,
		);

		$status = self::ur_install_individual_addon( $slug, $plugin, $name, $status );

		if ( isset( $status['success'] ) && ! $status['success'] ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => isset( $status['errorMessage'] ) ? $status['errorMessage'] : '',
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => isset( $status['message'] ) ? $status['message'] : '',
				),
				200
			);
		}
	}

	/**
	 * Handler for installing bulk extension.
	 *
	 * @since 1.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @see Plugin_Upgrader
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public static function ur_bulk_install_addons( $request ) {
		if ( ! isset( $request['addonData'] ) || empty( $request['addonData'] ) ) {

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Please select a addon to install', 'user-registration' ),
				),
				400
			);
		}

		$failed_addon = array();

		foreach ( $request['addonData'] as $addon ) {
			$slug        = isset( $addon['name'] ) ? sanitize_key( wp_unslash( $addon['name'] ) ) : '';
			$plugin_slug = isset( $addon['slug'] ) ? sanitize_text_field( $addon['slug'] ) : '';
			$name        = isset( $addon['name'] ) ? sanitize_text_field( $addon['name'] ) : '';
			$plugin      = plugin_basename( sanitize_text_field( $plugin_slug ) );
			$status      = array(
				'install' => 'plugin',
				'slug'    => $slug,
			);
			$status      = self::ur_install_individual_addon( $slug, $plugin, $name, $status );
			if ( isset( $status['success'] ) && ! $status['success'] ) {
				array_push( $failed_addon, $name );
				continue;
			}
		}

		if ( count( $failed_addon ) > 0 ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					/* translators: 1: Failed Addon Names */
					'message' => sprintf( __( '%1$s installation failed. Please try again sometime later.', 'user-registration' ), implode( ', ', $failed_addon ) ),
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'All of the selected addons have been installed successfully.', 'user-registration' ),
				),
				400
			);
		}
	}

	/**
	 * Handler for installing a extension.
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug Slug of the addon to install.
	 * @param string $plugin Plugin file of the addon to install.
	 * @param string $name Name of the addon to install.
	 * @param array  $status Staus array to track addon installation status.
	 *
	 * @see Plugin_Upgrader
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public static function ur_install_individual_addon( $slug, $plugin, $name, $status ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
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
					'item_name' => ! empty( $name ) ? sanitize_text_field( wp_unslash( $name ) ) : '',
				)
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['success']      = false;
			$status['errorMessage'] = $api['msg'];
			return $status;
		}

		$status['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

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

		$status['success'] = true;
		$status['message'] = __( 'Addon installed Successfully', 'user-registration' );
		return $status;
	}

	/**
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public static function check_admin_plugin_activation_permissions( $request ) {
		return current_user_can( 'activate_plugin' );
	}

	/**
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public static function check_admin_plugin_installation_permissions( $request ) {
		return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugin' );
	}
}
