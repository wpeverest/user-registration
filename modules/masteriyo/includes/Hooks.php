<?php
/**
 * URM_MASTERIYO_Hooks setup
 *
 * @package URM_MASTERIYO_Hooks
 * @since  1.0.0
 */

namespace WPEverest\URM\Masteriyo;

use Masteriyo\Taxonomy\Taxonomy;
use Masteriyo\Enums\CourseAccessMode;
use Masteriyo\Enums\PostStatus;
use Masteriyo\PostType\PostType;



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Hooks' ) ) :

	/**
	 * Hooks masteriyo integration Clas s
	 *
	 */
	class Hooks {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function init() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			// add_filter( 'masteriyo_course_object_query_args', array( $this, 'filter_the_get_course_args' ) );
			// add_filter( 'masteriyo_get_course', array( $this, 'get_masteriyo_course' ), 10, 2 );

			// add_filter( 'masteriyo_setup_course_data', array( $this, 'check_course_data' ) );

			// add_action( 'masteriyo_before_courses_loop', array( $this, 'add_args' ) );

			add_filter( 'masteriyo_course_query_tax_query', array( $this, 'add_tax_query' ) ); //working
			add_filter( 'masteriyo_course_add_to_cart_text', array( $this, 'add_join_now_text' ), 10, 2 );

			add_filter( 'masteriyo_can_start_course', array( $this, 'can_start_course' ), 10, 3 );

			add_filter( 'masteriyo_course_add_to_cart_url', array( $this, 'modify_url' ), 10, 2 );

			// add_action( 'masteriyo_single_course_sidebar_content', array( $this, 'modify_single_side_content' ), 90 );

			add_filter( 'masteriyo_price', array( $this, 'price_html' ), 10, 5 );

			add_filter( 'masteriyo_get_related_courses', array( $this, 'get_related_courses' ), 10, 3 );

			add_filter( 'masteriyo_course_filter_ajax_prepare_query_args', array( $this, 'filter_ajax_prepare_query_args' ), );

			add_action( 'masteriyo_single_course_sidebar_content', array( $this, 'add_single_course_sidebar_content' ), 10 );
			add_action( 'masteriyo_single_course_sidebar_content_after_progress', array( $this, 'add_single_course_sidebar_content' ), 10 );
			if ( masteriyo_string_to_bool( masteriyo_get_setting( 'course_archive.filters_and_sorting.enable_price_filter' ) ) ) {
				masteriyo_set_setting( 'course_archive.filters_and_sorting.enable_price_filter', false );
			}

			// add_filter( 'masteriyo_get_account_url', array( $this, 'get_my_account_url' ) );

			add_filter( 'masteriyo_rest_response_user_course_data', array( $this, 'filter_user_courses' ), 10, 4 );
			// add_filter( 'masteriyo_after_process_objects_collection', array( $this, 'filter_process' ), 10, 3 );

			add_filter( 'masteriyo_is_account_page', array( $this, 'override_account_page' ), 10, 3 );
		}

		public function override_account_page(
			$is_account_page,
			$page_id,
			$account_page_id
		) {

			global $wp;

			if ( array_key_exists( 'urm-course-portal', $wp->query_vars ) ) {
					return true;
			}

			return $is_account_page;
		}

		// public function filter_process( $objects, $query_args, $query_results ) {
		//  if ( isset( $objects['courses_stat'] ) ) {
		//      error_log( print_r( $objects, true ) );
		//  }

		//  return $objects;
		// }

		// public function get_user_enrolled_courses_count( $user = null ) {
		//  $user_id = is_a( $user, 'Masteriyo\Models\User' ) ? $user->get_id() : absint( $user ) ?? get_current_user_id();

		//  if ( ! $user_id ) {
		//      return 0;
		//  }

		//  $args = array(
		//      'post_type'   => PostType::COURSE,
		//      'post_status' => PostStatus::PUBLISH,
		//      'paged'       => 1,
		//      'order'       => 'DESC',
		//      'orderby'     => 'date',
		//      'tax_query'   => array(
		//          'relation' => 'AND',
		//      ),
		//      'meta_query'  => array(
		//          'relation' => 'AND',
		//      ),
		//  );

		//  $args['meta_query'][] = array(
		//      'relation' => 'OR',
		//      array(
		//          'key'     => '_access_mode',
		//          'value'   => 'open',
		//          'compare' => '=',
		//      ),
		//  );

		//  return $courses_count ? absint( $courses_count ) : 0;
		// }

		public function filter_user_courses( $data, $user_course, $context, $obj ) {
			$access_courses = array();
			if ( is_user_logged_in() ) {
				$user_obj = masteriyo( 'user' );
				$user     = masteriyo_get_current_user();

				if ( is_a( $user, 'Masteriyo\Database\Model' ) ) {
					$id = $user->get_id();
				} elseif ( is_a( $user, 'WP_User' ) ) {
					$id = $user->ID;
				} else {
					$id = $user;
				}

				$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
				$membership         = $members_repository->get_member_membership_by_id( $id );

				$access_courses = Helper::get_courses_based_on_membership( $membership['post_id'] );
			}

			$course_id   = $data['course']['id'];
			$access_mode = get_post_meta( $course_id, '_access_mode', true );
			if ( CourseAccessMode::OPEN === $access_mode ) {
				return $data;
			}

			if ( CourseAccessMode::NEED_REGISTRATION === $access_mode && in_array( $course_id, $access_courses, true ) ) {
				return $data;
			}

			return array();
		}

		public function add_single_course_sidebar_content() {
			echo '<div class="masteriyo-single-course-stats urm-masteriyo-membership-list"> Default Membership</div>';
		}

		public function filter_ajax_prepare_query_args( $args ) {

			$price_type = isset( $_POST['price-type'] ) ? sanitize_text_field( $_POST['price-type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( empty( $price_type ) || 'all' === $price_type ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$args['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => '_access_mode',
						'value'   => 'need_registration',
						'compare' => '=',
					),
					array(
						'key'     => '_access_mode',
						'value'   => 'open',
						'compare' => '=',
					),
				);
			}

			if ( ! empty( $price_type ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'free' === $price_type ) {

					$args['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key'     => '_access_mode',
							'value'   => 'open',
							'compare' => '=',
						),
					);
				}
				if ( 'paid' === $price_type ) {
					$args['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key'     => '_access_mode',
							'value'   => 'need_registration',
							'compare' => '=',
						),
					);
				}
			}

			unset( $args['tax_query'] );

			return $args;
		}

		public function get_related_courses( $related_courses, $query, $course ) {

			if ( is_null( $course ) ) {
				return $html;
			}

			$related_courses = array_filter(
				$related_courses,
				function ( $cr ) {
					$mode = $cr->get_access_mode();
					return CourseAccessMode::NEED_REGISTRATION === $mode || CourseAccessMode::OPEN === $mode;
				}
			);

			return $related_courses;
		}

		public function price_html( $html, $price, $args, $unformatted_price, $course ) {

			if ( is_null( $course ) ) {
				return $html;
			}

			if ( CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {
				$html = '';
			}

			return $html;
		}

		// public function modify_single_side_content( $course ) {

		//  if ( ! is_user_logged_in() ) {
		//      return '<button>Buy Now</button>';
		//  }
		// }

		public function modify_url( $url, $course ) {

			if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
				$url = $course->get_permalink();

				if ( masteriyo_is_single_course_page() && ! $this->check_course_access( $course ) ) {
					$url = Helper::get_checkout_page_url();
				}

				return $url;
			}

			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {
				$user_obj = masteriyo( 'user' );
				$user     = masteriyo_get_current_user();

				if ( is_a( $user, 'Masteriyo\Database\Model' ) ) {
					$id = $user->get_id();
				} elseif ( is_a( $user, 'WP_User' ) ) {
					$id = $user->ID;
				} else {
					$id = $user;
				}

				$user_registration_source = get_user_meta( $id, 'ur_registration_source', true );

				if ( 'membership' !== $user_registration_source ) {

					if ( masteriyo_is_single_course_page() ) {
						$url = Helper::get_checkout_page_url();
					} else {
						$url = $course->get_permalink();
					}
				} else {
					$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
					$membership         = $members_repository->get_member_membership_by_id( $id );

					if ( empty( $membership ) || empty( $membership['status'] ) || 'active' !== $membership['status'] ) {
						if ( masteriyo_is_single_course_page() ) {
							$url = Helper::get_checkout_page_url();
						} else {
							$url = $course->get_permalink();
						}
					} else {
						$access_course = Helper::get_courses_based_on_membership( $membership['post_id'] );

						if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
							$course_id = $course->get_id();
						} elseif ( is_a( $course, 'WP_Post' ) ) {
							$course_id = $course->ID;
						} else {
							$course_id = absint( $course );
						}

						$url = in_array( (string) $course_id, $access_course, true ) ? $url : ( masteriyo_is_single_course_page() ? Helper::get_checkout_page_url()
						: $course->get_permalink() );
					}
				}
			} elseif ( ! masteriyo_is_single_course_page() ) {

				$url = $course->get_permalink();
			}

			return $url;
		}

		public function can_start_course( $can_start_course, $course, $user ) {

			if ( $can_start_course ) {
				if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {

					$user_obj = masteriyo( 'user' );

					if ( is_a( $user, 'Masteriyo\Database\Model' ) ) {
						$id = $user->get_id();
					} elseif ( is_a( $user, 'WP_User' ) ) {
						$id = $user->ID;
					} else {
						$id = $user;
					}

					$user_registration_source = get_user_meta( $id, 'ur_registration_source', true );

					if ( 'membership' !== $user_registration_source ) {
						$can_start_course = false;
					} else {
						$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
						$membership         = $members_repository->get_member_membership_by_id( $id );

						if ( empty( $membership ) || empty( $membership['status'] ) || 'active' !== $membership['status'] ) {
							$can_start_course = false;
						} else {

							$membership_courses = Helper::get_courses_based_on_membership( $membership['post_id'] );
							if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
								$course_id = $course->get_id();
							} elseif ( is_a( $course, 'WP_Post' ) ) {
								$course_id = $course->ID;
							} else {
								$course_id = absint( $course );
							}

							$can_start_course = in_array( $course_id, $membership_courses, true );
						}
					}
				}
			}

			return $can_start_course;
		}

		public function add_join_now_text( $text, $course_obj ) {
			if ( ! is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course_obj->get_access_mode() ) {
				$text = __( 'Join Now', 'learning-management-system' );

				if ( masteriyo_is_single_course_page() ) {
					$text = __( 'Buy Now', 'learning-management-system' );
				}
			}

			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course_obj->get_access_mode() ) {
				if ( ! $this->check_course_access( $course_obj ) ) {
					$text = __( 'Join Now', 'learning-management-system' );
				}

				if ( masteriyo_is_single_course_page() && ! $this->check_course_access( $course_obj ) ) {
					$text = __( 'Upgrade Now', 'learning-management-system' );
				}
			}

			return $text;
		}

		public function add_tax_query( $args ) {

			$args['tax_query'] = array(
				'taxonomy' => Taxonomy::COURSE_VISIBILITY,
				'field'    => 'name',
				'terms'    => 'free',
			);

			return $args;
		}

		public function add_args( $args = array() ) {
			$default_args = array();

			$default_args['meta_query'] = array(
				'key'     => '_access_mode',
				'value'   => 'need_registration',
				'compare' => '=',
				'type'    => 'STRING',
			);

			if ( isset( $GLOBALS['masteriyo_loop'] ) ) {
				$default_args              = array_merge( $default_args, $GLOBALS['masteriyo_loop'] );
				$GLOBALS['masteriyo_loop'] = wp_parse_args( $args, $default_args );
			}
		}

		public function check_course_access( $course ) {
			$can_start_course = false;

			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {

				$user = masteriyo_get_current_user();

				if ( is_a( $user, 'Masteriyo\Database\Model' ) ) {
					$id = $user->get_id();
				} elseif ( is_a( $user, 'WP_User' ) ) {
					$id = $user->ID;
				} else {
					$id = $user;
				}

				$user_registration_source = get_user_meta( $id, 'ur_registration_source', true );

				if ( 'membership' !== $user_registration_source ) {
					$can_start_course = false;
				} else {
					$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
					$membership         = $members_repository->get_member_membership_by_id( $id );

					if ( empty( $membership ) || empty( $membership['status'] ) || 'active' !== $membership['status'] ) {
						$can_start_course = false;
					} else {
						$membership_courses = Helper::get_courses_based_on_membership( $membership['post_id'] );

						if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
							$course_id = $course->get_id();
						} elseif ( is_a( $course, 'WP_Post' ) ) {
							$course_id = $course->ID;
						} else {
							$course_id = absint( $course );
						}

						$can_start_course = in_array( (string) $course_id, $membership_courses, true );
					}
				}
			}

			return $can_start_course;
		}
	}
endif;
