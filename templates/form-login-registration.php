<?php
/**
 * Login and Registration Form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-login-registration.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php
/**
 * Filter to modify the notice before login registration form.
 *
 * @param string content for login registration form notice.
 *
 * @return string content for login registration form notice.
 */
apply_filters( 'user_registration_login_registration_form_before_notice', ur_print_notices() );
?>

<?php
/**
 * Action to fire before the rendering of customer login registration form.
 */
do_action( 'user_registration_before_customer_login_registration_form' );
?>

<div class="ur-frontend-form login-registration">
	<div class="ur-form-row">
		<div class="ur-form-grid">
			<h2 class="ur-form-title"><?php echo esc_html__( 'Login', 'user-registration' ); ?></h2>
			<?php echo $login_form;  //phpcs:ignore;?>
		</div>
		<div class="ur-form-grid">
			<h2 class="ur-form-title"><?php echo esc_html__( get_the_title( $form_id ), 'user-registration' );  //phpcs:ignore;?></h2>
			<?php echo $registration_form;  //phpcs:ignore;?>
		</div>
	</div>
</div>

<?php
/**
 * Action to fire after the rendering of login registration form.
 */
do_action( 'user_registration_after_login_registration_form' ); ?>
