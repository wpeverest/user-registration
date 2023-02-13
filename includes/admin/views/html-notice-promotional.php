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
				<a href="#" class="close-btn notice-dismiss notice-dismiss-temporarily">&times;</a>
			</div>
			<?php
				switch ($notice_type) {
					case 'review':
						?>
						<p><?php echo wp_kses_post( __( '( The above word is just to draw your attention. <span class="dashicons dashicons-smiley smile-icon"></span> )', 'user-registration' ) ); ?> </p>
						<p><?php echo wp_kses_post( __( 'Hope you are having nice experience with <strong>User Registration</strong> plugin. Please provide this plugin a nice review.', 'user-registration' ) ); ?></p>
						<p class="extra-pad">
								<?php
								echo wp_kses_post(
									__(
										'<strong>What benefit would you have?</strong> <br>
						Basically, it would encourage us to release updates regularly with new features & bug fixes so that you can keep on using the plugin without any issues and also to provide free support like we have been doing. <span class="dashicons dashicons-smiley smile-icon"></span><br>',
										'user-registration'
									)
								);
								?>
						</p>
						<?php
						break;

					case 'survey':
						?>
							<p>
							<?php
							echo wp_kses_post(
								__(
									'<strong>Hey there!</strong> <br>
								We would be grateful if you could spare a moment and help us fill this survey. This survey will take approximately 4 minutes to complete.',
									'user-registration'
								)
							);
							?>
								</p>
							<p class="extra-pad">
							<?php
							echo wp_kses_post(
								__(
									'<strong>What benefit would you have?</strong> <br>
								We will take your feedback from the survey and use that information to make the plugin better. As a result, you will have a better plugin as you wanted. <span class="dashicons dashicons-smiley smile-icon"></span><br>',
									'user-registration'
								)
							);
							?>
							</p>
						<?php
						break;

					default:
						break;
				}
			?>

			<div class="user-registration-notice-links">
				<ul class="user-registration-notice-ul">
					<li><a class="button button-primary" href="<?php echo esc_url( $notice_target_link ); ?>" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure, I\'d love to!', 'user-registration' ); ?></a></li>
					<li><a href="#" class="button button-secondary notice-dismiss notice-dismiss-permanently"><span  class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I already did!', 'user-registration' ); ?></a></li>
					<li><a href="#" class="button button-secondary notice-dismiss notice-dismiss-temporarily"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e( 'Maybe later', 'user-registration' ); ?></a></li>
					<li><a href="https://wpeverest.com/support-forum/" class="button button-secondary notice-have-query" target="_blank"><span class="dashicons dashicons-testimonial"></span><?php esc_html_e( 'I have a query', 'user-registration' ); ?></a></li>
				</ul>
				<a href="#" class="notice-dismiss notice-dismiss-permanently"><?php esc_html_e( 'Never show again', 'user-registration' ); ?></a>
			</div>
		</div>
	</div>
