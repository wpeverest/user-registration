<?php
/**
 * UserRegistrationContentRestriction Admin.
 *
 * @class    URCR_Admin
 * @version  4.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Admin Class
 */
class URCR_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		/**
		 * Remove unnecessary notices.
		 */
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );

		/**
		 * Register admin menus.
		 */
		add_action( 'admin_menu', array( $this, 'add_urcr_menus' ), 30 );

		/**
		 * Register a settings in the core settings list.
		 */
		add_filter( 'user_registration_get_settings_pages', array( $this, 'add_content_restriction_setting' ), 10, 1 );

		/**
		 * Elementor Section Restriction
		 */
		add_action( 'elementor/element/before_section_end', array(
			$this,
			'urcr_add_option_to_restrict_elementor_section'
		), 10, 3 );

		/**
		 * Run migration on admin init (only once)
		 */
		add_action( 'admin_init', array( $this, 'run_migration' ), 5 );
	}

	/**
	 * Add Control to restrict Elementor section.
	 *
	 * @param mixed $element Element.
	 * @param mixed $args Arguments.
	 */
	public function urcr_add_option_to_restrict_elementor_section( $element, $section_id, $args ) {
		if ( 'section_layout_additional_options' === $section_id ) {
			$element->add_control(
				'urcr_restrict_section',
				array(
					'label'        => esc_html__( 'Restrict This Section', 'user-registration' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'user-registration' ),
					'label_off'    => esc_html__( 'No', 'user-registration' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);
		}
	}

	/**
	 * Remove Notices.
	 */
	public static function hide_unrelated_notices() {
		global $wp_filter;

		// Return on other than access rule creator page.
		if ( empty( $_REQUEST['page'] ) || 'user-registration-content-restriction' !== $_REQUEST['page'] ) {
			return;
		}

		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
			if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {
				foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						// Remove all notices except user registration plugins notices.
						if ( ! strstr( $name, 'user_registration_' ) ) {
							unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Add admin menus for Content Restriction settings.
	 *
	 */
	public function add_urcr_menus() {
		$rules_page = add_submenu_page(
			'user-registration',
			__( 'Content Restriction - Content Rules', 'user-registration' ),
			__( 'Content Rules', 'user-registration' ),
			'edit_posts',
			'user-registration-content-restriction',
			array(
				$this,
				'render_content_access_rules',
			)
		);
	}

	/**
	 * Render settings page with Content Access Rules (React version).
	 *
	 */
	public function render_content_access_rules() {
		$script_url = UR()->plugin_url() . '/chunks/content-access-rules.js';

		// Enqueue WordPress editor (free TinyMCE) for rich text editing
		wp_enqueue_editor();

		// Enqueue standalone content access rules script
		wp_enqueue_script(
			'ur-content-access-rules-script',
			$script_url,
			array(
				'wp-element',
				'wp-blocks',
				'wp-editor',
			),
			UR()->version,
			true
		);

		// Localize script with necessary data
		wp_localize_script(
			'ur-content-access-rules-script',
			'_UR_DASHBOARD_',
			array(
				'adminURL'       => esc_url( admin_url() ),
				'assetsURL'      => esc_url( UR()->plugin_url() . '/assets/' ),
				'urRestApiNonce' => wp_create_nonce( 'wp_rest' ),
				'restURL'        => rest_url(),
				'version'        => UR()->version,
			)
		);

		// Localize urcr_localized_data for React components
		if ( class_exists( 'URCR_Admin_Assets' ) ) {
			URCR_Admin_Assets::localize_react_scripts( 'ur-content-access-rules-script' );
		}

		// Render React mount point
		?>
		<div>
			<?php echo user_registration_plugin_main_header(); ?>
			<div id="user-registration-content-access-rules"></div>
		</div>
		<?php
	}


	/**
	 * Include Content Restriction settings in the settings list.
	 *
	 * @param array $settings List of setting pages.
	 */
	public function add_content_restriction_setting( $settings ) {
		if ( class_exists( 'UR_Settings_Page' ) ) {
			$settings[] = include 'settings/class-urcr-settings-file.php';
		}

		return $settings;
	}

	/**
	 * Run migration script to migrate old restriction settings to new content rules.
	 * This runs only once or when there are unmigrated posts/pages.
	 *
	 */
	public function run_migration() {
		// Only run in admin and for users with proper capabilities
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if we should run migration
		$global_migrated = get_option( 'urcr_global_restriction_migrated', false );
		$post_page_migrated = get_option( 'urcr_post_page_restrictions_migrated', false );

		// Check if there are unmigrated posts/pages
		$has_unmigrated = false;
		if ( ! $post_page_migrated ) {
			$args = array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => 'urcr_meta_checkbox',
						'value' => 'on',
					),
				),
			);
			$posts = get_posts( $args );

			$has_unmigrated = ! empty( $posts );
		}

		// Run migration if needed
		if ( ! $global_migrated || $has_unmigrated ) {
			if ( function_exists( 'urcr_run_migration' ) ) {
				urcr_run_migration();
			}
		}
	}
}

return new URCR_Admin();
