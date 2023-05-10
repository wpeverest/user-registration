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
	<?php
		/* translators: 1: License Page 2: Plugin Name */
		echo wp_kses_post( sprintf( __( '%1$s Please enter your license key%2$s in the plugin list below to get updates for <strong>%3$s</strong> Add-Ons.', 'user-registration' ), '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=license' ) ) . '">', '</a>', esc_html( $this->plugin_data['Name'] ) ) );
	?>
	</p>
</div>
