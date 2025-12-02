<?php
/**
 * UserRegistrationContentRestriction Admin Assets
 *
 * Load Admin Assets.
 *
 * @class    URCR_Admin_Assets
 * @version  1.0.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Admin_Assets Class
 */
class URCR_Admin_Assets {
	public $current_page = '';
	public $action = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		$this->current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$this->action       = isset( $_GET['action'] ) ? $_GET['action'] : '';
	}

	public function enqueue_admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		/**
		 * Local JS scripts.
		 */
		wp_register_script(
			'urcr-content-access-rule-creator',
			UR()->plugin_url() . '/assets/js/modules/content-restriction/admin/urcr-content-access-rule-creator' . $suffix . '.js',
			array(
				'jquery'
			),
			'1.0.0',
			true
		);

		if ( 'user-registration-content-restriction' === $this->current_page ) {
			wp_enqueue_script( 'urcr-content-access-rule-creator' );
		}
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_admin_styles() {
		/**
		 * Third party style scripts.
		 */
		if ( function_exists( 'UR' ) ) {
			wp_register_style( 'flatpickr', UR()->plugin_url() . '/assets/css/flatpickr/flatpickr.min.css', '4.5.1' );
		}

		/**
		 * Local style scripts.
		 */

		if ( function_exists( 'UR' ) ) {
			wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		}

		if ( 'user-registration-content-restriction' === $this->current_page ) {
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'sweetalert2' );
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_style( 'urcr-content-access-rule-creator' );
			wp_enqueue_style( 'ur-core-builder-style' );
			// React viewer mode - only load viewer styles
			wp_register_style(
				'urcr-content-access-restriction',
				UR()->plugin_url() . '/assets/css/urcr-content-access-restriction.css',
				array(),
				'1.0.0'
			);
			wp_enqueue_style( 'urcr-content-access-restriction' );

		}
	}


	/**
	 * Get localized data array for scripts.
	 * This method prepares all the localized data that can be used for script localization.
	 *
	 * @return array Localized data array.
	 */
	public static function get_localized_data() {

//		Prepare rule to edit, if a rule id has been provided.
		$rule_id      = ! empty( $_GET['post-id'] ) ? $_GET['post-id'] : null;
		$rule_to_edit = null;
		$is_draft     = false;
		$title        = esc_html__( 'Untitled', 'user-registration' );

		if ( $rule_id ) {
			$rule_as_wp_post = get_post( $rule_id, ARRAY_A );

			if ( $rule_as_wp_post ) {
				$title        = $rule_as_wp_post['post_title'];
				$rule_to_edit = json_decode( stripslashes( $rule_as_wp_post['post_content'] ), true );
			} else {
				$rule_id = null;
			}

			if ( isset( $rule_as_wp_post ) && 'draft' === $rule_as_wp_post['post_status'] ) {
				$is_draft = true;
			} else {
				$GLOBALS['urcr_hide_save_draft_button'] = true;
			}
		}


		// Prepare user registration sources.

		$ur_forms = ur_get_all_user_registration_form();

		$networks                = array(
			'facebook' => esc_html__( 'Facebook', 'user-registration' ),
			'linkedin' => esc_html__( 'LinkedIn', 'user-registration' ),
			'google'   => esc_html__( 'Google', 'user-registration' ),
			'twitter'  => esc_html__( 'Twitter', 'user-registration' ),
		);
		$registration_source_ids = array_merge( array_keys( $ur_forms ), array_keys( $networks ) );
		$registration_sources    = array_combine( $registration_source_ids, array_merge( $ur_forms, $networks ) );

		foreach ( $ur_forms as $form_id => $label ) {
			$form_data   = ur_pro_get_form_fields( $form_id );
			$form_fields = array();
			foreach ( $form_data as $field_name => $data ) {
				$form_fields[ $field_name ] = $data['label'];
			}
			$ur_forms[ $form_id ] = $form_fields;
		}


		// Prepare list of posttypes.

		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );


		// Prepare list of taxonomy.

		$taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'objects'
		);

		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );


		// Prepare terms of taxonomy.

		$terms_list = array();

		foreach ( $taxonomies as $tax_name => $tax_label ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $tax_name,
					'hide_empty' => false,
				)
			);

			// Handle WP_Error or empty results
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				$terms_list[ $tax_name ] = array();
			} else {
				$terms_list[ $tax_name ] = wp_list_pluck( $terms, 'name', 'term_id' );
			}
		}


		// Prepare list of posts.

		$posts = get_posts(
			array(
				'post_status' => 'publish',
				'numberposts' => 100,
			)
		);
		$posts = wp_list_pluck( $posts, 'post_title', 'ID' );


		// Prepare list of pages.

		$pages = get_pages(
			array(
				'post_status' => 'publish',
				'numberposts' => 100,
			)
		);
		$pages = wp_list_pluck( $pages, 'post_title', 'ID' );


		// Prepare list of shortcodes.

		global $shortcode_tags;
		$shortcode_names       = array_keys( $shortcode_tags );
		$shortcodes_list       = array_combine( $shortcode_names, $shortcode_names );
		$formatted_memberships = array();

		if ( ( ( function_exists( 'ur_check_module_activation' ) ) && ur_check_module_activation( 'membership' ) ) ) {
			$membership_repository = new MembershipRepository();
			$memberships           = $membership_repository->get_all_membership();
			array_map(
				function ( $membership ) use ( &$formatted_memberships ) {
					$formatted_memberships[ $membership['ID'] ] = $membership['post_title'];
				},
				$memberships
			);
		}

		return array(
			'URCR_DEBUG'                => apply_filters( 'urcr_debug_mode', true ),
			'_nonce'                    => wp_create_nonce( 'urcr_manage_content_access_rule' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'rule_id'                   => $rule_id,
			'is_draft'                  => $is_draft,
			'title'                     => $title,
			'access_rule_data'          => $rule_to_edit,
			'wp_roles'                  => ur_get_all_roles(),
			'wp_capabilities'           => urcr_get_all_capabilities(),
			'ur_forms'                  => ur_get_all_user_registration_form(),
			'registration_sources'      => $registration_sources,
			'post_types'                => $post_types,
			'taxonomies'                => $taxonomies,
			'terms_list'                => $terms_list,
			'posts'                     => $posts,
			'pages'                     => $pages,
			'ur_form_data'              => $ur_forms,
			'shortcodes'                => $shortcodes_list,
			'content_rule_url'          => admin_url( 'admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule' ),
			'is_advanced_logic_enabled' => get_option( 'urcr_content_access_rule_is_advanced_logic_enabled', true ),
			'payment_status'            => array(
				'pending'   => __( 'Pending', 'user-registration' ),
				'completed' => __( 'Completed', 'user-registration' ),
				'failed'    => __( 'Failed', 'user-registration' ),
			),
			'memberships'               => $formatted_memberships,
			'is_pro'                    => UR_PRO_ACTIVE
		);
	}

	/**
	 * Localize React scripts with urcr_localized_data.
	 *
	 * @param string $script_handle The script handle to localize.
	 */
	public static function localize_react_scripts( $script_handle ) {
		$localized_data = self::get_localized_data();
		wp_localize_script(
			$script_handle,
			'urcr_localized_data',
			$localized_data
		);
	}
}

return new URCR_Admin_Assets();

