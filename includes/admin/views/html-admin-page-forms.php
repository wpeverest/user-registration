<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wrap ur-form-container">
	<div id="menu-management-liquid" class="ur-form-subcontainer">
		<div id="menu-management">
			<div class="menu-edit ">
				<input type="hidden" name="ur_form_id" id="ur_form_id" value="<?php echo $post_id; ?>"/>

				<div id="nav-menu-header">
					<div class="ur-brand-logo">
						<img src="<?php echo UR()->plugin_url() . '/assets/images/logo.svg'; ?>" alt="">
					</div>
					<div class="major-publishing-actions wp-clearfix">
						<div class="publishing-action">
							<?php
							if ( isset( $post_data[0] ) ) {

								?>
									<input type="text" onfocus="this.select();" readonly="readonly"
										value='[user_registration_form id=<?php echo '"' . $post_data[0]->ID . '"'; ?>]'
										class=" code" size="35">

									<button id="copy-shortcode" class="button button-primary button-large ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode!', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied!', 'user-registration' ); ?>">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16">
											<path fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"/>
										</svg>
									</button>

								<?php
							}
							?>
							<button id="ur-full-screen-mode" class="button button-secondary button-large closed" title="<?php echo __( 'Fullscreen', 'user-registration' ); ?>"><span class="ur-fs-open-label dashicons dashicons-editor-expand"></span><span class="ur-fs-close-label dashicons dashicons-editor-contract"></span></button>
							<a href="<?php echo esc_url( $preview_link ); ?>" target="_blank" class="button button-secondary button-large" title="<?php echo __( 'Preview Form', 'user-registration' ); ?>"><?php echo __( 'Preview', 'user-registration' ); ?></a>
							<input type="button" name="save_form" id="save_form_footer" class="button button-primary button-large menu-form ur_save_form_action_button" value="<?php echo $save_label; ?> ">
						</div><!-- END .publishing-action -->
					</div><!-- END .major-publishing-actions -->
				</div><!-- END .nav-menu-header -->
				<div id="post-body">
					<div class="ur-registered-from">
						<div class="ur-registered-inputs">
							<nav class="nav-tab-wrapper ur-tabs">
								<ul class="ur-tab-lists">
									<li><a href="#ur-tab-registered-fields"
										   class="nav-tab active"><?php esc_html_e( 'Fields', 'user-registration' ); ?></a>
									</li>
									<li class="ur-no-pointer"><a href="#ur-tab-field-options" class="nav-tab"><?php esc_html_e( 'Field Options', 'user-registration' ); ?></a>
									</li>
									<li><a href="#ur-tab-field-settings"
										   class="nav-tab"><?php esc_html_e( 'Form Setting', 'user-registration' ); ?></a>
									</li>
								</ul>
								<div style="clear:both"></div>

								<div id="ur-tab-registered-fields" class="ur-tab-content">
									<h2><?php echo __( 'Default User Fields', 'user-registration' ); ?></h2>
									<hr/>
									<?php $this->get_registered_user_form_fields(); ?>
									<h2><?php echo __( 'Extra Fields', 'user-registration' ); ?></h2>
									<hr/>
									<?php $this->get_registered_other_form_fields(); ?>
									<?php do_action( 'user_registration_extra_fields' ); ?>
								</div>
								<div id="ur-tab-field-options" class="ur-tab-content">

								</div>
								<div id="ur-tab-field-settings" class="ur-tab-content">

									<form method="post" id="ur-field-settings" onsubmit="return false;">
										<?php
											$form_id = isset( $post_data[0]->ID ) ? $post_data[0]->ID : 0;
										?>
												<div id ="ur-field-all-settings">
													<?php ur_admin_form_settings( $form_id ); ?>
												</div>
											<?php
											do_action( 'user_registration_after_form_settings', $form_id );
											?>
									</form>

								</div>
							</nav>
						</div>
						<div class='ur-builder-wrapper'>
							<div class="ur-form-name-wrapper" >
								<?php
								$form_title = isset( $post_data[0]->post_title ) ? trim( $post_data[0]->post_title ) : '';
								?>
								<input name="ur-form-name" id="ur-form-name" type="text" class="ur-form-name regular-text menu-item-textbox" value="<?php echo esc_html( $form_title ); ?>">
							</div>
							<?php
							if ( isset( $post_data[0] ) && isset( $_GET['edit-registration'] ) && is_numeric( $_GET['edit-registration'] ) ) {
								$this->get_edit_form_field( $post_data );
							} else {
								?>
								<div class="ur-selected-inputs">

								</div>
							<?php } ?>
						</div>
					</div>
				</div><!-- /#post-body -->
				<div id="nav-menu-footer">
					<div class="major-publishing-actions wp-clearfix">
						<div class="publishing-action">
							<input type="button" name="save_form" id="save_form_footer"
								   class="button button-primary button-large menu-form ur_save_form_action_button"
								   value="<?php echo $save_label; ?> "/>
						</div><!-- END .publishing-action -->
					</div><!-- END .major-publishing-actions -->
				</div><!-- /#nav-menu-footer -->
			</div><!-- /.menu-edit -->
		</div><!-- /#menu-management -->
	</div>
</div>
