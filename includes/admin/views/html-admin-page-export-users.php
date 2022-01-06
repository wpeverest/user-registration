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
<h3 class="ur-settings-section-header main_header"><?php esc_html_e( 'Export Users', 'user-registration' ); ?></h3>
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'GENERAL', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php echo wp_kses_post( 'Export your users along with their extra information registered with a user registration form as a <strong>CSV</strong> file.', 'user-registration' ); ?>
						</p>

						<p>
							<select name="export_users" class="ur-input forms-list">
								<?php
								foreach ( $all_forms as $form_id => $form ) {
									echo '<option value ="' . esc_attr( $form_id ) . '">' . esc_html( $form ) . '</option>';
								}
								?>
							</select>
						</p>

						<input type="submit" class="button button-primary" name="user_registration_export_users" value="<?php esc_attr_e( 'Export Users', 'user-registration' ); ?>">

					</div>
				</div><!-- .postbox -->

		</div>
	</div>
</div>
