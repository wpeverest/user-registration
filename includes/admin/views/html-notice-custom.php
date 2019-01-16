<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="updated user-registration-message">
	<a class="user-registration-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ur-hide-notice', $notice ), 'user_registration_hide_notices_nonce', '_ur_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'user-registration' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
