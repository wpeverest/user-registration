<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class UR_Admin_Deactivation_Feedback
{
	const FEEDBACK_URL = '';

	public function __construct()
	{
		add_action('current_screen', function () {
			if (!$this->is_plugins_screen()) {
				return;
			}

			add_action('admin_enqueue_scripts', [$this, 'scripts']);
		});


		// Ajax.
		add_action('wp_ajax_ur_deactivate_feedback', [$this, 'send']);

		//add_action('wp_ajax_user_registration_deactivation_notice', [$this, 'deactivation_notice']);
	}

	/**
	 * AJAX plugin deactivation notice.
	 *
	 * @since  1.4.2
	 */
	public static function deactivation_notice()
	{

		check_ajax_referer('deactivation-notice', 'security');

		ob_start();

		include_once UR_ABSPATH . 'includes/admin/views/html-notice-deactivation.php';

		$content = ob_get_clean();

		wp_send_json($content); // WPCS: XSS OK.
	}

	public function scripts()
	{
		add_action('admin_footer', [$this, 'feedback_html']);

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'ur-admin-deactivation-feedback',
			UR()->plugin_url() . '/assets/js/admin/deactivation-feedback' . $suffix . '.js',
			[
				'jquery'
			],
			UR_VERSION,
			true
		);

		wp_enqueue_style(
			'ur-admin-deactivation-feedback',
			UR()->plugin_url() . '/assets/css/deactivation-feedback' . $suffix . '.css',
			[
			],
			UR_VERSION,
		);

		wp_localize_script(
			'ur-admin-deactivation-feedback',
			'ur_plugins_params',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'deactivation_nonce' => wp_create_nonce('deactivation-notice'),
			)
		);


	}


	public function feedback_html()
	{
		$deactivate_reasons = [
			'feature_unavailable' => [
				'title' => esc_html__('I didn’t find the feature I was looking for', 'user-registration'),
				'input_placeholder' => esc_html__('Please share the reason', 'user-registration'),			],
			'complex_to_use' => [
				'title' => esc_html__('I found the plugin complex to use', 'user-registration'),
				'input_placeholder' => esc_html__('Please share which plugin', 'user-registration'),
			],
			'couldnt_build_the_form' => [
				'title' => esc_html__('I couldn\'t build the form', 'user-registration'),
				'input_placeholder' => esc_html__('Please share the reason', 'user-registration'),
			],
			'found_a_better_plugin' => [
				'title' => esc_html__('I found better alternative', 'user-registration'),
				'input_placeholder' => esc_html__('Please share the reason', 'user-registration'),
			],
			'temporary_deactivation' => [
				'title' => esc_html__('It\'s temporary deactivation', 'user-registration'),
				'input_placeholder' => '',
			],
			'no_longer_needed' => [
				'title' => esc_html__('No longer need the plugin', 'user-registration'),
				'input_placeholder' => '',
			],
			'other' => [
				'title' => esc_html__('Other', 'user-registration'),
				'input_placeholder' => esc_html__('Please share the reason', 'user-registration'),
			],
		];

		?>
		<div id="ur-deactivate-feedback-dialog-wrapper">
			<div class="ur-deactivate-feedback-dialog-inner">
				<div id="ur-deactivate-feedback-dialog-header">
				<span
					id="ur-deactivate-feedback-dialog-header-title"><?php echo esc_html__('Quick Feedback', 'user-registration'); ?></span>
				</div>
				<form id="ur-deactivate-feedback-dialog-form" method="post">
					<?php
					wp_nonce_field('_ur_deactivate_feedback_nonce');
					?>
					<input type="hidden" name="action" value="ur_deactivate_feedback"/>

					<div
						id="ur-deactivate-feedback-dialog-form-caption"><?php echo esc_html__('If you have a moment, please share why you are deactivating User Registration:', 'user-registration'); ?></div>
					<div id="ur-deactivate-feedback-dialog-form-body">
						<?php foreach ($deactivate_reasons as $reason_key => $reason) : ?>
							<div class="ur-deactivate-feedback-dialog-input-wrapper">
								<input id="ur-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
									   class="ur-deactivate-feedback-dialog-input" type="radio" name="reason_key"
									   value="<?php echo esc_attr($reason_key); ?>"/>
								<label for="ur-deactivate-feedback-<?php echo esc_attr($reason_key); ?>"
									   class="ur-deactivate-feedback-dialog-label"><?php echo esc_html($reason['title']); ?></label>
								<?php if (!empty($reason['input_placeholder'])) : ?>
									<input class="ur-feedback-text" type="text"
										   name="reason_<?php echo esc_attr($reason_key); ?>"
										   placeholder="<?php echo esc_attr($reason['input_placeholder']); ?>"/>
								<?php endif; ?>
								<?php if (!empty($reason['alert'])) : ?>
									<div class="ur-feedback-text"><?php echo esc_html($reason['alert']); ?></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div id="ur-deactivate-feedback-dialog-form-footer">
						<button class="submit">Submit &amp; Deactivate
						</button>
						<button class="skip">Skip &amp; Deactivate</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function send()
	{
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], '_ur_deactivate_feedback_nonce')) {
			wp_send_json_error();
		}

		$reason_text = '';
		$reason_key = '';

		if (!empty($_POST['reason_key'])) {
			$reason_key = $_POST['reason_key'];
		}

		if (!empty($_POST["reason_{$reason_key}"])) {
			$reason_text = $_POST["reason_{$reason_key}"];
		}

		wp_remote_post(self::FEEDBACK_URL, [
			'timeout' => 30,
			'body' => [
				'feedback_key' => $reason_key,
				'feedback' => $reason_text,
			],
		]);

		wp_send_json_success();
	}

	private function is_plugins_screen()
	{
		return in_array(get_current_screen()->id, ['plugins', 'plugins-network']);
	}
}

new UR_Admin_Deactivation_Feedback();
