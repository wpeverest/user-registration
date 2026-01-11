<?php
/**
 * Import / Export Forms.
 *
 * @package Admin View: Page - Import / Export Forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'Export Forms With Settings', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php echo wp_kses_post( __( 'Export your forms along with their settings as <strong>JSON</strong> file.', 'user-registration' ) ); ?>
						</p>

						<p>
							<select name="formid[]" id="selected-export-forms" class="ur-input forms-list ur-select2-multiple" multiple>
								<?php
								foreach ( $all_forms as $form_id => $form ) {
									echo '<option value ="' . esc_attr( $form_id ) . '">' . esc_html( $form ) . '</option>';
								}
								?>
							</select>
						</p>

						<input type="button" class="button button-primary ur_export_form_action_button" name="user_registration_export_form" value="<?php esc_html_e( 'Export Forms', 'user-registration' ); ?>">

					</div>
				</div><!-- .postbox -->
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'Import Forms With Settings', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php echo wp_kses_post( __( 'Import your forms along with their settings from <strong>JSON</strong> file.', 'user-registration' ) ); ?>
						</p>
						<div class="ur-form-group">
							<div class="user-registration-custom-file">
								<input type="file" class="user-registration-custom-file__input" name="jsonfile" id="jsonfile" accept=".json"/>
								<label class="user-registration-custom-file__label" for="csvfile">
									<span class="user-registration-custom-selected-file"><?php esc_html_e( 'No file selected.', 'user-registration' ); ?></span>
									<span class="user-registration-custom-file__button">Browse File</span>
								</label>
							</div>
							<p class="help">Only JSON file format allowed.</p>
						</div>
						<div class="publishing-action">
							<input type="button" class="button button-primary ur_import_form_action_button" name="user_registration_import_form" value="<?php esc_html_e( 'Import Forms', 'user-registration' ); ?>">
						</div>
					</div>
				</div><!-- .postbox -->
		</div>
	</div>
</div>
