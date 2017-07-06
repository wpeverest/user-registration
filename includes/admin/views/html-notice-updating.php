<?php
/**
 * Admin View: Notice - Updating
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated user-registration-message ur-connect">
	<p><strong><?php _e( 'User Registration Data Update', 'user-registration' ); ?></strong> &#8211; <?php _e( 'Your database is being updated in the background.', 'user-registration' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_user_registration', 'true', admin_url( 'options-general.php?page=user-registration' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'user-registration' ); ?></a></p>
</div>
