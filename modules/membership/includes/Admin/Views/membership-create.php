<?php
/**
 * Membership Create/Edit Page Template
 *
 * @var object $membership Membership post object
 * @var array  $membership_details Membership details data
 * @var array  $roles Available roles
 * @var array  $memberships Available memberships
 * @var object $this Membership class instance
 * @var array  $membership_rule_data Membership rule data
 * @var array  $membership_condition_options Condition options
 * @var array  $membership_localized_data Localized data
 */

// Initialize variables
$return_url = admin_url( 'admin.php?page=user-registration-membership' );
$is_editing = ! empty( $_GET['post_id'] );
if ( isset( $membership->post_content ) && ! empty( $membership->post_content ) ) {
	$membership_content = json_decode( wp_unslash( $membership->post_content ), true );
}

// Get tabs configuration
$membership_tabs = $this->get_membership_create_tabs();

// Include header partial
require __DIR__ . '/Partials/membership-create-header.php';
?>
<div class="ur-membership">
	<div class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<form id="ur-membership-create-form" method="post">
			<?php
			// Include tab partials based on configuration
			foreach ( $membership_tabs as $index => $tab ) {
				$partial_path = __DIR__ . '/Partials/' . $tab['partial'];
				if ( file_exists( $partial_path ) ) {
					// Set active class for first tab
					$is_active    = ( $index === 0 );
					$active_class = $is_active ? 'user-registration-card--form-step-active' : '';

					echo '<div class="user-registration-card user-registration-card--form-step ' . esc_attr( $active_class ) . '">';
					include $partial_path;
					echo '</div>';
				}
			}
			?>
		</form>
	</div>
</div>
