<?php
/**
 * Admin View: Page - Status
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( wp_unslash( $_REQUEST['tab'] ) ) : 'logs';
$page_tabs   = array(
	'logs' => __( 'Logs', 'user-registration' ),
);
$page_tabs   = apply_filters( 'user_registration_admin_status_tabs', $page_tabs );
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
	UR_Admin_Status::status_logs();
	?>
</div>
