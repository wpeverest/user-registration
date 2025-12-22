<?php
/**
 * WPEverest\URM\Masteriyo Frontend.
 *
 * @class    Frontend
 * @package  WPEverest\URM\Masteriyo\Frontend
 * @category Frontend
 * @author   WPEverest
 */

namespace WPEverest\URM\Masteriyo;

use Masteriyo\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Class
 */
class Frontend {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'wp_loaded', array( $this, 'add_masteriyo_course_tab_endpoint' ) );
		add_filter( 'user_registration_account_menu_items', array( $this, 'membership_tab' ), 10, 1 );
		add_action(
			'user_registration_account_urm-courses_endpoint',
			array(
				$this,
				'tab_endpoint_content',
			)
		);
	}

	public function redirect_urm_course_portal() {
	}
	/**
	 * Add the item to $items array.
	 *
	 * @param array $items Items.
	 */
	public function membership_tab( $items ) {
		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return $items;
		}
		$new_items                = array();
		$new_items['urm-courses'] = __( 'My Courses', 'user-registration' );
		$items                    = array_merge( $items, $new_items );

		return $this->delete_account_insert_before_helper( $items, $new_items, 'user-logout' );
	}

	/**
	 * Delete Account insert after helper.
	 *
	 * @param mixed $items Items.
	 * @param mixed $new_items New items.
	 * @param mixed $before Before item.
	 */
	public function delete_account_insert_before_helper( $items, $new_items, $before ) {

		// Search for the item position.
		$position = array_search( $before, array_keys( $items ), true );

		// Insert the new item.
		$return_items  = array_slice( $items, 0, $position, true );
		$return_items += $new_items;
		$return_items += array_slice( $items, $position, count( $items ) - $position, true );

		return $return_items;
	}

	/**
	 * Membership tab content.
	 */
	public function tab_endpoint_content() {
		$user_id = get_current_user_id();

		$my_course = array( 177 );
		$courses   = array();

		foreach ( $my_course as $course_id ) {
			$courses[] = masteriyo_get_course( $course_id );
		}
		wp_enqueue_style( 'urm_masteriyo_pulic_style', plugins_url( '/assets/css/public.css', Constants::get( 'MASTERIYO_PLUGIN_FILE' ) ), array(), URM_MASTERIYO_VERSION );
		ur_get_template(
			'myaccount/courses.php',
			array( 'courses' => $courses )
		);
	}

	/**
	 * Add Course tab endpoint.
	 */
	public function add_masteriyo_course_tab_endpoint() {

		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return;
		}
		$mask = Ur()->query->get_endpoints_mask();

		add_rewrite_endpoint( 'urm-courses', $mask );
		add_rewrite_endpoint( 'urm-course-portal', $mask );
		flush_rewrite_rules();
	}
}
