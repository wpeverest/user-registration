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
?>
<div class="wrap user-registration">
	<form method="<?php echo esc_attr( apply_filters( 'user_registration_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
	<h1 class="screen-reader-text"><?php echo isset( $tabs[ $current_tab ] ) ? esc_html( $tabs[ $current_tab ] ) : ''; ?></h1>
		<div class="user-registration-settings" >
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
					<?php if ( isset( $tabs[ $current_tab ] ) ) { ?>
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
						<p class="submit">
							<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
								<input name="save" class="button-primary" type="submit" value="<?php echo esc_attr( apply_filters( 'user_registration_setting_save_label', esc_attr__( 'Save Changes', 'user-registration' ) ) ); ?>" />
							<?php endif; ?>
							<input type="hidden" name="subtab" id="last_tab" />
							<?php wp_nonce_field( 'user-registration-settings' ); ?>
						</p>
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

						do_action( 'user_registration_settings_' . $current_tab );
						do_action( 'user_registration_settings_tabs_' . $current_tab ); // @deprecated hook
					?>
				</div>
				<p class="submit">
					<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
						<input name="save" class="button-primary" type="submit" value="<?php echo esc_attr( apply_filters( 'user_registration_setting_save_label', esc_attr__( 'Save Changes', 'user-registration' ) ) ); ?>" />
					<?php endif; ?>
					<input type="hidden" name="subtab" id="last_tab" />
					<?php wp_nonce_field( 'user-registration-settings' ); ?>
				</p>
			</div>
		</div>
	</form>
</div>
