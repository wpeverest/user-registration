<?php
/**
 * Post Types Admin
 *
 * @class    URCR_Post_Types
 * @version  4.0
 * @package  UserRegistrationContentRestriction
 * @category Admin
 * @author   WPEverest
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Post_Types Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for post types.
 */
class URCR_Post_Types {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
	}

	/**
	 * Register post types.
	 *
	 * @since 4.0
	 */
	public static function register_post_types() {
		$post_type = 'urcr_access_rule';

		if ( ! is_blog_installed() || post_type_exists( $post_type ) ) {
			return;
		}

		$continue = apply_filters( 'urcr_pre_register_post_type', true, $post_type );

		if ( false === $continue ) {
			return;
		}

		do_action( 'urcr_pre_register_post_type', $post_type );

		register_post_type(
			$post_type,
			apply_filters(
				'urcr_urcr_access_rule_post_type',
				array(
					'labels'              => array(
						'name'               => esc_html__( 'Access Rules', 'user-registration' ),
						'singular_name'      => esc_html__( 'Access Rule', 'user-registration' ),
						'menu_name'          => esc_html_x( 'Access Rules', 'Admin menu name', 'user-registration' ),
						'add_new'            => esc_html__( 'Add content access rule', 'user-registration' ),
						'add_new_item'       => esc_html__( 'Add new content access rule', 'user-registration' ),
						'edit'               => esc_html__( 'Edit', 'user-content access rule' ),
						'edit_item'          => esc_html__( 'Edit content access rule', 'user-registration' ),
						'new_item'           => esc_html__( 'New content access rule', 'user-registration' ),
						'view'               => esc_html__( 'View content access rules', 'user-registration' ),
						'view_item'          => esc_html__( 'View content access rule', 'user-registration' ),
						'search_items'       => esc_html__( 'Search content access rules', 'user-registration' ),
						'not_found'          => esc_html__( 'No content access rules found', 'user-registration' ),
						'not_found_in_trash' => esc_html__( 'No content access rules found in trash', 'user-registration' ),
						'parent'             => esc_html__( 'Parent content access rule', 'user-registration' ),
					),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'post',
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

		do_action( 'urcr_post_register_post_type', $post_type );
	}
}

URCR_Post_Types::init();
