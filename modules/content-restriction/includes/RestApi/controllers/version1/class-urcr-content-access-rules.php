<?php
/**
 * Content Access Rules REST API controller class.
 *
 * @since 4.0
 *
 * @package  UserRegistrationContentRestriction/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Content_Access_Rules Class
 */
class URCR_Content_Access_Rules {
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
	protected $rest_base = 'content-access-rules';

	/**
	 * Register routes.
	 *
	 * @return void
	 * @since 4.0
	 *
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_rules' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_rule' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_rule' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/toggle-status',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'toggle_rule_status' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_rule' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/duplicate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'duplicate_rule' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_rule' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
			)
		);
	}

	/**
	 * Check if a given request has access.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function check_permissions( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Get all content access rules.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function get_rules( $request ) {
		$query_args = array(
			'numberposts' => - 1,
			'post_status' => 'publish',
			'post_type'   => 'urcr_access_rule',
			'orderby'     => 'date',
			'order'       => 'DESC',
		);

		/**
		 * @param array $query_args
		 * @param WP_REST_Request $request
		 */
		$query_args = apply_filters( 'urm_content_access_rules_query_args', $query_args, $request );

		$access_rules = get_posts( $query_args );

		$rules = array();

		foreach ( $access_rules as $rule_post ) {
			$rule_content = json_decode( $rule_post->post_content, true );

			$logic_map = isset( $rule_content['logic_map'] ) ? $rule_content['logic_map'] : array();

			// Check if rule is migrated
			$is_migrated = get_post_meta( $rule_post->ID, 'urcr_is_migrated', true );

			// Get rule type (membership or custom)
			$rule_type = get_post_meta( $rule_post->ID, 'urcr_rule_type', true );
			if ( empty( $rule_type ) ) {
				// Default to 'custom' for backwards compatibility
				$rule_type = 'custom';
			}

			// Get membership ID if this is a membership rule
			$membership_id = '';
			if ( 'membership' === $rule_type ) {
				$membership_id = get_post_meta( $rule_post->ID, 'urcr_membership_id', true );
			}

			$rule_data = array(
				'id'              => $rule_post->ID,
				'title'           => $rule_post->post_title,
				'content'         => $rule_content,
				'enabled'         => urcr_is_access_rule_enabled( $rule_content ),
				'access_control'  => isset( $rule_content['actions'][0]['access_control'] ) ? $rule_content['actions'][0]['access_control'] : 'access',
				'action_type'     => isset( $rule_content['actions'][0]['type'] ) ? $rule_content['actions'][0]['type'] : '',
				'redirect_url'    => isset( $rule_content['actions'][0]['redirect_url'] ) ? $rule_content['actions'][0]['redirect_url'] : '',
				'local_page'      => isset( $rule_content['actions'][0]['local_page'] ) ? $rule_content['actions'][0]['local_page'] : '',
				'logic_map'       => $logic_map,
				'target_contents' => isset( $rule_content['target_contents'] ) ? $rule_content['target_contents'] : array(),
				'is_migrated'     => ! empty( $is_migrated ),
				'rule_type'       => $rule_type,
				'membership_id'   => $membership_id,
				'created_at'      => $rule_post->post_date,
			);

			/**
			 * @param array $rule_data
			 * @param WP_Post $rule_post
			 * @param WP_REST_Request $request
			 */
			$rule_data = apply_filters( 'urm_content_access_rule_data', $rule_data, $rule_post, $request );

			$rules[] = $rule_data;
		}

