<?php
/**
 * Admin View: Notice - Allow Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="error user-registration-message">
 	<p><?php echo sprintf( __( 'Please enable %s Anyone can register %s option on %s general setting %s.', 'user-registration' ), '<a target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">', '</a>', '<a target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">', '</a>' ); ?></p>
</div>

