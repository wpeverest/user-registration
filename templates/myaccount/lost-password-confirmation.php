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
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
	<div class="ur-message-container ">
	<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
	<path d="M12.5 22.4995C18.0228 22.4995 22.5 18.0224 22.5 12.4995C22.5 6.97666 18.0228 2.49951 12.5 2.49951C6.97715 2.49951 2.5 6.97666 2.5 12.4995C2.5 18.0224 6.97715 22.4995 12.5 22.4995Z" stroke="#49C85F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	<path d="M9.5 12.5005L11.5 14.5005L15.5 10.5005" stroke="#49C85F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
		<?php
			ur_print_notice( apply_filters( 'ur_password_reset_change_message', __( 'Password reset email has been sent.', 'user-registration' ) ) );
		?>

		<div class="ur-message-content">
			<?php
			echo esc_html(
				/**
				 * Filter to modify the user registration lost password message.
				 *
				 * @param string message content for user registration lost password.
				 * @return string message content of user registration lost password.
				 */
				apply_filters( 'user_registration_lost_password_message', esc_html__( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'user-registration' ) )
			);
			?>
	</div>
