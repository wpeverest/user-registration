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
					<div class="ur-scroll-ui__scroll-nav">
						<ul class="subsubsub  ur-scroll-ui__items">
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration' ) ); ?>" class="current ur-scroll-ui__item">Registration Forms</a></li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration&tab=login-forms' ) ); ?>" class=" ur-scroll-ui__item active">Login Forms</a></li>
						</ul>
					</div>
					<div class="ur-scroll-ui__scroll-nav ur-scroll-ui__scroll-nav--forward is-disabled">
						<i class="ur-scroll-ui__scroll-nav__icon dashicons dashicons-arrow-right-alt2"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<hr class="wp-header-end">
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
							<div class='ur-builder-wrapper'>
								<div class="ur-selected-inputs">
									<div class="ur-builder-wrapper-content ur-login-form-wrapper">
										<div class="ur-login-shortcode">
											<h1><?php echo esc_html__( 'Login Shortcode', 'user-registration' ); ?></h1>
											<div class="ur-login-shortcode--wrapper">
											<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/login_form.png' ); ?>" alt="Login Form">
											<div class="ur-login-content">
												<?php printf( '<p>%s <strong>%s</strong> %s</p>', esc_html( 'You can add this shortcode', 'user-registration' ), esc_html__( '[user_registration_login]', 'user-registration' ), esc_html__( 'in the pages where you want to show your login form which is provided below:-', 'user-registration' ) ); ?>
												<div class="major-publishing-actions wp-clearfix">
													<div class="login-forms-shortcode-action shortcode">
														<input type="text" onfocus="this.select();" readonly="readonly"
														value='[user_registration_login]'
														class="widefat code" size="35"></span>

														<button id="copy-shortcode" class="button ur-copy-shortcode help_tip" href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
															<span class="dashicons dashicons-admin-page"></span>
														</button>
													</div>
												</div>
												<?php printf( '<p>%s <strong>%s</strong> %s</p>', esc_html( 'If you want to create your own my account page, you can create a new page and You can add this shortcode', 'user-registration' ), esc_html__( '[user_registration_myaccount]', 'user-registration' ), esc_html__( 'in the pages where you want to show your myaccount login form which is provided below:-', 'user-registration' ) ); ?>
												<div class="major-publishing-actions wp-clearfix">
													<div class="login-forms-shortcode-action shortcode">
														<input type="text" onfocus="this.select();" readonly="readonly"
														value='[user_registration_myaccount]'
														class=" code" size="35"></span>

														<button id="copy-shortcode" class="button ur-copy-shortcode help_tip" href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
															<span class="dashicons dashicons-admin-page"></span>
														</button>
													</div>
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
</div>
