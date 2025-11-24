<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap user-registration-content-restriction">
	<form method="<?php echo esc_attr( apply_filters( 'user_registration_content_restriction_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper ur-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=user-registration-content-restriction-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}

				do_action( 'user_registration_content_restriction_settings_tabs' );
			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
			do_action( 'user_registration_content_restriction_sections_' . $current_tab );

			self::show_messages();

			do_action( 'user_registration_content_restriction_settings_' . $current_tab );
			do_action( 'user_registration_content_restriction_settings_tabs_' . $current_tab ); // @deprecated hook
		?>
		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php echo apply_filters( 'user-registration-content-restriction-setting-save-label', esc_attr( 'Save Changes', 'user-registration' ) ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'user-registration-content-restriction-settings' ); ?>
		</p>
	</form>
</div>

