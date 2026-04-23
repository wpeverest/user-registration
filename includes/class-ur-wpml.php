<?php
/**
 * WPML Compatibility
 *
 * @package UserRegistration
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_WPML' ) ) {

	/**
	 * UR_WPML class.
	 */
	class UR_WPML {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Run at priority 11 to clean up after WPML's priority 10 filter.
			add_filter( 'comments_clauses', array( $this, 'prevent_duplicate_wpml_joins' ), 11, 2 );
		}

		/**
		 * Prevents duplicate JOINs from WPML in comment queries.
		 *
		 * WPML appends JOIN icltr2 + LEFT JOIN icltr_comment as a single block in
		 * comments_clauses_filter (priority 10). If that filter fires more than once
		 * for the same query the block is appended twice, causing MySQL error
		 * "Not unique table/alias: 'icltr2'". We detect the duplicate by finding a
		 * second occurrence of the icltr2 JOIN and truncate everything from that
		 * point, which removes the entire duplicate block cleanly.
		 *
		 * @param array            $clauses The query clauses.
		 * @param WP_Comment_Query $query   The query object.
		 * @return array Modified clauses.
		 */
		public function prevent_duplicate_wpml_joins( $clauses, $query ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			global $wpdb;

			if ( empty( $clauses['join'] ) || ! is_string( $clauses['join'] ) ) {
				return $clauses;
			}

			$icl_table = $wpdb->prefix . 'icl_translations';
			$pattern   = '/JOIN\s+' . preg_quote( $icl_table, '/' ) . '\s+icltr2\b/i';

			if ( preg_match_all( $pattern, $clauses['join'], $matches, PREG_OFFSET_CAPTURE ) < 2 ) {
				return $clauses;
			}

			// Offset of the second (duplicate) icltr2 JOIN inside the string.
			$second_offset = $matches[0][1][1];

			// Walk back over any whitespace so we remove the full duplicate block cleanly.
			while ( $second_offset > 0 && preg_match( '/\s/', $clauses['join'][ $second_offset - 1 ] ) ) {
				--$second_offset;
			}

			$clauses['join'] = substr( $clauses['join'], 0, $second_offset );

			return $clauses;
		}
	}
}
