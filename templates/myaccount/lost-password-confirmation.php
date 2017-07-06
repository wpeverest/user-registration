<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/lost-password-confirmation.php.
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
	exit;
}

ur_print_notices();
ur_print_notice( __( 'Password reset email has been sent.', 'user-registration' ) );
?>

<p><?php echo apply_filters( 'user_registration_lost_password_message', __( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'user-registration' ) ); ?></p>
