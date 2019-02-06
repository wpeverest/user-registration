<?php
/**
 * Dashboard widget for user activity.
 *
 *
 * This template can be overridden by copying it to yourtheme/user-registration/dashboard-widget.php.
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
 * @since   1.5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php
	/**
	 * Dashboard Widget.
	 *
	 * @since 1.5.8
	 */
	do_action( 'user_registration_dashboard_widget_end' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
