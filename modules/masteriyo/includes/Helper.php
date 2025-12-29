<?php
/**
 * URM_MASTERIYO_Helper setup
 *
 * @package URM_MASTERIYO_Helper
 * @since  1.0.0
 */

namespace WPEverest\URM\Masteriyo;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Helper' ) ) :

	/**
	 * Helper masteriyo integration Clas s
	 *
	 */
	class Helper {

		/**
		 * Get item listing for the integration.
		 *
		 * @since xx.xx.xx
		 *
		 * @param array  $items The integration items.
		 * @param string $search The search term.
		 * @param string $course_price The course price | free | paid.
		 *
		 * @return array The items for the integration.
		 */
		public static function get_courses( $items = array(), $search = '', $course_price = '' ) {

			$meta_query = array();

			if ( 'paid' === $course_price ) {
				$meta_query[] = array(
					'key'     => '_regular_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				);
			} elseif ( 'free' === $course_price ) {
				$meta_query[] = array(
					'key'     => '_regular_price',
					'value'   => 0,
					'compare' => '=',
					'type'    => 'NUMERIC',
				);

				$meta_query[] = array(
					'key'     => '_access_mode',
					'value'   => 'need_registration',
					'compare' => '=',
					'type'    => 'STRING',
				);
			}

			$course_query = new \WP_Query(
				array(
					'post_type'      => 'mto-course',
					's'              => $search,
					'posts_per_page' => -1,
					'meta_query'     => $meta_query,
				)
			);

			if ( ( isset( $course_query->posts ) ) && ( ! empty( $course_query->posts ) ) ) {
				$items = wp_list_pluck( $course_query->posts, 'post_title', 'ID' );
			}

			return $items;
		}

		/**
		 * Get checkout page URL.
		 *
		 * @return string
		 */
		public static function get_checkout_page_url() {
			$registration_page_id = get_option( 'user_registration_member_registration_page_id' );

			$url = get_permalink( $registration_page_id );

			return $url;
		}

		/**
		 * Get courses based on membership ID.
		 *
		 * @param int $membership_id Membership ID.
		 *
		 * @return array
		 */
		public static function get_courses_based_on_membership( $membership_id ) {
			$content_rules = $membership_id ? urcr_get_membership_rule_data( $membership_id ) : array();

			$user_courses = array();

			if ( $content_rules['enabled'] && ! empty( $content_rules['access_control'] ) && 'access' === $content_rules['access_control'] ) {
				foreach ( $content_rules['target_contents'] as $content ) {
					if ( 'masteriyo_courses' === $content['type'] ) {
						$user_courses = $content['value'];
					}
				}
			}

			return $user_courses;
		}

		/**
		 * Get membership list by course ID.
		 *
		 * @param int $course_id Course ID.
		 *
		 * @return array
		 */
		public static function get_membership_list_by_course( $course_id ) {
			$membership_list = array();

			$membership_access_rules = self::urcr_get_rules_based_membership_type();

			if ( ! empty( $membership_access_rules ) ) {
				foreach ( $membership_access_rules as $rule ) {
					$membership_id    = $rule['membership_id'];
					$membership_title = get_the_title( $membership_id );
					$target_contents  = isset( $rule['target_contents'] ) ? $rule['target_contents'] : array();

					$membership_meta = json_decode( $rule['membership_meta'], true );

					$time = '';
					if ( 'paid' === $membership_meta['type'] ) {
						$time = esc_html__( 'Lifetime', 'user-registration' );
					}

					if ( ! empty( $target_contents ) ) {
						foreach ( $target_contents as $content ) {
							if ( 'masteriyo_courses' === $content['type'] && in_array( (string) $course_id, $content['value'], true ) ) {

								$membership_list[ $membership_id ] = array(
									'title'        => $membership_title,
									'amount'       => $membership_meta['amount'],
									'time'         => $time,
									'type'         => $membership_meta['type'],
									'subscription' => isset( $membership_meta['subscription'] ) ? $membership_meta['subscription'] : array(),
								);
							}
						}
					}
				}
			}

			return $membership_list;
		}
		/**
		 * Get all rules based on membership type.
		 *
		 * @return array
		 */
		public static function urcr_get_rules_based_membership_type() {

			// Find existing rule for this membership
			$existing_rules = get_posts(
				array(
					'post_type'   => 'urcr_access_rule',
					'post_status' => 'publish',
					'meta_query'  => array(
						array(
							'key'   => 'urcr_rule_type',
							'value' => 'membership',
						),
					),
				)
			);
			$rules          = array();
			if ( empty( $existing_rules ) ) {
				return array();
			}

			foreach ( $existing_rules as $rule_post ) {
				$rule_content = json_decode( $rule_post->post_content, true );

				if ( ! $rule_content ) {
					continue;
				}

				$member_id = get_post_meta( $rule_post->ID, 'urcr_membership_id', true );
				// Add rule ID and other metadata
				$rule_content['id']              = $rule_post->ID;
				$rule_content['title']           = $rule_post->post_title;
				$rule_content['membership_id']   = $member_id;
				$rule_content['membership_meta'] = get_post_meta( $member_id, 'ur_membership', true );

				// Default to true if not set (matches default for new rules)
				if ( ! isset( $rule_content['enabled'] ) ) {
					$rule_content['enabled'] = true;
				}

				$rules[] = $rule_content;
			}

			return $rules;
		}
	}
endif;