		/**
		 * @param array $rules
		 * @param WP_REST_Request $request
		 */
		$rules = apply_filters( 'urm_content_access_rules_list', $rules, $request );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'rules'   => $rules,
			),
			200
		);
	}

	/**
	 * Create a new content access rule.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function create_rule( $request ) {
		// Get title from request, default to 'Untitled Rule'
		$title = isset( $request['title'] ) ? sanitize_text_field( $request['title'] ) : __( 'Untitled Rule', 'user-registration' );

		// Get access_rule_data from request, or use default empty structure
		$access_rule_data = isset( $request['access_rule_data'] ) ? $request['access_rule_data'] : array();

		// If no access_rule_data provided, use default structure
		if ( empty( $access_rule_data ) ) {
			$access_rule_data = array(
				'enabled'         => true,
				'logic_map'       => array(
					'conditions' => array(),
				),
				'target_contents' => array(),
				'actions'         => array(
					array(
						'access_control' => 'access',
						'type'           => '',
					),
				),
			);
		}

		/**
		 * @param array $access_rule_data
		 * @param WP_REST_Request $request
		 * @param string $context
		 */
		$access_rule_data = apply_filters( 'urm_content_access_rule_data_before_process', $access_rule_data, $request, 'create' );

		// Prepare the post data similar to prepare_access_rule_as_wp_post
		$access_rule_post = apply_filters(
			'urcr_prepared_access_rule_as_wp_post',
			array(
				'ID'             => '',
				'post_title'     => $title,
				'post_content'   => wp_json_encode( $access_rule_data ),
				'post_type'      => 'urcr_access_rule',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'create-content-access-rule'
		);

		// Fire pre-create action
		do_action( 'urcr_pre_create_content_access_rule', $access_rule_post );

		// Insert the post
		$rule_id = wp_insert_post( $access_rule_post );

		if ( $rule_id ) {
			// Set rule type to 'custom' for newly created rules
			update_post_meta( $rule_id, 'urcr_rule_type', 'custom' );

			// Fire post-create action
			do_action( 'urcr_post_create_content_access_rule', $access_rule_post, $rule_id );

			// Get the created rule
			$rule_post    = get_post( $rule_id );
			$rule_content = json_decode( $rule_post->post_content, true );

			$logic_map = isset( $rule_content['logic_map'] ) ? $rule_content['logic_map'] : array();

			$response_data = array(
				'success' => true,
				'rule'    => array(
					'id'              => $rule_post->ID,
					'title'           => $rule_post->post_title,
					'content'         => $rule_content,
					'enabled'         => urcr_is_access_rule_enabled( $rule_content ),
					'access_control'  => isset( $rule_content['actions'][0]['access_control'] ) ? $rule_content['actions'][0]['access_control'] : 'access',
					'action_type'     => isset( $rule_content['actions'][0]['type'] ) ? $rule_content['actions'][0]['type'] : '',
					'redirect_url'    => isset( $rule_content['actions'][0]['redirect_url'] ) ? $rule_content['actions'][0]['redirect_url'] : '',
					'local_page'      => isset( $rule_content['actions'][0]['local_page'] ) ? $rule_content['actions'][0]['local_page'] : '',
					'logic_map'       => $logic_map,
					'target_contents' => isset( $rule_content['target_contents'] ) ? $rule_content['target_contents'] : array(),
				),
				'message' => esc_html__( 'Successfully created an Access Rule.', 'user-registration' ),
			);

			/**
			 * @param array $response_data
			 * @param WP_Post $rule_post
			 * @param WP_REST_Request $request
			 */
			$response_data = apply_filters( 'urm_content_access_rule_create_response', $response_data, $rule_post, $request );

			return new \WP_REST_Response( $response_data, 200 );
		} else {
			// Fire failure action
			do_action( 'urcr_create_content_access_rule_failure', $access_rule_post, $rule_id );

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Sorry! There was an unexpected error while creating the Content Access Rule.', 'user-registration' ),
				),
				500
			);
		}
	}

	/**
	 * Get a single content access rule.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function get_rule( $request ) {
		$rule_id   = absint( $request['id'] );
		$rule_post = get_post( $rule_id );

		if ( ! $rule_post || 'urcr_access_rule' !== $rule_post->post_type ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid rule ID.', 'user-registration' ),
				),
				404
			);
		}

		$rule_content = json_decode( $rule_post->post_content, true );

		$logic_map = isset( $rule_content['logic_map'] ) ? $rule_content['logic_map'] : array();

		// Check if rule is migrated
		$is_migrated = get_post_meta( $rule_post->ID, 'urcr_is_migrated', true );

		$rule_data = array(
			'id'              => $rule_post->ID,
			'title'           => $rule_post->post_title,
			'content'         => $rule_content,
			'enabled'         => urcr_is_access_rule_enabled( $rule_content ),
			'access_control'  => isset( $rule_content['actions'][0]['access_control'] ) ? $rule_content['actions'][0]['access_control'] : 'access',
			'action_type'     => isset( $rule_content['actions'][0]['type'] ) ? $rule_content['actions'][0]['type'] : '',
			'redirect_url'    => isset( $rule_content['actions'][0]['redirect_url'] ) ? $rule_content['actions'][0]['redirect_url'] : '',
			'local_page'      => isset( $rule_content['actions'][0]['local_page'] ) ? $rule_content['actions'][0]['local_page'] : '',
			'logic_map'       => $logic_map,
			'target_contents' => isset( $rule_content['target_contents'] ) ? $rule_content['target_contents'] : array(),
			'is_migrated'     => ! empty( $is_migrated ),
		);

		/**
		 * @param array $rule_data
		 * @param WP_Post $rule_post
		 * @param WP_REST_Request $request
		 */
		$rule_data = apply_filters( 'urm_content_access_rule_get_response', $rule_data, $rule_post, $request );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'rule'    => $rule_data,
			),
			200
		);
	}

	/**
	 * Toggle rule status (enabled/disabled).
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function toggle_rule_status( $request ) {
		$rule_id = absint( $request['id'] );
		$enabled = isset( $request['enabled'] ) ? filter_var( $request['enabled'], FILTER_VALIDATE_BOOLEAN ) : false;

		$content_rule = get_post( $rule_id );

		if ( ! $content_rule || 'urcr_access_rule' !== $content_rule->post_type ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid rule ID.', 'user-registration' ),
				),
				404
			);
		}

		$content_rule_content = json_decode( $content_rule->post_content, true );

		if ( ! is_array( $content_rule_content ) ) {
			$content_rule_content = array();
		}

		$content_rule_content['enabled'] = $enabled;
		$enabled_text                    = $enabled ? 'enabled' : 'disabled';

		$content_rule->post_content = wp_json_encode( $content_rule_content );

		$saved_post = wp_insert_post( $content_rule );

		if ( $saved_post ) {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'rule_id' => $saved_post,
					'enabled' => $enabled,
					'message' => sprintf( esc_html__( 'Successfully %s the Access Rule.', 'user-registration' ), $enabled_text ),
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Sorry! There was an unexpected error while saving the Content Access Rule.', 'user-registration' ),
				),
				500
			);
		}
	}

	/**
	 * Update rule data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function update_rule( $request ) {
		$rule_id = absint( $request['id'] );

		$content_rule = get_post( $rule_id );

		if ( ! $content_rule || 'urcr_access_rule' !== $content_rule->post_type ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid rule ID.', 'user-registration' ),
				),
				404
			);
		}

		// Get title from request, or keep existing title
		$title = isset( $request['title'] ) ? sanitize_text_field( $request['title'] ) : $content_rule->post_title;

		// Get access_rule_data from request
		$access_rule_data = isset( $request['access_rule_data'] ) ? $request['access_rule_data'] : null;

		// If access_rule_data is provided, use it directly (same as old AJAX handler)
		if ( $access_rule_data && is_array( $access_rule_data ) ) {
			/**
			 * @param array $access_rule_data
			 * @param WP_REST_Request $request
			 * @param string $context
			 */
			$access_rule_data = apply_filters( 'urm_content_access_rule_data_before_process', $access_rule_data, $request, 'update' );

			// Use the same logic as prepare_access_rule_as_wp_post
			$access_rule_data_json = wp_json_encode( $access_rule_data );

			$access_rule_post = apply_filters(
				'urcr_prepared_access_rule_as_wp_post',
				array(
					'ID'             => $rule_id,
					'post_title'     => $title,
					'post_content'   => $access_rule_data_json,
					'post_type'      => 'urcr_access_rule',
					'post_status'    => $content_rule->post_status,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				),
				'save-content-access-rule'
			);

			do_action( 'urcr_pre_save_content_access_rule', $access_rule_post );

			$saved_post = wp_insert_post( $access_rule_post );

			if ( $saved_post ) {
				do_action( 'urcr_post_save_content_access_rule', $access_rule_post, $saved_post );

				$response_data = array(
					'success' => true,
					'rule_id' => $saved_post,
					'message' => esc_html__( 'Successfully saved the Access Rule.', 'user-registration' ),
				);

				/**
				 * @param array $response_data
				 * @param int $saved_post
				 * @param WP_REST_Request $request
				 */
				$response_data = apply_filters( 'urm_content_access_rule_update_response', $response_data, $saved_post, $request );

				return new \WP_REST_Response( $response_data, 200 );
			} else {
				do_action( 'urcr_save_content_access_rule_failure', $access_rule_post, $saved_post );

				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => esc_html__( 'Sorry! There was an unexpected error while saving the Content Access Rule.', 'user-registration' ),
					),
					500
				);
			}
		} else {
			// Fallback: Handle legacy format (access_control and redirect_url only)
			$access_control = isset( $request['access_control'] ) ? sanitize_text_field( $request['access_control'] ) : 'access';
			$redirect_url   = isset( $request['redirect_url'] ) ? esc_url_raw( $request['redirect_url'] ) : '';

			$content_rule_content = json_decode( $content_rule->post_content, true );

			if ( ! is_array( $content_rule_content ) ) {
				$content_rule_content = array();
			}

			if ( ! isset( $content_rule_content['actions'] ) || ! is_array( $content_rule_content['actions'] ) ) {
				$content_rule_content['actions'] = array();
			}

			if ( empty( $content_rule_content['actions'] ) ) {
				$content_rule_content['actions'][] = array();
			}

			$content_rule_content['actions'][0]['access_control'] = $access_control;
			if ( ! empty( $redirect_url ) ) {
				$content_rule_content['actions'][0]['type']         = 'redirect';
				$content_rule_content['actions'][0]['redirect_url'] = $redirect_url;
			}

			$content_rule->post_title   = $title;
			$content_rule->post_content = wp_json_encode( $content_rule_content );

			$saved_post = wp_insert_post( $content_rule );

			if ( $saved_post ) {
				return new \WP_REST_Response(
					array(
						'success' => true,
						'rule_id' => $saved_post,
						'message' => esc_html__( 'Successfully saved the Access Rule.', 'user-registration' ),
					),
					200
				);
			} else {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => esc_html__( 'Sorry! There was an unexpected error while saving the Content Access Rule.', 'user-registration' ),
					),
					500
				);
			}
		}
	}

	/**
	 * Duplicate a rule.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function duplicate_rule( $request ) {
		$rule_id   = absint( $request['id'] );
		$rule_post = get_post( $rule_id );

		if ( ! $rule_post || 'urcr_access_rule' !== $rule_post->post_type ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid rule ID.', 'user-registration' ),
				),
				404
			);
		}

		$new_post = array(
			'post_title'   => $rule_post->post_title . ' (Copy)',
			'post_content' => $rule_post->post_content,
			'post_status'  => 'publish',
			'post_type'    => 'urcr_access_rule',
		);

		$new_rule_id = wp_insert_post( $new_post );

		if ( $new_rule_id ) {
			// Set rule type to 'custom' for duplicated rules (duplicated rules are always custom)
			update_post_meta( $new_rule_id, 'urcr_rule_type', 'custom' );

			return new \WP_REST_Response(
				array(
					'success' => true,
					'rule_id' => $new_rule_id,
					'message' => esc_html__( 'Rule duplicated successfully.', 'user-registration' ),
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Sorry! There was an unexpected error while duplicating the rule.', 'user-registration' ),
				),
				500
			);
		}
	}

	/**
	 * Delete/trash a rule.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function delete_rule( $request ) {
		$rule_id = absint( $request['id'] );
		$force   = isset( $request['force'] ) ? filter_var( $request['force'], FILTER_VALIDATE_BOOLEAN ) : false;

		$rule_post = get_post( $rule_id );

		if ( ! $rule_post || 'urcr_access_rule' !== $rule_post->post_type ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Invalid rule ID.', 'user-registration' ),
				),
				404
			);
		}

		// Prevent deletion of membership rules
		$rule_type = get_post_meta( $rule_id, 'urcr_rule_type', true );
		if ( 'membership' === $rule_type && !UR_DEV) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Membership rules cannot be deleted.', 'user-registration' ),
				),
				403
			);
		}

		// Clear rule meta before deletion
		delete_post_meta( $rule_id, 'urcr_rule_type' );
		delete_post_meta( $rule_id, 'urcr_membership_id' );
		delete_post_meta( $rule_id, 'urcr_is_migrated' );

		if ( $force ) {
			$result = wp_delete_post( $rule_id, true );
		} else {
			$result = wp_trash_post( $rule_id );
		}

		if ( $result ) {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => esc_html__( 'Rule deleted successfully.', 'user-registration' ),
				),
				200
			);
		} else {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the rule.', 'user-registration' ),
				),
				500
			);
		}
	}
}
