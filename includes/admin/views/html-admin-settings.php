<?php
/**
 * Admin View: Settings
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$collapse_by_default = isset( $_GET['tab'] ) && ( strpos( $_GET['tab'], 'user-registration-customize-my-account' ) !== false || strpos( $_GET['tab'], 'user-registration-invite-codes' ) !== false ); //phpcs:ignore

/**
 * Filter to add form method tabs.
 *
 * @param mixed $current_tab Currently seclected method tab.
 */
$user_registration_settings_form_method_tab = apply_filters( 'user_registration_settings_form_method_tab_' . $current_tab, 'post' );

$setup_tab_lists             = ur_quick_settings_tab_content();
$quick_setup_completed       = get_option( 'user_registration_quick_setup_completed', false );
$is_settings_sidebar_enabled = isset( $_COOKIE['isSidebarEnabled'] ) ? ur_string_to_bool( sanitize_text_field( wp_unslash( $_COOKIE['isSidebarEnabled'] ) ) ) : true;

$is_pro_active = is_plugin_active( 'user-registration-pro/user-registration.php' );
?>
<div class="wrap user-registration">
	<form method="<?php echo esc_attr( $user_registration_settings_form_method_tab ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<h1 class="screen-reader-text"><?php echo isset( $tabs[ $current_tab ] ) ? esc_html( $tabs[ $current_tab ] ) : ''; ?></h1>
		<div class="user-registration-settings" >
			<div class="user-registration-settings-wrapper">

				<header class="user-registration-header <?php echo $collapse_by_default ? 'collapsed' : ''; ?>">
					<div class="user-registration-header--top">
						<div class="user-registration-header--top-logo">
							<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/onboard-icons/logo.png' ); ?>" alt="">
						</div>
						<div class="ur-search-input ur-search--top-settings">
							<input id="ur-search-settings" class="ur-type-text" type="text" placeholder="<?php esc_html_e( 'Search Settings...', 'user-registration' ); ?>" fdprocessedid="8fe27c">
							<div class="user-registration-search-icon">
								<svg xmlns="http://www.w3.org/2000/svg" height="24px" width="24px" viewBox="0 0 24 24" fill="#a1a4b9"><path d="M21.71,20.29,18,16.61A9,9,0,1,0,16.61,18l3.68,3.68a1,1,0,0,0,1.42,0A1,1,0,0,0,21.71,20.29ZM11,18a7,7,0,1,1,7-7A7,7,0,0,1,11,18Z"></path></svg>
							</div>
						</div>
						<!-- <div class="ur-search-input ur-search--top-toggle">
							<label for="user_registration_hide_premium_features"><?php esc_html_e( 'Hide Premium Features', 'user-registration' ); ?></label>
							<div class="ur-toggle-section">
								<span class="user-registration-toggle-form">
									<input type="checkbox" name="user_registration_hide_premium_features" id="user_registration_hide_premium_features">
									<span class="slider round"></span>
								</span>
							</div>
						</div> -->
					</div>
					<div class="user-registration-header--nav">
						<nav class="nav-tab-wrapper ur-nav ur-nav--tab ur-nav-tab-wrapper">
							<?php
							foreach ( $tabs as $name => $label ) {
								?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=' . $name ) ); ?>" class="nav-tab ur-nav__link <?php echo ( $current_tab === $name ? 'nav-tab-active is-active' : '' ); ?>">
									<span class="ur-nav__link-icon">
										<?php echo ur_file_get_contents( '/assets/images/settings-icons/' . $name . '.svg' ); //phpcs:ignore ?>
									</span>
									<span class="ur-nav__link-label">
										<p>
											<?php echo esc_html( $label ); ?>
										</p>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
											<path stroke="#383838" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
										</svg>
									</span>
								</a>
								<?php
							}
							do_action( 'user_registration_settings_tabs' );
							?>
							<button id="ur-settings-collapse" class="<?php echo $collapse_by_default ? 'open' : 'close'; ?> nav-tab ur-nav__link">
								<span class="ur-nav-icon">
									<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/settings-icons/chevron-right-fill.svg' ); ?>" alt="">
								</span>
								<span class="ur-nav__link-label">
									<?php esc_html_e( 'Collapse Menu', 'user-registration' ); ?>
								</span>
							</button>
						</nav>
					</div>
				</header>
				<div class="user-registration-settings-container">
					<div class="user-registration-options-header">
						<div class="user-registration-options-header--top">
							<div class="user-registration-options-header--top__left">
								<?php if ( isset( $tabs[ $current_tab ] ) ) { ?>
									<span class="user-registration-options-header--top__left--icon">
										<?php echo ur_file_get_contents( '/assets/images/settings-icons/' . $current_tab . '.svg' ); //phpcs:ignore ?>
									</span>
									<h3><?php echo esc_html( $tabs[ $current_tab ] ); ?></h3>
									<?php
								} else {
									$redirect_url = home_url( '/wp-admin/admin.php?page=user-registration-settings&tab=general' );
									?>
									<script>
									var redirect = '<?php echo esc_url_raw( $redirect_url ); ?>';
									window.location.href = redirect;
									</script>
									<?php
								}
								?>
							</div>
							<?php
							if ( ! $is_pro_active || ! $quick_setup_completed ) {
								?>
								<div class="user-registration-options-header--top__right">
									<span class="user-registration-toggle-text"><?php esc_html_e( 'Sidebar', 'user-registration' ); ?></span>
									<div class="ur-toggle-section">
										<span class="user-registration-toggle-form">
											<input type="checkbox" name="user_registration_enable_sidebar" id="user_registration_hide_show_sidebar" <?php echo esc_attr( $is_settings_sidebar_enabled ? 'checked="checked"' : '' ); ?>>
											<span class="slider round active"></span>
										</span>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<div class="user-registration-options-header--bottom" >
							<div class="ur-scroll-ui">
								<div class="ur-scroll-ui__scroll-nav ur-scroll-ui__scroll-nav--backward is-disabled">
									<i class="ur-scroll-ui__scroll-nav__icon dashicons dashicons-arrow-left-alt2"></i>
								</div>
								<?php
								do_action( 'user_registration_sections_' . $current_tab );
								?>
								<div class="ur-scroll-ui__scroll-nav ur-scroll-ui__scroll-nav--forward is-disabled">
									<i class="ur-scroll-ui__scroll-nav__icon dashicons dashicons-arrow-right-alt2"></i>
								</div>
							</div>
						</div>
					</div>
					<div class="user-registration-options-container">
						<?php
							self::show_messages();

							/**
							 * Action to show current tab.
							 */
							do_action( 'user_registration_settings_' . $current_tab );
							/**
							 * Action for current settings tab.
							 */
							do_action( 'user_registration_settings_tabs_' . $current_tab ); // @deprecated hook
						?>
					</div>
					<p class="submit">
						<?php
						if ( ! isset( $GLOBALS['hide_save_button'] ) ) :
							/**
							 * Filter to save the setting label.
							 *
							 * @param string Setting Save Label.
							 */
							$user_registration_setting_save_label = apply_filters( 'user_registration_setting_save_label', esc_attr__( 'Save Changes', 'user-registration' ) );
							?>
							<input name="save" class="button-primary" type="submit" value="<?php echo esc_attr( $user_registration_setting_save_label ); ?>" />
						<?php endif; ?>
						<input type="hidden" name="subtab" id="last_tab" />
						<?php wp_nonce_field( 'user-registration-settings' ); ?>
					</p>
				</div>
			</div>
			<?php
			if ( ! $is_pro_active || ! $quick_setup_completed ) {
				?>
				<div class="user-registration-settings-sidebar-container" id="user-registration-settings-sidebar">
					<?php
					if ( ! $quick_setup_completed ) {
						?>
						<div class="user-registration-settings-sidebar">
							<?php
							$content = '<div class="user-registration-settings-sidebar__header"><h3>' . esc_html( 'Setup Checklist', 'user-registration' ) . '</h3></div><div class="user-registration-settings-sidebar__body">
										<p>' . esc_html( 'Follow these steps to start registering users on your website.', 'user-registration' ) . '</p>
										<div class="user-registration-settings-sidebar__body--list">';

							foreach ( $setup_tab_lists as $list ) {
								if ( isset( $list['text'] ) ) {
									$completed = isset( $list['completed'] ) && $list['completed'];
									$content  .= '<div class="user-registration-settings-sidebar__body--list-item card ' . esc_attr( $completed ? 'completed' : '' ) . '">';

									if ( $completed ) {
										$content .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
														<circle cx="10" cy="10" r="10" fill="#6DBA50"/>
														<path stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15.333 6-7.334 7.333L4.666 10"/>
													</svg>';
									} else {
										$content .= '<svg xmlns="http://www.w3.org/2000/svg" fill="#999" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M10 1.395a8.605 8.605 0 1 0 0 17.21 8.605 8.605 0 0 0 0-17.21ZM0 10C0 4.477 4.477 0 10 0s10 4.477 10 10-4.477 10-10 10S0 15.523 0 10Z" clip-rule="evenodd"/>
													</svg>';
									}

									$content .= '<span>';
									$content .= wp_kses_post( $list['text'] );

									if ( ! $completed && isset( $list['documentation'] ) ) {
										$content .= '<span class="ur-portal-tooltip no-icon" data-tip="' . esc_attr__( 'Visit Documentation', 'user-registration' ) . '"><a href="' . esc_url( $list['documentation'] ) . '" target="_blank">
														<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
															<path d="M9.99935 18.3327C5.41601 18.3327 1.66602 14.5827 1.66602 9.99935C1.66602 5.41601 5.41601 1.66602 9.99935 1.66602C14.5827 1.66602 18.3327 5.41601 18.3327 9.99935C18.3327 14.5827 14.5827 18.3327 9.99935 18.3327ZM9.99935 3.33268C6.33268 3.33268 3.33268 6.33268 3.33268 9.99935C3.33268 13.666 6.33268 16.666 9.99935 16.666C13.666 16.666 16.666 13.666 16.666 9.99935C16.666 6.33268 13.666 3.33268 9.99935 3.33268ZM9.99935 13.8327C9.49935 13.8327 9.16601 13.4993 9.16601 12.9993V9.99935C9.16601 9.49935 9.49935 9.16601 9.99935 9.16601C10.4993 9.16601 10.8327 9.49935 10.8327 9.99935V12.9993C10.8327 13.4993 10.4993 13.8327 9.99935 13.8327ZM9.99935 7.83268C9.49935 7.83268 9.16601 7.49935 9.16601 6.99935C9.16601 6.49935 9.49935 6.16601 9.99935 6.16601C10.4993 6.16601 10.8327 6.49935 10.8327 6.99935C10.8327 7.49935 10.4993 7.83268 9.99935 7.83268Z" fill="#6B6B6B"/>
														</svg>
													</a></span>';
									}
									$content .= '</span>';
									$content .= '</div>';
								}
							}

							$content .= '</div></div>';
							echo $content; // phpcs:ignore
							?>
						</div>
						<?php
					}

					if ( ! $is_pro_active ) {
						?>
						<div class="user-registration-settings-sidebar">
						<?php
						$content = '<div class="user-registration-settings-sidebar__header"><h3>' . esc_html( 'Premium Benefits', 'user-registration' ) . '</h3></div>
										<div class="user-registration-settings-sidebar__body"><p>' . esc_html( 'Get Even More from User Registration with the Premium Plan', 'user-registration' ) . '</p>
										<div class="user-registration-settings-sidebar__body"><p>' . esc_html( 'The free version of User Registration is just the start. Upgrade to our Pro version for everything you need for advanced form building.', 'user-registration' ) . '</p>
										<div class="user-registration-settings-sidebar__body--list normal">';

						$premium_benefits = array(
							esc_html__( 'Instant access to 40+ unique addons', 'user-registration' ),
							esc_html__( 'Advanced fields to enhance your registration forms', 'user-registration' ),
							esc_html__( 'Simple WooCommerce integration with billing and shipping fields', 'user-registration' ),
							esc_html__( 'Customization options for user accounts', 'user-registration' ),
							esc_html__( 'Support for 12 different file types in the file upload option', 'user-registration' ),
							esc_html__( 'Eye-catching forms with the advanced style customizer', 'user-registration' ),
							esc_html__( 'Dynamic forms with Conditional Logic', 'user-registration' ),
							esc_html__( 'Full control over content visibility with Content Restriction', 'user-registration' ),
						);

						foreach ( $premium_benefits as $list ) {
							$content .= '<div class="user-registration-settings-sidebar__body--list-item">';

							$content .= '<svg xmlns="http://www.w3.org/2000/svg" fill="#383838" viewBox="0 0 20 20">
												<path fill-rule="evenodd" d="M6.91 4.41a.833.833 0 0 1 1.179 0l5 5a.833.833 0 0 1 0 1.179l-5 5A.833.833 0 0 1 6.91 14.41L11.32 10 6.91 5.588a.833.833 0 0 1 0-1.179Z" clip-rule="evenodd"/>
											</svg>';

							$content .= '<span>';
							$content .= wp_kses_post( $list );
							$content .= '</span>';
							$content .= '</div>';
						}

						$content .= '</div>';
						$content .= '</div>';
						$content .= '<div class="user-registration-settings-sidebar__footer">';
						$content .= '<p>' . esc_html( 'Thank you for choosing User Registration! 😊', 'user-registration' ) . '</p>';
						$content .= '<div class="user-registration-settings-sidebar__footer--card">';
						$content .= '<svg xmlns="http://www.w3.org/2000/svg" width="62" height="62" viewBox="0 0 62 62" fill="none">
											<rect x="3.00521" y="3.00521" width="55.9896" height="55.9896" rx="27.9948" fill="#7878E1" stroke="#7878E1" stroke-width="5.98958"/>
											<path d="M31.0013 17.1074L39.3346 39.3296H22.668L31.0013 17.1074Z" fill="#EBEBEB"/>
											<path fill-rule="evenodd" clip-rule="evenodd" d="M17.1055 22.666L19.0896 39.3327H42.8991L44.8833 22.666L30.9944 34.1243L17.1055 22.666ZM42.9037 40.9155H19.0942V44.8838H42.9037V40.9155Z" fill="white"/>
										</svg>';
						$content .= '<p>' . esc_html( 'Get More Features with Pro', 'user-registration' ) . '</p>';
						$content .= '<a rel="noreferrer noopener" target="_blank" href="https://wpuserregistration.com/pricing/?utm_source=settings-sidebar-right&amp;utm_medium=premium-benefits-card&amp;utm_campaign=lite-version">Upgrade to Pro</a>';
						$content .= '</div>';
						$content .= '</div>';
						echo $content; // phpcs:ignore
						?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
	</form>
</div>
