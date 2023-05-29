<?php
/**
 * Dashboard widget for user activity.
 *
 * This template can be overridden by copying it to yourtheme/user-registration/dashboard-widget.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @author  WPEverest
 * @package UserRegistration/Templates
 * @since   1.5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

	<div class="ur-dashboard-widget">
		<div class="ur-logo-wrap">
			<img src="<?php echo UR()->plugin_url() . '/assets/images/logo.png'; ?>">
			<div class="ur-plugin-info">
				<?php echo 'User Registration'; ?>
				<span class="ur-version"><?php echo __( 'Current Version:', 'user-registration' ) . UR_VERSION; ?></span>
			</div>
		</div>
		<div class="ur-dashboard-widget-forms">
			<select id="ur-dashboard-widget-forms" class="components-select-control__input">
			</select>
		</div>
	</div>
	<div class="ur-dashboard-widget-statictics">
		<ul>
			<li>
				<?php echo __( 'Today', 'user-registration' ); ?>
				<div class="ur-today-users">
				</div>
			</li>

			<li>
				<?php echo __( 'Last Week', 'user-registration' ); ?>
				<div class="ur-last-week-users">
				</div>
			</li>

			<li>
				<?php echo __( 'Last Month', 'user-registration' ); ?>
				<div class="ur-last-month-users">
				</div>
			</li>

			<li>
				<?php echo __( 'Total', 'user-registration' ); ?>
				<div class="ur-total-users">
				</div>
			</li>
		</ul>
	</div>
<?php
	/**
	 * Dashboard Widget.
	 *
	 * @since 1.5.8
	 */
	do_action( 'user_registration_dashboard_widget_end' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
