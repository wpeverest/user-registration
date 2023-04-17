<?php
/**
 * Admin View: Page - Export Users
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<h3 class="ur-settings-section-header main_header"></h3>
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'GENERAL', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php echo wp_kses_post( __( 'Export your users along with their extra information registered with a user registration form as a <strong>CSV</strong> file.', 'user-registration' ) ); ?>
						</p>

						<p>
							<select name="export_users" class="ur-input forms-list">
								<option value="" ><?php esc_html_e( 'Select Form', 'user-registration' ); ?></option>
								<?php
								foreach ( $all_forms as $form_id => $form ) {
									echo '<option value ="' . esc_attr( $form_id ) . '">' . esc_html( $form ) . '</option>';
								}
								?>
							</select>
						</p>
						<?php do_action( 'user_registration_custom_export_template', array_keys( $all_forms )[0] ); ?>

						<input type="submit" class="button button-primary" name="user_registration_export_users" value="<?php esc_attr_e( 'Export Users', 'user-registration' ); ?>">

					</div>
				</div><!-- .postbox -->

		</div>
	</div>
</div>
