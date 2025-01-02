<div class="ur-membership-header ur-d-flex ur-mr-0 ur-pl-3 ur-pr-3 ur-align-items-center ur-justify-content-between">
	<div class="membership-menu-left ur-d-flex ur-p-3 ur-mr-0 ur-align-items-center">
		<img style="max-width: 30px"
			 src="<?php echo UR()->plugin_url() . '/assets/images/logo.svg'; ?>" alt="">
		<?php
		// Render menu
		foreach ( $menu_items as $item ) {
			$class = $item['active'] ? 'row-title' : '';
			printf(
				'<a href="%s" class="%s">%s</a>',
				esc_url( $item['url'] ),
				esc_attr( $class ),
				esc_html( $item['label'] )
			);
		}
		?>
	</div>
	<div class="membership-menu-right ur-d-flex ur-p-3 ur-mr-0 ur-align-items-center">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=membership' ) ); ?>"
		   class="chakra-link css-e6i1ju">
			<span><?php esc_html_e( 'Settings', 'user-registration' ); ?></span>
		</a>
	</div>
</div>
