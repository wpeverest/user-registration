<?php
/**
 * UserRegistration From Templates
 *
 * @package  UserRegistration/Admin/From Templates
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class UR_Admin_Form_Templates
{

	private static function get_default_template()
	{
		$template = new stdClass();
		$template->title = __('Start From Scratch', 'user-registration');
		$template->slug = 'blank';
		$template->image = untrailingslashit(plugin_dir_url(UR_PLUGIN_FILE)) . '/assets/images/templates/blank.png';
		$template->plan = ['free'];

		return [$template];
	}

	/**
	 * Get section content for the template screen.
	 *
	 * @return array
	 */
	public static function get_template_data()
	{
		$template_data = get_transient('ur_template_section_list');

		$template_url = "https://ur-form-templates-pack.s3.ap-south-1.amazonaws.com/";

		if (false === $template_data) {

			$template_json_url = $template_url . 'templates.json';
			try {
				$content = wp_remote_get($template_json_url);
				$content_json = wp_remote_retrieve_body($content);
				$template_data = json_decode($content_json);
			} catch (Exception $e) {

			}


			// Removing directory so the templates can be reinitialized.
			$folder_path = untrailingslashit(plugin_dir_path(UR_PLUGIN_FILE) . '/assets/images/templates');
			if (isset($template_data->templates)) {

				foreach ($template_data->templates as $template_tuple) {

					$image_url = isset($template_tuple->image) ? $template_tuple->image : ($template_url . 'images/' . $template_tuple->slug . '.png');

					$template_tuple->image = $image_url;

					$temp_name = explode('/', $image_url);
					$relative_path = $folder_path . '/' . end($temp_name);
					$exists = file_exists($relative_path);

					// If it exists, utilize this file instead of remote file.
					if ($exists) {
						$template_tuple->image = untrailingslashit(plugin_dir_url(UR_PLUGIN_FILE)) . '/assets/images/templates/'.untrailingslashit($template_tuple->slug).'.png';
					}
				}

				set_transient('ur_template_section_list', $template_data, WEEK_IN_SECONDS);
			}
		}

		return isset($template_data->templates) ? apply_filters('user_registration_template_section_data', $template_data->templates) : self::get_default_template();
	}

	public static function load_template_view()
	{
		$templates = array();
		$current_section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '_all'; // phpcs:ignore WordPress.Security.NonceVerification
		$category = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : 'free'; // phpcs:ignore WordPress.Security.NonceVerification
		$templates = UR_Admin_Form_Templates::get_template_data();

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script('ur-setup');
		wp_localize_script(
			'ur-setup',
			'ur_setup_params',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'create_form_nonce' => wp_create_nonce('user_registration_create_form'),
				'template_licence_check_nonce' => wp_create_nonce('user_registration_template_licence_check'),
				'i18n_form_name' => esc_html__('Give it a name.', 'user-registration'),
				'i18n_form_error_name' => esc_html__('You must provide a Form name', 'user-registration'),
				'i18n_install_only' => esc_html__('Activate Plugins', 'user-registration'),
				'i18n_activating' => esc_html__('Activating', 'user-registration'),
				'i18n_install_activate' => esc_html__('Install & Activate', 'user-registration'),
				'i18n_installing' => esc_html__('Installing', 'user-registration'),
				'i18n_ok' => esc_html__('OK', 'user-registration'),
				'upgrade_url' => apply_filters('user_registration_upgrade_url', 'https://wpeverest.com/wordpress-plugins/user-registration/pricing/?utm_source=form-template&utm_medium=button&utm_campaign=evf-upgrade-to-pro'),
				'upgrade_button' => esc_html__('Upgrade Plan', 'user-registration'),
				'upgrade_message' => esc_html__('This template requires premium addons. Please upgrade to the Premium plan to unlock all these awesome Templates.', 'user-registration'),
				'upgrade_title' => esc_html__('is a Premium Template', 'user-registration'),
				'i18n_form_ok' => esc_html__('Continue', 'user-registration'),
				'i18n_form_placeholder' => esc_html__('Untitled Form', 'user-registration'),
				'i18n_form_title' => esc_html__('Uplift your form experience to the next level.', 'user-registration'),
			)
		);

		wp_enqueue_script('ur-form-templates');
		wp_localize_script(
			'ur-form-templates',
			'ur_templates',
			array(
				'ur_template_all' => UR_Admin_Form_Templates::get_template_data(),
				'i18n_get_started' => esc_html__('Get Started', 'user-registration'),
				'i18n_get_preview' => esc_html__('Preview', 'user-registration'),
				'i18n_pro_feature' => esc_html__('Pro', 'user-registration'),
				'template_refresh' => esc_html__('Updating Templates', 'user-registration'),
				'ur_plugin_url' => esc_url(UR()->plugin_url()),
			)
		);

		// Forms template area.
		include_once dirname(__FILE__) . '/views/html-admin-page-form-templates.php';
	}
}
