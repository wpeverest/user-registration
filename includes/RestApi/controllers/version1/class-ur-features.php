<?php
/**
 * Features controller class.
 *
 * @since 3.1.6
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_FeaturesClass
 */
class UR_Features {
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
	protected $rest_base = 'features';

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
				'callback'            => array( __CLASS__, 'ur_get_features' ),
				'permission_callback' => array( __CLASS__, 'check_admin_feature_enable_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/enable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_enable_feature' ),
				'permission_callback' => array( __CLASS__, 'check_admin_feature_enable_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/disable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_disable_feature' ),
				'permission_callback' => array( __CLASS__, 'check_admin_feature_enable_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-enable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_enable_feature' ),
				'permission_callback' => array( __CLASS__, 'check_admin_feature_enable_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-disable',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_bulk_disable_feature' ),
				'permission_callback' => array( __CLASS__, 'check_admin_feature_enable_permissions' ),
			)
		);
	}

	/**
	 * Get Features Lists.
	 *
	 * @since 3.1.6
	 *
	 * @return array Features lists.
	 */
	public static function ur_get_features() {
		$raw_section = wp_safe_remote_get( UR()->plugin_url() . '/assets/extensions-json/all-features.json', array( 'user-agent' => 'UserRegistration Features Page' ) );

		if ( is_wp_error( $raw_section ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Cannot access Features. Please try again some time later.', 'user-registration' ),
				),
				400
			);
		}

		$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

		if ( empty( $section_data->features ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Cannot access Features. Please try again some time later.', 'user-registration' ),
				),
				400
			);
		}

		$features_lists   = $section_data->features;
		$enabled_features = get_option( 'user_registration_enabled_features', array() );

		foreach ( $features_lists as $key => $feature ) {
			if ( in_array( $feature->slug, $enabled_features, true ) ) {
				$feature->status = 'enabled';
			} else {
				$feature->status = 'disabled';
			}
			$features_lists[ $key ] = $feature;
		}

		return new \WP_REST_Response(
			array(
				'success'        => true,
				'features_lists' => $features_lists,
			),
			200
		);
	}

	/**
	 * Enable a feature.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_enable_feature( $request ) {
		if ( ! isset( $request['slug'] ) || empty( trim( $request['slug'] ) ) ) { //phpcs:ignore

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Feature slug is a required field', 'user-registration' ),
				),
				400
			);
		}

		$slug = is_array( $request['slug'] ) ? current( $request['slug'] ) : $request['slug'];

		// Logic to enable Feature.
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		array_push( $enabled_features, $slug );
		update_option( 'user_registration_enabled_features', $enabled_features );

		if ( ! in_array( $slug, $enabled_features, true ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Feature couldn\'t be enabled. Please try again later.', 'user-registration' ),
				),
				400
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'Feature enabled successfully', 'user-registration' ),
				),
				200
			);
		}
	}

	/**
	 * Disable a feature.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_disable_feature( $request ) {
		if ( ! isset( $request['slug'] ) || empty( trim( $request['slug'] ) ) ) { //phpcs:ignore

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Feature slug is a required field', 'user-registration' ),
				),
				400
			);
		}

		$slug = is_array( $request['slug'] ) ? current( $request['slug'] ) : $request['slug'];

		// Logic to enable Feature.
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		$enabled_features = array_values( array_diff( $enabled_features, array( $slug ) ) );
		update_option( 'user_registration_enabled_features', $enabled_features );

		if ( ! in_array( $slug, $enabled_features, true ) ) {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'Feature disabled successfully.', 'user-registration' ),
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'Feature couldn\'t be disabled. Please try again some time later.', 'user-registration' ),
				),
				200
			);
		}
	}

	/**
	 * Bulk enable features.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_bulk_enable_feature( $request ) {
		if ( ! isset( $request['slugs'] ) || empty( $request['slugs']  ) ) { //phpcs:ignore

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Feature slug is a required field', 'user-registration' ),
				),
				400
			);
		}

		// Logic to enable Feature.
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		$enabled_features = array_merge( $enabled_features, $request['slugs'] );
		update_option( 'user_registration_enabled_features', $enabled_features );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => esc_html__( 'All selected have been features enabled successfully', 'user-registration' ),
			),
			200
		);
	}

	/**
	 * Bulk disable features.
	 *
	 * @since 3.1.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function ur_bulk_disable_feature( $request ) {
		if ( ! isset( $request['slugs'] ) || empty( $request['slugs'] ) ) { //phpcs:ignore

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Feature slug is a required field', 'user-registration' ),
				),
				400
			);
		}

		// Logic to enable Feature.
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		$enabled_features = array_values( array_diff( $enabled_features, $request['slugs'] ) );
		update_option( 'user_registration_enabled_features', $enabled_features );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => esc_html__( 'All selected features have been disabled successfully.', 'user-registration' ),
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
	public static function check_admin_feature_enable_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
