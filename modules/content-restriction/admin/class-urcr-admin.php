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
		// add_action( 'admin_menu', array( $this, 'add_urcr_menus' ), 30 );

		/**
		 * Register a settings in the core settings list.
		 */
		// add_filter( 'user_registration_get_settings_pages', array( $this, 'add_content_restriction_setting' ), 10, 1 );

		/**
		 * Elementor Section Restriction
		 */
		add_action(
			'elementor/element/before_section_end',
			array(
				$this,
				'urcr_add_option_to_restrict_elementor_section',
			),
			10,
			3
		);

		/**
		 * Run migration on admin init (only once)
		 */
		add_action( 'admin_init', array( $this, 'run_migration' ), 5 );

		/**
		 * Create content access rule when a new membership is created
		 */
		add_filter( 'ur_membership_before_create_membership_response', array( $this, 'create_rule_for_new_membership' ), 10, 1 );

		/**
		 * Delete content access rule when a membership is deleted.
		 */
		add_action( 'before_delete_post', array( $this, 'delete_rule_for_membership' ), 10, 1 );

		/**
		 * AJAX handler to get membership rule data
		 */
		add_action( 'wp_ajax_urcr_get_membership_rule', array( $this, 'ajax_get_membership_rule' ) );
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

		// Check if membership module is enabled and count memberships
		$membership_count         = 0;
		$has_multiple_memberships = false;

		if ( function_exists( 'ur_check_module_activation' ) && ur_check_module_activation( 'membership' ) ) {
			if ( class_exists( '\WPEverest\URMembership\Admin\Services\MembershipService' ) ) {
				$membership_service       = new \WPEverest\URMembership\Admin\Services\MembershipService();
				$memberships              = $membership_service->list_active_memberships();
				$membership_count         = is_array( $memberships ) ? count( $memberships ) : 0;
				$has_multiple_memberships = $membership_count > 1;
			}
		}

		// Determine menu title based on membership count
		$menu_title = $has_multiple_memberships
			? __( 'Content Rules', 'user-registration' )
			: __( 'Content Rules', 'user-registration' );

		$rules_page = add_submenu_page(
			'user-registration',
			__( 'Content Restriction - Content Rules', 'user-registration' ),
			$menu_title,
			'edit_posts',
			'user-registration-content-restriction',
			array(
				$this,
				'render_content_access_rules',
			),
			4
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
			'_URCR_DASHBOARD_',
			array(
				'adminURL'       => esc_url( admin_url() ),
				'assetsURL'      => esc_url( UR()->plugin_url() . '/assets/' ),
				'urRestApiNonce' => wp_create_nonce( 'wp_rest' ),
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
	 * Create content access rule when a new membership is created.
	 *
	 * @param array $response The response array containing membership_id.
	 */
	public function create_rule_for_new_membership( $response ) {
		if ( ! isset( $response['membership_id'] ) || empty( $response['membership_id'] ) ) {
			return $response;
		}

		$membership_id = absint( $response['membership_id'] );

		// Check if content restriction module is active
		if ( ! function_exists( 'ur_check_module_activation' ) || ! ur_check_module_activation( 'content-restriction' ) ) {
			return $response;
		}

		// Check if rule data was provided from the UI
		$rule_data = isset( $_POST['urcr_membership_access_rule_data'] )
			? json_decode( wp_unslash( $_POST['urcr_membership_access_rule_data'] ), true )
			: null;

		// Create or update rule for the new membership
		if ( function_exists( 'urcr_create_or_update_membership_rule' ) ) {
			urcr_create_or_update_membership_rule( $membership_id, $rule_data );
		} elseif ( function_exists( 'urcr_create_membership_rule' ) ) {
			urcr_create_membership_rule( $membership_id );
		}

		return $response;
	}

	/**
	 * Delete content access rules associated with a membership.
	 * Helper method used by all deletion hooks.
	 *
	 * @param int $membership_id The membership ID.
	 */
	private function delete_rules_for_membership( $membership_id ) {

		$membership_id = absint( $membership_id );

		// Verify this is actually a membership post
		$post = get_post( $membership_id );
		if ( ! $post || 'ur_membership' !== $post->post_type ) {
			return;
		}

		// Find and delete the associated content access rules
		$existing_rules = get_posts(
			array(
				'post_type'      => 'urcr_access_rule',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'urcr_membership_id',
						'value' => $membership_id,
					),
				),
			)
		);

		// Delete all associated rules
		foreach ( $existing_rules as $rule ) {
			// Clear rule meta before deletion
			delete_post_meta( $rule->ID, 'urcr_rule_type' );
			delete_post_meta( $rule->ID, 'urcr_membership_id' );
			delete_post_meta( $rule->ID, 'urcr_is_migrated' );

			wp_delete_post( $rule->ID, true ); // true = force delete (skip trash)
		}
	}

	/**
	 * Delete content access rule when a membership is deleted.
	 *
	 * @param int $post_id The post ID being deleted.
	 */
	public function delete_rule_for_membership( $post_id ) {
		$this->delete_rules_for_membership( $post_id );
	}


	/**
	 * AJAX handler to get membership rule data.
	 */
	public function ajax_get_membership_rule() {
		check_ajax_referer( 'urcr_manage_content_access_rule', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'user-registration' ) ) );
		}

		$membership_id = isset( $_POST['membership_id'] ) ? absint( $_POST['membership_id'] ) : 0;

		if ( ! $membership_id ) {
			wp_send_json_error( array( 'message' => __( 'Membership ID is required', 'user-registration' ) ) );
		}

		// Find existing rule for this membership
		$existing_rules = get_posts(
			array(
				'post_type'      => 'urcr_access_rule',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => 'urcr_membership_id',
						'value' => $membership_id,
					),
				),
			)
		);

		if ( empty( $existing_rules ) ) {
			wp_send_json_success( array( 'data' => null ) );
		}

		$rule_post    = $existing_rules[0];
		$rule_content = json_decode( $rule_post->post_content, true );

		if ( ! $rule_content ) {
			wp_send_json_success( array( 'data' => null ) );
		}

		// Add rule ID and other metadata
		$rule_content['id']    = $rule_post->ID;
		$rule_content['title'] = $rule_post->post_title;

		// Get enabled status from rule content (stored in post_content JSON)
		// Default to true if not set (matches default for new rules)
		if ( ! isset( $rule_content['enabled'] ) ) {
			$rule_content['enabled'] = true;
		}

		wp_send_json_success( array( 'data' => $rule_content ) );
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

		//For new fresh user.
		if ( ur_string_to_bool( get_option( 'urm_is_new_installation' ) ) ) {
			return;
		}

		// Check if we should run migration
		$global_migrated      = get_option( 'urcr_global_restriction_migrated', false );
		$post_page_migrated   = get_option( 'urcr_post_page_restrictions_migrated', false );
		$memberships_migrated = get_option( 'urcr_memberships_migrated', false );

		// Check if there are unmigrated posts/pages
		$has_unmigrated_posts = false;
		if ( ! $post_page_migrated ) {
			global $wpdb;

			$query = $wpdb->prepare(
				"SELECT DISTINCT wp.ID
				FROM {$wpdb->posts} AS wp
				INNER JOIN {$wpdb->postmeta} AS wpm
					ON wpm.post_id = wp.ID
				LEFT JOIN {$wpdb->postmeta} AS wpm_override
					ON wpm_override.post_id = wp.ID
						AND wpm_override.meta_key = %s
				WHERE wpm.meta_key = %s
					AND wpm.meta_value = %s
					AND wp.post_type IN ('post', 'page')
					AND wp.post_status = 'publish'
					AND (
						wpm_override.post_id IS NULL
						OR wpm_override.meta_value = ''
					)
				LIMIT 1",
				'urcr_meta_override_global_settings',
				'urcr_meta_checkbox',
				'on'
			);

			$posts = $wpdb->get_results( $query );

			$has_unmigrated_posts = ! empty( $posts );
		}

		// Check if there are unmigrated memberships
		$has_unmigrated_memberships = false;
		if ( ! $memberships_migrated ) {
			if ( function_exists( 'urcr_has_unmigrated_memberships' ) ) {
				$has_unmigrated_memberships = urcr_has_unmigrated_memberships();
			}
		}

		// Run migration if any step needs to run
		if ( ! $global_migrated || $has_unmigrated_posts || ! $memberships_migrated || $has_unmigrated_memberships ) {
			if ( function_exists( 'urcr_run_migration' ) ) {
				urcr_run_migration();
			}
		}
	}
}

return new URCR_Admin();
