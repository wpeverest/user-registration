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
		// add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );
	}

	/**
	 * Enqueue styles for the course portal page.
	 *
	 * @return void
	 */
	public function load_scripts() {
		global $post;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	public function apply_content_drip() {
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

			// Validate against empty variables.
			if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				for ( $i = 0; $i < $posts_length; $i++ ) {
					$target_post = $posts[ $i ];
					//Filtering the type = whole site.
					$target_contents = array_filter(
						$access_rule['target_contents'],
						function ( $target_content ) {
							return 'whole_site' !== $target_content['type'] || empty( $target_content['drip'] );
						}
					);
					$is_target       = $this->is_target_post( $target_contents, $target_post );

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

									$waiting = $this->show_content_drip_message( $drip, $post );
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
			'../content-drip/base-content-drip-template.php',
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

	/**
	 * See if the post is in the provided targets list.
	 *
	 * @param array $targets Targets list.
	 * @param object|null $target_post Post to check against.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public static function is_target_post( $targets = array(), $target_post = null ) {

		if ( is_array( $targets ) ) {
			foreach ( $targets as $target ) {
				if ( isset( $target['type'] ) && ! empty( $target['value'] ) ) {

					$result = apply_filters(
						'urmdrip_match_target_type',
						null,
						$target,
						$target_post
					);

					if ( true === $result ) {
						return true;
					}

					if ( false === $result ) {
						continue;
					}

					switch ( $target['type'] ) {
						case 'wp_posts':
							$post_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
							$target_post_ids = (array) $target['value'];

							if ( in_array( $post_id, $target_post_ids, true ) ) {
								return true;
							}
							break;

						case 'wp_pages':
							$page_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
							$target_page_ids = (array) $target['value'];

							if ( in_array( $page_id, $target_page_ids, true ) ) {
								return true;
							}
							break;

						case 'post_types':
							$post_type         = ( 'object' === gettype( $target_post ) && $target_post->post_type ) ? strval( $target_post->post_type ) : '';
							$post_type         = ( is_singular( 'product' ) && is_array( $target_post ) ) ? $target_post[0]->post_type : $post_type;
							$target_post_types = (array) $target['value'];

							if ( in_array( $post_type, $target_post_types, true ) ) {
								return true;
							}
							$products_page_id = intval( get_option( 'woocommerce_shop_page_id' ) );
							if ( is_object( $target_post ) && (int) $products_page_id === $target_post->ID ) {
								return true;
							}

							break;

						case 'taxonomy':
							if ( ! empty( $target['taxonomy'] ) && ! empty( $target['value'] ) ) {
								$products_page_id = intval( get_option( 'woocommerce_shop_page_id' ) );
								$post_taxonomies  = get_post_taxonomies( $target_post );
								if ( is_numeric( $target_post ) && $target_post == $products_page_id ) {
									return true;
								}

								if ( ! in_array( $target['taxonomy'], (array) $post_taxonomies ) ) {
									return - 1;
								}

								/**
								 * Filter to modify the post taxonomy status.
								 *
								 * @since xx.xx.xx
								 */
								$post_status = apply_filters( 'user_registration_membership_post_taxonomy_status', '' );

								if ( ! empty( $post_status ) && isset( $target_post->post_status ) && $target_post->post_status === $post_status ) {
									return - 1;
								}

								$terms = get_the_terms( $target_post, $target['taxonomy'] );

								if ( empty( $terms ) || is_wp_error( $terms ) ) {
									return - 1;
								}

								foreach ( $terms as $term ) {
									if ( in_array( $term->term_id, $target['value'] ) ) {
										return true;
									}
								}
							}
							break;
						default:
							break;
					}
				}
			}
		}

		return false;
	}
}
