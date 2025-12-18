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
			add_filter( 'masteriyo_course_object_query_args', array( $this, 'filter_the_get_course_args' ) );
			// add_filter( 'masteriyo_get_course', array( $this, 'get_masteriyo_course' ), 10, 2 );

			// add_filter( 'masteriyo_setup_course_data', array( $this, 'check_course_data' ) );

			// add_action( 'masteriyo_before_courses_loop', array( $this, 'add_args' ) );

			add_filter( 'masteriyo_course_query_tax_query', array( $this, 'add_tax_query' ) ); //working
			add_filter( 'masteriyo_course_add_to_cart_text', array( $this, 'add_join_now_text' ), 10, 2 );

			add_filter( 'masteriyo_can_start_course', array( $this, 'can_start_course' ), 10, 3 );

			add_filter( 'masteriyo_course_add_to_cart_url', array( $this, 'modify_url' ), 10, 2 );

			add_action( 'masteriyo_single_course_sidebar_content', array( $this, 'modify_single_side_content' ), 90 );

			add_filter( 'masteriyo_price', array( $this, 'price_html' ), 10, 5 );
		}

		public function price_html( $html, $price, $args, $unformatted_price, $course ) {

			if ( CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {
				$html = '';
			}

			return $html;
		}

		public function modify_single_side_content( $course ) {
			if ( ! is_user_logged_in() ) {
				return '<button>Buy Now</button>';
			}
		}

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
						$access_course = array( 177 );

						if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
							$course_id = $course->get_id();
						} elseif ( is_a( $course, 'WP_Post' ) ) {
							$course_id = $course->ID;
						} else {
							$course_id = absint( $course );
						}

						$url = in_array( $course_id, $access_course, true ) ? $url : ( masteriyo_is_single_course_page() ? Helper::get_checkout_page_url()
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
							$access_course = array( 177 );

							if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
								$course_id = $course->get_id();
							} elseif ( is_a( $course, 'WP_Post' ) ) {
								$course_id = $course->ID;
							} else {
								$course_id = absint( $course );
							}

							$can_start_course = in_array( $course_id, $access_course, true );
						}
					}
				}
			}

			return $can_start_course;
		}

		public function add_join_now_text( $text, $course_obj ) {

			if ( CourseAccessMode::NEED_REGISTRATION === $course_obj->get_access_mode() ) {
				$text = __( 'Join Now', 'learning-management-system' );

				if ( ! is_user_logged_in() && masteriyo_is_single_course_page() ) {
					$text = __( 'Buy Now', 'learning-management-system' );
				}

				if ( ! $this->check_course_access( $course_obj ) ) {
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
			$can_strat_course = false;

			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {

					$user = masteriyo( 'user' );

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
						$access_course = array( 177 );

						if ( is_a( $course, 'Masteriyo\Models\Course' ) ) {
							$course_id = $course->get_id();
						} elseif ( is_a( $course, 'WP_Post' ) ) {
							$course_id = $course->ID;
						} else {
							$course_id = absint( $course );
						}

						$can_start_course = in_array( $course_id, $access_course, true );
					}
				}
			}

			return $can_strat_course;
		}
	}
endif;
