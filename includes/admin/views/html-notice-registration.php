<?php
/**
 * Admin View: Notice - Allow Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="error user-registration-message">
 	<p><?php echo sprintf( __( 'Please enable %sAnyone can register%s option on %sgeneral setting%s.', 'user-registration' ), '<a target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">', '</a>', '<a target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">', '</a>' ); ?></p>
</div>

