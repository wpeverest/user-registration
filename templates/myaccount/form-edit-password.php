<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-edit-password.php.
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

ur_do_deprecated_action( 'user_registration_before_edit_account_form', array(), '1.4.2', 'user_registration_before_change_password_form' );
do_action( 'user_registration_before_change_password_form' );
?>
<div class="ur-frontend-form login" id="ur-frontend-form">
	<form class="user-registration-EditAccountForm edit-password" action="" method="post">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<?php 
					ur_do_deprecated_action( 'user_registration_edit_account_form_start', array(), '1.4.2', 'user_registration_change_password_form_start' );
					do_action( 'user_registration_change_password_form_start' );
				?>
					<fieldset>
					<legend><?php _e( 'Password change', 'user-registration' ); ?></legend>

					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_current"><?php _e( 'Current password', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_current" id="password_current" />
					</p>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_1"><?php _e( 'New password', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_1" id="password_1" />
					</p>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_2"><?php _e( 'Confirm new password', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_2" id="password_2" />
					</p>
				</fieldset>
				<div class="clear"></div>

				<?php 
					ur_do_deprecated_action( 'user_registration_edit_account_form', array(), '1.4.2', 'user_registration_change_password_form' );
					do_action( 'user_registration_change_password_form' );
				?>

				<p>
					<?php wp_nonce_field( 'save_change_password' ); ?>
					<input type="submit" class="user-registration-Button button" name="save_change_password" value="<?php esc_attr_e( 'Save changes', 'user-registration' ); ?>" />
					<input type="hidden" name="action" value="save_change_password" />
				</p>

				<?php 
					ur_do_deprecated_action( 'user_registration_edit_account_form_end', array(), '1.4.2', 'user_registration_change_password_form_end' );
					do_action( 'user_registration_change_password_form_end' );
				?>
			</div>
		</div>
	</form>
</div>

<?php 
	ur_do_deprecated_action( 'user_registration_after_edit_account_form', array(), '1.4.1', 'user_registration_after_change_password_form' );
	do_action( 'user_registration_after_edit_account_form' ); 
?>
