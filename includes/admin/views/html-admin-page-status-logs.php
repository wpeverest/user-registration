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
					<?php
					$delete_all_url = wp_nonce_url(
						add_query_arg(
							array( 'handle_all' => sanitize_title( 'delete-all-logs' ) ),
							admin_url( 'admin.php?page=user-registration-status&tab=logs' )
						),
						'remove_all_logs'
					);
					?>
					<a 
						class="button page-title-action page-title-action-all" 
						style="border-color: #F25656; background: #F25656; color: #ffffff; font-size: 14px; line-height: 20px; padding: 8px 14px; font-weight: 500;" 
						href="<?php echo esc_url( $delete_all_url ); ?>"
					>
						<?php esc_html_e( 'Delete all logs', 'user-registration' ); ?>
					</a>
				<?php endif; ?>
				
				<?php if ( ! empty( $viewed_log ) ) : ?>
					<?php
					$delete_log_url = wp_nonce_url(
						add_query_arg(
							array( 'handle' => sanitize_title( $viewed_log ) ),
							admin_url( 'admin.php?page=user-registration-status&tab=logs' )
						),
						'remove_log'
					);
					?>
					<a 
						class="button page-title-action" 
						href="<?php echo esc_url( $delete_log_url ); ?>"
					>
						<?php esc_html_e( 'Delete log', 'user-registration' ); ?>
					</a>
				<?php endif; ?>
			</h2>
		</div>
		
		<div class="alignright">
			<form action="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-status' ) ); ?>" method="post">
				<select name="log_file" style="max-width: 450px; vertical-align: inherit;">
					<?php foreach ( $logs as $log_key => $log_file ) : ?>
						<?php
						$file_date = date_i18n(
							get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
							filemtime( UR_LOG_DIR . $log_file )
						);
						?>
						<option value="<?php echo esc_attr( $log_key ); ?>" <?php selected( sanitize_title( $viewed_log ), $log_key ); ?>>
							<?php echo esc_html( $log_file ); ?> (<?php echo esc_html( $file_date ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
				<input type="submit" class="button" value="<?php esc_attr_e( 'View', 'user-registration' ); ?>" />
			</form>
		</div>
		
		<div class="clear"></div>
	</div>
	<style>
		#log-viewer .log-level-info    { color: #2271b1; font-weight: bold; }
		#log-viewer .log-level-notice  { color: #00a0d2; font-weight: bold; }
		#log-viewer .log-level-warning { color: #f0b849; font-weight: bold; }
		#log-viewer .log-level-error   { color: #dc3232; font-weight: bold; }
		#log-viewer .log-level-debug   { color: #7e57c2; font-weight: bold; }
		#log-viewer .log-level-success { color: #46b450; font-weight: bold; }
	</style>
	
	<div id="log-viewer">
		<?php
		$log_content    = file_get_contents( UR_LOG_DIR . $viewed_log );
		$is_payment_log = ( strpos( $viewed_log, 'urm-pg-' ) === 0 );

		if ( $is_payment_log ) {
			// Enhanced display for payment gateway logs with colored log levels
			echo '<pre>';
			$lines = explode( "\n", $log_content );
			
			foreach ( $lines as $line ) {
				// Match log pattern: timestamp LEVEL message
				$pattern = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2})\s+(INFO|NOTICE|WARNING|ERROR|DEBUG|SUCCESS)\s+(.+)$/s';
				
				if ( preg_match( $pattern, $line, $matches ) ) {
					$timestamp   = $matches[1];
					$level       = $matches[2];
					$message     = $matches[3];
					
					// Format timestamp to be more readable using WordPress timezone
					try {
						$date         = new DateTime( $timestamp );
						$wp_timezone  = wp_timezone();
						$date->setTimezone( $wp_timezone );
						$formatted_time = $date->format( 'M d, Y g:i:s A' );
					} catch ( Exception $e ) {
						$formatted_time = $timestamp; // Fallback to original if parsing fails
					}
					
					// Output formatted log line with colored level
					echo esc_html( $formatted_time ) . ' ';
					echo '<span class="log-level-' . esc_attr( strtolower( $level ) ) . '">' . esc_html( $level ) . '</span> ';
					echo esc_html( $message ) . "\n";
				} else {
					// Output non-matching lines as-is
					echo esc_html( $line ) . "\n";
				}
			}
			
			echo '</pre>';
		} else {
			// Standard display for other logs
			echo '<pre>' . esc_html( $log_content ) . '</pre>';
		}
		?>
	</div>
	
<?php else : ?>
	<div class="updated user-registration-message inline">
		<p><?php esc_html_e( 'There are currently no logs to view.', 'user-registration' ); ?></p>
	</div>
<?php endif; ?>
