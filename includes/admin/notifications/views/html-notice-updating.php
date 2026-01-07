<?php
/**
 * Admin View: Notice - Updating
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated user-registration-message ur-connect">
	<p><strong><?php esc_html_e( 'User Registration & Membership Data Update', 'user-registration' ); ?></strong> &#8211; <?php esc_html_e( 'Your database is being updated in the background.', 'user-registration' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_user_registration', 'true', admin_url( 'options-general.php?page=user-registration' ) ) ); ?>"><?php esc_html_e( 'Taking a while? Click here to run it now.', 'user-registration' ); ?></a></p>
</div>
