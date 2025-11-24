<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		<div class="meta-box-sortables">
			<div class="urcr-settings-widget postbox user-registration-card">
				<div class="urcr-settings-widget-header user-registration-card__header ur-border-0">
					<div class="hndle ui-sortable-handle ur-d-flex ur-align-items-center ur-border-0">
						<h4 class="ur-h4 ur-m-0"><?php esc_html_e( 'Choose actions for this rule?', 'user-registration' ); ?></h4>
						<div class="ur-d-flex ur-ml-auto">
							<button type="button" class="handlediv"><span class="toggle-indicator"></span></button>
						</div>
					</div>
				</div>
				<div class="inside user-registration-card__body ur-border-top ur-pt-2">
					<div class="main urcr-rule-actions-container">
						<!-- URCR Actions -->
						<div class="urcr-label-input-pair urcr-rule-action ur-row ur-align-items-center ur-form-group">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Access Control', 'user-registration' ); ?></span>
								<span class="urcr-puncher"></span>
								<span class="user-registration-help-tip tooltipstered" data-tip="<?php esc_html_e( 'Action to perform for restricting the access or not', 'user-registration' ); ?>"></span>
							</label>
							<div class="urcr-input-container ur-col-8">
								<select class="urcr-rule-access-control urcr-enhanced-select2" data-placeholder="<?php esc_html_e( 'Select an Action', 'user-registration' ); ?>">
								<?php
									$options = array(
										'access'   => 'Access',
										'restrict' => 'Restrict',
									);
									if ( isset( $_GET['access_control'] ) && ! isset( $_GET['post-id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
										$selected_value = ! empty( $_GET['access_control'] ) ? wp_unslash( $_GET['access_control'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
									} elseif ( isset( $_GET['post-id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
										$id   = ! empty( $_GET['post-id'] ) ? sanitize_text_field( wp_unslash( $_GET['post-id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.WP.GlobalVariablesOverride.Prohibited
										$post = get_post( $id ); //phpcs:ignore Recommended,WordPress.WP.GlobalVariablesOverride.Prohibited
										if ( $post ) {
											$post_content        = json_decode( $post->post_content );
											$action_array        = $post_content->actions;
											$restriction_message = isset( $action_array[0]->access_control ) ? __( urldecode( $action_array[0]->access_control ), 'user-registration' ) : '';
											$selected_value      = ! empty( $restriction_message ) ? $restriction_message : '';
										}
									}

									foreach ( $options as $value => $label ) {
											$selected = ( $selected_value === $value ) ? 'selected' : '';
											echo '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
									}
									?>
							</select>
							</div>
						</div>
						<div class="urcr-label-input-pair urcr-rule-action ur-row ur-align-items-center ur-form-group">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Action', 'user-registration' ); ?></span>
								<span class="urcr-puncher"></span>
								<span class="user-registration-help-tip" data-tip="<?php esc_html_e( 'Action to perform for restricting the specified contents', 'user-registration' ); ?>"></span>
							</label>
							<div class="urcr-input-container ur-col-8">
								<select class="urcr-rule-action-type-input urcr-enhanced-select2" data-placeholder="<?php esc_html_e( 'Select an Access control', 'user-registration' ); ?>">
									<option></option>
									<option value="message"><?php esc_html_e( 'Show Message', 'user-registration' ); ?></option>
									<option value="redirect"><?php esc_html_e( 'Redirect', 'user-registration' ); ?></option>
									<option value="redirect_to_local_page"><?php esc_html_e( 'Redirect to a Local Page', 'user-registration' ); ?></option>
									<option value="ur-form"><?php esc_html_e( 'Show UR Form', 'user-registration' ); ?></option>
									<option value="shortcode"><?php esc_html_e( 'Render Shortcode', 'user-registration' ); ?></option>
								</select>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-message-input-container ur-row ur-form-group" style="display:none;">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Redirection Message', 'user-registration' ); ?></span>
							</label>

							<div class="urcr-body ur-col-8">
							<?php
								$value   = esc_html__( 'You do not have sufficient permission to access this content.', 'user-registration' );
								$post_id = isset( $_GET['post-id'] ) ? sanitize_text_field( $_GET['post-id'] ) : 0;
								$post    = get_post( $post_id );

							if ( $post ) {
								$post_content        = json_decode( $post->post_content );
								$action_array        = $post_content->actions;
								$restriction_message = __( urldecode( $action_array[0]->message ), 'user-registration' );
								$value               = ! empty( $restriction_message ) ? $restriction_message : $value;
							}

								$settings = array(
									'quicktags'  => false,
									'show-smart-tags-button' =>  false,
									'tinymce'    => array(
										'toolbar1' => 'undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
										'statusbar' => false,
										'plugins' => 'wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists',
										'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
										'theme_advanced_buttons2' => '',
									),
									'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
								);

								wp_editor( $value, 'urcr-rule-action-message-input', $settings );
								?>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-input-container ur-row ur-form-group" style="display:none;">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Redirection URL', 'user-registration' ); ?></span>
							</label>

							<div class="urcr-body ur-col-8">
								<input type="url" class="urcr-input" placeholder="<?php esc_attr_e( 'Enter a URL to redirect to...' ); ?>"/>
								<div class="urcr-notice urcr-notice-warning">
									<p><b><?php esc_html_e( 'Warning', 'user-registration' ); ?>:</b> <?php esc_html_e( 'Empty redirect URL will redirect to the admin page.', 'user-registration' ); ?></p>
								</div>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-to-local-page-input-container ur-row ur-form-group" style="display:none;">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Redirect to a local page', 'user-registration' ); ?></span>
							</label>

							<div class="urcr-body ur-col-8"></div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-ur-form-input-container ur-row ur-form-group" style="display:none;">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Display User Registration Form', 'user-registration' ); ?></span>
							</label>

							<div class="urcr-body ur-col-8"></div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-shortcode-input-container ur-row ur-form-group" style="display:none;">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Render a Shortcode', 'user-registration' ); ?></span>
							</label>

							<div class="urcr-body ur-col-8"></div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

