<?php
/**
 * Getting Started REST API Controller.
 *
 * Handles the setup wizard endpoints for User Registration & Membership plugin.
 *
 * @since 4.0
 *
 * @package UserRegistration/Classes
 */

use WPEverest\URMembership\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

class UR_Getting_Started {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'user-registration/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'getting-started';

	/**
	 * Wizard steps.
	 *
	 * @var array
	 */
	protected static $steps = array(
		1 => 'welcome',
		2 => 'membership',
		3 => 'payment',
		4 => 'finish',
	);

	/**
	 * Option key used to store membership form id created in step 2.
	 */
	const OPTION_MEMBERSHIP_FORM_ID = 'urm_membership_default_form_page_id';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		// Get wizard state.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_wizard_state' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		// Step 1: Welcome.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/welcome',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_welcome_data' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_welcome_data' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
					'args'                => array(
						'membership_type' => array(
							'type'              => 'string',
							'required'          => true,
							'enum'              => array( 'paid_membership', 'free_membership', 'normal' ),
							'sanitize_callback' => 'sanitize_text_field',
						),
						'allow_usage_tracking' => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'allow_email_updates' => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'admin_email' => array(
							'type'              => 'string',
							'format'            => 'email',
							'required'          => false,
							'sanitize_callback' => 'sanitize_email',
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/memberships',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_memberships' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_memberships' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
			)
		);


