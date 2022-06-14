<?php
/**
 * Admin View: Notice - License Unvalidated
 *
 * @package  UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated">
	<p class="ur-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $this->plugin_slug ), '1' ) ); ?>"><?php esc_html_e( 'Hide notice', 'user-registration' ); ?></a></p>
	<p>
	<?php
		/* translators: 1: License Page 2: Plugin Name */
		echo wp_kses_post( sprintf( __( '%1$s Please enter your license key%2$s in the plugin list below to get updates for <strong>%3$s</strong> Add-Ons.', 'user-registration' ), '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=license' ) ) . '">', '</a>', esc_html( $this->plugin_data['Name'] ) ) );
	?>
	</p>
</div>
