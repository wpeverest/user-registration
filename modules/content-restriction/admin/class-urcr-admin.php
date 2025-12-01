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
		add_action( 'elementor/element/before_section_end', array( $this, 'urcr_add_option_to_restrict_elementor_section' ), 10, 3 );
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
	 * @since 4.0
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
				'render_content_restriction_page',
			)
		);

//		add_action( 'load-' . $rules_page, array( $this, 'content_restriction_initializations' ) );
	}

	/**
	 * Do initializations before loading content restriction pages.
	 *
	 * @since 4.0
	 */
	public function content_restriction_initializations() {
		if ( isset( $_GET['page'] ) && 'user-registration-content-restriction' === $_GET['page'] ) {

			$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

			switch ( $action_page ) {
				case 'add_new_urcr_content_access_rule':
					break;

				default:
					global $content_access_rules_table_list;

					require_once __DIR__ . '/class-urcr-admin-content-access-rules-table-list.php';
					$content_access_rules_table_list = new URCR_Admin_Content_Access_Rules_Table_List();
					$content_access_rules_table_list->process_actions();
					break;
			}
		}
	}

	/**
	 * Render content restriction page.
	 *
	 * @since 4.0
	 */
	public function render_content_restriction_page() {
		$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

		switch ( $action_page ) {
			case 'add_new_urcr_content_access_rule':
				$this->render_content_access_rules_creator();
				break;

			default:
				// Use React viewer instead of old PHP viewer
				$this->render_content_access_rules();
				break;
		}
	}

	/**
	 * Render settings page to manage Content Access Rules.
	 *
	 * @since 4.0
	 */
	public function render_content_access_rules_creator() {
		include __DIR__ . '/content-access-rules-creator.php';
	}

	/**
	 * Render settings page with Content Access Rules (React version).
	 *
	 * @since 4.0
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
	public function render_content_access_rules_viewer_old() {
		global $content_access_rules_table_list;

		if ( ! $content_access_rules_table_list ) {
			return;
		}

		$content_access_rules_table_list->display_page();
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
}

return new URCR_Admin();
