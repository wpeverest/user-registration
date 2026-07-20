<?php
/**
 * WPML compatibility for User Registration & Membership.
 *
 * Centralises WPML (SitePress) integration in a single, safe place. This class
 * deliberately uses WPML's documented filter API instead of instantiating
 * `new SitePress()` — creating a fresh SitePress instance re-registers WPML's
 * `terms_clauses` / `comments_clauses` JOIN filters on every call, which stacks
 * duplicate `JOIN ...icl_translations` clauses and triggers MySQL
 * "Not unique table/alias" errors. See `UR_WPML::convert_url()`.
 *
 * It also does NOT re-register the endpoint-URL, nav-menu or page-language
 * filters that already live in functions-ur-core.php / functions-ur-page.php,
 * so loading this class cannot double-hook those queries.
 *
 * @package  UserRegistration\WPML
 * @since    5.2.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_WPML class.
 */
class UR_WPML {

	/**
	 * The single instance of the class.
	 *
	 * @var UR_WPML|null
	 */
	protected static $instance = null;

	/**
	 * Post types registered by User Registration that must never be treated
	 * as translatable by WPML. Mirrors the <custom-types> block in
	 * wpml-config.xml as a runtime safety net so that secondary-language
	 * queries (e.g. registration forms on a /de/ or /fr/ page) do not come
	 * back empty when the XML config is missing or overridden.
	 *
	 * @var string[]
	 */
	protected $non_translatable_post_types = array(
		'user_registration',
		'ur_membership',
		'ur_membership_groups',
		'ur_pro_popup',
		'ur_membership_team',
		'ur_frontend_listings',
		'ur_coupons',
		'urfd_file',
		'urcr_access_rule',
	);

	/**
	 * Main UR_WPML instance.
	 *
	 * @return UR_WPML
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Registers only safe, non-duplicating hooks.
	 */
	public function __construct() {
		// Keep UR's own post types out of WPML translation at runtime.
		add_filter( 'wpml_is_translated_post_type', array( $this, 'exclude_post_types_from_translation' ), 10, 2 );
	}

	/**
	 * Whether WPML (SitePress) is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || class_exists( 'SitePress', false );
	}

	/**
	 * Force User Registration post types to be reported as non-translatable.
	 *
	 * @param bool|null $is_translated Current value provided by WPML.
	 * @param string    $post_type     Post type being checked.
	 *
	 * @return bool|null
	 */
	public function exclude_post_types_from_translation( $is_translated, $post_type ) {
		if ( in_array( $post_type, $this->non_translatable_post_types, true ) ) {
			return false;
		}
		return $is_translated;
	}

	/**
	 * Current language code (e.g. "de", "fr").
	 *
	 * @param string $fallback Value to use when WPML returns nothing.
	 *
	 * @return string
	 */
	public function get_current_language( $fallback = 'en' ) {
		$language = apply_filters( 'wpml_current_language', $fallback );
		return ! empty( $language ) ? $language : $fallback;
	}

	/**
	 * Default (site) language code.
	 *
	 * @return string
	 */
	public function get_default_language() {
		return (string) apply_filters( 'wpml_default_language', null );
	}

	/**
	 * Convert a URL to the current language using WPML's official filter.
	 *
	 * Uses `wpml_permalink` rather than `SitePress::convert_url()` so that no
	 * additional SitePress instance is created and no duplicate query filters
	 * are registered.
	 *
	 * @param string $url      URL to convert.
	 * @param string $language Optional target language code.
	 *
	 * @return string
	 */
	public function convert_url( $url, $language = null ) {
		return (string) apply_filters( 'wpml_permalink', $url, $language );
	}

	/**
	 * Get the translated object ID for the current (or given) language.
	 *
	 * @param int    $element_id   Object ID (post/page/term).
	 * @param string $element_type Element type, e.g. "page", "post".
	 * @param string $language     Optional target language code.
	 *
	 * @return int
	 */
	public function get_object_id( $element_id, $element_type = 'page', $language = null ) {
		$translated = apply_filters( 'wpml_object_id', $element_id, $element_type, true, $language );
		return $translated ? absint( $translated ) : absint( $element_id );
	}

	/**
	 * Get a page ID translated to the current language.
	 *
	 * Thin wrapper around the existing helper so callers can use the
	 * object-oriented API without duplicating the lookup logic.
	 *
	 * @param int $page_id Page ID.
	 *
	 * @return int
	 */
	public function get_translated_page_id( $page_id ) {
		if ( function_exists( 'ur_get_wpml_page_language' ) ) {
			return absint( ur_get_wpml_page_language( $page_id ) );
		}
		return $this->get_object_id( $page_id, 'page' );
	}
}
