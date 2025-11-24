<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		<div class="meta-box-sortables">

			<div class="urcr-settings-widget postbox user-registration-card">
				<div class="urcr-settings-widget-header user-registration-card__header ur-border-0">
					<div class="hndle ui-sortable-handle ur-d-flex ur-align-items-center ur-border-0">
						<h4 class="ur-h4 ur-m-0"><?php esc_html_e( 'Add target contents', 'user-registration' ); ?></h4>
						<div class="ur-d-flex ur-ml-auto">
							<select class="button button-secondary urcr-add-new-target-contents urcr-constant-selection-enabled">
								<option class="urcr-logic-field-placeholder" selected hidden disabled>+ <?php esc_html_e( 'Add Field', 'user-registration' ); ?></option>
								<option value="post_types"><?php esc_html_e( 'Post Types', 'user-registration' ); ?></option>
								<option value="taxonomy"><?php esc_html_e( 'Taxonomy', 'user-registration' ); ?></option>
								<option value="wp_posts"><?php esc_html_e( 'Pick Posts', 'user-registration' ); ?></option>
								<option value="wp_pages"><?php esc_html_e( 'Pick Pages', 'user-registration' ); ?></option>
								<option value="whole_site"><?php esc_html_e( 'Whole Site', 'user-registration' ); ?></option>
							</select>
							<button type="button" class="handlediv"><span class="toggle-indicator"></span></button>
						</div>
					</div>
				</div>
				<div class="inside urcr-cld-wrapper user-registration-card__body ur-border-top ur-pt-2">
					<div class="main urcr-target-contents-container">
						<!-- URCR target contents list goes here -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

