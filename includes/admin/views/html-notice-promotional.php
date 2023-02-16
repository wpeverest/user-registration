<?php
/**
 * Admin View: Notice - Promotional
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
	<div id="user-registration-<?php echo esc_attr( $notice_type );?>-notice" class="notice notice-info user-registration-notice" data-purpose="<?php echo esc_attr( $notice_type );?>">
		<div class="user-registration-notice-thumbnail">
			<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/UR-Logo.png' ); ?>" alt="">
		</div>
		<div class="user-registration-notice-text">
			<div class="user-registration-notice-header">
				<h3><?php echo wp_kses_post( $notice_header ); ?></h3>
				<?php
				if ( "allow_usage" !== $notice_type ) {
				 ?>
					<a href="#" class="close-btn notice-dismiss notice-dismiss-temporarily">&times;</a>
				<?php
				}
				 ?>
			</div>
			<?php
				promotional_notice_content( $notice_type );
			?>
			<div class="user-registration-notice-links">
			<?php
				promotional_notice_links( $notice_type, $notice_target_link);
			?>
			</div>


		</div>
	</div>
	<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		$( document ).on( 'click', '.ur-allow-usage', function ( event ) {
			event.preventDefault();
			var allow_usage_tracking = true;

			$.post( ajaxurl, {
				action: 'user_registration_allow_usage_dismiss',
				allow_usage_tracking: allow_usage_tracking,
				_wpnonce: '<?php echo esc_js( wp_create_nonce( 'allow_usage_nonce' ) ); ?>'
			} );
			$( '.user-registration-allow_usage-notice' ).remove();
		} );
	} );
</script>
