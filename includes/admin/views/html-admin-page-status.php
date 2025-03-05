<?php
/**
 * Admin View: Page - Status
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( wp_unslash( $_REQUEST['tab'] ) ) : 'logs'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$page_tabs   = array(
	'logs'        => __( 'Logs', 'user-registration' ),
	'system_info' => __( 'System Info', 'user-registration' ),
);

/**
 * Filter to add admin status tabs.
 *
 * @param array $page_tabs Tabs to be added.
 */
$page_tabs = apply_filters( 'user_registration_admin_status_tabs', $page_tabs );
?>
<div class="wrap user-registration">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php
		foreach ( $page_tabs as $name => $label ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-status&tab=' . $name ) ) . '" class="nav-tab ';
			if ( $current_tab === $name ) {
				echo 'nav-tab-active';
			}
			echo '">' . esc_html( $label ) . '</a>';
		}
		?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $page_tabs[ $current_tab ] ); ?></h1>
	<?php
	error_log( print_r( $current_tab, true ) );
	switch ( $current_tab ) {
		case 'logs':
			UR_Admin_Status::status_logs();
			break;
		case 'system_info':
			UR_Admin_Status::system_info();
			break;
		default:
			break;
	}

	?>
</div>
