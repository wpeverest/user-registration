<?php
/**
 * Admin View: Notice - License Deactivated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated notice is-dismissible">
	<p><?php printf( __( 'Your licence for <strong>%s</strong> has been deactivated.', 'user-registration' ), esc_html( $this->plugin_data['Name'] ) ); ?></p>
</div>
