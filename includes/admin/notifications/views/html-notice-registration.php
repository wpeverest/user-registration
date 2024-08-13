<?php
/**
 * Admin View: Notice - Allow Registration
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div id="message" class="error user-registration-message">
	<a class="user-registration-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ur-hide-notice', 'register' ), 'user_registration_hide_notices_nonce', '_ur_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'user-registration' ); ?></a>

	<p>
	<?php
		echo wp_kses_post(
			sprintf(
					/* translators: 1 General Settings, 2: Link to anyone can register settings */
				__( 'To allow users to register for your website via User registration, you must first enable user registration. Go to %1$sSettings > General%2$s tab, and under Membership make sure to check <strong>Anyone can register</strong>.', 'user-registration' ),
				'<a rel="noreferrer noopener" target="_blank" href="' . admin_url( 'options-general.php#admin_email' ) . '">',
				'</a>'
			)
		);
		?>
	</p>
</div>
