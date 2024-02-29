<?php
/**
 * Changelog Controller.
 *
 * @since 3.1.6
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_Changelog Class.
 */
class UR_Changelog {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string The namespace of this controller's route.
	 */
	protected $namespace = 'user-registration/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string The base of this controller's route.
	 */
	protected $rest_base = 'changelog';

	/**
	 * Register routes.
	 *
	 * @since 3.1.6
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);
	}

	/**
	 * Get item.
	 *
	 * @param \WP_Rest_Request $request Full detail about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$changelog = $this->read_changelog();
		$changelog = $this->parse_changelog( $changelog );
		return new \WP_REST_Response(
			array(
				'success'   => true,
				'changelog' => $changelog,
			),
			200
		);
	}

	/**
	 * Read changelog.
	 *
	 * @return \WP_Error|string
	 */
	protected function read_changelog() {
		$raw_changelog = ur_file_get_contents( 'CHANGELOG.txt' );
		if ( ! $raw_changelog ) {
			return new \WP_Error( 'changelog_read_error', esc_html__( 'Failed to read changelog.', 'user-registration' ) );
		}

		return $raw_changelog;
	}

	protected function parse_changelog( $raw_changelog ) {
		if ( is_wp_error( $raw_changelog ) ) {
			return $raw_changelog;
		}

		$entries = preg_split( '/(?=\=\s\d+\.\d+\.\d+|\Z)/', $raw_changelog, -1, PREG_SPLIT_NO_EMPTY );
		array_shift( $entries );

		$parsed_changelog = array();

		foreach ( $entries as $entry ) {
			$date    = null;
			$version = null;

			if ( preg_match( '/^= (\d+\.\d+\.\d+) *- (\d+\/\d+\/\d+)/', $entry, $matches ) ) {
				$version = $matches[1] ?? null;
				$date    = $matches[2] ?? null;
			}

			$changes_arr = array();

			if ( preg_match_all( '/^\* (\w+(\s*-\s*.+)?)$/m', $entry, $matches ) ) {
				$changes = $matches[1] ?? null;

				if ( is_array( $changes ) ) {
					foreach ( $changes as $change ) {
						$parts = explode( ' - ', $change );
						$tag   = trim( $parts[0] ?? '' );
						$data  = isset( $parts[1] ) ? trim( $parts[1] ) : '';

						if ( isset( $changes_arr[ $tag ] ) ) {
							$changes_arr[ $tag ][] = $data;
						} else {
							$changes_arr[ $tag ] = array( $data );
						}
					}
				}
			}

			if ( $version && $date && $changes_arr ) {
				$parsed_changelog[] = array(
					'version' => $version,
					'date'    => $date,
					'changes' => $changes_arr,
				);
			}
		}

		return $parsed_changelog;
	}

	/**
	 * Prepare item for response.
	 *
	 * @param array            $item Item.
	 * @param \WP_Rest_Request $request Full detail about the request.
	 * @return \WP_Rest_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = $this->add_additional_fields_to_object( $item, $request );
		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return apply_filters( 'user_registration_prepare_changelog', $response, $item, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param array $item
	 * @return array
	 */
	protected function prepare_links( $item ) {
		return array(
			'self' => array(
				'href' => rest_url(
					sprintf(
						'%s/%s/%s',
						$this->namespace,
						$this->rest_base,
						$item['version']
					)
				),
			),
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
}
