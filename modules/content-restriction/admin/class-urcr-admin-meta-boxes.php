<?php
/**
 * User Registration Content Restriction Meta Boxes
 *
 * Sets up the write panels used by custom post types.
 *
 * @class    URCR_Admin_Meta_Boxes
 * @version  4.0
 * @package  UserRegistrationContentRestriction/Admin/Meta Boxes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URCR_Admin_Meta_Boxes Class
 */
if ( ! defined( 'UR_PLUGIN_FILE' ) ) {
	return;
}
if ( ! class_exists( 'UR_Meta_Boxes' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-meta-boxes.php';
}

class URCR_Admin_Meta_Box extends UR_Meta_Boxes {
	/**
	 * ID for module
	 *
	 * @var bool
	 */
	public $id = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id = 'content_restriction';

		if ( get_option( 'user_registration_content_restriction_enable' ) == 'no' ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		}
	}

	public function register_scripts( $hook ) {
		// enqueue scripts here.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( in_array( $screen_id, ur_get_screen_ids(), true ) || $hook == 'post-new.php' || $hook == 'post.php' ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'custom-js', UR()->plugin_url() . '/assets/js/modules/content-restriction/admin/urcr-custom' . $suffix . '.js', array( 'jquery', 'ur-enhanced-select', 'selectWoo' ), UR_VERSION );
			wp_enqueue_script( 'custom-js' );
			wp_enqueue_style( 'select2', UR()->plugin_url() . '/assets/css/select2/select2.css', array(), UR_VERSION );

			wp_register_style( 'user-registration-metabox', UR()->plugin_url() . '/assets/css/metabox.css', array(), UR_VERSION );
			wp_enqueue_style( 'user-registration-metabox' );
		}
	}

	public function register_styles() {
		// enqueue styles here.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( in_array( $screen_id, ur_get_screen_ids(), true ) ) {
			wp_enqueue_style( 'select2' );
		}
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {
		add_meta_box(
			'urcr-meta-box',
			__( 'Restrict This Content', 'user-registration' ),
			array( $this, 'render_metabox' ),
			$screen = null,
			'advanced',
			'default'
		);
	}

	/**
	 * Renders the meta box.
	 */
	public function render_metabox( $post ) {

		echo '<p>' . esc_html__( 'Use shortcode [urcr_restrict]....[/urcr_restrict] to restrict partial contents.', 'user-registration' ) . '</p>';
		$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

		$this->ur_metabox_checkbox(
			array(
				'id'       => 'urcr_meta_checkbox',
				'label'    => 'Restrict Access to This Page/Post',
				'type'     => 'Checkbox',
				'disabled' => $whole_site_access_restricted ? true : false,
			)
		);

		if ( $whole_site_access_restricted ) {
			echo '<p class="notice notice-info " style="padding: 10px; margin: -10px 0 0 0;">' . sprintf( __( 'Currently this setting is disabled and will not work because whole site restriction is enabled in <a href="%s" target="_blank" style="text-decoration:underline;" >global restriction settings</a>', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=content_restriction' ) ) . '</p>';
		} else {
			echo '<p style="margin: -10px 0 0 0;">' . sprintf( __( 'When enabled, the page/post will be restricted as per the <a href="%s" target="_blank" style="text-decoration:underline;" >global restriction settings</a>', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=content_restriction' ) ) . '</p>';
		}

		$this->ur_metabox_checkbox(
			array(
				'id'    => 'urcr_meta_override_global_settings',
				'label' => 'Override Global Settings:',
				'type'  => 'Checkbox',
			)
		);
		echo '<p  style="margin: -10px 0 0 0;">' . sprintf( __( 'Set custom restriction setting for this page/post, overriding the <a href="%s" target="_blank" style="text-decoration:underline;" >global restriction settings</a>', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=content_restriction' ) ) . '</p>';

		$this->ur_metabox_select(
			array(
				'id'      => 'urcr_allow_to',
				'label'   => __( 'Allow Access To: ', 'user-registration' ),
				'options' => array( 'All Logged In Users', 'Choose Specific Roles', 'Guest Users', 'Memberships' ),
				'desc'    => __( 'Only select this if you want to override global setting for allow option', 'user-registration' ),
				'class'   => 'ur-enhanced-select',
			)
		);

		$this->ur_metabox_multiple_select(
			array(
				'id'      => 'urcr_meta_roles[]',
				'label'   => __( 'Allow Access To Roles: ', 'user-registration' ),
				'options' => ur_get_all_roles(),
				'desc'    => __( 'Only select this if you want to override global setting for access roles', 'user-registration' ),
				'class'   => 'ur-enhanced-select',
			)
		);

		$this->ur_metabox_multiple_select(
			array(
				'id'      => 'urcr_meta_memberships[]',
				'label'   => __( 'Allow Access To Memberships: ', 'user-registration' ),
				'options' => get_active_membership_id_name(),
				'desc'    => __( 'Only select this if you want to override global setting for membership', 'user-registration' ),
				'class'   => 'ur-enhanced-select',
			)
		);

		$this->ur_metabox_textarea(
			array(
				'id'      => 'urcr_meta_content',
				'label'   => __( 'Restricted Content Message: ', 'user-registration' ),
				'desc'    => __( 'Enter the message to show to users who do not have access to this content.', 'user-registration' ),
				'type'   => 'tinymce',
				'default' =>  __( 'This content is restricted!', 'user-registration' ),
			)
		);

		do_action( 'render_metabox_complete' );
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return null
	 */
	public function save_metabox( $post_id, $post ) {

		if ( empty( $_POST ) ) {
			return false;
		}

		$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

		$checkbox = isset( $_POST['urcr_meta_checkbox'] ) ? $_POST['urcr_meta_checkbox'] : '';

		$override_global_settings = isset( $_POST['urcr_meta_override_global_settings'] ) ? $_POST['urcr_meta_override_global_settings'] : '';

		$allow_to = isset( $_POST['urcr_allow_to'] ) ? $_POST['urcr_allow_to'] : '';

		$array_of_roles = isset( $_POST['urcr_meta_roles'] ) ? $_POST['urcr_meta_roles'] : '';

		$array_of_memberships = isset( $_POST['urcr_meta_memberships'] ) ? $_POST['urcr_meta_memberships'] : '';

		$restricted_message = isset( $_POST['urcr_meta_content'] ) ? $_POST['urcr_meta_content'] : '';

		if ( ! $whole_site_access_restricted ) {
			update_post_meta( $post_id, 'urcr_meta_checkbox', $checkbox );
		}

		update_post_meta( $post_id, 'urcr_meta_override_global_settings', $override_global_settings );

		update_post_meta( $post_id, 'urcr_allow_to', $allow_to );

		update_post_meta( $post_id, 'urcr_meta_roles', $array_of_roles );

		update_post_meta( $post_id, 'urcr_meta_memberships', $array_of_memberships );

		update_post_meta( $post_id, 'urcr_meta_content', $restricted_message );

		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['custom_nonce'] ) ? $_POST['custom_nonce'] : '';
		$nonce_action = 'custom_nonce_action';

		// Check if nonce is set.
		if ( ! isset( $nonce_name ) ) {
			return;
		}

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
	}
}

new URCR_Admin_Meta_Box();
