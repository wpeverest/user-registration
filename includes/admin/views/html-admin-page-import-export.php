<?php
/**
 * Admin View: Page - Import/Export
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="nav-tab-content">
    <div class="nav-tab-inside">
        <h3><?php _e( 'Export Forms', 'user-registration' ); ?></h3>

        <p><?php _e( 'You can export your form, and the users along with their extra information registered with a user regstration form.', 'user-registration' ); ?></p>
		<div class="postboxes metabox-holder two-col">
			<div class="postbox">
				<h3 class="hndle"><?php _e( 'Export Forms', 'user-registration' ); ?></h3>

				<div class="inside">
					<p class="help">
						<?php _e( 'You can export your existing registration forms and import the same forms into a different site.', 'user-registration' ); ?>
					</p>

					<form action="admin-post.php?action=user_registration_export_forms" method="post">
						<select name="export_forms[]" class="select ur-enhanced-select forms-list" multiple="multiple">
							<option></option>
						</select>
						<?php wp_nonce_field( 'user-registration-export-forms' ); ?>
						<input type="submit" class="button button-primary" name="user_registration_export_forms" value="<?php _e( 'Export Forms', 'user-registration' ) ?>">
					</form>
				</div>
			</div><!-- .postbox -->

		 	<div class="postbox">
                <h3 class="hndle"><?php _e( 'Export Users', 'user-registration' ); ?></h3>

                <div class="inside">
                    <p class="help">
                        <?php _e( 'Export your users along with their extra information registered with a user registration form as a <strong>CSV</strong> file.', 'user-registration' ); ?>
                    </p>

                        <form action="admin-post.php?action=user_registration_export_user_entries" method="post">
                            <p>
                                <select name="export_users" class="forms-list">
                                    <option></option>
                                </select>
                            </p>

                            <?php wp_nonce_field( 'user-registration-export-users' ); ?>
                            <input type="submit" class="button button-primary" name="user-registration_export_users" value="<?php _e( 'Export Users', 'user-registration' ) ?>">
                        </form>
                   </div>
            </div><!-- .postbox -->
        </div>
	</div>
</div>

<div class="nav-tab-content">
 	<div class="nav-tab-inside">
     	<h3><?php _e( 'Import Registration Form', 'user-registration' ); ?></h3>

        <p><?php _e( 'Browse and locate a json file you backed up before.', 'user-registration' ); ?></p>

        <form action="" method="post" enctype="multipart/form-data" style="margin-top: 20px;">
            <input type="file" name="import-form" accept="application/json" />
            <button class="button button-primary" type="submit">Import</button>
        </form>
	</div>
</div>
