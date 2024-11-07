<div class="submit ur-d-flex ur-justify-content-end ur-p-3" style="gap: 10px">
	<button class="button-secondary">
		<a href="<?php echo esc_attr( empty( $_SERVER['HTTP_REFERER'] ) ? '#' : $_SERVER['HTTP_REFERER'] ); ?>">
			<?php echo esc_html__( 'Cancel', 'user-registration-membership' ); ?>
		</a>
	</button>
	<button class="button-primary <?php echo esc_attr( $save_btn_class ); ?>">
		<?php echo esc_html__( $create_btn_text ); ?>
	</button>
</div>
