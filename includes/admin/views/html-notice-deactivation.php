<?php
/**
 * Deactivation admin notice
 *
 * Link to WPEverst contact form page.
 *
 * @author      WPEverest
 * @category    Admin
 * @package     User Registration/Admin
 * @since       1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$reason_deactivation_url = 'https://wpeverest.com/deactivation/user-registration/';
global $status, $page, $s;

$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . UR_PLUGIN_BASENAME . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . UR_PLUGIN_BASENAME );
?>
<tr class="plugin-update-tr active updated" data-slug="user-registration" data-plugin="user-registration/user-registration.php">
	<td colspan ="3" class="plugin-update colspanchange">
		<div class="notice inline notice-alt notice-warning">
			<p><?php printf( __( 'Before we deactivate User Registration, would you care to <a href="%1$s" target="_blank">let us know why</a> so we can improve it for you? <a href="%2$s">No, deactivate now</a>.', 'user-registration' ), $reason_deactivation_url, $deactivate_url ); ?></p>
		</div>
	</td>
</tr>
