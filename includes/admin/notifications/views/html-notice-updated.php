<?php
/**
 * Admin View: Notice - Updated
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated user-registration-message ess-connect">
	<a class="user-registration-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ur-hide-notice', 'update', remove_query_arg( 'do_update_user_registration' ) ), 'user_registration_hide_notices_nonce', '_ur_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'user-registration' ); ?></a>

	<p><?php esc_html_e( 'User Registration & Membership data update complete. Thank you for updating to the latest version!', 'user-registration' ); ?></p>
</div>
