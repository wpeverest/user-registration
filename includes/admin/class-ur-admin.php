<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Admin
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin Class
 */
class UR_Admin {

	/**
	 * UR_Admin Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ), 10, 2 );
		add_action( 'load-users.php', array( $this, 'live_user_read' ), 10, 2 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'admin_notices', array( $this, 'survey_notice' ) );
		add_action( 'admin_notices', array( $this, 'allow_usage_notice' ) );
		add_action( 'admin_footer', 'ur_print_js', 25 );
		add_filter( 'heartbeat_received', array( $this, 'new_user_live_notice' ), 10, 2 );
		add_filter( 'admin_body_class', array( $this, 'user_registration_add_body_classes' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'admin_init', array( $this, 'template_actions' ) );
		add_filter( 'display_post_states', array( $this, 'ur_add_post_state' ), 10, 2 );
	}

	/**
	 * Add Tag for My Account to know which page is current my account page.
	 *
	 * @param mixed  $post_states Tags.
	 * @param object $post Post.
	 */
	public function ur_add_post_state( $post_states, $post ) {

		$my_account_page_id = get_option( 'user_registration_myaccount_page_id' );

		if ( $post->ID === $my_account_page_id ) {
			$post_states[] = __( 'UR My Account Page', 'user-registration' );
		}

		return $post_states;
	}

	/**
	 * Includes any classes we need within admin.
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/functions-ur-admin.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-notices.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-menus.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-export-users.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-import-export-forms.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-form-modal.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-user-list-manager.php';
		include_once UR_ABSPATH . 'includes' . UR_DS . 'admin' . UR_DS . 'class-ur-admin-assets.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-form-templates.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-deactivation-feedback.php';

		// Setup/welcome.
		if ( ! empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			switch ( $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				case 'user-registration-welcome':
					include_once dirname( __FILE__ ) . '/class-ur-admin-welcome.php';
					break;
			}
		}
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		switch ( $screen->id ) {
			case 'users':
			case 'user':
			case 'profile':
			case 'user-edit':
				include 'class-ur-admin-profile.php';
				break;
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' from accessing admin.
	 */
	public function prevent_admin_access() {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}
		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$user_meta    = get_userdata( $user_id );
			$user_roles   = $user_meta->roles;
			$option_roles = get_option( 'user_registration_general_setting_disabled_user_roles', array() );
			if ( ! is_array( $option_roles ) ) {
				$option_roles = array();
			}

