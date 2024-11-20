<div class="user-registration-email-body" style="padding: 100px 0; background-color: #ebebeb;">
	<div class="user-registration-email"
		 style="width: 50%; margin: 0 auto; background: #ffffff; padding: 30px 30px 26px; border: 0.4px solid #d3d3d3; border-radius: 11px; font-family: 'Segoe UI', sans-serif; ">
		<p><?php echo wp_kses_post( $message ); ?></p>
		<p><?php echo wp_kses_post( $extra_message ); ?></p>
		<p><?php echo wp_kses_post( $final_greeting ); ?></p>

	</div>
</div>
