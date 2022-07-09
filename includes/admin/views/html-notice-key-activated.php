<?php
/**
 * Admin View: Notice - License Activated
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated notice is-dismissible">
	<p>
		<?php
			/* translators: %s - Link to logout. */
			wp_kses_post( sprintf( __( 'Your licence for <strong>%s</strong> has been activated. Thanks!', 'user-registration' ), esc_html( $this->plugin_data['Name'] ) ) );
		?>
	</p>
</div>
