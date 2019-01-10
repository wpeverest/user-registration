<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
 * @author  WPEverest
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<p>
<?php
	/* translators: 1: user display name 2: logout url */
	printf(
		__( 'Hello %1$s (not %1$s? <a href="%2$s">Sign out</a>)', 'user-registration' ),
		'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
		esc_url( ur_logout_url( ur_get_page_permalink( 'myaccount' ) ) )
	);

	?>
	</p>

<p>
<?php
	/* translators: 1 profile details url, 2: change password url */
	printf(
		__( 'From your account dashboard you can edit your <a href="%1$s"> profile details</a> and <a href="%2$s">edit your password</a>.', 'user-registration' ),
		esc_url( ur_get_endpoint_url( 'edit-profile' ) ),
		esc_url( ur_get_endpoint_url( 'edit-password' ) )
	);
	?>
	</p>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'user_registration_account_dashboard' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
