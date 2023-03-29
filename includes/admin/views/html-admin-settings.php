<?php
/**
 * Admin View: Settings
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap user-registration">
	<form method="<?php echo esc_attr( apply_filters( 'user_registration_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<div class="user-registration-settings" >
			<header class="user-registration-header">
				<div class="user-registration-header--top">
					<div class="user-registration-header--top-logo">
						<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/onboard-icons/logo.png' ); ?>" alt="">
					</div>
					<div class="ur-search-input ur-search--top-settings">
						<input id="ur-search-settings" class="ur-type-text" type="text" placeholder="<?php esc_html_e( "Search Settings...", "user-registration" ); ?>" fdprocessedid="8fe27c">
						<svg xmlns="http://www.w3.org/2000/svg" height="24px" width="24px" viewBox="0 0 24 24" fill="#a1a4b9"><path d="M21.71,20.29,18,16.61A9,9,0,1,0,16.61,18l3.68,3.68a1,1,0,0,0,1.42,0A1,1,0,0,0,21.71,20.29ZM11,18a7,7,0,1,1,7-7A7,7,0,0,1,11,18Z"></path></svg>
					</div>
					<div class="ur-search-input ur-search--top-toggle">
						<label for="user_registration_hide_premium_features"><?php esc_html_e( "Hide Premium Features", "user-registration" ); ?></label>
						<div class="ur-toggle-section">
							<span class="user-registration-toggle-form">
								<input type="checkbox" name="user_registration_hide_premium_features" id="user_registration_hide_premium_features">
								<span class="slider round"></span>
							</span>
						</div>
					</div>
				</div>
				<div class="user-registration-header--nav">
					<nav class="nav-tab-wrapper ur-nav ur-nav--tab ur-nav-tab-wrapper ur-scroll-ui__items">
						<?php
						foreach ( $tabs as $name => $label ) {
							echo '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=' . $name ) ) . '" class="nav-tab ur-nav__link ur-scroll-ui__item ' . ( $current_tab === $name ? 'nav-tab-active is-active' : '' ) . '">' . esc_html( $label ) . '</a>';
						}

							do_action( 'user_registration_settings_tabs' );
						?>
					</nav>
				</div>
			</header>
			<div class="user-registration-settings-container">
				<div class="user-registration-options-header">
					<div class="user-registration-options-header--top">
						<h1><?php echo ucwords( str_replace( "_", " ", $current_tab ) ); ?></h1>
						<p class="submit">
							<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
								<input name="save" class="button-primary" type="submit" value="<?php echo esc_attr( apply_filters( 'user_registration_setting_save_label', esc_attr__( 'Save Changes', 'user-registration' ) ) ); ?>" />
							<?php endif; ?>
							<input type="hidden" name="subtab" id="last_tab" />
							<?php wp_nonce_field( 'user-registration-settings' ); ?>
						</p>
					</div>
					<div class="user-registration-options-header--bottom">
						<?php
						do_action( 'user_registration_sections_' . $current_tab );
						?>
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