		// Step 2: Content endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/content',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_available_content' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		// Step 3: Payments.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/payments',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_payment_settings' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_payment_settings' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
			)
		);

		// Step 4: Finish.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/finish',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_finish_data' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'complete_wizard' ),
					'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
				),
			)
		);

		// Skip step.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/skip',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'skip_step' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		// Navigate to step (back button).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/navigate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'navigate_to_step' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Wizard State
	|--------------------------------------------------------------------------
	*/

	public static function get_wizard_state( $request ) {
		$current_step     = self::get_current_step();
		$membership_type  = get_option( 'urm_onboarding_membership_type', '' );
		$is_completed     = ! get_option( 'user_registration_first_time_activation_flag', true );
		$is_skipped       = get_option( 'user_registration_onboarding_skipped', false );

		$wizard_state = array(
			'is_completed'    => $is_completed,
			'is_skipped'      => $is_skipped,
			'current_step'    => $current_step,
			'membership_type' => $membership_type,
			'steps'           => self::get_steps_with_status( $current_step, $membership_type ),
			'urls'            => array(
				'dashboard'         => admin_url( 'admin.php?page=user-registration' ),
				'registration_page' => self::get_registration_page_url(),
				'settings'          => admin_url( 'admin.php?page=user-registration-settings' ),
				'memberships'       => admin_url( 'admin.php?page=user-registration-membership' ),
			),
		);

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $wizard_state,
			),
			200
		);
	}

	protected static function get_steps_with_status( $current_step, $membership_type ) {
		$steps_config = array(
			1 => array( 'id' => 'welcome',     'label' => __( 'Welcome', 'user-registration' ) ),
			2 => array( 'id' => 'membership',  'label' => __( 'Membership', 'user-registration' ) ),
			3 => array( 'id' => 'payment',     'label' => __( 'Payment', 'user-registration' ) ),
			4 => array( 'id' => 'finish',      'label' => __( 'Finish', 'user-registration' ) ),
		);

		$steps = array();

		foreach ( $steps_config as $step_number => $step ) {
			$is_accessible = self::is_step_accessible( $step_number, $membership_type );

			$steps[] = array(
				'step'          => $step_number,
				'id'            => $step['id'],
				'label'         => $step['label'],
				'is_complete'   => $step_number < $current_step,
				'is_current'    => $step_number === $current_step,
				'is_accessible' => $is_accessible,
			);
		}

		return $steps;
	}

	protected static function is_step_accessible( $step_number, $membership_type ) {
		if ( 2 === $step_number && 'normal' === $membership_type ) {
			return false;
		}
		if ( 3 === $step_number && 'paid_membership' !== $membership_type ) {
			return false;
		}
		return true;
	}

	/*
	|--------------------------------------------------------------------------
	| Step 1: Welcome
	|--------------------------------------------------------------------------
	*/

	public static function get_welcome_data( $request ) {
		$data = array(
			'membership_type'      => get_option( 'urm_onboarding_membership_type', '' ),
			'allow_usage_tracking' => get_option( 'user_registration_allow_usage_tracking', false ),
			'allow_email_updates'  => get_option( 'user_registration_allow_email_updates', true ),
			'admin_email'          => get_option( 'user_registration_updates_admin_email', get_option( 'admin_email' ) ),
			'membership_options'   => array(
				array(
					'value'       => 'paid_membership',
					'label'       => __( 'Paid Membership', 'user-registration' ),
					'description' => __( 'Paid members can access protected content. Choose this even if you have combination of both free and paid.', 'user-registration' ),
				),
				array(
					'value'       => 'free_membership',
					'label'       => __( 'Free Membership', 'user-registration' ),
					'description' => __( 'Registered users can access protected content.', 'user-registration' ),
				),
				array(
					'value'       => 'normal',
					'label'       => __( 'Other URM Features (no membership now)', 'user-registration' ),
					'description' => __( 'I want registration and other features without membership.', 'user-registration' ),
				),
			),
		);

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $data,
			),
			200
		);
	}

	/**
	 * Step 1 (POST): Save settings + create ONLY initial/common pages.
	 * IMPORTANT: DO NOT create membership pages/tables/features here.
	 */
	public static function save_welcome_data( $request ) {
		$membership_type      = isset( $request['membership_type'] ) ? sanitize_text_field( $request['membership_type'] ) : '';
		$allow_usage_tracking = isset( $request['allow_usage_tracking'] ) ? $request['allow_usage_tracking'] : false;
		$allow_email_updates  = isset( $request['allow_email_updates'] ) ? $request['allow_email_updates'] : false;
		$admin_email          = isset( $request['admin_email'] ) ? sanitize_email( $request['admin_email'] ) : get_option( 'admin_email' );

		if ( empty( $membership_type ) || ! in_array( $membership_type, array( 'paid_membership', 'free_membership', 'normal' ), true ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Please select a valid membership type.', 'user-registration' ),
				),
				400
			);
		}

		update_option( 'urm_onboarding_membership_type', $membership_type );

		$tracking_value = ur_string_to_bool( $allow_usage_tracking ) ? true : false;
		$email_value    = ur_string_to_bool( $allow_email_updates ) ? true : false;

		update_option( 'user_registration_allow_usage_tracking', $tracking_value );
		update_option( 'user_registration_allow_email_updates', $email_value );

		if ( ! empty( $admin_email ) ) {
			update_option( 'user_registration_updates_admin_email', $admin_email );
		}

		if ( $email_value ) {
			$email_to_send = ! empty( $admin_email ) ? $admin_email : get_option( 'admin_email' );
			self::send_email_to_tracking_server( $email_to_send );
		}

		$page_details = self::install_initial_pages();

		$next_step = self::calculate_next_step( 1, $membership_type );
		self::update_current_step( $next_step );
	
		return new \WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( 'Welcome settings saved successfully.', 'user-registration' ),
				'page_details' => $page_details,
				'next_step'    => $next_step,
			),
			200
		);
	}

	/**
	 * Create only common/initial pages (and normal default form).
	 * No membership pages/features/tables here.
	 */
	protected static function install_initial_pages() {
		update_option( 'users_can_register', true );
		update_option( 'user_registration_login_options_prevent_core_login', true );

		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		$page_details = array();

		$normal_form_id = self::ensure_default_form( 'normal' );

		$page_details['default_form_id'] = array(
			'title'         => esc_html__( 'Default Registration Form', 'user-registration' ),
			'page_url'      => admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $normal_form_id ),
			'page_url_text' => esc_html__( 'View Form', 'user-registration' ),
			'page_slug'     => sprintf( esc_html__( 'Form Id: %s', 'user-registration' ), $normal_form_id ),
			'status'        => 'enabled',
			'status_label'  => esc_html__( 'Ready to use', 'user-registration' ),
		);

		$pages = array(
			'registration' => array(
				'name'    => _x( 'registration', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Registration', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $normal_form_id ) . '"]',
			),
			'login' => array(
				'name'    => _x( 'login', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Login', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_login_shortcode_tag', 'user_registration_login' ) . ']',
			),
			'myaccount' => array(
				'name'    => _x( 'my-account', 'Page slug', 'user-registration' ),
				'title'   => _x( 'My Account', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_my_account_shortcode_tag', 'user_registration_my_account' ) . ']',
			),
			'lost_password' => array(
				'name'    => _x( 'lost-password', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Lost Password', 'Page title', 'user-registration' ),
				'content' => '[user_registration_lost_password]',
			),
		);

		foreach ( $pages as $key => $page ) {
			$post_id = ur_create_page(
				esc_sql( $page['name'] ),
				'user_registration_' . $key . '_page_id',
				wp_kses_post( $page['title'] ),
				wp_kses_post( $page['content'] )
			);

			if ( 'login' === $key ) {
				update_option( 'user_registration_login_options_login_redirect_url', $post_id );
			}

			$page_details[ get_post_field( 'post_name', $post_id ) ] = array(
				'page_url'      => get_permalink( $post_id ),
				'page_url_text' => esc_html__( 'View Page', 'user-registration' ),
				'title'         => get_the_title( $post_id ) . esc_html__( ' Page', 'user-registration' ),
				'page_slug'     => '/' . get_post_field( 'post_name', $post_id ),
			);
		}

		update_option( 'user_registration_membership_installed_flag', false );

		return $page_details;
	}

	/**
	 * Ensure default form exists.
	 * - normal: creates normal default form and stores in `user_registration_default_form_page_id`.
	 * - membership: creates membership form and stores in `urm_membership_default_form_page_id` (does NOT overwrite normal default form).
	 */
	protected static function ensure_default_form( $mode = 'normal' ) {
		$is_membership = ( 'membership' === $mode );

		if ( $is_membership ) {
			$existing = (int) get_option( self::OPTION_MEMBERSHIP_FORM_ID, 0 );
			if ( $existing > 0 ) {
				return $existing;
			}
		} else {
			$existing = (int) get_option( 'user_registration_default_form_page_id', 0 );
			if ( $existing > 0 ) {
				return $existing;
			}
		}

		$hasposts = get_posts( 'post_type=user_registration' );

		$membership_field_name = 'membership_field_' . ur_get_random_number();

		$post_content = $is_membership
			? '[[[{"field_key":"user_login","general_setting":{"label":"Username","description":"","field_name":"user_login","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":"","username_length":"","username_character":"1"},"icon":"ur-icon ur-icon-user"}],[{"field_key":"user_email","general_setting":{"label":"User Email","description":"","field_name":"user_email","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-email"}]],[[{"field_key":"user_pass","general_setting":{"label":"User Password","description":"","field_name":"user_pass","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password"}],[{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","description":"","field_name":"user_confirm_password","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password-confirm"}]],[[{"field_key":"membership","general_setting":{"label":"Membership Field","description":"","field_name":"' . $membership_field_name . '","placeholder":"","required":"false","hide_label":"false","membership_listing_option":"all"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-membership-field"}]]]'
			: '[[[{"field_key":"user_login","general_setting":{"label":"Username","description":"","field_name":"user_login","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":"","username_length":"","username_character":"1"},"icon":"ur-icon ur-icon-user"}],[{"field_key":"user_email","general_setting":{"label":"User Email","description":"","field_name":"user_email","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-email"}]],[[{"field_key":"user_pass","general_setting":{"label":"User Password","description":"","field_name":"user_pass","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password"}],[{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","description":"","field_name":"user_confirm_password","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password-confirm"}]]]';

		$title = $is_membership
			? esc_html__( 'Default Membership Registration Form', 'user-registration' )
			: esc_html__( 'Default Form', 'user-registration' );

		$new_id = wp_insert_post(
			array(
				'post_type'      => 'user_registration',
				'post_title'     => $title,
				'post_content'   => $post_content,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		if ( $is_membership ) {
			update_option( self::OPTION_MEMBERSHIP_FORM_ID, (int) $new_id );
			update_option( 'ur_membership_default_membership_field_name', $membership_field_name );
		} else {
			update_option( 'user_registration_default_form_page_id', (int) $new_id );
		}

		return (int) $new_id;
	}

	/*
	|--------------------------------------------------------------------------
	| Step 2: Memberships
	|--------------------------------------------------------------------------
	*/

	public static function get_memberships( $request ) {
		die;
		$membership_type = get_option( 'urm_onboarding_membership_type', 'free_membership' );
		$memberships     = self::fetch_memberships();


		$default_type = 'paid_membership' === $membership_type ? 'paid' : 'free';

		return new \WP_REST_Response(
			array(
				'success'           => true,
				'memberships'       => $memberships,
				'membership_type'   => $membership_type,
				'default_plan_type' => $default_type,
				'can_create_paid'   => 'paid_membership' === $membership_type,
			),
			200
		);
	}

	protected static function fetch_memberships() {
		if ( class_exists( 'WPEverest\URMembership\Admin\Database\Database' ) ) {
			$db = new Database();
			if ( method_exists( $db, 'get_all_memberships' ) ) {
				return $db->get_all_memberships();
			}
		}

		$posts = get_posts(
			array(
				'post_type'      => 'ur_membership',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$memberships = array();

		foreach ( $posts as $post ) {
			$memberships[] = array(
				'id'     => $post->ID,
				'name'   => $post->post_title,
				'type'   => get_post_meta( $post->ID, '_ur_membership_type', true ) ?: 'free',
				'status' => get_post_meta( $post->ID, '_ur_membership_status', true ) ?: 'active',
				'access' => get_post_meta( $post->ID, '_ur_membership_access_rules', true ) ?: array(),
			);
		}

		return $memberships;
	}

		/**
		 * Step 2 (POST): Save memberships.
		 *
		 * @since x.x.x
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public static function save_memberships( $request ) {

			$memberships = $request->get_param( 'memberships' );
			if ( ! is_array( $memberships ) ) {
				$memberships = array();
			}

			$membership_type = get_option( 'urm_onboarding_membership_type', 'free_membership' );

			if ( empty( $memberships ) ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => __( 'Please provide at least one membership.', 'user-registration' ),
					),
					400
				);
			}

			$allowed_type = 'paid_membership' === $membership_type ? array( 'free', 'paid' ) : array( 'free' );

			$results = array(
				'created' => array(),
				'updated' => array(),
				'errors'  => array(),
			);

			foreach ( $memberships as $index => $membership ) {
				$plan_type = isset( $membership['type'] ) ? sanitize_text_field( $membership['type'] ) : 'free';

				if ( ! in_array( $plan_type, $allowed_type, true ) ) {
					$results['errors'][] = array(
						'index'   => $index,
						'name'    => $membership['name'] ?? '',
						'message' => sprintf(
							__( 'Invalid membership type: %s. Only %s memberships are allowed based on your selection.', 'user-registration' ),
							$plan_type,
							implode( ' or ', $allowed_type )
						),
					);
					continue;
				}

				$result = self::save_single_membership( $membership );

				if ( is_wp_error( $result ) ) {
					$results['errors'][] = array(
						'index'   => $index,
						'name'    => $membership['name'] ?? '',
						'message' => $result->get_error_message(),
					);
				} elseif ( ! empty( $membership['id'] ) ) {
					$results['updated'][] = $result;
				} else {
					$results['created'][] = $result;
				}
			}

			$page_details = array();
			if ( in_array( $membership_type, array( 'paid_membership', 'free_membership' ), true ) ) {
				$page_details = self::install_membership_setup_pages();
			}

			$all_ids = array_merge( $results['created'], $results['updated'] );
			update_option( 'urm_onboarding_membership_ids', $all_ids );

			$next_step = self::calculate_next_step( 2, $membership_type );
			self::update_current_step( $next_step );

			return new \WP_REST_Response(
				array(
					'success'        => true,
					'message'        => __( 'Memberships saved successfully.', 'user-registration' ),
					'created_count'  => count( $results['created'] ),
					'updated_count'  => count( $results['updated'] ),
					'membership_ids' => $all_ids,
					'errors'         => $results['errors'],
					'page_details'   => $page_details,
					'next_step'      => $next_step,
				),
				200
			);
		}


	/**
 * Save a single membership from the setup wizard.
 *
 * Accepts the simplified wizard payload and converts it to the
 * standard membership structure used by the Membership module.
 *
 * @since 4.5.0
 *
 * @param array $membership
 *
 * @return int|\WP_Error Membership post ID on success, WP_Error on failure.
 */
protected static function save_single_membership( $membership ) {
	$membership_id = ! empty( $membership['id'] ) ? absint( $membership['id'] ) : 0;

	if ( empty( $membership['name'] ) ) {
		return new \WP_Error(
			'missing_name',
			__( 'Membership name is required.', 'user-registration' )
		);
	}

	$type         = ! empty( $membership['type'] ) ? sanitize_text_field( $membership['type'] ) : 'free';
	$amount       = isset( $membership['price'] ) ? floatval( $membership['price'] ) : 0;
	$currency     = ! empty( $membership['currency'] ) ? sanitize_text_field( $membership['currency'] ) : 'USD';
	$billing      = ! empty( $membership['billing_period'] ) ? sanitize_text_field( $membership['billing_period'] ) : 'yearly';

	$meta = array(
		'type'           => $type,
		'amount'         => $amount,
		'currency'       => $currency,
		'payment_gateways' => array(),
	);

	if ( 'weekly' === $billing ) {
		$meta['subscription'] = array(
			'value'    => 1,
			'duration' => 'week',
		);
	} elseif ( 'monthly' === $billing ) {
		$meta['subscription'] = array(
			'value'    => 1,
			'duration' => 'month',
		);
	} elseif ( 'yearly' === $billing ) {
		$meta['subscription'] = array(
			'value'    => 1,
			'duration' => 'year',
		);
	}

	$data = array(
		'post_data'      => array(
			'ID'          => $membership_id,
			'name'        => $membership['name'],
			'status'      => true,
			'description' => '',
		),
		'post_meta_data' => $meta,
	);

	$service  = new \WPEverest\URMembership\Admin\Services\MembershipService();
	$prepared = $service->prepare_membership_post_data( $data );

	if ( isset( $prepared['status'] ) && ! $prepared['status'] ) {
		$message = ! empty( $prepared['message'] )
			? $prepared['message']
			: __( 'Invalid membership data.', 'user-registration' );

		return new \WP_Error( 'invalid_membership', $message );
	}

	$prepared = apply_filters( 'ur_membership_after_create_membership_data_prepare', $prepared );

	if ( $membership_id ) {
		$result = wp_update_post( $prepared['post_data'], true );
	} else {
		$result        = wp_insert_post( $prepared['post_data'], true );
		$membership_id = $result;
	}

	if ( is_wp_error( $result ) || ! $result ) {
		return $result;
	}

	if ( ! empty( $prepared['post_meta_data'] ) ) {
		foreach ( $prepared['post_meta_data'] as $meta_data ) {
			if ( isset( $meta_data['meta_key'], $meta_data['meta_value'] ) ) {
				update_post_meta( $membership_id, $meta_data['meta_key'], $meta_data['meta_value'] );
			}
		}
	}

	if ( ! empty( $membership['access'] ) ) {
		self::sync_membership_access_rule( $membership_id, $membership['access'] );
	}

	return $membership_id;
}



	protected static function save_membership_access_rules( $membership_id, $access_rules ) {
		if ( empty( $access_rules ) || ! is_array( $access_rules ) ) {
			delete_post_meta( $membership_id, '_ur_membership_access_rules' );
			return true;
		}

		$formatted_rules = array();

		foreach ( $access_rules as $rule ) {
			if ( empty( $rule['type'] ) || empty( $rule['value'] ) ) {
				continue;
			}
			$formatted_rules[] = array(
				'type'  => sanitize_text_field( $rule['type'] ),
				'value' => array_map( 'sanitize_text_field', (array) $rule['value'] ),
			);
		}

		update_post_meta( $membership_id, '_ur_membership_access_rules', $formatted_rules );
		return true;
	}

	/**
 * Sync membership access rules with Content Restriction engine.
 *
 * @since x.x.x
 *
 * @param int   $membership_id Membership ID.
 * @param array $access_rules  Access rules from wizard.
 * @return void
 */
protected static function sync_membership_access_rule( $membership_id, $access_rules ) {
	if ( empty( $membership_id ) || empty( $access_rules ) || ! is_array( $access_rules ) ) {
		return;
	}

	if ( ! function_exists( 'urcr_create_or_update_membership_rule' ) ) {
		return;
	}

	$enabled_features    = get_option( 'user_registration_enabled_features', array() );
	$required_features   = array(
		'user-registration-membership',
		'user-registration-content-restriction',
	);
	$features_changed    = false;

	foreach ( $required_features as $feature ) {
		if ( ! in_array( $feature, $enabled_features, true ) ) {
			$enabled_features[] = $feature;
			$features_changed   = true;
		}
	}

	if ( $features_changed ) {
		update_option( 'user_registration_enabled_features', $enabled_features );
	}

	$access_rule_data = array(
		'enabled'        => 1,
		'access_control' => 'access',
		'logic_map'      => array(
			'type'       => 'group',
			'id'         => 'x' . ( time() * 1000 ),
			'conditions' => array(),
			'logic_gate' => 'AND',
		),
		'target_contents' => array(),
		'actions'         => array(
			array(
				'id'             => 'x' . ( time() * 1000 ),
				'type'           => 'message',
				'access_control' => 'access',
				'label'          => __( 'Show Message', 'user-registration' ),
				'message'        => __( 'You do not have sufficient permission to access this content.', 'user-registration' ),
				'redirect_url'   => '',
				'local_page'     => '',
				'ur_form'        => '',
				'shortcode'      => array(
					'tag'  => '',
					'args' => '',
				),
			),
		),
	);

	foreach ( $access_rules as $index => $rule ) {
		if ( empty( $rule['type'] ) || empty( $rule['value'] ) ) {
			continue;
		}

		$type    = $rule['type'];
		$cr_type = '';

		if ( 'pages' === $type ) {
			$cr_type = 'wp_pages';
		} elseif ( 'posts' === $type ) {
			$cr_type = 'wp_posts';
		}

		if ( ! $cr_type ) {
			continue;
		}

		$access_rule_data['target_contents'][] = array(
			'id'    => 'x' . ( time() * 1000 ) . '_' . $index,
			'type'  => $cr_type,
			'value' => array_map( 'intval', (array) $rule['value'] ),
		);
	}

	$rule_data = array(
		'title'            => '',
		'access_rule_data' => $access_rule_data,
		'rule_type'        => 'membership',
		'membership_id'    => $membership_id,
	);

	urcr_create_or_update_membership_rule( $membership_id, $rule_data );
}


	/**
	 * Create membership-related setup ONLY in step 2.
	 * - enable features
	 * - create tables
	 * - create membership form
	 * - create membership pages
	 *
	 * Idempotent: won’t create again if already created.
	 */
	protected static function install_membership_setup_pages() {
		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		$existing_member_reg = (int) get_option( 'user_registration_member_registration_page_id', 0 );
		if ( $existing_member_reg > 0 ) {
			return array();
		}

		$enabled_features    = get_option( 'user_registration_enabled_features', array() );
		$membership_features = array(
			'user-registration-membership',
			'user-registration-payment-history',
			'user-registration-content-restriction',
		);

		foreach ( $membership_features as $feature ) {
			if ( ! in_array( $feature, $enabled_features, true ) ) {
				$enabled_features[] = $feature;
			}
		}

		update_option( 'user_registration_enabled_features', $enabled_features );
		update_option( 'user_registration_membership_installed_flag', true );
		update_option( 'urm_initial_registration_type', 'membership' );

		if ( class_exists( 'WPEverest\URMembership\Admin\Database\Database' ) ) {
			Database::create_tables();
		}

		$membership_form_id = self::ensure_default_form( 'membership' );

		$pages = array(
			'membership_registration' => array(
				'name'    => _x( 'membership-registration', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Membership Registration', 'Page title', 'user-registration' ),
				'option'  => 'user_registration_member_registration_page_id',
				'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $membership_form_id ) . '"]',
			),
			'membership_pricing' => array(
				'name'    => _x( 'membership-pricing', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Membership Pricing', 'Page title', 'user-registration' ),
				'content' => '[user_registration_groups]',
			),
			'membership_thankyou' => array(
				'name'    => _x( 'membership-thankyou', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Membership ThankYou', 'Page title', 'user-registration' ),
				'option'  => 'user_registration_thank_you_page_id',
				'content' => '[user_registration_membership_thank_you]',
			),
		);

		$page_details = array();

		foreach ( $pages as $key => $page ) {
			$post_id = ur_create_page(
				esc_sql( $page['name'] ),
				'user_registration_' . $key . '_page_id',
				wp_kses_post( $page['title'] ),
				wp_kses_post( $page['content'] )
			);

			if ( ! empty( $page['option'] ) ) {
				update_option( $page['option'], $post_id );
			}

			$page_details[ get_post_field( 'post_name', $post_id ) ] = array(
				'page_url'      => get_permalink( $post_id ),
				'page_url_text' => esc_html__( 'View Page', 'user-registration' ),
				'title'         => get_the_title( $post_id ) . esc_html__( ' Page', 'user-registration' ),
				'page_slug'     => '/' . get_post_field( 'post_name', $post_id ),
			);
		}

		return $page_details;
	}

	/**
	 * Content endpoint.
	 */
	public static function get_available_content( $request ) {
		$content = array(
			'posts' => self::get_available_posts(),
			'pages' => self::get_available_pages(),
		);

		return new \WP_REST_Response(
			array(
				'success' => true,
				'content' => $content,
			),
			200
		);
	}

	protected static function get_available_posts() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		return array_map(
			function ( $post ) {
				return array(
					'value' => $post->ID,
					'label' => $post->post_title,
				);
			},
			$posts
		);
	}

	protected static function get_available_pages() {
		$pages = get_pages(
			array(
				'post_status' => 'publish',
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);

		if ( function_exists( 'urcr_get_excluded_page_ids' ) ) {
			$excluded_page_ids = urcr_get_excluded_page_ids();

			$pages = array_filter(
				$pages,
				function ( $page ) use ( $excluded_page_ids ) {
					return ! in_array( $page->ID, $excluded_page_ids, true );
				}
			);

			$pages = array_values( $pages );
		}

		return array_map(
			function ( $page ) {
				return array(
					'value' => $page->ID,
					'label' => $page->post_title,
				);
			},
			$pages
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Step 3: Payments
	|--------------------------------------------------------------------------
	*/

	public static function get_payment_settings( $request ) {
		$gateways = array(
			array(
				'id'           => 'offline_payment',
				'label'        => __( 'Offline Payment', 'user-registration' ),
				'description'  => __( 'Accept payments manually via bank transfer, check, or cash.', 'user-registration' ),
				'enabled'      => self::get_bool_option( 'urm_payment_offline_enabled' ),
				'configured'   => true,
				'settings_url' => '',
			),
			array(
				'id'           => 'paypal',
				'label'        => __( 'PayPal', 'user-registration' ),
				'description'  => __( 'Accept payments via PayPal.', 'user-registration' ),
				'enabled'      => self::get_bool_option( 'urm_payment_paypal_enabled' ),
				'configured'   => self::is_gateway_configured( 'paypal' ),
				'settings_url' => admin_url( 'admin.php?page=user-registration-settings&tab=ur_membership&section=payment_settings' ),
			),
			array(
				'id'           => 'stripe',
				'label'        => __( 'Stripe', 'user-registration' ),
				'description'  => __( 'Accept credit card payments via Stripe.', 'user-registration' ),
				'enabled'      => self::get_bool_option( 'urm_payment_stripe_enabled' ),
				'configured'   => self::is_gateway_configured( 'stripe' ),
				'settings_url' => admin_url( 'admin.php?page=user-registration-settings&tab=ur_membership&section=payment_settings' ),
			),
		);

		return new \WP_REST_Response(
			array(
				'success'          => true,
				'payment_gateways' => $gateways,
			),
			200
		);
	}

	public static function save_payment_settings( $request ) {
		$offline_payment = isset( $request['offline_payment'] ) ? (bool) $request['offline_payment'] : false;
		$paypal          = isset( $request['paypal'] ) ? (bool) $request['paypal'] : false;
		$stripe          = isset( $request['stripe'] ) ? (bool) $request['stripe'] : false;

		update_option( 'urm_payment_offline_enabled', $offline_payment );
		update_option( 'urm_payment_paypal_enabled', $paypal );
		update_option( 'urm_payment_stripe_enabled', $stripe );

		$enabled_gateways = array();
		if ( $offline_payment ) {
			$enabled_gateways[] = 'offline';
		}
		if ( $paypal ) {
			$enabled_gateways[] = 'paypal';
		}
		if ( $stripe ) {
			$enabled_gateways[] = 'stripe';
		}

		update_option( 'urm_enabled_payment_gateways', $enabled_gateways );

		$configuration_needed = array();

		if ( $paypal && ! self::is_gateway_configured( 'paypal' ) ) {
			$configuration_needed[] = array(
				'gateway'      => 'paypal',
				'message'      => __( 'PayPal requires API credentials configuration.', 'user-registration' ),
				'settings_url' => admin_url( 'admin.php?page=user-registration-settings&tab=ur_membership&section=payment_settings' ),
			);
		}

		if ( $stripe && ! self::is_gateway_configured( 'stripe' ) ) {
			$configuration_needed[] = array(
				'gateway'      => 'stripe',
				'message'      => __( 'Stripe requires API keys configuration.', 'user-registration' ),
				'settings_url' => admin_url( 'admin.php?page=user-registration-settings&tab=ur_membership&section=payment_settings' ),
			);
		}

		$next_step = 4;
		self::update_current_step( $next_step );

		return new \WP_REST_Response(
			array(
				'success'              => true,
				'message'              => __( 'Payment settings saved successfully.', 'user-registration' ),
				'enabled_gateways'     => $enabled_gateways,
				'configuration_needed' => $configuration_needed,
				'next_step'            => $next_step,
			),
			200
		);
	}

	protected static function is_gateway_configured( $gateway ) {
		switch ( $gateway ) {
			case 'paypal':
				$client_id     = get_option( 'urm_paypal_client_id', '' );
				$client_secret = get_option( 'urm_paypal_secret_key', '' );
				return ! empty( $client_id ) && ! empty( $client_secret );

			case 'stripe':
				$publishable_key = get_option( 'urm_stripe_publishable_key', '' );
				$secret_key      = get_option( 'urm_stripe_secret_key', '' );
				return ! empty( $publishable_key ) && ! empty( $secret_key );

			default:
				return true;
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Step 4: Finish
	|--------------------------------------------------------------------------
	*/

	public static function get_finish_data( $request ) {
		$membership_type  = get_option( 'urm_onboarding_membership_type', '' );
		$membership_ids   = get_option( 'urm_onboarding_membership_ids', array() );
		$enabled_gateways = get_option( 'urm_enabled_payment_gateways', array() );

		$is_membership = in_array( $membership_type, array( 'paid_membership', 'free_membership' ), true );

		$pages = array();

		if ( $is_membership ) {
			$pages['membership_registration'] = array(
				'id'    => get_option( 'user_registration_member_registration_page_id', 0 ),
				'label' => __( 'Membership Registration', 'user-registration' ),
				'url'   => self::get_page_url( 'user_registration_member_registration_page_id' ),
			);
			$pages['membership_pricing'] = array(
				'id'    => get_option( 'user_registration_membership_pricing_page_id', 0 ),
				'label' => __( 'Membership Pricing', 'user-registration' ),
				'url'   => self::get_page_url( 'user_registration_membership_pricing_page_id' ),
			);
		} else {
			$pages['registration'] = array(
				'id'    => get_option( 'user_registration_registration_page_id', 0 ),
				'label' => __( 'Registration', 'user-registration' ),
				'url'   => self::get_page_url( 'user_registration_registration_page_id' ),
			);
		}

		$pages['login'] = array(
			'id'    => get_option( 'user_registration_login_page_id', 0 ),
			'label' => __( 'Login', 'user-registration' ),
			'url'   => self::get_page_url( 'user_registration_login_page_id' ),
		);

		$pages['my_account'] = array(
			'id'    => get_option( 'user_registration_myaccount_page_id', 0 ),
			'label' => __( 'My Account', 'user-registration' ),
			'url'   => self::get_page_url( 'user_registration_myaccount_page_id' ),
		);

		$summary = array(
			'membership_type'     => $membership_type,
			'memberships_created' => count( $membership_ids ),
			'enabled_gateways'    => $enabled_gateways,
			'pages'               => $pages,
			'links'               => array(
				'registration_page' => self::get_registration_page_url(),
				'dashboard'         => admin_url( 'admin.php?page=user-registration' ),
				'settings'          => admin_url( 'admin.php?page=user-registration-settings' ),
				'memberships'       => admin_url( 'admin.php?page=user-registration-membership' ),
				'forms'             => admin_url( 'admin.php?page=user-registration' ),
			),
		);

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $summary,
			),
			200
		);
	}

	public static function complete_wizard( $request ) {
		update_option( 'user_registration_first_time_activation_flag', false );
		update_option( 'user_registration_onboarding_skipped', false );
		delete_option( 'user_registration_onboarding_skipped_step' );
		update_option( 'urm_onboarding_completed_at', current_time( 'mysql' ) );
		self::update_current_step( 4 );

		do_action( 'user_registration_getting_started_completed' );

		return new \WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( "Success! You're all set!", 'user-registration' ),
				'redirect_url' => admin_url( 'admin.php?page=user-registration' ),
				'links'        => array(
					'registration_page' => self::get_registration_page_url(),
					'dashboard'         => admin_url( 'admin.php?page=user-registration' ),
				),
			),
			200
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Navigation & Skip
	|--------------------------------------------------------------------------
	*/

	public static function skip_step( $request ) {
		$current_step    = isset( $request['step'] ) ? absint( $request['step'] ) : self::get_current_step();
		$membership_type = get_option( 'urm_onboarding_membership_type', '' );

		$next_step = self::calculate_next_step( $current_step, $membership_type );
		self::update_current_step( $next_step );

		$skipped_steps   = get_option( 'user_registration_onboarding_skipped_steps', array() );
		$skipped_steps[] = $current_step;
		update_option( 'user_registration_onboarding_skipped_steps', array_unique( $skipped_steps ) );

		if ( 4 === $next_step ) {
			update_option( 'user_registration_onboarding_skipped', true );
			update_option( 'user_registration_onboarding_skipped_step', $current_step );
		}

		return new \WP_REST_Response(
			array(
				'success'   => true,
				'message'   => __( 'Step skipped.', 'user-registration' ),
				'next_step' => $next_step,
			),
			200
		);
	}

	public static function navigate_to_step( $request ) {
		$target_step     = isset( $request['step'] ) ? absint( $request['step'] ) : 1;
		$current_step    = self::get_current_step();
		$membership_type = get_option( 'urm_onboarding_membership_type', '' );

		if ( ! self::is_step_accessible( $target_step, $membership_type ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'This step is not accessible with current settings.', 'user-registration' ),
				),
				400
			);
		}

		if ( $target_step > $current_step ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Cannot skip ahead to incomplete steps.', 'user-registration' ),
				),
				400
			);
		}

		self::update_current_step( $target_step );

		return new \WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( 'Navigation successful.', 'user-registration' ),
				'current_step' => $target_step,
			),
			200
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Helper Methods
	|--------------------------------------------------------------------------
	*/

	protected static function get_current_step() {
		return (int) get_option( 'urm_onboarding_current_step', 1 );
	}

	protected static function update_current_step( $step ) {
		return update_option( 'urm_onboarding_current_step', (int) $step );
	}

	protected static function calculate_next_step( $current_step, $membership_type ) {
		$next_step = $current_step + 1;

		if ( 2 === $next_step && 'normal' === $membership_type ) {
			$next_step = 4;
		}

		if ( 3 === $next_step && 'paid_membership' !== $membership_type ) {
			$next_step = 4;
		}

		return min( $next_step, 4 );
	}

	protected static function get_bool_option( $option_name ) {
		$value = get_option( $option_name, false );
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( $value, array( 'yes', '1', 1, true ), true );
	}

	protected static function get_page_url( $option_key ) {
		$page_id = (int) get_option( $option_key, 0 );
		if ( ! $page_id ) {
			return null;
		}
		return get_permalink( $page_id );
	}

	/**
	 * Prefer membership registration page if it exists, otherwise normal registration page.
	 */
	protected static function get_registration_page_url() {
		$member_page_id = (int) get_option( 'user_registration_member_registration_page_id', 0 );
		if ( $member_page_id > 0 ) {
			return get_permalink( $member_page_id );
		}

		$reg_page_id = (int) get_option( 'user_registration_registration_page_id', 0 );
		return $reg_page_id > 0 ? get_permalink( $reg_page_id ) : null;
	}

	private static function send_email_to_tracking_server( $email ) {
		if ( empty( $email ) || ! is_email( $email ) ) {
			return;
		}

		wp_remote_post(
			'https://stats.wpeverest.com/wp-json/tgreporting/v1/process-email/',
			array(
				'method'      => 'POST',
				'timeout'     => 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'headers'     => array(
					'user-agent' => 'UserRegistration/' . ( function_exists( 'UR' ) ? UR()->version : '1.0.0' ) . '; ' . get_bloginfo( 'url' ),
				),
				'body'        => array(
					'data' => array(
						'email'       => sanitize_email( $email ),
						'website_url' => get_bloginfo( 'url' ),
						'plugin_name' => ( defined( 'UR_PRO_ACTIVE' ) && UR_PRO_ACTIVE ) ? 'User Registration PRO' : 'User Registration',
						'plugin_slug' => defined( 'UR_PLUGIN_FILE' ) ? plugin_basename( UR_PLUGIN_FILE ) : 'user-registration',
					),
				),
			)
		);
	}

	public static function check_admin_permissions( $request ) {
		return true;
	}
}
