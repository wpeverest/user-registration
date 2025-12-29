<?php
/**
 * UR_Stats_Helpers Class for reusable stats calculation functions.
 *
 * @package User_Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Stats_Helpers' ) ) {

	/**
	 * UR_Stats_Helpers class.
	 */
	class UR_Stats_Helpers {

		/**
		 * Get content restriction statistics.
		 *
		 * @return array Statistics array with total_rules, logic_gates_and_count, logic_gates_or_count, and logic_gates_not_count.
		 */
		public static function get_content_restriction_stats() {
			$rules_query = new WP_Query(
				array(
					'post_type'      => 'urcr_access_rule',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				)
			);

			$total_rules      = 0;
			$and_gates_count  = 0;
			$or_gates_count   = 0;
			$not_gates_count  = 0;

			foreach ( $rules_query->posts as $rule_post ) {
				$access_rule = json_decode( $rule_post->post_content, true );

				if ( ! empty( $access_rule['enabled'] ) && $access_rule['enabled'] === true ) {
					$total_rules++;

					if ( ! empty( $access_rule['logic_map'] ) && is_array( $access_rule['logic_map'] ) ) {
						$stats = self::analyze_logic_map( $access_rule['logic_map'] );
						$and_gates_count += $stats['and_count'];
						$or_gates_count  += $stats['or_count'];
						$not_gates_count += $stats['not_count'];
					}
				}
			}

			return array(
				'total_rules'            => $total_rules,
				'logic_gates_and_count'  => $and_gates_count,
				'logic_gates_or_count'   => $or_gates_count,
				'logic_gates_not_count'  => $not_gates_count,
			);
		}

		/**
		 * Check if a plugin slug or path is the content-restriction plugin/module.
		 *
		 * @param string $plugin_slug_or_path Plugin slug (e.g., 'user-registration-content-restriction') or full path (e.g., 'user-registration-content-restriction/user-registration-content-restriction.php').
		 * @return bool True if it's the content-restriction plugin/module.
		 */
		public static function is_content_restriction_plugin( $plugin_slug_or_path ) {
			// Extract slug from full path if needed
			$slug = self::extract_plugin_slug( $plugin_slug_or_path );

			return 'user-registration-content-restriction' === $slug;
		}

		/**
		 * Check if a plugin slug or path is the email-templates plugin.
		 *
		 * @param string $plugin_slug_or_path Plugin slug (e.g., 'user-registration-email-templates') or full path (e.g., 'user-registration-email-templates/user-registration-email-templates.php').
		 * @return bool True if it's the email-templates plugin.
		 */
		public static function is_email_template_plugin( $plugin_slug_or_path ) {
			// Extract slug from full path if needed
			$slug = self::extract_plugin_slug( $plugin_slug_or_path );

			return 'user-registration-email-templates' === $slug;
		}

		/**
		 * Extract plugin slug from plugin path.
		 *
		 * @param string $plugin_path
		 * @return string Plugin slug.
		 */
		public static function extract_plugin_slug( $plugin_path ) {
			if ( false !== strpos( $plugin_path, '/' ) ) {
				$plugin_array = explode( '/', $plugin_path );
				return isset( $plugin_array[0] ) ? $plugin_array[0] : $plugin_path;
			}
			return $plugin_path;
		}

		/**
		 * Add content restriction stats to addon info array if applicable.
		 *
		 * @param array  $addon_info Addon info array to merge stats into.
		 * @param string $plugin_slug_or_path Plugin slug or path to check.
		 * @return array
		 */
		public static function maybe_add_content_restriction_stats( $addon_info, $plugin_slug_or_path ) {
			if ( self::is_content_restriction_plugin( $plugin_slug_or_path ) ) {
				$content_restriction_stats = self::get_content_restriction_stats();
				$addon_info = array_merge( $addon_info, $content_restriction_stats );
			}
			return $addon_info;
		}

		/**
		 * Add email template stats to addon info array if applicable.
		 *
		 * @param array  $addon_info Addon info array to merge stats into.
		 * @param string $plugin_slug_or_path Plugin slug or path to check.
		 * @return array
		 */
		public static function maybe_add_email_template_stats( $addon_info, $plugin_slug_or_path ) {
			if ( self::is_email_template_plugin( $plugin_slug_or_path ) ) {
				$email_template_count = self::get_email_template_stats();
				$addon_info['total_email_template_count'] = $email_template_count;
			}
			return $addon_info;
		}

		/**
		 * Get popup statistics.
		 *
		 * @return int Total count of active popup posts.
		 */
		public static function get_popup_stats() {
			$popup_query = new WP_Query(
				array(
					'post_type'      => 'ur_pro_popup',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			return $popup_query->found_posts;
		}

		/**
		 * Get email template statistics.
		 *
		 * @return int Total count of active email template posts.
		 */
		public static function get_email_template_stats() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$email_template_query = new WP_Query(
				array(
					'post_type'      => 'ur_email_templates',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			return $email_template_query->found_posts;
		}

		/**
		 * Analyze logic map structure to count gates.
		 *
		 * @param array $logic_map Logic map structure.
		 * @return array Statistics array with and_count, or_count, and not_count.
		 */
		private static function analyze_logic_map( $logic_map ) {
			$stats = array(
				'and_count' => 0,
				'or_count'  => 0,
				'not_count' => 0,
			);

			if ( ! is_array( $logic_map ) || empty( $logic_map['type'] ) ) {
				return $stats;
			}

			if ( isset( $logic_map['type'] ) && $logic_map['type'] === 'group' && ! empty( $logic_map['logic_gate'] ) ) {
				$gate = strtoupper( $logic_map['logic_gate'] );
				if ( $gate === 'AND' ) {
					$stats['and_count'] = 1;
				} elseif ( $gate === 'OR' ) {
					$stats['or_count'] = 1;
				} elseif ( $gate === 'NOT' ) {
					$stats['not_count'] = 1;
				}

				// Recurse into conditions
				if ( ! empty( $logic_map['conditions'] ) && is_array( $logic_map['conditions'] ) ) {
					foreach ( $logic_map['conditions'] as $condition ) {
						$child_stats = self::analyze_logic_map( $condition );
						$stats['and_count'] += $child_stats['and_count'];
						$stats['or_count']  += $child_stats['or_count'];
						$stats['not_count'] += $child_stats['not_count'];
					}
				}
			}

			return $stats;
		}
	}
}

