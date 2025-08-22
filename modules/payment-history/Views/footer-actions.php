<div class="submit ur-d-flex ur-justify-content-end ur-p-3" style="gap: 10px">
	<button class="button-secondary" type="button">
		<a style="text-decoration:none;color:#0b0b0b;" href="<?php echo $return_url; ?>">
			<?php echo esc_html__( 'Cancel', 'user-registration' ); ?>
		</a>
	</button>
	<button class="button-primary <?php echo esc_attr( $save_btn_class ); ?>">
		<?php echo esc_html__( $create_btn_text ); ?>
	</button>
</div>
