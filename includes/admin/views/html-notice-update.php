<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated user-registration-message ur-connect">
	<p><strong><?php _e( 'User Registration Data Update', 'user-registration' ); ?></strong> &#8211; <?php _e( 'We need to update your site\'s database to the latest version.', 'user-registration' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_user_registration', 'true', admin_url( 'options-general.php?page=user-registration' ) ) ); ?>" class="ur-update-now button-primary"><?php _e( 'Run the updater', 'user-registration' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.ur-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'user-registration' ) ); ?>' ); // jshint ignore:line
	});
</script>
