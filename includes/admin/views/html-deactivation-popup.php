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
				<span class="ur-deactivate-feedback-popup-header-title"><?php echo esc_html__('Quick Feedback', 'user-registration'); ?></span>
		</div>
		<form class="ur-deactivate-feedback-form" method="POST">
			<?php
			wp_nonce_field('_ur_deactivate_feedback_nonce');
			?>
			<input type="hidden" name="action" value="ur_deactivate_feedback"/>

			<div
				class="ur-deactivate-feedback-popup-form-caption"><?php echo sprintf(esc_html__('Could you please share why you are deactivating %sUser Registration%s plugin?', 'user-registration'), '<span>', '</span>'); ?></div>
			<div class="ur-deactivate-feedback-popup-form-body">
				<?php foreach ($deactivate_reasons as $reason_slug => $reason) : ?>
					<div class="ur-deactivate-feedback-popup-input-wrapper">
						<input id="ur-deactivate-feedback-<?php echo esc_attr($reason_slug); ?>"
							   class="ur-deactivate-feedback-input" type="radio" name="reason_slug"
							   value="<?php echo esc_attr($reason_slug); ?>"/>
						<label for="ur-deactivate-feedback-<?php echo esc_attr($reason_slug); ?>"
							   class="ur-deactivate-feedback-label"><?php echo esc_html($reason['title']); ?></label>
						<?php if (!empty($reason['input_placeholder'])) : ?>
							<input class="ur-feedback-text" type="text"
								   name="reason_<?php echo esc_attr($reason_slug); ?>"
								   placeholder="<?php echo esc_attr($reason['input_placeholder']); ?>"/>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ur-deactivate-feedback-popup-form-footer">
				<button class="submit" type="submit"><?php esc_html_e('Submit &amp; Deactivate', 'user-registration'); ?>
				</button>
				<a href="<?php echo esc_url($deactivate_url) ?>" class="skip"><?php esc_html_e('Skip &amp; Deactivate', 'user-registration'); ?></a>
			</div>
			<span class="consent">* <?php esc_html_e('By submitting this form, you will also be sending us your email address & website URL.', 'user-registration'); ?></span>
		</form>
	</div>
</div>
