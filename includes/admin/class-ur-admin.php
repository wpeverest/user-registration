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
		add_action( 'load-users.php', array( $this, 'live_user_read' ), 10, 2 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'admin_footer', 'ur_print_js', 25 );
		add_filter( 'heartbeat_received', array( $this, 'new_user_live_notice' ), 10, 2 );
		add_filter( 'admin_body_class', array( $this, 'user_registration_add_body_classes' ) );
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
				jQuery( 'a.ur-rating-link' ).on('click', function() {
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
					<img src="<?php echo UR()->plugin_url() . '/assets/images/UR-Logo.png'; ?>" alt="">
				</div>
				<div class="user-registration-review-text">

						<h3><?php _e( 'HAKUNA <strong>MATATA!</strong>', 'user-registration' ); ?></h3>
						<P><?php _e( '( The above word is just to draw your attention. <span class="dashicons dashicons-smiley smile-icon"></span> )', 'user-registration' ); ?> </P>
						<p><?php _e( 'Hope you are having nice experience with <strong>User Registration</strong> plugin. Please provide this plugin a nice review.', 'user-registration' ); ?></p> 
						<p class="extra-pad"><?php _e('<strong>What benefit would you have?</strong> <br>
				Basically, it would encourage us to release updates regularly with new features & bug fixes so that you can keep on using the plugin without any issues and also to provide free support like we have been doing. <span class="dashicons dashicons-smiley smile-icon"></span><br>', 'user-registration' ); ?></p>

					<ul class="user-registration-review-ul">
						<li><a class="button button-primary" href="https://wordpress.org/support/plugin/user-registration/reviews/#postform" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure, I\'d love to!', 'user-registration' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-dismiss"><span  class="dashicons dashicons-smiley"></span><?php _e( 'I already did!', 'user-registration' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-dismiss"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'user-registration' ); ?></a></li>
						<li><a href="https://wpeverest.com/support-forum/" class="button button-secondary notice-have-query"><span class="dashicons dashicons-testimonial"></span><?php _e( 'I have a query', 'user-registration' ); ?></a></li>
					 </ul>
				</div>
			</div>
		<?php
	}

	/**
	 * Mark the read time of the user list table.
	 */
	public function live_user_read() {
		$now = current_time( 'mysql' );
		update_option( 'user_registration_users_listing_viewed', $now );
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
			update_option( 'user_registration_users_listing_viewed', $now );
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

		$response['user_registration_new_user_message'] = sprintf( __( '%1$d new %2$s registered.', 'user-registration' ), $user_count, _n( 'User', 'Users', $user_count, 'user-registration' ) );
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
}

return new UR_Admin();
