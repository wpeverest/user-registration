<?php
/**
 * Admin View: Page - Login Forms
 *
 * @since 3.0.0.2
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<hr class="wp-header-end">

<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( admin_url('admin.php?page=user-registration') ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items">
					<p><?php esc_html_e( 'Login Form', 'user-registration' ); ?><span class="ur-editing-tag"><?php esc_html_e( 'Now Editing', 'user-registration' ); ?></span></p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="major-publishing-actions wp-clearfix">
				<div class="publishing-action">
					<button type="button" name="save_login_form" id="save_form_footer" class="button button-primary button-large menu-form ur_save_login_form_action_button"> <?php echo __( 'Update Form', 'user-registration' ); ?> </button>
				</div><!-- END .publishing-action -->
			</div>
		</div>
	</div>
</div>
<div class="user-registration-login-form-container">
	<div class="wrap user-registration__wrap ur-form-container">
		<h1 style="display:none"></h1> <!-- To manage notices -->
		<div id="menu-management-liquid" class="ur-form-subcontainer">
			<!-- <div class="ur-loading-container">
				<div class="ur-circle-loading"></div>
			</div> -->
			<div id="menu-management">
				<div class="menu-edit">
					<div id="post-body">
						<div class="ur-registered-from">
							<div class="ur-registered-inputs ur-login-form-settings">
								<nav class="nav-tab-wrapper ur-tabs">
									<div style="clear:both"></div>
									<div class="ur-tab-contents">
										<div id="ur-tab-login-options" class="ur-tab-content">
											<?php
												echo '<form id="ur-login-form-setting">';
											foreach ( $login_form_settings['sections'] as $section ) {
												echo '<div class="ur-login-form-setting-block" style="display:block;">';
												echo '<h2 class="ur-toggle-heading">' . __( $section['title'], 'user-registration' ) . '</h2>';
												echo '<hr/>';
												echo '<div class="ur-toggle-content" style="display:none;">';
												render_login_option_settings( $section );
												echo '</div>';
												echo '</div>';
											}
												echo '</form>';
											?>
										</div>
									</div>
								</nav>
								<button id="ur-collapse" class="close">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<path fill="#6B6B6B" d="M16.5 22a1.003 1.003 0 0 1-.71-.29l-9-9a1 1 0 0 1 0-1.42l9-9a1.004 1.004 0 1 1 1.42 1.42L8.91 12l8.3 8.29A.999.999 0 0 1 16.5 22Z"/>
									</svg>
								</button>
							</div>
							<div class='ur-builder-wrapper'>
								<div class="ur-selected-inputs">
									<div class="ur-builder-wrapper-content ur-login-form-wrapper">
										<?php echo do_shortcode( '[user_registration_login]' ); ?>
									</div>

									<?php do_action( 'user_registration_after_login_form_settings' ); ?>
								</div>
							</div>
							<div class="ur-registered-inputs ur-login-form-shortcode">
								<nav class="nav-tab-wrapper ur-tabs">
									<div style="clear:both"></div>
									<div class="ur-tab-contents">
										<div class="ur-login-shortcode">
											<div class="ur-login-shortcode--wrapper">
												<h2 class="ur-heading"><?php esc_html_e( 'Shortcode', 'user-registration' ); ?></h2>
												<div class="ur-login-content">
													<?php printf( '<p>%s</p>', esc_html__( 'You can add the following shortcode in the pages where you want to show the login form.', 'user-registration' ) ); ?>
													<div class='urm-shortcode'>
														<input type="text" onfocus="this.select();" readonly="readonly"
															   value='[user_registration_login]'
															   class="widefat code" size="35">
														<button id="login-copy-shortcode" class="button ur-copy-shortcode" href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
															<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'>
																<path fill='#383838' fill-rule='evenodd' d='M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z' clip-rule='evenodd'></path>
															</svg>
														</button>
													</div>

													<?php printf( '<p>%s</p>', esc_html__( 'If you want to create a My Account page, you need to create a new page and add the following shortcode. This will show the My Account Login Form.', 'user-registration' ) ); ?>
													<div class='urm-shortcode'>
														<input type="text" onfocus="this.select();" readonly="readonly"
															   value='[user_registration_my_account]'
															   class=" code" size="35">
														<button id="myaccount-copy-shortcode" class="button ur-copy-shortcode" href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
															<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'>
																<path fill='#383838' fill-rule='evenodd' d='M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z' clip-rule='evenodd'></path>
															</svg>
														</button>
													</div>
												</div>
											</div>
										</div>
										<div class="ur-login-view-doc">
											<a href="<?php echo esc_url_raw( 'https://docs.wpuserregistration.com/docs/how-to-show-login-form/' ); ?>" rel="noreferrer noopener" target="_blank"><?php echo esc_html__( 'View Documentation', 'user-registration' ); ?></a>
										</div>
									</div>
								</nav>
								<button id="ur-collapse" class="close">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<path fill="#6B6B6B" d="M16.5 22a1.003 1.003 0 0 1-.71-.29l-9-9a1 1 0 0 1 0-1.42l9-9a1.004 1.004 0 1 1 1.42 1.42L8.91 12l8.3 8.29A.999.999 0 0 1 16.5 22Z"/>
									</svg>
								</button>
							</div>
						</div>
					</div><!-- /#post-body -->
				</div><!-- /.menu-edit -->
			</div><!-- /#menu-management -->
		</div>
	</div>
</div>
