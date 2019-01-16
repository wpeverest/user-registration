<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated user-registration-message ur-connect">
	<p><?php _e( '<strong>Welcome to User Registration</strong> &#8211; You&lsquo;re almost ready to create registration :)', 'user-registration' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'install_user_registration_pages', 'true', admin_url( 'admin.php?page=user-registration-settings' ) ) ); ?>" class="button-primary"><?php _e( 'Install User Registration Pages', 'user-registration' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ur-hide-notice', 'install' ), 'user_registration_hide_notices_nonce', '_ur_notice_nonce' ) ); ?>"><?php _e( 'Skip setup', 'user-registration' ); ?></a></p>
</div>
