<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-lost-password.php.
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
	exit; // Exit if accessed directly.
}

ur_print_notices(); ?>

<div class="ur-frontend-form login" id="ur-frontend-form">
	<form method="post" class="user-registration-ResetPassword ur_lost_reset_password">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<p><?php echo esc_html( apply_filters( 'user_registration_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'user-registration' ) ) ); ?></p>

				<p class="user-registration-form-row user-registration-form-row--first form-row form-row-first">
					<label for="user_login"><?php esc_html_e( 'Username or email', 'user-registration' ); ?></label>
					<input class="user-registration-Input user-registration-Input--text input-text" type="text" name="user_login" id="user_login" />
				</p>

				<div class="clear"></div>

				<?php
				if ( ! empty( $recaptcha_node ) ) {
					echo '<div id="ur-recaptcha-node"> ' . $recaptcha_node . '</div>';  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>

				<?php do_action( 'user_registration_lostpassword_form' ); ?>

				<p class="user-registration-form-row form-row">
					<input type="hidden" name="ur_reset_password" value="true" />
					<input type="submit" class="user-registration-Button button" value="<?php echo esc_html( apply_filters( 'user_registration_lost_password_button_text', __( 'Reset password', 'user-registration' ) ) ); ?>" />
				</p>

				<?php wp_nonce_field( 'lost_password' ); ?>
			</div>
		</div>
	</form>
</div>
