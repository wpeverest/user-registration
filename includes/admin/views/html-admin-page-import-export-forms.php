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
<h3 class="ur-settings-section-header"><?php _e( 'IMPORT/EXPORT FORMS', 'user-registration'); ?></h3>
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
				<div class="postbox">
					<h3 class="hndle"><?php _e( 'EXPORT FORMS WITH SETTINGS', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php _e( 'Export your forms along with their settings as <strong>JSON</strong> file.', 'user-registration' ); ?>
						</p>

						<p>
							<select name="formid" class="ur-input forms-list">
								<?php
								foreach ( $all_forms as $form_id => $form ) {
									echo '<option value ="' . esc_attr( $form_id ) . '">' . esc_html( $form ) . '</option>';
								}
								?>
							</select>
						</p>

						<input type="submit" class="button button-primary" name="user_registration_export_form" value="<?php _e( 'Export Forms', 'user-registration' ); ?>">

					</div>
				</div><!-- .postbox -->
				<div class="postbox">
					<h3 class="hndle"><?php _e( 'IMPORT FORMS WITH SETTINGS', 'user-registration' ); ?></h3>

					<div class="inside">
						<p class="help">
							<?php _e( 'Import your forms along with their settings from <strong>JSON</strong> file.', 'user-registration' ); ?>
						</p>
						<div class="ur-form-group">
							<div class="user-registration-custom-file">
								<input type="file" class="user-registration-custom-file__input" name="jsonfile" id="jsonfile" accept=".json"/>
								<label class="user-registration-custom-file__label" for="csvfile">
									<span class="user-registration-custom-selected-file"><?php esc_html_e( 'No file selected.', 'user_registration_import_users' ); ?></span>
									<span class="user-registration-custom-file__button">Browse File</span>
								</label>
							</div>
							<p class="help">Only JSON file format allowed.</p>
						</div>
						<div class="publishing-action">
							<input type="button" class="button button-primary ur_import_form_action_button" name="user_registration_import_form" value="<?php _e( 'Import Forms', 'user-registration' ); ?>">
						</div>
					</div>
				</div><!-- .postbox -->
		</div>
	</div>
</div>
