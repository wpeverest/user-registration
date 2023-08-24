<?php
/**
 * Deactivation popup admin
 *
 * Link to WPEverst contact form page.
 *
 * @package     User Registration/Admin
 * @since       2.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $status, $page, $s;

$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . UR_PLUGIN_BASENAME . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . UR_PLUGIN_BASENAME );
?>
<div id="ur-deactivate-feedback-popup-wrapper">
	<div class="ur-deactivate-feedback-popup-inner">
		<div class="ur-deactivate-feedback-popup-header">
			<div class="ur-deactivate-feedback-popup-header__logo-wrap">
				<div class="ur-deactivate-feedback-popup-header__logo-icon">
					<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><defs><style>.cls-1{fill:#475bb2;}</style></defs><title>Artboard 1 copy 3</title><path class="cls-1" d="M27.58,4a27.9,27.9,0,0,0-5.17,4,27,27,0,0,0-4.09,5.08,33.06,33.06,0,0,1,2,4.65A23.78,23.78,0,0,1,24,12.15V18a8,8,0,0,1-5.89,7.72l-.21.05A27,27,0,0,0,16,17.61,27.9,27.9,0,0,0,9.59,8,27.9,27.9,0,0,0,4.42,4L4,3.77V18a12,12,0,0,0,9.93,11.82l.14,0a11.72,11.72,0,0,0,3.86,0l.14,0A12,12,0,0,0,28,18V3.77ZM8,18V12.15a23.86,23.86,0,0,1,5.89,13.57A8,8,0,0,1,8,18ZM16,2a3,3,0,1,0,3,3A3,3,0,0,0,16,2Z"/></svg>
				</div>
				<span class="ur-deactivate-feedback-popup-header-title"><?php echo esc_html__( 'Quick Feedback', 'user-registration' ); ?></span>
			</div>
			<a class="close-deactivate-feedback-popup"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
		<form class="ur-deactivate-feedback-form" method="POST">
			<?php
			wp_nonce_field( '_ur_deactivate_feedback_nonce' );
			?>
			<input type="hidden" name="action" value="ur_deactivate_feedback"/>

			<div class="ur-deactivate-feedback-popup-form-caption">
				<?php
				/*
				 * translators: %1$s HTML span tag.
				 * translators: %2$s HTML span closing tag.
				 */
					echo sprintf( esc_html__( 'Could you please share why you are deactivating %1$sUser Registration%2$s plugin?', 'user-registration' ), '<span>', '</span>' );
				?>
			</div>
			<div class="ur-deactivate-feedback-popup-form-body">
				<?php foreach ( $deactivate_reasons as $reason_slug => $reason ) : ?>
					<div class="ur-deactivate-feedback-popup-input-wrapper">
						<input id="ur-deactivate-feedback-<?php echo esc_attr( $reason_slug ); ?>"
							class="ur-deactivate-feedback-input" type="radio" name="reason_slug"
							value="<?php echo esc_attr( $reason_slug ); ?>"/>
						<label for="ur-deactivate-feedback-<?php echo esc_attr( $reason_slug ); ?>"
							class="ur-deactivate-feedback-label"><?php echo wp_kses_post( $reason['title'] ); ?></label>
						<?php if ( ! empty( $reason['input_placeholder'] ) ) : ?>
							<input class="ur-feedback-text" type="text"
								name="reason_<?php echo esc_attr( $reason_slug ); ?>"
								placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>"/>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ur-deactivate-feedback-popup-form-footer">
				<a href="<?php echo esc_url( $deactivate_url ); ?>" class="skip"><?php esc_html_e( 'Skip &amp; Deactivate', 'user-registration' ); ?></a>
				<button class="submit" type="submit"><?php esc_html_e( 'Submit &amp; Deactivate', 'user-registration' ); ?></button>
			</div>
			<span class="consent">* <?php esc_html_e( 'By submitting this form, you will also be sending us your email address & website URL.', 'user-registration' ); ?></span>
		</form>
	</div>
</div>
