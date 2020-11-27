<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap user-registration">
	<form method="<?php echo esc_attr( apply_filters( 'user_registration_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<header class="user-registration-header">
			<nav class="nav-tab-wrapper ur-nav ur-nav--tab ur-nav-tab-wrapper">
				<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=' . $name ) . '" class="nav-tab ur-nav__link ' . ( $current_tab == $name ? 'nav-tab-active is-active' : '' ) . '">' . $label . '</a>';
				}

					do_action( 'user_registration_settings_tabs' );
				?>
			</nav>
			<?php
				do_action( 'user_registration_sections_' . $current_tab );
			?>
		</header>
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
			self::show_messages();

			do_action( 'user_registration_settings_' . $current_tab );
			do_action( 'user_registration_settings_tabs_' . $current_tab ); // @deprecated hook
		?>
		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php echo apply_filters( 'user-registration-setting-save-label', esc_html__( 'Save Changes', 'user-registration' ) ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'user-registration-settings' ); ?>
		</p>
	</form>
</div>
