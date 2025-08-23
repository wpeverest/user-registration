<?php

/**
 * Admin View: Page - Status
 *
 * @package UserRegistration
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$current_tab = ! empty($_REQUEST['tab']) ? sanitize_title(wp_unslash($_REQUEST['tab'])) : 'logs'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$page_tabs   = array(
	'logs'        => __('Logs', 'user-registration'),
	'system_info' => __('System Info', 'user-registration'),
	'setup_wizard' => __('Setup Wizard', 'user-registration'),
);

/**
 * Filter to add admin status tabs.
 *
 * @param array $page_tabs Tabs to be added.
 */
$page_tabs = apply_filters('user_registration_admin_status_tabs', $page_tabs);
?>
<hr class="wp-header-end">
<?php
echo user_registration_plugin_main_header();

switch ($current_tab) {
	case 'logs':
		UR_Admin_Status::status_logs();
		break;
	case 'system_info':
		UR_Admin_Status::system_info();
		break;
	case 'setup_wizard':
		echo '<script type="text/javascript">' .
			'window.location.href ="' . admin_url('admin.php?page=user-registration-welcome&tab=setup-wizard') .
			'"</script>';
		break;
	default:
		break;
}

?>
</div>
