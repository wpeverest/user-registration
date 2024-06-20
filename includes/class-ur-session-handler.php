<?php
/**
 * Handle data for the current customers session.
 * Implements the UR_Session abstract class.
 *
 * @class    UR_Session_Handler
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Session_Handler class
 */
class UR_Session_Handler extends UR_Session {

	/**
	 * Cookie Name.
	 *
	 * @var string cookie name.
	 */
	private $_cookie;

	/**
	 * Session expiring flag.
	 *
	 * @var string session due to expire timestamp.
	 */
	private $_session_expiring;

	/**
	 * Session expiration timestamp.
	 *
	 * @var string session expiration timestamp
	 */
	private $_session_expiration;

	/**
	 * Bool based on whether a cookie exists.
	 *
	 * @var bool Bool based on whether a cookie exists
	 */
	private $_has_cookie = false;

	/**
	 * Custom session table name.
	 *
	 * @var string Custom session table name.
	 */
	private $_table;

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		global $wpdb;
		/**
		 * Applies a filter to customize the session cookie name for User Registration.
		 *
		 * The 'user_registration_cookie' filter allows developers to modify the default session cookie name
		 * used by the User Registration class. By default, it appends 'wp_user_registration_session_' with COOKIEHASH.
		 * Developers can use this filter to change the cookie name based on their specific requirements.
		 *
		 * @param string $default_cookie_name The default session cookie name.
		 */
		$this->_cookie = apply_filters( 'user_registrtaion_cookie', 'wp_user_registration_session_' . COOKIEHASH );
		$prefix = $wpdb->prefix;
		if (is_multisite()) {
			$prefix = $wpdb->base_prefix;
		}
		$this->_table  = $prefix . 'user_registration_sessions';

		$cookie = $this->get_session_cookie();

