<?php
/**
 * Post Types Admin
 *
 * @class    UR_Post_Types
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Post_Types Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for post types.
 */
class UR_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'user_registration_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
		add_action( 'user_registration_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
		add_action( 'deleted_post', array( __CLASS__, 'handle_form_deletion' ), 10, 2 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'user_registration' ) ) {
			return;
		}
		/**
		 * Fires an action hook to perform additional tasks after registering the custom post type in User Registration.
		 *
		 * The 'user_registration_register_post_type' action allows developers to hook into the registration process
		 * and execute custom code or tasks after the custom post type is successfully registered.
		 */
		do_action( 'user_registration_register_post_type' );

		register_post_type(
			'user_registration',
			/**
			 * Applies a filter to customize the arguments for registering the 'user_registration' custom post type.
			 *
			 * @param array $default_args The default arguments for registering the 'user_registration' post type.
			 */
			apply_filters(
				'user_registration_post_type',
				array(
					'labels'              => array(
						'name'               => __( 'Registrations', 'user-registration' ),
						'singular_name'      => __( 'Registration', 'user-registration' ),
						'menu_name'          => _x( 'Registrations', 'Admin menu name', 'user-registration' ),
						'add_new'            => __( 'Add registration', 'user-registration' ),
						'add_new_item'       => __( 'Add new registration', 'user-registration' ),
						'edit'               => __( 'Edit', 'user-registration' ),
						'edit_item'          => __( 'Edit registration', 'user-registration' ),
						'new_item'           => __( 'New registration', 'user-registration' ),
						'view'               => __( 'View registrations', 'user-registration' ),
						'view_item'          => __( 'View registration', 'user-registration' ),
						'search_items'       => __( 'Search registrations', 'user-registration' ),
						'not_found'          => __( 'No registrations found', 'user-registration' ),
						'not_found_in_trash' => __( 'No registrations found in trash', 'user-registration' ),
						'parent'             => __( 'Parent registration', 'user-registration' ),
					),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'user_registration',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => false,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
				)
			)
		);
		/**
		 * Fires an action hook after completing the registration process of the custom post type in User Registration.
		 *
		 * The 'user_registration_after_register_post_type' action allows developers to hook into the registration process
		 * and execute custom code or tasks after all actions related to registering the custom post type are completed.
		 */
		do_action( 'user_registration_after_register_post_type' );
	}

	/**
	 * Flush rules if the event is queued.
	 *
	 * @since 1.2.0
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( ur_option_checked( 'user_registration_queue_flush_rewrite_rules' ) ) {
			update_option( 'user_registration_queue_flush_rewrite_rules', 'no' );
			self::flush_rewrite_rules();
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Handle form and page deletion and clean up related options.
	 * Only triggers when post is permanently deleted from trash, not when moved to trash.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function handle_form_deletion( $post_id, $post ) {
		// Handle user_registration form deletion
		if ( 'user_registration' === $post->post_type ) {
			$default_form_id = get_option( 'user_registration_default_form_page_id', 0 );

			if ( $default_form_id && $default_form_id == $post_id ) {
				delete_option( 'user_registration_default_form_page_id' );
				delete_option( 'user_registration_registration_form' );

				$default_membership_form_id = get_option( 'user_registration_default_membership_form_id', 0 );
				if ( $default_membership_form_id && $default_membership_form_id == $post_id ) {
					delete_option( 'user_registration_default_membership_form_id' );
				}
				delete_option( 'ur_membership_default_membership_field_name' );
			}
		}

		// Handle page deletion
		if ( 'page' === $post->post_type ) {
			// Define all page options that need to be checked
			$page_options = array(
				'user_registration_login_page_id',
				'user_registration_lost_password_page_id',
				'user_registration_reset_password_page_id',
				'user_registration_member_registration_page_id',
				'user_registration_thank_you_page_id',
				'user_registration_myaccount_page_id',
				'user_registration_membership_pricing_page_id',
			);

			// Check each option and delete if it matches the deleted page
			foreach ( $page_options as $option_name ) {
				$option_value = get_option( $option_name, 0 );
				if ( $option_value && $option_value == $post_id ) {
					delete_option( $option_name );
				}
			}
		}
	}
}

UR_Post_Types::init();
