<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Admin
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
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
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'admin_footer', 'ur_print_js', 25 );

		if ( 'admin_approval' === get_option( 'user_registration_general_setting_login_options' ) ) {
			new UR_Admin_User_List_Manager();
		}
	}

	/**
	 * Includes any classes we need within admin.
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/functions-ur-admin.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-notices.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-menus.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-export-users.php';
		include_once dirname( __FILE__ ) . '/class-ur-admin-form-modal.php';

		include_once UR_ABSPATH . 'includes' . UR_DS . 'admin' . UR_DS . 'class-ur-admin-assets.php';
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		if ( ! $screen = get_current_screen() ) {
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
					wp_safe_redirect( ur_get_page_permalink( 'myaccount' ) );
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
	 * @param  string $footer_text
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
		if ( isset( $current_screen->id ) && apply_filters( 'user_registration_display_admin_footer_text', in_array( $current_screen->id, $ur_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'user_registration_admin_footer_text_rated' ) ) {
				$footer_text = sprintf(
					/* translators: 1: WooCommerce 2:: five stars */
					__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'user-registration' ),
					sprintf( '<strong>%s</strong>', esc_html__( 'User Registration', 'user-registration' ) ),
					'<a href="https://wordpress.org/support/plugin/user-registration/reviews?rate=5#new-post" target="_blank" class="ur-rating-link" data-rated="' . esc_attr__( 'Thank You!', 'user-registration' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
				ur_enqueue_js(
					"
				jQuery( 'a.ur-rating-link' ).click( function() {
						jQuery.post( '" . UR()->ajax_url() . "', { action: 'user_registration_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				"
				);
			} else {
				$footer_text = __( 'Thank you for using User Registration.', 'user-registration' );
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

		// Show only to Admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notice_dismissed = get_option( 'user_registration_review_notice_dismissed', 'no' );

		if ( 'yes' == $notice_dismissed ) {
			return;
		}

		// Return if activation date is less than 30 days.
		if ( ur_check_activation_date() === false ) {
			return;
		}

		?>
			<div id="user-registration-review-notice" class="notice notice-info user-registration-review-notice">
				<div class="user-registration-review-thumbnail">
					<img src="<?php echo UR()->plugin_url() . '/assets/images/logo.png'; ?>" alt="">
				</div>
				<div class="user-registration-review-text">

						<h3><?php _e( 'Enjoying <strong>User Registration</strong>?', 'user-registration' ); ?></h3>
						<p><?php _e( 'Looks like you have been using user registration for a while. Would you do us some favour and leave a review on <a href="https://wordpress.org/support/plugin/user-registration/reviews/#new-post" target="_blank"><strong>WordPress.org</strong></a>?', 'user-registration' ); ?></p>

					<ul class="user-registration-review-ul">
						<li><a class="button button-primary" href="https://wordpress.org/support/plugin/user-registration/reviews/#postform" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure, I\'d love to!', 'user-registration' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-dismiss"><span  class="dashicons dashicons-smiley"></span><?php _e( 'I already did!', 'user-registration' ); ?></a></li>
						<li><a href="#" class="button button-link notice-dismiss"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'user-registration' ); ?></a></li>
					 </ul>
				</div>
			</div>
		<?php
	}
}

return new UR_Admin();
