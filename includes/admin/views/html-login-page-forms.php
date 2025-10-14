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
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2"
			   href="<?php echo esc_attr( admin_url( 'admin.php?page=user-registration' ) ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
					 stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
					<line x1="19" y1="12" x2="5" y2="12"></line>
					<polyline points="12 19 5 12 12 5"></polyline>
				</svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items">
					<p><?php esc_html_e( 'Login Form', 'user-registration' ); ?><span
							class="ur-editing-tag"><?php esc_html_e( 'Now Editing', 'user-registration' ); ?></span></p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="major-publishing-actions wp-clearfix">
				<div class="publishing-action">
					<button type="button" name="save_login_form" id="save_form_footer"
							class="button button-primary button-large menu-form ur_save_login_form_action_button"> <?php echo __( 'Update Form', 'user-registration' ); ?> </button>
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
													<?php
													if ( ! empty( $login_form_settings['sections'] ) ):
														foreach ( $login_form_settings['sections'] as $section ):
														?>
														<div id="<?php echo strtolower($section['title']) ?>-settings">
															<h3><?php echo esc_html__( $section['title'] , 'user-registration' ) ?></h3>
															<?php
																render_login_option_settings( $section );
															?>
														</div>
													<?php
													endforeach;
													endif;
													?>
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
