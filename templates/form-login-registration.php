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
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
 * @package UserRegistration/Templates
 * @version 1.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php apply_filters( 'user_registration_login_registration_form_before_notice', ur_print_notices() ); ?>

<?php do_action( 'user_registration_before_customer_login_registration_form' ); ?>

<div class="ur-frontend-form login-registration">
	<div class="ur-form-row">
		<div class="ur-form-grid">
			<h2 class="ur-form-title"><?php echo __( 'Login', 'user-registration' ); ?></h2>
			<?php echo $login_form; ?>
		</div>
		<div class="ur-form-grid">
			<h2 class="ur-form-title"><?php echo __( 'Registration', 'user-registration' ); ?></h2>
			<?php echo $registration_form; ?>
		</div>
	</div>
</div>

<?php do_action( 'user_registration_after_login_registration_form' ); ?>
