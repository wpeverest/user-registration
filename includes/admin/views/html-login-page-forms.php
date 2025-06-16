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
		<div class="ur-page-title__wrapper-logo">
			<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
				<path d="M29.2401 2.25439C27.1109 3.50683 25.107 5.13503 23.3536 6.88846C21.6002 8.64188 19.972 10.6458 18.7195 12.6497C19.5962 14.4031 20.3477 16.1566 20.9739 18.0352C22.1011 15.6556 23.4788 13.5264 25.2323 11.6477V18.4109C25.2323 22.544 22.4769 26.1761 18.4691 27.3033H18.2185C17.9681 24.047 17.2166 20.9158 16.0894 17.91C14.4612 13.7769 11.9563 10.0196 8.69995 6.88846C6.94652 5.13503 4.94263 3.63208 2.81347 2.25439L2.3125 2.00388V18.2857C2.3125 24.9237 7.07177 30.6849 13.7097 31.8121H13.835C15.3379 32.0626 16.8409 32.0626 18.2185 31.8121H18.3438C24.9818 30.6849 29.7411 24.9237 29.7411 18.2857V2.00388L29.2401 2.25439ZM6.82128 18.2857V11.6477C10.7039 16.0313 13.0835 21.4168 13.5845 27.1781C9.57669 26.0509 6.82128 22.4188 6.82128 18.2857ZM15.9642 0C14.0855 0 12.5825 1.50291 12.5825 3.38158C12.5825 5.26025 14.0855 6.7632 15.9642 6.7632C17.8428 6.7632 19.3457 5.26025 19.3457 3.38158C19.3457 1.50291 17.8428 0 15.9642 0Z" fill="#475BB2"/>
			</svg>
		</div>
		<div class="ur-page-title__wrapper-menu">
			<ul class="ur-page-title__wrapper-menu__items">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration' ) ); ?>" class=""><?php esc_html_e( 'Registration Forms', 'user-registration' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-login-forms' ) ); ?>" class="current"><?php esc_html_e( 'Login Form', 'user-registration' ); ?></a><span class="ur-editing-tag"><?php esc_html_e( 'Now Editing', 'user-registration' ); ?></span></li>
			</ul>
		</div>
	</div>
	<div class="major-publishing-actions wp-clearfix">
		<div class="publishing-action">
			<button type="button" name="save_login_form" id="save_form_footer" class="button button-primary button-large menu-form ur_save_login_form_action_button"> <?php echo __( 'Update Form', 'user-registration' ); ?> </button>
		</div><!-- END .publishing-action -->
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
