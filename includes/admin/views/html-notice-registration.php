<?php
/**
 * Admin View: Notice - Allow Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="error user-registration-message">
	<a class="user-registration-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ur-hide-notice', 'register' ), 'user_registration_hide_notices_nonce', '_ur_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'user-registration' ); ?></a>

	<p><?php echo sprintf( __( 'To allow users to register for your website via User registration, you must first enable user registration. Go to %sSettings > General%s tab, and under Membership make sure to check <strong>Anyone can register</strong>.', 'user-registration' ), '<a target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">', '</a>' ); ?></p>
</div>

