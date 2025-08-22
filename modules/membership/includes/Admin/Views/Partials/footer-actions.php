<div class="submit ur-d-flex ur-justify-content-end ur-p-3" style="gap: 10px">
	<button class="button button-secondary" type="button">
		<a href="<?php echo $return_url; ?>">
			<?php echo esc_html__( 'Cancel', 'user-registration' ); ?>
		</a>
	</button>
	<button class="button button-primary <?php echo esc_attr( $save_btn_class ); ?>">
		<?php echo esc_html__( $create_btn_text ); ?>
	</button>
</div>
