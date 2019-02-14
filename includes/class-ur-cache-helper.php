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
}

UR_Cache_Helper::init();
