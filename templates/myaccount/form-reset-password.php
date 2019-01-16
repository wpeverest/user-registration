<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-reset-password.php.
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
	<form method="post" class="user-registration-ResetPassword lost_reset_password">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<p><?php echo apply_filters( 'user_registration_reset_password_message', __( 'Enter a new password below.', 'user-registration' ) ); ?></p>

				<p class="user-registration-form-row user-registration-form-row--first form-row form-row-first">
					<label for="password_1"><?php _e( 'New password', 'user-registration' ); ?> <span class="required">*</span></label>
					<input type="password" class="user-registration-Input user-registration-Input--text input-text" name="password_1" id="password_1" />
				</p>
				<p class="user-registration-form-row user-registration-form-row--last form-row form-row-last">
					<label for="password_2"><?php _e( 'Re-enter new password', 'user-registration' ); ?> <span class="required">*</span></label>
					<input type="password" class="user-registration-Input user-registration-Input--text input-text" name="password_2" id="password_2" />
				</p>

				<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
				<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

				<div class="clear"></div>

				<?php do_action( 'user_registration_resetpassword_form' ); ?>

				<p class="user-registration-form-row form-row">
					<input type="hidden" name="ur_reset_password" value="true" />
					<input type="submit" class="user-registration-Button button" value="<?php esc_attr_e( 'Save', 'user-registration' ); ?>" />
				</p>

				<?php wp_nonce_field( 'reset_password' ); ?>
			</div>
		</div>
	</form>
</div>
