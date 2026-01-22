<?php
/**
 * WPEverest\URM\Masteriyo Frontend.
 *
 * @class    Frontend
 * @package  WPEverest\URM\Masteriyo\Frontend
 * @category Frontend
 */

namespace WPEverest\URM\Masteriyo;

use Masteriyo\Constants;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

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
		add_action( 'init', array( $this, 'urm_create_course_portal_page' ) );
		add_action( 'wp_loaded', array( $this, 'add_masteriyo_course_tab_endpoint' ) );
		add_filter( 'user_registration_account_menu_items', array( $this, 'masteriyo_course_tab' ), 10, 1 );

		add_action(
			'user_registration_account_urm-courses_endpoint',
			array( $this, 'tab_endpoint_content' )
		);

		add_action(
			'wp_ajax_urm_masteriyo_single_membership_redirect',
			array( $this, 'single_membership_redirect_intent' )
		);

		add_action(
			'wp_ajax_nopriv_urm_masteriyo_single_membership_redirect',
			array( $this, 'single_membership_redirect_intent' )
		);
	}

	public function single_membership_redirect_intent() {
		$membership_id = isset( $_POST['membership_id'] ) ? absint( $_POST['membership_id'] ) : 0;

		if ( ! $membership_id ) {
			wp_send_json_error( __( 'Invalid membership ID', 'masteriyo' ) );
		}

		$user_membership_ids = array();

		if ( is_user_logged_in() ) {

			$current_user_id    = get_current_user_id();
			$members_repository = new MembersRepository();

			if ( $current_user_id ) {
				$user_memberships    = $members_repository->get_member_membership_by_id( $current_user_id );
				$user_membership_ids = array_filter(
					array_map(
						function ( $user_memberships ) {
							return $user_memberships['post_id'];
						},
						$user_memberships
					)
				);
			}
		}

		$membership_service = new MembershipService();
		$membership         = (array) get_post( $membership_id );

		$intended_action = $membership_service->fetch_intended_action( 'upgrade', $membership, $user_membership_ids );
		if ( $intended_action ) {

			$registration_page_id = (int) get_option( 'user_registration_member_registration_page_id' );
			$thankyou_page_id     = (int) get_option( 'user_registration_thank_you_page_id' );

			if ( $registration_page_id ) {
				$base_url = get_permalink( $registration_page_id );

				$full_url = add_query_arg(
					array(
						'action'        => $intended_action,
						'thank_you'     => $thankyou_page_id,
						'membership_id' => $membership_id,
					),
					$base_url
				);

				wp_send_json_success(
					array(
						'redirect_url' => esc_url_raw( $full_url ),
					)
				);
			}
		}

		wp_send_json_error( __( 'Invalid membership ID', 'masteriyo' ) );
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
			URM_MASTERIYO_VERSION
		);

		wp_register_script(
			'urm-masteriyo-frontend-script',
			URM_MASTERIYO_JS_ASSETS_URL . '/frontend' . $suffix . '.js',
			array( 'jquery' ),
			URM_MASTERIYO_VERSION,
			true
		);

		$course_portal_page = get_page_by_path( 'course-portal' );

		if ( ! $course_portal_page ) {
			return;
		}

		if ( ! $post || ! $course_portal_page ) {
			return;
		}

		if ( ( $course_portal_page->ID === $post->ID ) || masteriyo_is_single_course_page() ) {
			wp_enqueue_style( 'urm-masteriyo-frontend-style' );
			wp_localize_script(
				'urm-masteriyo-frontend-script',
				'ur_members_localized_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'urm_masteriyo_nonce' ),
				)
			);
			wp_enqueue_script( 'urm-masteriyo-frontend-script' );
			return;
		}
	}

	/**
	 * Create the Course Portal page once and set it as Masteriyo's account page.
	 *
	 * @return void
	 */
	public function urm_create_course_portal_page() {

		$page_title = 'Course Portal';
		$page_slug  = 'course-portal';

		$page = get_page_by_path( $page_slug );

		if ( $page ) {
			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'     => $page_title,
					'post_name'      => $page_slug,
					'post_content'   => '[masteriyo_account]',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				return;
			}
		}

		$account_page_id = masteriyo_get_setting( 'general.pages.account_page_id' );

		if ( intval( $account_page_id ) === intval( $page_id ) ) {
			return;
		}

		masteriyo_set_setting( 'general.pages.account_page_id', $page_id );
	}

	/**
	 * Placeholder for redirect logic.
	 *
	 * @return void
	 */
	public function redirect_urm_course_portal() {}

	/**
	 * Add the "My Courses" menu tab for membership users.
	 *
	 * @param array $items Existing menu items.
	 * @return array
	 */
	public function masteriyo_course_tab( $items ) {
		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return $items;
		}

		if ( empty( $this->get_current_user_course() ) ) {
			return $items;
		}

		$new_items                = array();
		$new_items['urm-courses'] = __( 'My Courses', 'user-registration' );
		$items                    = array_merge( $items, $new_items );

		return $this->insert_after_edit_profile( $items, $new_items, 'edit-profile' );
	}

	/**
	 * Insert new menu items before a specific item.
	 *
	 * @param array  $items     Existing items.
	 * @param array  $new_items New items to insert.
	 * @param string $before    Key to insert before.
	 * @return array
	 */
	public function insert_after_edit_profile( $items, $new_items, $after ) {

		$keys     = array_keys( $items );
		$position = array_search( $after, $keys, true );

		if ( false === $position ) {
			return array_merge( $items, $new_items );
		}

		++$position;

		$return_items  = array_slice( $items, 0, $position, true );
		$return_items += $new_items;
		$return_items += array_slice( $items, $position, null, true );

		return $return_items;
	}

	/**
	 * Render the content for the My Courses tab.
	 *
	 * @return void
	 */
	public function tab_endpoint_content() {
		$user_courses = $this->get_current_user_course();

		$courses = array();

		if ( ! empty( $user_courses ) ) {
			foreach ( $user_courses as $course_id ) {
				$courses[] = masteriyo_get_course( $course_id );
			}
		}

		wp_enqueue_style(
			'urm_masteriyo_pulic_style',
			plugins_url( '/assets/css/public.css', Constants::get( 'MASTERIYO_PLUGIN_FILE' ) ),
			array(),
			URM_MASTERIYO_VERSION
		);

		ur_get_template(
			'myaccount/courses.php',
			array( 'courses' => $courses )
		);
	}

	/**
	 * Register custom endpoints for the account page.
	 *
	 * @return void
	 */
	public function add_masteriyo_course_tab_endpoint() {

		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );
		if ( 'membership' !== $user_source ) {
			return;
		}
		if ( empty( $this->get_current_user_course() ) ) {
			return;
		}

		$mask = Ur()->query->get_endpoints_mask();

		add_rewrite_endpoint( 'urm-courses', $mask );
		add_rewrite_endpoint( 'urm-course-portal', $mask );
		flush_rewrite_rules();
	}

	public function get_current_user_course() {
		$user_id = get_current_user_id();

		$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
		$memberships        = $members_repository->get_member_membership_by_id( $user_id );

		if ( empty( $memberships ) ) {
			return array();
		}

		$user_courses = array();
		foreach ( $memberships as $key => $membership ) {
			if ( empty( $membership['post_id'] ) ) {
				continue;
			}
			$user_courses = array_merge( $user_courses, Helper::get_courses_based_on_membership( $membership['post_id'] ) );
		}

		return $user_courses;
	}
}
