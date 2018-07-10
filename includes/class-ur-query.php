<?php
/**
 * Contains the query functions for UserRegistration which alter the front-end post queries and loops
 *
 * @class    UR_Query
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Query Class.
 */
class UR_Query {

	/** @public array Query vars to add to wp */
	public $query_vars = array();

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_action( 'wp', array( $this, 'remove_post_query' ) );
		}
		$this->init_query_vars();
	}

	/**
	 * Get any errors from querystring.
	 */
	public function get_errors() {
		if ( ! empty( $_GET['ur_error'] ) && ( $error = sanitize_text_field( $_GET['ur_error'] ) ) && ! ur_has_notice( $error, 'error' ) ) {
			ur_add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			// My account actions.
			'edit-password'  => get_option( 'user_registration_myaccount_change_password_endpoint', 'edit-password' ),
			'edit-profile'  => get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' ),
			'lost-password' => get_option( 'user_registration_myaccount_lost_password_endpoint', 'lost-password' ),
			'user-logout'   => get_option( 'user_registration_logout_endpoint', 'user-logout' ),
		);
	}

	/**
	 * Get page title for an endpoint.
	 *
	 * @param  string
	 * @return string
	 */
	public function get_endpoint_title( $endpoint ) {
		global $wp;

		switch ( $endpoint ) {
			case 'edit-password' :
				$title = __( 'Change Password', 'user-registration' );
			break;
			case 'edit-profile' :
				$title = __( 'Profile Details', 'user-registration' );
			break;
			case 'lost-password' :
				$title = __( 'Lost password', 'user-registration' );
			break;
			default :
				$title = '';
			break;
		}

		return apply_filters( 'user_registration_endpoint_' . $endpoint . '_title', $title, $endpoint );
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @return int
	 */
	public function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' );
			$myaccount_page_id = get_option( 'user_registration_myaccount_page_id', 4 );

			if ( in_array( $page_on_front, array( $myaccount_page_id ) ) ) {
				return EP_ROOT | EP_PAGES;
			}
		}

		return EP_PAGES;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = $this->get_endpoints_mask();

		foreach ( $this->query_vars as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}
	}

	/**
	 * Add query vars.
	 *
	 * @access public
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'user_registration_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;
		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	/**
	 * Are we currently on the front page?
	 *
	 * @param object $q
	 *
	 * @return bool
	 */
	private function is_showing_page_on_front( $q ) {
		return $q->is_home() && 'page' === get_option( 'show_on_front' );
	}

	/**
	 * Is the front page a page we define?
	 *
	 * @param int $page_id
	 *
	 * @return bool
	 */
	private function page_on_front_is( $page_id ) {
		return absint( get_option( 'page_on_front' ) ) === absint( $page_id );
	}

	/**
	 * Hook into pre_get_posts to do the main query.
	 *
	 * @param object $q query object
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query.
		if ( ! $q->is_main_query() ) {
			return;
		}

		// Fix for endpoints on the homepage.
		if ( $this->is_showing_page_on_front( $q ) && ! $this->page_on_front_is( $q->get( 'page_id' ) ) ) {
			$_query = wp_parse_args( $q->query );
			if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->query_vars ) ) ) {
				$q->is_page     = true;
				$q->is_home     = false;
				$q->is_singular = true;
				$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
				add_filter( 'redirect_canonical', '__return_false' );
			}
		}

		// And remove the pre_get_posts hook.
		$this->remove_post_query();
	}

	/**
	 * Remove the query.
	 */
	public function remove_post_query() {
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}
}
