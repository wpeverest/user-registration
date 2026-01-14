<?php
/**
 * Content Drip message template.
 *
 * Expected $args:
 * [
 *   'activeType' => 'fixed_date'|'days_after',
 *   'value' => [
 *     'fixed_date' => ['date' => 'Y-m-d', 'time' => 'H:i'],
 *     'days_after' => ['days' => int],
 *   ],
 * ]
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$active_type = isset( $args['activeType'] ) ? $args['activeType'] : 'fixed_date';
$value       = isset( $args['value'] ) && is_array( $args['value'] ) ? $args['value'] : array();

$d_title = __( 'Content Locked', 'user-registration' );
$message = __( 'This content isn’t available yet.', 'user-registration' );
$meta    = '';

if ( 'fixed_date' === $active_type ) {
	$date = isset( $value['fixed_date']['date'] ) ? $value['fixed_date']['date'] : '';
	$time = isset( $value['fixed_date']['time'] ) ? $value['fixed_date']['time'] : '';

	if ( $date ) {
		$time = $time ?: '00:00';

		// Build a timestamp (server-time comparison elsewhere)
		$timestamp = strtotime( $date . ' ' . $time );

		if ( $timestamp ) {
			// Show date/time in WP site format + time format
			$formatted = date_i18n(
				get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				$timestamp
			);

			$message = __( 'This content will unlock on:', 'user-registration' );
			$meta    = $formatted;

			// Optional: show "time left" hint
			$now = current_time( 'timestamp' );
			if ( $now && $now < $timestamp ) {
				$diff = $timestamp - $now;

				$days  = floor( $diff / DAY_IN_SECONDS );
				$hours = floor( ( $diff % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
				$mins  = floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

				if ( $days > 0 ) {
					$hint = sprintf(
						/* translators: %d: days, %d: hours */
						_n( 'Available in %1$d day %2$d hours.', 'Available in %1$d days %2$d hours.', $days, 'user-registration' ),
						$days,
						$hours
					);
				} elseif ( $hours > 0 ) {
					$hint = sprintf(
						/* translators: %d: hours, %d: minutes */
						__( 'Available in %1$d hours %2$d minutes.', 'user-registration' ),
						$hours,
						$mins
					);
				} else {
					$hint = sprintf(
						/* translators: %d: minutes */
						__( 'Available in %d minutes.', 'user-registration' ),
						max( 1, $mins )
					);
				}

				$meta .= ' <span class="urcr-content-drip__hint">(' . esc_html( $hint ) . ')</span>';
			}
		}
	}
} elseif ( 'days_after' === $active_type ) {
	$days = isset( $value['days_after']['days'] ) ? absint( $value['days_after']['days'] ) : 0;

	if ( $days > 0 ) {
		$message = __( 'This content will unlock after:', 'user-registration' );
		$meta    = sprintf(
			/* translators: %d: number of days */
			_n( '%d day', '%d days', $days, 'user-registration' ),
			$days
		);
	} else {
		$message = __( 'This content isn’t available yet.', 'user-registration' );
	}
}

?>
<style>
	/* Hide page d_ for whole site restrictions */
	body.urcr-hide-page-title .wp-block-post-title,
	body.urcr-hide-page-title .entry-header,
	body.urcr-hide-page-title .page-header,
	body.urcr-hide-page-title .entry-title,
	body.urcr-hide-page-title .page-title,
	body.urcr-hide-page-title h1.entry-title,
	body.urcr-hide-page-title h1.page-title,
	body.urcr-hide-page-title .post-title,
	body.urcr-hide-page-title .single-post-title,
	body.urcr-hide-page-title .single-page-title,
	body.urcr-hide-page-title article header.entry-header,
	body.urcr-hide-page-title article .entry-title {
		display: none !important;
	}
	.urcr-access-card {
		display: flex;
		flex-direction: column;
		background-color: #ffffff;
		border: 1px solid #f1f5f9;
		border-radius: 7px;
		padding: 32px;
		max-width: 500px !important;
		width: 100%;
		box-shadow: 0 6px 26px 0 rgba(10, 10, 10, 0.06);
		margin: 24px auto !important;
		box-sizing: border-box;
	}
	.urcr-access-card h3 {
		font-weight: 700;
		font-size: 28px;
		line-height: 36px;
		color: #1a1a1a;
		margin: 0 0 20px;
	}
	.urcr-access-card p {
		font-weight: 400;
		font-size: 16px;
		line-height: 24px;
		color: #6B6B6B;
		margin: 0 0 12px;
	}
	.urcr-access-card p:last-child {
		margin: 12px 0 0;
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px 20px;
	}
	.urcr-access-card p > a {
		font-size: 16px;
		line-height: 24px;
		padding: 14px 32px;
		font-weight: 500;
		border-radius: 4px;
		text-decoration: none;
		margin: 0;
		cursor: pointer;
		background: transparent;
		color: #4e4e4e;
		transition: all .3s ease;
	}
	.urcr-access-card p > a.urcr-signup-link {
		background: #475bb2;
		color: #ffffff;
	}
	.urcr-access-card p > a.urcr-signup-link:hover {
		background: #38488e;
		color: #ffffff;
	}
	.urcr-access-card p > a.urcr-access-button {
		background: transparent;
		text-decoration: underline;
		padding: 14px 16px;
	}
	.urcr-access-card p > a.urcr-access-button:hover {
		color: #475bb2;
	}
	.urcr-access-heading {
		font-size: 28px;
		font-weight: 700;
		color: #1a1a1a;
		margin: 0 0 16px 0;
		line-height: 1.2;
	}
	.urcr-access-description {
		font-size: 16px;
		color: #6B6B6B;
		margin: 0 0 40px 0;
		line-height: 1.5;
	}
	.urcr-access-description br {
		display: none;
	}
	.urcr-actions {
		display: block;
		text-align: center;
	}
	.urcr-actions br {
		display: none;
	}
	.urcr-actions a {
		text-decoration: none;
		margin: 0;
		display: block;
		text-align: left;
	}
	@media (max-width: 480px) {
		.urcr-access-card {
			padding: 24px;
		}
		.urcr-access-card h3 {
			font-size: 22px;
			line-height: 30px;
		}
		.urcr-access-card p {
			font-size: 15px;
			line-height: 25px;
		}
		.urcr-access-heading {
			font-size: 24px;
		}
	}
</style>
<div class="urcr-access-card">
		<strong class="urcr-content-drip__title"><?php echo esc_html( $d_title ); ?></strong>

	<div class="urcr-content-drip__body">
		<p class="urcr-content-drip__message"><?php echo esc_html( $message ); ?></p>

		<?php if ( $meta ) : ?>
			<p class="urcr-content-drip__meta"><?php echo wp_kses_post( $meta ); ?></p>
		<?php endif; ?>

		<p class="urcr-content-drip__note">
			<?php echo esc_html__( 'Please check back later.', 'user-registration' ); ?>
		</p>
	</div>
</div>
