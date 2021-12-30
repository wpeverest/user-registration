<?php
/**
 * Admin View: Notice - License Error
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="<?php echo did_action( 'all_admin_notices' ) ? 'update-message notice inline notice-alt notice-error' : 'error'; ?>">
	<p><?php echo wp_kses_post( $error ); ?></p>
</div>