		if ( $cookie ) {
			$this->_customer_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;

			// Update session if its close to expiring.
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$this->update_session_timestamp( $this->_customer_id, $this->_session_expiration );
			}
		} else {
			$this->set_session_expiration();
			$this->_customer_id = $this->generate_customer_id();
		}

		$this->_data = $this->get_session_data();

		// Actions.
		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_session' ) );
		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ) );
		}
	}

	/**
	 * Sets the session cookie on-demand (usually after adding an item to the cart).
	 *
	 * Since the cookie name (as of 2.1) is prepended with wp, cache systems like batcache will not cache pages when set.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Cookie flag.
	 */
	public function set_customer_session_cookie( $set ) {
		if ( $set ) {
			// Set/renew our cookie.
			$to_hash           = $this->_customer_id . '|' . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

			// Set the cookie.
			/**
			 * Applies a filter to determine whether to use a secure session cookie.
			 *
			 * The 'ur_session_use_secure_cookie' filter allows developers to customize the decision
			 * on whether to use a secure cookie when setting/renewing the customer session cookie.
			 * By default, it sets the cookie without using a secure connection. Developers can use
			 * this filter to modify the behavior based on their security requirements or environment.
			 *
			 * @param bool $default_use_secure_cookie The default decision to use a secure cookie, initially set to false.
			 */
			ur_setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, apply_filters( 'ur_session_use_secure_cookie', false ) );
		}
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in();
	}

	/**
	 * Set session expiration.
	 */
	public function set_session_expiration() {
		/**
		 * Applies a filter to customize the duration before User Registration session expiration.
		 *
		 * @param int $default_duration The default duration before session expiration in seconds.
		 */
		$this->_session_expiring = time() + intval( apply_filters( 'ur_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		/**
		 * Applies a filter to customize the duration User Registration session expiration.
		 *
		 * @param int $default_duration The default duration session expiration in seconds.
		 */
		$this->_session_expiration = time() + intval( apply_filters( 'ur_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
	}

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return int|string
	 */
	public function generate_customer_id() {
		if ( is_user_logged_in() ) {

			return get_current_user_id();
		} else {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 32 ) );
		}
	}

	/**
	 * Get session cookie.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		if ( empty( $_COOKIE[ $this->_cookie ] ) || ! is_string( $_COOKIE[ $this->_cookie ] ) ) {
			return false;
		}

		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $_COOKIE[ $this->_cookie ] ); //phpcs:ignore;

		// Validate hash.
		$to_hash = $customer_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->_customer_id, array() ) : array();
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once.
	 *
	 * @param  string $group Group.
	 * @return string
	 */
	private function get_cache_prefix( $group = UR_SESSION_CACHE_GROUP ) {
		// Get cache key.
		$prefix = wp_cache_get( 'ur_' . $group . '_cache_prefix', $group );

		if ( false === $prefix ) {
			$prefix = 1;
			wp_cache_set( 'ur_' . $group . '_cache_prefix', $prefix, $group );
		}

		return 'ur_cache_' . $prefix . '_';
	}

	/**
	 * Increment group cache prefix (invalidates cache).
	 *
	 * @param string $group Group.
	 */
	public function incr_cache_prefix( $group = UR_SESSION_CACHE_GROUP ) {
		wp_cache_incr( 'ur_' . $group . '_cache_prefix', 1, $group );
	}

	/**
	 * Save data.
	 */
	public function save_data() {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->_dirty && $this->has_session() ) {
			global $wpdb;

			$wpdb->replace(
				$this->_table,
				array(
					'session_key'    => $this->_customer_id,
					'session_value'  => maybe_serialize( $this->_data ),
					'session_expiry' => $this->_session_expiration,
				),
				array(
					'%s',
					'%s',
					'%d',
				)
			);

			// Set cache.
			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, UR_SESSION_CACHE_GROUP, $this->_session_expiration - time() );

			// Mark session clean after saving.
			$this->_dirty = false;
		}
	}

	/**
	 * Destroy all session data.
	 */
	public function destroy_session() {
		// Clear cookie.
		/**
		 * Applies a filter to determine whether to use a secure session cookie during User Registration session destruction.
		 *
		 * @param bool $default_use_secure_cookie The default decision to use a secure cookie, initially set to false.
		 */
		ur_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, apply_filters( 'ur_session_use_secure_cookie', false ) );

		$this->delete_session( $this->_customer_id );

		// Clear data.
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the customer/session ID.
	 *
	 * @param int $uid Uid.
	 *
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		return $this->has_session() && $this->_customer_id ? $this->_customer_id : $uid;
	}

	/**
	 * Cleanup sessions.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		if ( ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {

			// Delete expired sessions.
			$wpdb->query( $wpdb->prepare( "DELETE FROM `{$this->_table}` WHERE session_expiry < %d", time() ) );

			// Invalidate cache.
			$this->incr_cache_prefix();
		}
	}

	/**
	 * Returns the session.
	 *
	 * @param string $customer_id Customer Id.
	 * @param mixed  $default Default false.
	 * @return string|array
	 */
	public function get_session( $customer_id, $default = false ) {
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		// Try get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $customer_id, UR_SESSION_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM `{$this->_table}` WHERE session_key = %s", $customer_id ) );

			if ( is_null( $value ) ) {
				$value = $default;
			}

			wp_cache_add( $this->get_cache_prefix() . $customer_id, $value, UR_SESSION_CACHE_GROUP, $this->_session_expiration - time() );
		}

		return ur_maybe_unserialize( $value );
	}

	/**
	 * Delete the session from the cache and database.
	 *
	 * @param int $customer_id Customer Id.
	 */
	public function delete_session( $customer_id ) {
		global $wpdb;

		wp_cache_delete( $this->get_cache_prefix() . $customer_id, UR_SESSION_CACHE_GROUP );

		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => $customer_id,
			)
		);
	}

	/**
	 * Update the session expiry timestamp.
	 *
	 * @param string $customer_id Customer ID.
	 * @param int    $timestamp Timestamp.
	 */
	public function update_session_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp,
			),
			array(
				'session_key' => $customer_id,
			),
			array(
				'%d',
			)
		);
	}
}
