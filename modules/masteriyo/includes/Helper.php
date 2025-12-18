<?php
/**
 * URM_MASTERIYO_Helper setup
 *
 * @package URM_MASTERIYO_Helper
 * @since  1.0.0
 */

namespace WPEverest\URM\Masteriyo;

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

		public static function get_checkout_page_url() {
			$registration_page_id = get_option( 'user_registration_member_registration_page_id' );

			$url = get_permalink( $registration_page_id );

			return $url;
		}
	}
endif;
