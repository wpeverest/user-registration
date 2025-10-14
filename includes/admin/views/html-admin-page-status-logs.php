<?php
/**
 * Admin View: Page - Status Logs
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php if ( $logs ) : ?>
	<div id="log-viewer-select">
		<div class="alignleft">
			<h2>
				<?php echo esc_html( $viewed_log ); ?>
				<?php if ( 1 < count( $logs ) ) : ?>
				<a class="button page-title-action page-title-action-all" style="border-color: #F25656; background: #F25656; color: #ffffff; font-size: 14px; line-height: 20px; padding: 8px 14px; font-weight: 500;" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle_all' => sanitize_title( 'delete-all-logs' ) ), admin_url( 'admin.php?page=user-registration-status&tab=logs' ) ), 'remove_all_logs' ) ); ?>" class="button"><?php esc_html_e( 'Delete all logs', 'user-registration' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $viewed_log ) ) : ?>
					<a class="button page-title-action" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle' => sanitize_title( $viewed_log ) ), admin_url( 'admin.php?page=user-registration-status&tab=logs' ) ), 'remove_log' ) ); ?>" class="button"><?php esc_html_e( 'Delete log', 'user-registration' ); ?></a>
				<?php endif; ?>
			</h2>
		</div>
		<div class="alignright">
			<form action="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-status' ) ); ?>" method="post">
				<select name="log_file" style="max-width: 450px;vertical-align:inherit">
					<?php foreach ( $logs as $log_key => $log_file ) : ?>
						<option
							value="<?php echo esc_attr( $log_key ); ?>" <?php selected( sanitize_title( $viewed_log ), $log_key ); ?>><?php echo esc_html( $log_file ); ?>
							(<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), filemtime( UR_LOG_DIR . $log_file ) ) ); ?>
							)
						</option>
					<?php endforeach; ?>
				</select>
				<input type="submit" class="button" value="<?php esc_attr_e( 'View', 'user-registration' ); ?>"/>
			</form>
		</div>
		<div class="clear"></div>
	</div>
	<div id="log-viewer">
		<pre><?php echo esc_html( file_get_contents( UR_LOG_DIR . $viewed_log ) ); ?></pre>
	</div>
<?php else : ?>
	<div class="updated user-registration-message inline">
		<p><?php esc_html_e( 'There are currently no logs to view.', 'user-registration' ); ?></p></div>
<?php endif; ?>
