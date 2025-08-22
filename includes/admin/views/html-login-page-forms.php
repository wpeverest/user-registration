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
			<div id="menu-management">
				<div class="menu-edit">
					<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
						<div class="ur-page-title__wrapper">
							<div class="ur-page-title__wrapper-logo">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"
									 fill="none">
									<path
										d="M29.2401 2.25439C27.1109 3.50683 25.107 5.13503 23.3536 6.88846C21.6002 8.64188 19.972 10.6458 18.7195 12.6497C19.5962 14.4031 20.3477 16.1566 20.9739 18.0352C22.1011 15.6556 23.4788 13.5264 25.2323 11.6477V18.4109C25.2323 22.544 22.4769 26.1761 18.4691 27.3033H18.2185C17.9681 24.047 17.2166 20.9158 16.0894 17.91C14.4612 13.7769 11.9563 10.0196 8.69995 6.88846C6.94652 5.13503 4.94263 3.63208 2.81347 2.25439L2.3125 2.00388V18.2857C2.3125 24.9237 7.07177 30.6849 13.7097 31.8121H13.835C15.3379 32.0626 16.8409 32.0626 18.2185 31.8121H18.3438C24.9818 30.6849 29.7411 24.9237 29.7411 18.2857V2.00388L29.2401 2.25439ZM6.82128 18.2857V11.6477C10.7039 16.0313 13.0835 21.4168 13.5845 27.1781C9.57669 26.0509 6.82128 22.4188 6.82128 18.2857ZM15.9642 0C14.0855 0 12.5825 1.50291 12.5825 3.38158C12.5825 5.26025 14.0855 6.7632 15.9642 6.7632C17.8428 6.7632 19.3457 5.26025 19.3457 3.38158C19.3457 1.50291 17.8428 0 15.9642 0Z"
										fill="#475BB2"/>
								</svg>
							</div>
							<div class="ur-page-title__wrapper-menu">
								<ul class="ur-page-title__wrapper-menu__items">
									<li>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration' ) ); ?>"
										   class=""><?php esc_html_e( 'Registration Forms', 'user-registration' ); ?></a>
									</li>
									<li>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-login-forms' ) ); ?>"
										   class="current"><?php esc_html_e( 'Login Form', 'user-registration' ); ?></a><span
											class="ur-editing-tag"><?php esc_html_e( 'Now Editing', 'user-registration' ); ?></span>
									</li>
								</ul>
							</div>
						</div>
						<div class="major-publishing-actions wp-clearfix">
							<div class="publishing-action">
								<button type="button" name="save_login_form" id="save_form_footer"
										class="button button-primary button-large menu-form ur_save_login_form_action_button"> <?php echo __( 'Update Form', 'user-registration' ); ?> </button>
							</div><!-- END .publishing-action -->
						</div>
					</div>
					<div id="post-body">
						<div class="ur-registered-from">
							<div class="ur-registered-inputs ur-login-form-settings">
								<nav class="nav-tab-wrapper ur-tabs">
									<ul class="ur-tab-lists">
										<li><a href="#ur-tab-field-options"
											   class="nav-tab"><?php esc_html_e( 'Field Options', 'user-registration' ); ?></a>
										</li>

										<?php
										/**
										 * Filter to add form builder tabs.
										 */
										do_action( 'user_registration_form_bulder_tabs' ); // TODO:: Needs refactor. Move after field-settings and sort.
										?>

										<li><a href="#ur-tab-login-form-settings"
											   class="nav-tab"><?php esc_html_e( 'Form Settings', 'user-registration' ); ?></a>
										</li>
									</ul>
									<div style="clear:both"></div>

									<div class="ur-tab-contents">
											<div id="ur-tab-field-options" class="ur-tab-content">
												<?php
												echo '<form id="ur-login-form-setting">';
												foreach ( $login_option_settings['sections'] as $section ) {
													echo '<div class="ur-login-form-setting-block" style="display:block;">';
													echo '<h2>' . __( $section['title'], 'user-registration' ) . '</h2>';
													echo '<hr/>';
													echo '<div class="ur-toggle-content">';
													render_login_option_settings( $section );
													echo '</div>';
													echo '</div>';
												}
												echo '</form>';
												?>
										</div>
										<div id="ur-tab-login-form-settings" class="ur-tab-content">
											<form method="post" id="ur-field-settings" onsubmit="return false;"
												  style='display:none'>
												<div id="ur-field-all-settings">
													<div id="general-settings" ><h3><?php echo esc_html__( 'General', 'user-registration' )  ?></h3>

														<?php
													echo '<form id="ur-login-form-setting">';
													foreach ( $login_form_settings['sections'] as $section ) {
														render_login_option_settings( $section );
													}
													echo '</form>';
													?>
												</div>
											</form>
										</div>

									</div>
								</nav>
								<button id="ur-collapse" class="close">
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<path fill="#6B6B6B"
											  d="M16.5 22a1.003 1.003 0 0 1-.71-.29l-9-9a1 1 0 0 1 0-1.42l9-9a1.004 1.004 0 1 1 1.42 1.42L8.91 12l8.3 8.29A.999.999 0 0 1 16.5 22Z"/>
									</svg>
								</button>
							</div>
							<?php
							/**
							 * Filter to add the builder class.
							 *
							 * @param array.
							 */
							$builder_class = apply_filters( 'user_registration_builder_class', array() );
							$builder_class = implode( ' ', $builder_class );
							?>
							<div class='ur-builder-wrapper'>
								<div class="ur-selected-inputs">
									<div class="ur-builder-wrapper-content ur-login-form-wrapper">
										<?php echo do_shortcode( '[user_registration_login]' ); ?>
									</div>

									<?php do_action( 'user_registration_after_login_form_settings' ); ?>
								</div>
							</div>
						</div>
					</div><!-- /#post-body -->
				</div><!-- /.menu-edit -->
			</div><!-- /#menu-management -->
		</div>
	</div>
</div>
