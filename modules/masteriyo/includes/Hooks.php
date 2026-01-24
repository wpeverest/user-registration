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
	 * Masteriyo Integration Hooks Class
	 */
	class Hooks {

		/**
		 * Instance of this class.
		 *
		 * @var Hooks|null
		 */
		protected static $instance = null;

		/**
		 * Returns a single instance of this class.
		 *
		 * @return Hooks
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Registers all filters and actions for Masteriyo integrations.
		 */
		public function __construct() {

			add_filter( 'masteriyo_course_query_tax_query', array( $this, 'add_tax_query' ) );
			add_filter( 'masteriyo_course_add_to_cart_text', array( $this, 'add_join_now_text' ), 10, 2 );

			add_filter( 'masteriyo_can_start_course', array( $this, 'can_start_course' ), 10, 3 );
			add_filter( 'masteriyo_course_add_to_cart_url', array( $this, 'modify_url' ), 10, 2 );

			add_filter( 'masteriyo_price', array( $this, 'price_html' ), 10, 5 );
			add_filter( 'masteriyo_get_related_courses', array( $this, 'get_related_courses' ), 10, 3 );

			add_filter( 'masteriyo_course_filter_ajax_prepare_query_args', array( $this, 'filter_ajax_prepare_query_args' ) );

			add_action( 'masteriyo_single_course_sidebar_content', array( $this, 'add_single_course_sidebar_content' ), 9 );
			add_action( 'masteriyo_single_course_sidebar_content_after_progress', array( $this, 'add_single_course_sidebar_content' ), 9 );
			$layout = masteriyo_get_setting( 'single_course.display.template.layout' );

			add_action( 'masteriyo_single_course_minimal_sidebar_content', array( $this, 'add_single_course_sidebar_content' ), 9 );
			add_action( 'masteriyo_template_enroll_button', array( $this, 'add_single_course_sidebar_content1' ), 9 );

			add_action(
				'init',
				function () {
					if ( function_exists( 'masteriyo_get_setting' ) && function_exists( 'masteriyo_set_setting' ) ) {
						if ( masteriyo_string_to_bool( masteriyo_get_setting( 'course_archive.filters_and_sorting.enable_price_filter' ) ) ) {
							masteriyo_set_setting( 'course_archive.filters_and_sorting.enable_price_filter', false );
						}
					}
				},
				99
			);

			add_filter( 'masteriyo_rest_response_user_course_data', array( $this, 'filter_user_courses' ), 10, 4 );
			add_filter( 'masteriyo_is_account_page', array( $this, 'override_account_page' ), 10, 3 );
		}

		/**
		 * Override Masteriyo account page if viewing the course portal.
		 *
		 * @param bool $is_account_page Current state.
		 * @param int  $page_id Page ID.
		 * @param int  $account_page_id Masteriyo account page ID.
		 * @return bool
		 */
		public function override_account_page( $is_account_page, $page_id, $account_page_id ) {
			global $post;

			$page = get_page_by_path( 'course-portal' );

			if ( ! $page ) {
				return $is_account_page;
			}

			if ( ! $post ) {

				return $account_page_id;
			}

			$account_page_id = $page->ID === $post->ID;

			return $account_page_id;
		}

		/**
		 * Filters user course API response based on membership access.
		 *
		 * @param array $data Response data.
		 * @param mixed $user_course Course object.
		 * @param mixed $context REST context.
		 * @param mixed $obj Additional object.
		 * @return array
		 */
		public function filter_user_courses( $data, $user_course, $context, $obj ) {
			$access_courses = array();

			if ( is_user_logged_in() ) {
				$access_courses = $this->get_current_user_access_course();
			}

			$access_courses = array_map( 'intval', (array) $access_courses );

			$course_id   = isset( $data['course']['id'] ) ? (int) $data['course']['id'] : 0;
			$access_mode = get_post_meta( $course_id, '_access_mode', true );
			$access_mode = is_string( $access_mode ) ? strtolower( $access_mode ) : $access_mode;

			if ( CourseAccessMode::OPEN === $access_mode || 'open' === $access_mode ) {
				return $data;
			}

			if ( CourseAccessMode::NEED_REGISTRATION === $access_mode || 'need_registration' === $access_mode ) {
				return in_array( $course_id, $access_courses, true ) ? $data : array();
			}

			return $data;
		}

		/**
		 * Adds membership block text inside the course sidebar.
		 *
		 * @return void
		 */
		public function add_single_course_sidebar_content1( $course ) {

			$layout = masteriyo_get_setting( 'single_course.display.template.layout' ) ?? 'default';
			if ( masteriyo_is_single_course_page() && 'layout1' === $layout ) {
				$this->add_single_course_sidebar_content( $course );
			}

			return '';
		}
		/**
		 * Adds membership block text inside the course sidebar.
		 *
		 * @return void
		 */
		public function add_single_course_sidebar_content( $course ) {
			$course_id = $course->get_id();

			if ( Helper::check_course_access( $course ) ) {
				return;
			}

			if ( CourseAccessMode::OPEN === $course->get_access_mode() ) {
				return;
			}

			$get_membership_list = Helper::get_membership_list_by_course( $course_id );
			$currency            = get_option( 'user_registration_payment_currency', 'USD' );
			$currencies          = ur_payment_integration_get_currencies();
			$symbol              = $currencies[ $currency ]['symbol'];
			if ( ! empty( $get_membership_list ) ) {
				echo '<div class="masteriyo-single-course-stats urm-masteriyo-membership-list">';
				echo '<div class="urm-membership-titles">';
				foreach ( $get_membership_list as $id => $membership ) {
					?>
					<div class="membership-block">
						<label class="ur_membership_input_label ur-label"
								for="ur-membership-select-membership-<?php echo esc_attr( $id ); ?>">
							<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field urm-masteriyo-single-membership-radio"
									id="ur-membership-select-membership-<?php echo esc_attr( $id ); ?>"
									type="radio"
									name="membership_id"
									value="<?php echo esc_attr( $id ); ?>"
							>
							<div class="ur-membership-title-wrapper">
							<span class="ur-membership-duration"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>

							<?php if ( 'free' !== $membership['type'] ) { ?>
								<div class="ur-membership-amount-wrapper">
									<span
										class="membership-amount">
										<?php echo esc_html( sprintf( '%s%.2f', $symbol, $membership['amount'] ) ); ?>
									</span>
									<span class="ur-membership-duration">
										<?php
										if ( $membership['time'] || $membership['subscription'] ) {
											echo ' / ' . ( 'subscription' === $membership['type'] ? esc_html( $membership['subscription']['value'] ) . ' ' . esc_html( ucfirst( $membership['subscription']['duration'] ) ) : esc_html( $membership['time'] ) ); }
										?>
									</span>
								</div>
							<?php } else { ?>
								<div class="ur-membership-amount-wrapper">

									<span
									class="membership-amount">
										<?php echo esc_html__( 'Free', 'user-registration' ); ?>
									</span>
								</div>
								<?php } ?>
							</div>
						</label>
					</div>
					<?php
				}
				echo '</div></div>';
				return;
			} elseif ( CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {
					echo '<div class="masteriyo-single-course-stats urm-masteriyo-membership-list">';
					echo '<span>' . esc_html__( 'This course is not associated with any membership.', 'user-registration' ) . '</span><br />';
					echo '</div>';
			} else {
				return;
			}
		}

		/**
		 * Adjust course query arguments on AJAX filtering.
		 *
		 * @param array $args Query args.
		 * @return array
		 */
		public function filter_ajax_prepare_query_args( $args ) {

			$price_type = isset( $_POST['price-type'] ) ? sanitize_text_field( $_POST['price-type'] ) : '';

			if ( empty( $price_type ) || 'all' === $price_type ) {

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

			if ( ! empty( $price_type ) ) {

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

		/**
		 * Filter related courses to show only membership-accessible or open courses.
		 *
		 * @param array $related_courses Course list.
		 * @param mixed $query WP_Query object.
		 * @param mixed $course Current course object.
		 * @return array
		 */
		public function get_related_courses( $related_courses, $query, $course ) {

			if ( is_null( $course ) ) {
				return $related_courses;
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

		/**
		 * Modify price HTML output based on membership access mode.
		 *
		 * @param string $html Price markup.
		 * @param mixed  $price Price value.
		 * @param array  $args Extra args.
		 * @param string $unformatted_price Raw price.
		 * @param mixed  $course Course object.
		 * @return string
		 */
		public function price_html( $html, $price, $args, $unformatted_price, $course ) {

			if ( is_null( $course ) ) {
				return $html;
			}

			if ( CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {
				$html = '';
			}

			return $html;
		}

		/**
		 * Modifies the add-to-cart URL based on membership restrictions.
		 *
		 * @param string $url Default URL.
		 * @param mixed  $course Course object.
		 * @return string
		 */
		public function modify_url( $url, $course ) {

			// Admins bypass restrictions
			if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
				$url = $course->get_permalink();

				if ( masteriyo_is_single_course_page() && ! Helper::check_course_access( $course ) ) {
					$url = Helper::get_checkout_page_url();
				}

				return $url;
			}

			// Logged-in users
			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {

				$user = masteriyo_get_current_user();
				$id   = is_a( $user, 'WP_User' ) ? $user->ID : $user->get_id();

				$user_registration_source = get_user_meta( $id, 'ur_registration_source', true );

				// Non-membership users
				if ( 'membership' !== $user_registration_source ) {

					if ( masteriyo_is_single_course_page() ) {
						$url = Helper::get_membership_upgrade_url();
					} else {
						$url = $course->get_permalink();
					}
				} else {

					// Membership users
					$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
					$memberships        = $members_repository->get_member_membership_by_id( $id );

					$compare_data = array(
						'courses' => array(),
						'status'  => array(),
					);

					foreach ( $memberships as $membership ) {
						$compare_data['status'][] = $membership['status'];
						if ( 'active' !== $membership['status'] ) {
							continue;
						}
						$access_course           = Helper::get_courses_based_on_membership( $membership['post_id'] );
						$compare_data['courses'] = array_merge( $compare_data['courses'], $access_course );
					}

					if ( empty( $compare_data['status'] ) || ! in_array( 'active', $compare_data['status'], true ) ) {

						if ( masteriyo_is_single_course_page() ) {
							$url = Helper::get_membership_upgrade_url();
						} else {
							$url = $course->get_permalink();
						}
					} else {
						$access_course = $compare_data['courses'];

						$course_id = is_a( $course, 'Masteriyo\Models\Course' ) ? $course->get_id() : $course->ID;

						$url = in_array( (string) $course_id, $access_course, true )
							? $url
							: ( masteriyo_is_single_course_page() ? Helper::get_membership_upgrade_url() : $course->get_permalink() );
					}
				}
			} elseif ( ! masteriyo_is_single_course_page() ) {

				$url = $course->get_permalink();
			}

			if ( ! is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() && masteriyo_is_single_course_page() ) {

				$url = Helper::get_checkout_page_url();
			}

			return $url;
		}

		/**
		 * Controls whether a user can start a course based on membership access rules.
		 *
		 * @param bool  $can_start_course Current access state.
		 * @param mixed $course Course object.
		 * @param mixed $user User object.
		 * @return bool
		 */
		public function can_start_course( $can_start_course, $course, $user ) {

			if ( $can_start_course ) {

				if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course->get_access_mode() ) {

					$id                       = is_a( $user, 'WP_User' ) ? $user->ID : $user->get_id();
					$user_registration_source = get_user_meta( $id, 'ur_registration_source', true );

					if ( 'membership' !== $user_registration_source ) {
						$can_start_course = false;
					} else {

						$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
						$memberships        = $members_repository->get_member_membership_by_id( $id );

						$compare_data = array(
							'courses' => array(),
							'status'  => array(),
						);

						foreach ( $memberships as $membership ) {
							$compare_data['status'][] = $membership['status'];
							if ( 'active' !== $membership['status'] ) {
								continue;
							}
							$access_course           = Helper::get_courses_based_on_membership( $membership['post_id'] );
							$compare_data['courses'] = array_merge( $compare_data['courses'], $access_course );
						}

						if ( empty( $compare_data['status'] ) || ! in_array( 'active', $compare_data['status'], true ) ) {
							$can_start_course = false;
						} else {

							$membership_courses = $compare_data['courses'];
							$course_id          = is_a( $course, 'Masteriyo\Models\Course' )
								? $course->get_id()
								: $course->ID;

							$can_start_course = in_array( (string) $course_id, $membership_courses, true );
						}
					}
				}
			}

			return $can_start_course;
		}

		/**
		 * Updates course CTA text (Join Now / Buy Now / Upgrade Now) depending on membership status.
		 *
		 * @param string $text Original button text.
		 * @param mixed  $course_obj Course object.
		 * @return string
		 */
		public function add_join_now_text( $text, $course_obj ) {

			if ( ! is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course_obj->get_access_mode() ) {

				$text = __( 'Join Now', 'learning-management-system' );

				if ( masteriyo_is_single_course_page() ) {
					$text = __( 'Sign Up', 'learning-management-system' );
				}
			}

			if ( is_user_logged_in() && CourseAccessMode::NEED_REGISTRATION === $course_obj->get_access_mode() ) {

				if ( ! Helper::check_course_access( $course_obj ) ) {
					$text = __( 'Join Now', 'learning-management-system' );
				}

				if ( masteriyo_is_single_course_page() && ! Helper::check_course_access( $course_obj ) ) {
					$text = __( 'Upgrade Now', 'learning-management-system' );
				}
			}

			return $text;
		}

		/**
		 * Adds a tax query filter for course visibility.
		 *
		 * @param array $args Query args.
		 * @return array
		 */
		public function add_tax_query( $args ) {

			$args['tax_query'] = array(
				'taxonomy' => Taxonomy::COURSE_VISIBILITY,
				'field'    => 'name',
				'terms'    => 'free',
			);

			return $args;
		}

		/**
		 * Adds metadata filter args into the Masteriyo loop.
		 *
		 * @param array $args Additional args.
		 * @return void
		 */
		public function add_args( $args = array() ) {
			$default_args = array(
				'meta_query' => array(
					'key'     => '_access_mode',
					'value'   => 'need_registration',
					'compare' => '=',
					'type'    => 'STRING',
				),
			);

			if ( isset( $GLOBALS['masteriyo_loop'] ) ) {
				$default_args              = array_merge( $default_args, $GLOBALS['masteriyo_loop'] );
				$GLOBALS['masteriyo_loop'] = wp_parse_args( $args, $default_args );
			}
		}

		public function get_current_user_access_course() {
			$user    = masteriyo_get_current_user();
			$user_id = is_a( $user, 'WP_User' ) ? $user->ID : $user->get_id();

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
endif;
