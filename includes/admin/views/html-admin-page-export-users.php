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
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'Export Users', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php echo wp_kses_post( __( 'Export your users along with their extra information registered with a user registration form as a <strong>CSV</strong> file.', 'user-registration' ) ); ?>
						</p>

						<p>
							<select name="export_users" id="selected-export-user-form" class="ur-input forms-list ur-enhanced-select">
								<option value="" >
								<?php
								if ( ! empty( $all_forms ) ) {
									esc_html_e( 'Select Form', 'user-registration' );
								} else {
									esc_html_e( 'No Forms Available, please create one.', 'user-registration' );
								}
								?>
								</option>
								<?php
								foreach ( $all_forms as $form_id => $form ) {
									echo '<option value ="' . esc_attr( $form_id ) . '">' . esc_html( $form ) . '</option>';
								}
								?>
							</select>
						</p>

						<?php
						if ( ! empty( $all_forms ) ) {
							do_action( 'user_registration_custom_export_template', array_keys( $all_forms )[0] );
						}
						?>

						<input type="button"  class="button button-primary ur_export_user_action_button " name="user_registration_export_users" value="<?php esc_attr_e( 'Export Users', 'user-registration' ); ?>">

					</div>
				</div><!-- .postbox -->

		</div>
	</div>
</div>
