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
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration' ); ?></h1>
	<div class="user-registration-settings-container">
		<div class="user-registration-options-header">
			<div class="user-registration-options-header--bottom" >
				<div class="ur-scroll-ui">
					<div class="ur-scroll-ui__scroll-nav ur-scroll-ui__scroll-nav--backward is-disabled">
						<i class="ur-scroll-ui__scroll-nav__icon dashicons dashicons-arrow-left-alt2"></i>
					</div>
					<div class="ur-scroll-ui__scroll-nav"><ul class="subsubsub  ur-scroll-ui__items"><li><a href="http://localhost/milan/wp-admin/admin.php?page=user-registration" class="current ur-scroll-ui__item">Registration Forms</a></li><li><a href="http://localhost/milan/wp-admin/admin.php?page=user-registrationtab=login-forms" class=" ur-scroll-ui__item">Login Forms</a></li></ul></div>							<div class="ur-scroll-ui__scroll-nav ur-scroll-ui__scroll-nav--forward is-disabled">
						<i class="ur-scroll-ui__scroll-nav__icon dashicons dashicons-arrow-right-alt2"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
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
						<div class='ur-builder-wrapper'>
							<div class="ur-selected-inputs">
								<div class="ur-builder-wrapper-content ur-login-form-wrapper">
									<div class="ur-login-shortcode">
										<h1><?php echo esc_html__( 'Login Shortcode', 'user-registration' ); ?></h1>
										<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/login_form.png' ); ?>" alt="Login Form">
										<div class="ur-login-content">
											<?php printf( '<p>%s <strong>%s</strong> %s</p>', esc_html( 'You can add this shortcode', 'user-registration' ), esc_html__( '[user_registration_login]', 'user-registration' ), esc_html__( 'in the pages where you want to show your login form which is provided below:-', 'user-registration' ) ); ?>
											<div class="major-publishing-actions wp-clearfix">
												<div class="login-forms-shortcode-action">
													<input type="text" onfocus="this.select();" readonly="readonly"
													value='[user_registration_login]'
													class=" code" size="35">

													<button id="copy-shortcode" class="button button-primary button-large ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode!', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied!', 'user-registration' ); ?>">
														<svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16">
															<path fill="#383838"  fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"/>
														</svg>
														<!-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
															<path fill="#383838" fill-rule="evenodd" d="M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z" clip-rule="evenodd"/>
														</svg> -->
													</button>
												</div>
											</div>
											<?php printf( '<p>%s <strong>%s</strong> %s</p>', esc_html( 'If you want to create your own my account page, you can create a new page and You can add this shortcode', 'user-registration' ), esc_html__( '[user_registration_myaccount]', 'user-registration' ), esc_html__( 'in the pages where you want to show your myaccount login form which is provided below:-', 'user-registration' ) ); ?>
											<div class="major-publishing-actions wp-clearfix">
												<div class="login-forms-shortcode-action">
													<input type="text" onfocus="this.select();" readonly="readonly"
													value='[user_registration_myaccount]'
													class=" code" size="40">

													<button id="copy-shortcode" class="button button-primary button-large ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode!', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied!', 'user-registration' ); ?>">
														<svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16">
															<path fill="#383838" fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"/>
														</svg>
														<!-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
															<path fill="#383838" fill-rule="evenodd" d="M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z" clip-rule="evenodd"/>
														</svg> -->
													</button>
												</div>
											</div>
										</div>
									</div>
									<div class="ur-login-view-doc">
										<a href="<?php echo esc_url_raw( 'https://docs.wpuserregistration.com/docs/how-to-show-login-form/' ); ?>" target="_blank"><?php echo esc_html__( 'View Documentation For More Details', 'user-registration' ); ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- /#post-body -->
			</div><!-- /.menu-edit -->
		</div><!-- /#menu-management -->
	</div>
</div>
