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
	public $action       = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		$this->current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$this->action       = isset( $_GET['action'] ) ? $_GET['action'] : '';
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_admin_styles() {
		/**
		 * Local style scripts.
		 */

		if ( function_exists( 'UR' ) ) {
			wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		}

		if ( 'user-registration-content-restriction' === $this->current_page ) {
			// Only load creator styles when in creator mode
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
	 * Enqueue JS scripts.
	 */
	public function enqueue_admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';

		/**
		 * Third party JS scripts.
		 */
		if ( function_exists( 'UR' ) ) {
			wp_register_script( 'sweetalert2', UR()->plugin_url() . '/assets/js/sweetalert2/sweetalert2.min.js', array( 'jquery' ), '8.17.1' );
			wp_register_script( 'flatpickr', UR()->plugin_url() . '/assets/js/flatpickr/flatpickr.min.js', array( 'jquery' ), '1.17.0' );
			wp_register_script( 'jquery-tiptip', UR()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), UR_VERSION, true );
		}

		/**
		 * Local JS scripts.
		 */
		wp_register_script(
			'urcr-content-access-rule-creator',
			UR()->plugin_url() . '/assets/js/modules/content-restriction/admin/urcr-content-access-rule-creator' . $suffix . '.js',
			array(
				'jquery',
				'selectWoo',
				'ur-snackbar',
			),
			'1.0.0',
			true
		);

		if ( function_exists( 'UR' ) ) {
			wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
			wp_register_script( 'ur-components', UR()->plugin_url() . '/assets/js/ur-components/ur-components' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		}

		if ( 'user-registration-content-restriction' === $this->current_page ) {
			// Only load creator scripts when in creator mode
			if ( 'add_new_urcr_content_access_rule' === $this->action ) {
				wp_enqueue_script( 'sweetalert2' );
				wp_enqueue_script( 'flatpickr' );
				wp_enqueue_script( 'jquery-tiptip' );
				wp_enqueue_script( 'ur-components' );
				wp_enqueue_script( 'urcr-content-access-rule-creator' );
				$this->localize_scripts();
			}
			// React viewer scripts are handled in class-urcr-admin.php
			// No need to enqueue old viewer scripts here
		}
	}

	/**
	 * Localize scripts.
	 */
	public function localize_scripts() {
		//
		// Prepare rule to edit, if a rule id has been provided.
		//
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

			if ( 'draft' === $rule_as_wp_post['post_status'] ) {
				$is_draft = true;
			} else {
				$GLOBALS['urcr_hide_save_draft_button'] = true;
			}
		}

		//
		// Prepare user registration sources.
		//
		$ur_forms = ur_get_all_user_registration_form();

		$networks                = array(
			'facebook' => esc_html__( 'Facebook', 'user-registration' ),
			'linkedin' => esc_html__( 'LinkedIn', 'user-registration' ),
			'google'   => esc_html__( 'Google', 'user-registration' ),
			'twitter'  => esc_html__( 'Twitter', 'user-registration' ),
		);
		$registration_source_ids = array_merge( array_keys( $ur_forms ), array_keys( $networks ) );
		$registration_sources    = array_combine( $registration_source_ids, array_merge( $ur_forms, $networks ) );

		$ur_form_data = array();
		foreach ( $ur_forms as $form_id => $label ) {
			$form_data   = ur_pro_get_form_fields( $form_id );
			$form_fields = array();
			foreach ( $form_data as $field_name => $data ) {
				$form_fields[ $field_name ] = $data['label'];
			}
			$ur_forms[ $form_id ] = $form_fields;
		}

		//
		// Prepare list of posttypes.
		//
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );

		//
		// Prepare list of taxonomy.
		//
		$taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'objects'
		);

		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );

		//
		// Prepare terms of taxonomy.
		//
		$terms_list = array();

		foreach ( $taxonomies as $tax_name => $tax_label ) {
			$terms                   = get_terms(
				array(
					'taxonomy'   => $tax_name,
					'hide_empty' => false,
				)
			);
			$terms_list[ $tax_name ] = wp_list_pluck( $terms, 'name', 'term_id' );
		}

		//
		// Prepare list of posts.
		//
		$posts = get_posts(
			array(
				'post_status' => 'publish',
				'numberposts' => 100,
			)
		);
		$posts = wp_list_pluck( $posts, 'post_title', 'ID' );

		//
		// Prepare list of pages.
		//
		$pages = get_pages(
			array(
				'post_status' => 'publish',
				'numberposts' => 100,
			)
		);
		$pages = wp_list_pluck( $pages, 'post_title', 'ID' );

		//
		// Prepare list of shortcodes.
		//
		global $shortcode_tags;
		$shortcode_names       = array_keys( $shortcode_tags );
		$shortcodes_list       = array_combine( $shortcode_names, $shortcode_names );
		$formatted_memberships = array();

		if ( ( (function_exists('ur_check_module_activation')) && ur_check_module_activation('membership') )) {
			$membership_repository = new MembershipRepository();
			$memberships           = $membership_repository->get_all_membership();
			array_map(
				function ( $membership ) use ( &$formatted_memberships ) {
					$formatted_memberships[ $membership['ID'] ] = $membership['post_title'];
				},
				$memberships
			);
		}

		wp_localize_script(
			'urcr-content-access-rule-creator',
			'urcr_localized_data',
			array(
				'URCR_DEBUG'           => apply_filters( 'urcr_debug_mode', true ),
				'_nonce'               => wp_create_nonce( 'urcr_manage_content_access_rule' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'rule_id'              => $rule_id,
				'is_draft'             => $is_draft,
				'title'                => $title,
				'access_rule_data'     => $rule_to_edit,
				'wp_roles'             => ur_get_all_roles(),
				'wp_capabilities'      => urcr_get_all_capabilities(),
				'ur_forms'             => ur_get_all_user_registration_form(),
				'registration_sources' => $registration_sources,
				'post_types'           => $post_types,
				'taxonomies'           => $taxonomies,
				'terms_list'           => $terms_list,
				'posts'                => $posts,
				'pages'                => $pages,
				'ur_form_data'         => $ur_forms,
				'shortcodes'           => $shortcodes_list,
				'labels'               => $this->get_i18_labels(),
				'templates'            => $this->get_templates(),
				'content_rule_url'     => admin_url( 'admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule' ),
				'payment_status'       => array(
					'pending'   => __( 'Pending', 'user-registration' ),
					'completed' => __( 'Completed', 'user-registration' ),
					'failed'    => __( 'Failed', 'user-registration' ),
				),
				'memberships'          => $formatted_memberships,
			)
		);
	}

	/**
	 * Get translated labels.
	 */
	public function get_i18_labels() {
		return array(
			'roles'                        => esc_html__( 'Roles', 'user-registration' ),
			'user_registered_date'         => esc_html__( 'User Registered Date', 'user-registration' ),
			'access_period'                => esc_html__( 'Period after Registration', 'user-registration' ),
			'user_state'                   => esc_html__( 'User State', 'user-registration' ),
			'payment_status'               => esc_html__( 'Payment Status', 'user-registration' ),
			'membership'                   => esc_html__( 'Memberships', 'user-registration' ),
			'profile_completeness'         => esc_html__( 'Profile Completeness', 'user-registration' ),
			'logged_in'                    => esc_html__( 'Logged In', 'user-registration' ),
			'logged_out'                   => esc_html__( 'Logged Out', 'user-registration' ),
			'post_count'                   => esc_html__( 'Minimum Public Posts Count', 'user-registration' ),
			'capabilities'                 => esc_html__( 'Capabilities', 'user-registration' ),
			'content_published_date'       => esc_html__( 'Content Published Date', 'user-registration' ),
			'ur_form_field_value'          => esc_html__( 'UR Form Field Value', 'user-registration' ),
			'registration_source'          => esc_html__( 'Registration Source', 'user-registration' ),
			'ur_form_field'                => esc_html__( 'UR Forms', 'user-registration' ),
			'email_domain'                 => esc_html__( 'Allowed Email Domains', 'user-registration' ),
			'post_types'                   => esc_html__( 'Post Types', 'user-registration' ),
			'taxonomy'                     => esc_html__( 'Taxonomy', 'user-registration' ),
			'archives'                     => esc_html__( 'Archives', 'user-registration' ),
			'pick_posts'                   => esc_html__( 'Pick Posts', 'user-registration' ),
			'pick_pages'                   => esc_html__( 'Pick Pages', 'user-registration' ),
			'whole_site'                   => esc_html__( 'Whole Site', 'user-registration' ),
			'select_ur_form'               => esc_html__( 'Select a UR form', 'user-registration' ),
			'select_ur_shortcode'          => esc_html__( 'Select a shortcode', 'user-registration' ),
			'enter_shortcode_args'         => esc_html__( 'Enter shortcode arguments here. Eg: id="345"', 'user-registration' ),
			'save_rule'                    => esc_html__( 'Save', 'user-registration' ),
			'save_draft'                   => esc_html__( 'Save Draft', 'user-registration' ),
			'publish_rule'                 => esc_html__( 'Publish', 'user-registration' ),
			'edit_access_rule'             => esc_html__( 'Edit Access Rule', 'user-registration' ),
			'publish_draft_warning'        => esc_html__( 'Are you sure you want to publish this draft? You will not be able to revert this.', 'user-registration' ),
			'network_error'                => esc_html__( 'Network error', 'user-registration' ),
			'title_is_required'            => esc_html__( 'Title cannot be empty. Please give the Access Rule a short descriptive title.', 'user-registration' ),
			'enabled'                      => esc_html__( 'Enabled', 'user-registration' ),
			'disabled'                     => esc_html__( 'Disabled', 'user-registration' ),
			'are_you_sure'                 => esc_html__( 'Are you sure?', 'user-registration' ),
			'cannot_revert'                => esc_html__( 'You will not be able to revert this!', 'user-registration' ),
			'clfog_deletion_message'       => esc_html__( 'Are you sure you want to delete this field/group? You will not be able to revert this!', 'user-registration' ),
			// clfog => Conditional Logic Field or Group.
			'delete'                       => esc_html__( 'Delete', 'user-registration' ),
			'publish'                      => esc_html__( 'Publish', 'user-registration' ),
			'select_a_page'                => esc_html__( 'Select a page', 'user-registration' ),
			'main_logic_group'             => esc_html__( 'Main Logic Group', 'user-registration' ),
			'add_new'                      => esc_html__( 'Add New Content Rules', 'user-registration' ),
			'content_rule_name'            => esc_html__( 'Content Rule Name', 'user-registration' ),
			'cancel_text'                  => esc_html__( 'Cancel', 'user-registration' ),
			'confirm_text'                 => esc_html__( 'Continue', 'user-registration' ),
			'access_control'               => esc_html__( 'Access Control', 'user-registration' ),
			'access'                       => esc_html__( 'Access', 'user-registration' ),
			'restrict'                     => esc_html__( 'Restrict', 'user-registration' ),

			/**
			 * Tooltips.
			 */
			'roles_tooltip'                => esc_html__( 'User should have one of the selected roles', 'user-registration' ),
			'registered_date_tooltip'      => esc_html__( 'Users must have been registered in the specified date range', 'user-registration' ),
			'access_period_tooltip'        => esc_html__( 'User will access content during or after numbers of days from the registration', 'user-registration' ),
			'user_state_tooltip'           => esc_html__( 'Whether users should be logged in or logged out', 'user-registration' ),
			'payment_status_tooltip'       => esc_html__( 'Select the payment state at which to display the content.', 'user-registration' ),
			'membership_tooltip'           => esc_html__( 'Select the membership for which to show or restrict the content.', 'user-registration' ),
			'profile_completeness_tooltip' => esc_html__( 'User must have profile completeness above the specified threshold', 'user-registration' ),
			'email_domains_tooltip'        => esc_html__( 'Email domain of User must be included in the list', 'user-registration' ),
			'min_post_count_tooltip'       => esc_html__( 'Users must have published minimum specified posts as public', 'user-registration' ),
			'capabilities_tooltip'         => esc_html__( 'Users must have all of the listed capabilities', 'user-registration' ),
			'registration_source_tooltip'  => esc_html__( 'Users must have been registered through one of the listed sources', 'user-registration' ),
			'ur_form_field_tooltip'        => esc_html__( 'Users must have the specified form field value', 'user-registration' ),
			'post_types_tooltip'           => esc_html__( 'Target post types to apply restriction', 'user-registration' ),
			'taxonomy_tooltip'             => esc_html__( 'Target taxonomies to apply restriction', 'user-registration' ),
			'pick_posts_tooltip'           => esc_html__( 'Cherry picked posts to apply restriction', 'user-registration' ),
			'whole_site_tooltip'           => esc_html__( 'Enable to whole site restriction', 'user-registration' ),
			'pick_pages_tooltip'           => esc_html__( 'Cherry picked pages to apply restriction', 'user-registration' ),
		);
	}

	/**
	 * Get frontend templates.
	 */
	public function get_templates() {
		ob_start();
		include URCR_TEMPLATES_DIR . '/conditional-logic-group-template.php';
		$conditional_logic_group_template = ob_get_clean();

		ob_start();
		include URCR_TEMPLATES_DIR . '/conditional-logic-field-template.php';
		$conditional_logic_field_template = ob_get_clean();

		ob_start();
		include URCR_TEMPLATES_DIR . '/urcr-target-content-template.php';
		$target_content_template = ob_get_clean();

		return array(
			'conditional_logic_group_template' => $conditional_logic_group_template,
			'conditional_logic_field_template' => $conditional_logic_field_template,
			'target_content_template'          => $target_content_template,
		);
	}

	/**
	 * Localize viewer scripts.
	 */
	public function localize_viewer_scripts() {
				wp_localize_script(
					'urcr-content-access-rules-viewer-v2',
					'urcr_viewer_data',
					array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'nonce'           => wp_create_nonce( 'urcr_manage_content_access_rule' ),
						'edit_url'        => admin_url( 'admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=%RULE_ID%' ),
						'labels'          => array(
							'rule_enabled'   => esc_html__( 'Rule enabled successfully', 'user-registration' ),
							'rule_disabled'  => esc_html__( 'Rule disabled successfully', 'user-registration' ),
							'rule_saved'     => esc_html__( 'Rule saved successfully', 'user-registration' ),
							'saving'         => esc_html__( 'Saving...', 'user-registration' ),
							'save'           => esc_html__( 'Save', 'user-registration' ),
							'error_occurred' => esc_html__( 'An error occurred. Please try again.', 'user-registration' ),
						),
					)
				);
	}
}

return new URCR_Admin_Assets();

