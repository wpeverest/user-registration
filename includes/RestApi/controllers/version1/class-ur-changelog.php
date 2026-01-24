<?php

/**
 * Changelog Controller.
 *
 * @since 3.2.0
 *
 * @package  UserRegistration/Classes
 */

defined('ABSPATH') || exit;

/**
 * UR_Changelog Class.
 */
class UR_Changelog
{

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
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_routes()
	{
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array($this, 'get_item'),
					'permission_callback' => array($this, 'check_admin_permissions'),
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
	public function get_item($request)
	{
		$changelog = $this->read_changelog();
		$changelog = $this->parse_changelog($changelog);

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
	protected function read_changelog()
	{
		$raw_changelog = ur_file_get_contents('CHANGELOG.txt');
		if (! $raw_changelog) {
			return new \WP_Error('changelog_read_error', esc_html__('Failed to read changelog.', 'user-registration'));
		}

		return $raw_changelog;
	}

	/**
	 * Parse Changelog contents.
	 *
	 * @param string $raw_changelog Raw changelog that needs to be parsed properly.
	 */
	protected function parse_changelog($raw_changelog)
	{
		if (is_wp_error($raw_changelog)) {
			return $raw_changelog;
		}

		$entries = preg_split(
			'/(?=^\s*=\s*\d+(?:\.\d+){1,3}\s*-\s*\d{1,2}\/\d{1,2}\/\d{4}\s*=*\s*$)/m',
			$raw_changelog,
			-1,
			PREG_SPLIT_NO_EMPTY
		);

		$parsed_changelog = array();

		foreach ($entries as $entry) {
			$date    = null;
			$version = null;

			if (preg_match(
				'/^\s*=\s*(\d+(?:\.\d+){1,3})\s*-\s*(\d{1,2}\/\d{1,2}\/\d{4})\s*=*\s*$/m',
				$entry,
				$matches
			)) {
				$version = $matches[1] ?? null;
				$date    = $matches[2] ?? null;
			}

			$changes_arr = array();

			if (preg_match_all(
				'/^\*\s*([A-Za-z]+)\s*-\s*(.*(?:\n(?!\s*(?:\*|=)).*)*)/m',
				$entry,
				$matches,
				PREG_SET_ORDER
			)) {
				foreach ($matches as $m) {
					$tag  = trim($m[1]);
					$data = trim(preg_replace("/\r\n|\r/", "\n", $m[2])); // normalize newlines
					$data = preg_replace("/\n\s+/", "\n", $data);        // tidy indentation on wrapped lines
					$data = trim($data);

					if (!isset($changes_arr[$tag])) {
						$changes_arr[$tag] = array();
					}
					$changes_arr[$tag][] = $data;
				}
			}

			if ($version && $date && !empty($changes_arr)) {
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
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public static function check_admin_permissions($request)
	{
		return current_user_can('manage_options');
	}
}
