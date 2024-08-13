<?php
/**
 * Admin View: Notice - Promotional
 *
 * @package UserRegistration/Admin
 * @since       2.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$notice_border = 'notice-info';

switch ( $notice_type ) {
	case 'important':
		$notice_border = 'notice-error';
		break;
	case 'warning':
		$notice_border = 'notice-warning';
		break;
	default:
		$notice_border = 'notice-info';
}
?>
	<div id="user-registration-<?php echo esc_attr( $notice_id ); ?>-notice" class="notice <?php echo esc_attr( $notice_border ); ?> user-registration-notice" data-purpose="<?php echo esc_attr( $notice_type ); ?>" data-notice-id="<?php echo esc_attr( $notice_id ); ?>">
		<div class="user-registration-notice-thumbnail">
			<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-Logo.gif' ); ?>" alt="">
		</div>
		<div class="user-registration-notice-text">
			<div class="user-registration-notice-header">
				<h3><?php echo wp_kses_post( $notice_header ); ?></h3>
				<?php
				if ( 'allow_usage' !== $notice_type ) {
					?>
					<a href="#" class="close-btn notice-dismiss notice-dismiss-temporarily">&times;</a>
					<?php
				}
				?>
			</div>
			<?php
				echo wp_kses_post( $notice_content );
			?>
			<div class="user-registration-notice-links">
			<?php
				promotional_notice_links( $notice_target_links, $permanent_dismiss );
			?>
			</div>


		</div>
	</div>
	<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		$( document ).on( 'click', '.ur-allow-usage', function ( event ) {
			event.preventDefault();
			var allow_usage_tracking = true;
			ajaxCall( allow_usage_tracking );
		} );
		$( document ).on( 'click', '.ur-deny-usage', function ( event ) {
			event.preventDefault();
			var allow_usage_tracking = false;
			ajaxCall( allow_usage_tracking );
		} );

		function ajaxCall( allow_usage_tracking ) {
			$.post( ajaxurl, {
				action: 'user_registration_allow_usage_dismiss',
				allow_usage_tracking: allow_usage_tracking,
				_wpnonce: '<?php echo esc_js( wp_create_nonce( 'allow_usage_nonce' ) ); ?>'
			} );
			$( '#user-registration-allow_usage-notice' ).remove();
		}
	} );
</script>
