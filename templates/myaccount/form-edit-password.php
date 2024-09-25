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
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Deprecated in version 1.4.2. Use 'user_registration_before_change_password_form' instead.
 *
 * @deprecated 1.4.2 Use 'user_registration_before_change_password_form' instead.
 *
 * @param array array value.
 * @param string deprecated_version.
 * @param string hook_name to be used instead.
 */
ur_do_deprecated_action( 'user_registration_before_edit_account_form', array(), '1.4.2', 'user_registration_before_change_password_form' );

/**
 * Fires before rendering the user registration password form.
 *
 * The 'user_registration_before_change_password_form' action allows developers to hook into the process
 * and execute custom code or modifications before the change password form is displayed.
 */
do_action( 'user_registration_before_change_password_form' );

$layout = get_option( 'user_registration_my_account_layout', 'horizontal' );

if ( 'vertical' === $layout ) {
	?>
	<div class="user-registration-MyAccount-content__header">
		<h1><?php echo wp_kses_post( $endpoint_label ); ?></h1>
	</div>
	<?php
}
?>
<div class="user-registration-MyAccount-content__body">
	<div class="ur-frontend-form login" id="ur-frontend-form">
		<form class="user-registration-EditAccountForm edit-password" action="" method="post" data-enable-strength-password="<?php echo esc_attr( $enable_strong_password ); ?>" data-minimum-password-strength="<?php echo esc_attr( $minimum_password_strength ); ?>" >
			<div class="ur-form-row">
				<div class="ur-form-grid">
					<?php
						/**
						 * Deprecated in version 1.4.2. Use 'user_registration_change_password_form_start' instead.
						 *
						 * @deprecated 1.4.2 Use 'user_registration_change_password_form_start' instead.
						 *
						 * @param array array value.
						 * @param string derprected_version.
						 * @param string hook_name to be used instead.
						 */
						ur_do_deprecated_action( 'user_registration_edit_account_form_start', array(), '1.4.2', 'user_registration_change_password_form_start' );

						/**
						 * Fires at the starting of user registration change password form.
						 *
						 * The 'user_registration_change_password_form_start' action allows developers to hook into the process
						 * and execute custom code or modifications on the start of the change password form.
						 */
						do_action( 'user_registration_change_password_form_start' );
					?>
					<fieldset>

						<?php
						if (
							/**
							 *  Filters the display of the current password input field in the change password form.
							 *
							 *  @param bool Whether to display the current password input field. Default is true.
							 *  @return bool.
							 */
							apply_filters( 'user_registration_change_password_current_password_display', true ) ) {
							?>
						<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide hide_show_password">
							<label for="password_current"><?php esc_html_e( 'Current password', 'user-registration' ); ?></label>
							<span class="password-input-group">
							<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_current" id="password_current" />
									<?php
									if ( ur_option_checked( 'user_registration_login_option_hide_show_password', false ) ) {
										echo '<a href="javaScript:void(0)" class="password_preview dashicons dashicons-hidden" title="' . esc_attr__( 'Show Password', 'user-registration' ) . '"></a>';
									}
									?>
							</span>
						</p>
						<?php } ?>
						<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide hide_show_password">
							<label for="password_1"><?php esc_html_e( 'New password', 'user-registration' ); ?></label>
							<span class="password-input-group">
							<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_1" id="password_1" />
							<?php
							if ( ur_option_checked( 'user_registration_login_option_hide_show_password', false ) ) {
								echo '<a href="javaScript:void(0)" class="password_preview dashicons dashicons-hidden" title="' . esc_attr__( 'Show Password', 'user-registration' ) . '"></a>';
							}
							?>
							</span>
						</p>
						<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide hide_show_password">
							<label for="password_2"><?php esc_html_e( 'Confirm new password', 'user-registration' ); ?></label>
							<span class="password-input-group">
							<input type="password" class="user-registration-Input user-registration-Input--password input-text" name="password_2" id="password_2" />
							<?php
							if ( ur_option_checked( 'user_registration_login_option_hide_show_password' ) ) {
								echo '<a href="javaScript:void(0)" class="password_preview dashicons dashicons-hidden" title="' . esc_attr__( 'Show Password', 'user-registration' ) . '"></a>';
							}
							?>
							</span>
						</p>
					</fieldset>
					<div class="clear"></div>

					<?php
						/**
						 * Deprecated in version 1.4.2. Use 'user_registration_change_password_form' instead.
						 *
						 * @deprecated 1.4.2 Use 'user_registration_change_password_form' instead.
						 *
						 * @param array array value.
						 * @param string derprected_version.
						 * @param string hook_name to be used instead.
						 */
						ur_do_deprecated_action( 'user_registration_edit_account_form', array(), '1.4.2', 'user_registration_change_password_form' );

						/**
						 * Fires after rendering the change password form.
						 *
						 * The 'user_registration_change_password_form' action allows developers to hook into the process
						 * and execute custom code or modifications after the change password form is displayed.
						 * Developers can use this action to add additional elements, perform actions, or make
						 * adjustments to the form as needed.
						 */
						do_action( 'user_registration_change_password_form' );
					?>

					<p>
						<?php wp_nonce_field( 'save_change_password' ); ?>
						<input type="submit" class="user-registration-Button button" name="save_change_password" value="<?php esc_attr_e( 'Save changes', 'user-registration' ); ?>" />
						<input type="hidden" name="action" value="save_change_password" />
					</p>

					<?php
						/**
						 * Deprecated in version 1.4.2. Use 'user_registration_change_password_form_end' instead.
						 *
						 * @deprecated 1.4.2 Use 'user_registration_change_password_form_end' instead.
						 *
						 * @param array array value.
						 * @param string derprected_version.
						 * @param string hook_name to be used instead.
						 */
						ur_do_deprecated_action( 'user_registration_edit_account_form_end', array(), '1.4.2', 'user_registration_change_password_form_end' );

						/**
						 * Fires at the end of rendering the change password form.
						 *
						 * The 'user_registration_change_password_form_end' action allows developers to hook into the process
						 * and execute custom code or modifications at the end of change password form.
						 */
						do_action( 'user_registration_change_password_form_end' );
					?>
				</div>
			</div>
		</form>
	</div>

	<?php
	/**
	 * Deprecated in version 1.4.2. Use 'user_registration_after_change_password_form' instead.
	 *
	 * @deprecated 1.4.2 Use 'user_registration_after_change_password_form' instead.
	 *
	 * @param array array value.
	 * @param string derprected_version.
	 * @param string hook_name to be used instead.
	 */
		ur_do_deprecated_action( 'user_registration_after_edit_account_form', array(), '1.4.1', 'user_registration_after_change_password_form' );

		/**
		 * Fires after rendering the edit account form.
		 *
		 * The 'user_registration_after_edit_account_form' action allows developers to hook into the process
		 * and execute custom code or modifications after edit account form is displayed.
		 */
		do_action( 'user_registration_after_edit_account_form' );
	?>
</div>
