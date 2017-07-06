<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-edit-account.php.
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

do_action( 'user_registration_before_edit_account_form' ); ?>

<div class="ur-frontend-form login" id="ur-frontend-form">
	<form class="user-registration-EditAccountForm edit-account" action="" method="post">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<?php do_action( 'user_registration_edit_account_form_start' ); ?>

				<p class="user-registration-form-row user-registration-form-row--first form-row form-row-first">
					<label for="account_first_name"><?php _e( 'First name', 'user-registration' ); ?> <span class="required">*</span></label>
					<input type="text" class="user-registration-Input user-registration-Input--text input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
				</p>
				<p class="user-registration-form-row user-registration-form-row--last form-row form-row-last">
					<label for="account_last_name"><?php _e( 'Last name', 'user-registration' ); ?> <span class="required">*</span></label>
					<input type="text" class="user-registration-Input user-registration-Input--text input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
				</p>
				<div class="clear"></div>

				<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
					<label for="account_email"><?php _e( 'Email address', 'user-registration' ); ?> <span class="required">*</span></label>
					<input type="email" class="user-registration-Input user-registration-Input--email input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" />
				</p>

				<fieldset>
					<legend><?php _e( 'Password change', 'user-registration' ); ?></legend>

					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_current"><?php _e( 'Current password (leave blank to leave unchanged)', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_current" id="password_current" />
					</p>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_1"><?php _e( 'New password (leave blank to leave unchanged)', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_1" id="password_1" />
					</p>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<label for="password_2"><?php _e( 'Confirm new password', 'user-registration' ); ?></label>
						<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_2" id="password_2" />
					</p>
				</fieldset>
				<div class="clear"></div>

				<?php do_action( 'user_registration_edit_account_form' ); ?>

				<p>
					<?php wp_nonce_field( 'save_account_details' ); ?>
					<input type="submit" class="user-registration-Button button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'user-registration' ); ?>" />
					<input type="hidden" name="action" value="save_account_details" />
				</p>

				<?php do_action( 'user_registration_edit_account_form_end' ); ?>
			</div>
		</div>
	</form>
</div>

<?php do_action( 'user_registration_after_edit_account_form' ); ?>
