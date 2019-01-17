<?php
/**
 * Cache Helper Class
 *
 * @class   UR_Cache_Helper
 * @since   1.5.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Cache_Helper Class.
 */
class UR_Cache_Helper {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		// add_action( 'wp', array( __CLASS__, 'prevent_caching' ) );
		add_action( 'user_registration_before_registration_form', array( __CLASS__, 'flush_w3tc_cache' ) );
	}

	/**
	 * Prevent caching on certain pages
	 */
	public static function prevent_caching( $id ) {

		if ( ! is_blog_installed() ) {
			return;
		}

		$page_ids = array_filter( array( ur_get_page_id( 'myaccount' ), $id ) );

		if ( is_page( $page_ids ) ) {
			self::set_nocache_constants();
			nocache_headers();
		}
	}

	/**
	 * Flush already set cache on registration page.
	 */
	public static function flush_w3tc_cache() {
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			$page_id = get_the_ID();
			w3tc_pgcache_flush_post( $page_id );
		}
	}

	/**
	 * Set constants to prevent caching by some plugins.
	 *
	 * @param  mixed $return Value to return. Previously hooked into a filter.
	 * @return mixed
	 */
	public static function set_nocache_constants( $return = true ) {
		ur_maybe_define_constant( 'DONOTCACHEPAGE', true );
		ur_maybe_define_constant( 'DONOTCACHEOBJECT', true );
		ur_maybe_define_constant( 'DONOTCACHEDB', true );
		return $return;
	}

	/**
	 * Notices function.
	 */
	public static function notices() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		$config   = w3_instance( 'W3_Config' );
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( '_ur_session_', $settings, true ) ) {
			?>
			<div class="error">
				<p><?php echo wp_kses_post( sprintf( __( 'In order for <strong>database caching</strong> to work with User Registration you must add %1$s to the "Ignored Query Strings" option in <a href="%2$s">W3 Total Cache settings</a>.', 'user-registration' ), '<code>_ur_session_</code>', esc_url( admin_url( 'admin.php?page=w3tc_dbcache' ) ) ) ); ?></p>
			</div>
			<?php
		}
	}
}

UR_Cache_Helper::init();
