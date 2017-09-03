<?php
/**
 * Admin View: Notice - License Unvalidated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated">
	<p class="ur-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $this->plugin_slug ), '1' ) ); ?>"><?php _e( 'Hide notice', 'user-registration' ); ?></a></p>
	<p><?php printf( __( '%sPlease enter your license key%s in the plugin list below to get updates for <strong>%s</strong> Add-Ons.', 'user-registration' ), '<a href="' . esc_url( admin_url( 'plugins.php#' . sanitize_title( $this->plugin_slug ) ) ) . '">', '</a>', esc_html( $this->plugin_data[ 'Name' ] ) ); ?></p>
</div>