			if ( ! in_array( 'administrator', $user_roles, true ) ) {
				$result = array_intersect( $user_roles, $option_roles );

				if ( count( $result ) > 0 && apply_filters( 'user_registration_prevent_admin_access', true ) ) {
					wp_safe_redirect( esc_url_raw( ur_get_page_permalink( 'myaccount' ) ) );
					exit;
				}
			}
		}
	}

	/**
	 * Change the admin footer text on User Registration admin pages.
	 *
	 * @since  1.1.2
	 *
	 * @param  string $footer_text User Registration Plugin footer text.
	 *
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_user_registration' ) || ! function_exists( 'ur_get_screen_ids' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();
		$ur_pages       = ur_get_screen_ids();

		// Set only UR pages.
		$ur_pages = array_diff( $ur_pages, array( 'profile', 'user-edit' ) );

		// Check to make sure we're on a User Registration admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'user_registration_display_admin_footer_text', in_array( $current_screen->id, $ur_pages, true ) ) ) {
			// Change the footer text.
			if ( ! get_option( 'user_registration_admin_footer_text_rated' ) ) {
				$footer_text = wp_kses_post(
					sprintf(
						/* translators: 1: User Registration 2:: five stars */
						__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'user-registration' ),
						sprintf( '<strong>%s</strong>', esc_html( 'User Registration' ) ),
						'<a href="https://wordpress.org/support/plugin/user-registration/reviews?rate=5#new-post" target="_blank" class="ur-rating-link" data-rated="' . esc_attr__( 'Thank You!', 'user-registration' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
					)
				);
				ur_enqueue_js(
					"
				jQuery( 'a.ur-rating-link' ).on('click', function() {
						jQuery.post( '" . UR()->ajax_url() . "', { action: 'user_registration_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				"
				);
			} else {
				$footer_text = esc_html__( 'Thank you for using User Registration.', 'user-registration' );
			}
		}

		return $footer_text;
	}

	/**
	 * Review notice on header.
	 *
	 * @since  1.5.8
	 * @return void
	 */
	public function review_notice() {

		$notice_type = 'review';
		$show_notice = $this->show_promotional_notice( $notice_type );
		if ( ! $show_notice ) {
			return;
		}
		// Return if activation date is less than 7 days.
		if ( ur_check_activation_date( '7' ) === false ) {
			return;
		}

		$notice_header      = __( 'HAKUNA <strong>MATATA!</strong>', 'user-registration' );
		$notice_target_link = 'https://wordpress.org/support/plugin/user-registration/reviews/#postform';

		include dirname( __FILE__ ) . '/views/html-notice-promotional.php';
	}

	/**
	 * Check whether notice is showable or not.
	 *
	 * @param string $notice_type Notice Type.
	 * @param string $days Number of days for temparary dismissed.
	 * @return bool
	 */
	public function show_promotional_notice( $notice_type, $days = '1' ) {

		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$notice_dismissed             = get_option( 'user_registration_' . $notice_type . '_notice_dismissed', 'no' );
		$notice_dismissed_temporarily = get_option( 'user_registration_' . $notice_type . '_notice_dismissed_temporarily', '' );

		if ( 'yes' === $notice_dismissed ) {
			return false;
		}

		// Return if dismissed date is less than a day.
		if ( '' !== $notice_dismissed_temporarily ) {

			$days_to_validate = strtotime( $notice_dismissed_temporarily );
			$days_to_validate = strtotime( '+' . $days . ' day', $days_to_validate );
			$days_to_validate = date_i18n( 'Y-m-d', $days_to_validate );

			$current_date = date_i18n( 'Y-m-d' );

			if ( $current_date < $days_to_validate ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Allow Usage Notice
	 *
	 * @since  2.3.2
	 */
	public function allow_usage_notice() {

		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$allow_usage_tracking     = get_option( 'user_registration_allow_usage_tracking', null );
		$allow_usage_notice_shown = get_option( 'user_registration_allow_usage_notice_shown', false );

		if ( null !== $allow_usage_tracking || $allow_usage_notice_shown ) {
			return false;
		}

		if ( ur_check_updation_date( '1' ) === true ) {
			$notice_type        = 'allow_usage';
			$notice_header      = __( 'Contribute to the enhancement', 'user-registration' );
			$notice_target_link = '#';
			include dirname( __FILE__ ) . '/views/html-notice-promotional.php';
		} else {
			return false;
		}
	}

	/**
	 * Survey notice on header.
	 *
	 * @since  2.0.1
	 * @return void
	 */
	public function survey_notice() {

		$notice_type = 'survey';
		$show_notice = $this->show_promotional_notice( $notice_type );
		if ( ! $show_notice ) {
			return;
		}

		// Return if license key not found.
		$license_key = trim( get_option( 'user-registration_license_key' ) );

		if ( $license_key && ur_check_activation_date( '10' ) === true ) {
			$notice_header      = __( 'User Registration Plugin Survey', 'user-registration' );
			$notice_target_link = 'https://forms.office.com/pages/responsepage.aspx?id=c04iBAejyEWvNQDb6GzDCILyv8m6NoBDvJVtRTCcOvBUNk5OSTA4OEs1SlRPTlhFSFZXRFA0UFEwRCQlQCN0PWcu';

			include dirname( __FILE__ ) . '/views/html-notice-promotional.php';
		} else {
			return;
		}
	}

	/**
	 * Mark the read time of the user list table.
	 */
	public function live_user_read() {
		$now = current_time( 'mysql' );
		update_option( 'user_registration_users_listing_viewed', sanitize_text_field( $now ) );
	}

	/**
	 * Check for new user by read time.
	 *
	 * @param array $response Heartbeat response data to pass back to front end.
	 * @param array $data Data received from the front end (unslashed).
	 */
	public function new_user_live_notice( $response, $data ) {

		if ( empty( $data['user_registration_new_user_notice'] ) ) {
			return $response;
		}

		$read_time = get_option( 'user_registration_users_listing_viewed' );
		if ( ! $read_time ) {
			$now = current_time( 'mysql' );
			update_option( 'user_registration_users_listing_viewed', sanitize_text_field( $now ) );
			$read_time = $now;
		}

		$user_args  = array(
			'meta_key'    => 'ur_form_id',
			'count_total' => true,
			'date_query'  => array(
				array(
					'after'     => $read_time,
					'inclusive' => false,
				),
			),
		);
		$user_query = new WP_User_Query( $user_args );
		$user_count = $user_query->get_total();

		/* translators: 1: Newly registered user count 2: User */
		$response['user_registration_new_user_message'] = sprintf( esc_html__( '%1$d new %2$s registered.', 'user-registration' ), $user_count, _n( 'User', 'Users', $user_count, 'user-registration' ) );
		$response['user_registration_new_user_count']   = $user_count;
		return $response;
	}

	/**
	 * Add user-registration class to body in admin.
	 *
	 * @param string $classes Class to add to body.
	 */
	public function user_registration_add_body_classes( $classes ) {
		global $current_screen;

		// Check if the screen contains user-registration_page_ as prefix inorder to make sure the page is user registration plugin's page.
		if ( strpos( $current_screen->id, 'user-registration_page_' ) !== false ) {
			$classes = 'user-registration';
		};
		return $classes;
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 *
	 * @since 2.1.4
	 */
	public function admin_redirects() {
		if ( ! get_transient( '_ur_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_ur_activation_redirect' );

		if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'user-registration-welcome' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.StrictInArray.MissingTrueStrict
			return;
		}

		// If plugin is running for first time, redirect to onboard page.
		if ( '1' === get_option( 'user_registration_first_time_activation_flag' ) ) {
			wp_safe_redirect( admin_url( 'index.php?page=user-registration-welcome' ) );
			exit;
		}
	}

	/**
	 * Handle redirects after template refresh.
	 */
	public function template_actions() {
		if ( isset( $_GET['page'], $_REQUEST['action'] ) && 'add-new-registration' === $_GET['page'] ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );

			$templates = UR_Admin_Form_Templates::get_template_data();

			$templates = is_array( $templates ) ? $templates : array();

			if ( 'ur-template-refresh' === $action && ! empty( $templates ) ) {
				if ( empty( $_GET['ur-template-nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['ur-template-nonce'] ) ), 'refresh' ) ) {
					wp_die( esc_html_e( 'Could not verify nonce', 'user-registration' ) );
				}

				foreach ( array( 'ur_pro_license_plan', 'ur_template_section_list' ) as $transient ) {
					delete_transient( $transient );
				}

				// Redirect to the builder page normally.
				wp_safe_redirect( admin_url( 'admin.php?page=add-new-registration' ) );
				exit;
			}
		}
	}
}

return new UR_Admin();
