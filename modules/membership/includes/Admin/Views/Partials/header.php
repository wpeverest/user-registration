<div class="ur-membership-header ur-d-flex ur-mr-0 ur-pl-3 ur-pr-3 ur-align-items-center ur-justify-content-between">
	<div class="membership-menu-left ur-d-flex ur-p-3 ur-mr-0 ur-align-items-center">
		<img style="max-width: 30px"
			 src="<?php echo UR()->plugin_url() . '/assets/images/logo.svg'; ?>" alt="">

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-membership' ) ); ?>"
		   class="<?php echo esc_attr( ( $_GET['page'] == 'user-registration-membership' ) ? 'row-title' : '' ); ?>"
		>
			<?php esc_html_e( 'Memberships', 'user-registration-membership' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-members' ) ); ?>"
		   class="<?php echo esc_attr( ( $_GET['page'] == 'user-registration-members' ) ? 'row-title' : '' ); ?>"
		>
			<?php esc_html_e( 'Members', 'user-registration-membership' ); ?>
		</a>
	</div>
	<div class="membership-menu-right ur-d-flex ur-p-3 ur-mr-0 ur-align-items-center">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=membership' ) ); ?>"
		   class="chakra-link css-e6i1ju">
			<span><?php echo __( 'Settings', 'user-registration-membership' ); ?></span>
		</a>
	</div>

</div>
