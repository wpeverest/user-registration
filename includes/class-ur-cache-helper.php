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
		add_action( 'user_registration_before_registration_form', array( __CLASS__, 'flush_w3tc_cache' ) );
		add_action( 'user_registration_before_registration_form', array( __CLASS__, 'flush_wpsuper_cache' ) );
		add_action( 'user_registration_before_registration_form', array( __CLASS__, 'flush_wprocket_cache' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_disable_cache_for_dynamic_pages' ), 0 );
	}

	/**
	 * Flush already set cache by w3total cache plugin on registration page.
	 */
	public static function flush_w3tc_cache() {
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			$post_id = get_the_ID();
			w3tc_pgcache_flush_post( $post_id );
		}
	}

	/**
	 * Flush already set cache by wp super cache plugin on registration page.
	 */
	public static function flush_wpsuper_cache() {
		if ( function_exists( 'wpsc_delete_post_cache' ) ) {
			$post_id = get_the_ID();
			wpsc_delete_post_cache( $post_id );
		}
	}

	/**
	 * Flush already set cache by wp rocket cache plugin on registration page.
	 */
	public static function flush_wprocket_cache() {
		if ( function_exists( 'rocket_clean_post' ) ) {
			$post_id = get_the_ID();
			rocket_clean_post( $post_id );
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

	/**
	 * Prevent caching for dynamic User Registration pages.
	 *
	 * This handles cases like the lost-password form, etc.
	 */
	public static function maybe_disable_cache_for_dynamic_pages() {
		global $wp_query;

		// Detect UR routes that should never be cached.
		$is_ur_lost_password_page = false;
		$lost_pw_id               = get_option( 'user_registration_lost_password_page_id' );

		if ( isset( $wp_query->post ) && (int) $wp_query->post->ID === (int) $lost_pw_id ) {
			$is_ur_lost_password_page = true;
		}

		if ( ! $is_ur_lost_password_page ) {
			return;
		}

		// Define constants for common cache plugins.
		self::set_nocache_constants();

		// Send strong HTTP headers so browsers, proxies, and CDNs won't cache.
		if ( ! headers_sent() ) {
			nocache_headers();

			// Add no-store and vary headers for extra safety.
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			header( 'Vary: Cookie, Authorization' );

			// Some CDNs respect these additional headers.
			header( 'Surrogate-Control: no-store' );
			header( 'X-LiteSpeed-Cache-Control: no-cache' );
			header( 'X-Accel-Expires: 0' );
		}
	}
}

UR_Cache_Helper::init();
