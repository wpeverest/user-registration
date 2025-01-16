<div class="ur-membership-header ur-d-flex ur-mr-0 ur-p-3 ur-align-items-center" id=""
	 style="margin-left: -20px; background:white; gap: 20px; position: sticky; top: 32px; z-index: 700">
	<img style="max-width: 30px"
		 src="<?php echo UR()->plugin_url() . '/assets/images/logo.svg' ?>" alt="">

	<a href="<?php echo esc_url( admin_url( 'admin.php?page='.$this->page ) ); ?>"
	   class="<?php echo esc_attr( ( $_GET['page'] == $this->page ) ? 'row-title' : '' ) ?>"
	   style="text-decoration: none"
	>
		<?php esc_html_e( 'Payment History', 'user-registration' ); ?>
	</a>

</div>
