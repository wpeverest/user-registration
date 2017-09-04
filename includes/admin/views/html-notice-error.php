<?php
/**
 * Admin View: Notice - License Error
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="error ur-updater-license-key-error <?php if ( did_action( 'all_admin_notices' ) ) echo 'inline'; ?>">
	<p><?php echo wp_kses_post( $error ); ?></p>
</div>
