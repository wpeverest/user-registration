<?php
/**
 *  Frontend.
 *
 * @class    Frontend
 * @package  Frontend
 * @category Frontend
 */

namespace WPEverest\URM\ContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	/**
	 * Constructor – initialize hooks.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register all frontend hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );
	}

	/**
	 * Enqueue styles for the course portal page.
	 *
	 * @return void
	 */
	public function load_scripts() {
		global $post;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'urm-masteriyo-frontend-style',
			URM_MASTERIYO_CSS_ASSETS_URL . '/urm-course-portal.css',
			array(),
			URM_CONTENT_DRIP_VERSION
		);

		wp_register_script(
			'urm-masteriyo-frontend-script',
			URM_MASTERIYO_JS_ASSETS_URL . '/frontend' . $suffix . '.js',
			array( 'jquery' ),
			URM_CONTENT_DRIP_VERSION,
			true
		);
	}

	public static function apply_content_drip() {
		global $wp_query;
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		$posts        = $wp_query->posts;
		$posts_length = empty( $posts ) ? 0 : count( $posts );
		$waiting      = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}
			// Check if the logic map data is in array format.
			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				for ( $i = 0; $i < $posts_length; $i++ ) {
					$post = $posts[ $i ];
					//Filtering the type = whole site.
					$target_contents = array_filter(
						$access_rule['target_contents'],
						function ( $target_content ) {
							return 'whole_site' !== $target_content['type'] || empty( $target_content['drip'] );
						}
					);

					$is_target = urcr_is_target_post( $target_contents, $post );

					if ( $is_target ) {

						foreach ( $target_contents as $content ) {
							if ( empty( $content['drip'] ) ) {
								continue;
							}
							$drip = $content['drip'];
							if ( empty( $drip['activeType'] ) ) {
								continue;
							}
							if ( empty( $drip['value'] ) ) {
								continue;
							}

							$active_type = $drip['activeType'];
							$value       = $drip['value'];
							$user_id     = get_current_user_id();

							$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
							$memberships        = $members_repository->get_member_membership_by_id( $user_id );

							if ( ! empty( $memberships ) ) {

								$current_timestamp = current_time( 'timestamp' );

								$earliest_drip_timestamp = null;

								foreach ( $memberships as $membership ) {

									$start_date = $membership['start_date']; // e.g. "2026-01-16 00:00:00"
									if ( 'fixed_date' === $active_type ) {

										$date = isset( $value['fixed_date']['date'] ) ? $value['fixed_date']['date'] : '';
										$time = isset( $value['fixed_date']['time'] ) ? $value['fixed_date']['time'] : '';

										if ( ! empty( $date ) && ! empty( $time ) ) {
											$drip_timestamp = strtotime( $date . ' ' . $time );
										} else {
											continue; // skip if date/time missing
										}

										// Set earliest fixed-date drip time across memberships
										if ( is_null( $earliest_drip_timestamp ) || $drip_timestamp < $earliest_drip_timestamp ) {
											$earliest_drip_timestamp = $drip_timestamp;
										}
									} elseif ( 'days_after' === $active_type ) {

										$days_after       = isset( $value['days_after']['days'] ) ? $value['days_after']['days'] : 0;
										$enroll_timestamp = strtotime( $start_date );
										$drip_timestamp   = strtotime( "+{$days_after} days", $enroll_timestamp );

										// Pick earliest drip time across memberships
										if ( is_null( $earliest_drip_timestamp ) || $drip_timestamp < $earliest_drip_timestamp ) {
											$earliest_drip_timestamp = $drip_timestamp;
										}
									}
								}

								if ( ! is_null( $earliest_drip_timestamp ) && $current_timestamp < $earliest_drip_timestamp ) {

									// calculate remaining days ONCE here
									$diff           = $earliest_drip_timestamp - $current_timestamp;
									$remaining_days = (int) ceil( $diff / DAY_IN_SECONDS );

									// pass it to drip args
									$drip['remaining_days'] = $remaining_days;

									$waiting = self::show_content_drip_message( $drip, $post );
								}
							}
						}
					}
				}
			}
		}

		return $waiting;
	}

	public static function show_content_drip_message( $drip, &$target_post = null ) {
		global $post;
		if ( ! is_object( $target_post ) ) {
			$target_post = $post;
		}

		ob_start();
		urcr_get_template(
			'base-content-drip-template.php',
			$drip
		);
		$styled_content = ob_get_clean();

		$target_post->post_content = $styled_content;

		add_filter(
			'elementor/frontend/the_content',
			function () use ( $styled_content ) {
				if ( ! urcr_is_elementor_content_restricted() ) {
					urcr_set_elementor_content_restricted();

					return $styled_content;
				}

				return '';
			}
		);

		return true;
	}
}
