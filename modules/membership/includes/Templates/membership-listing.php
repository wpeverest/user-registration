<?php
if ( 'block' === $type ) :
	?>
	<div class="ur-membership-list-container">

		<!--		<div class="membership-list-notice-div">-->
		<!--			<div class="membership-title">-->
		<!--				-->
		<?php
		//echo esc_html__( 'Available Memberships', 'user-registration' );
		?>
		<!--			</div>-->
		<!--			<div class="subscription-message">-->
		<!--				<p>-->
		<!--					-->
		<?php
		//echo esc_html__( 'We have the following subscriptions available for our site. Please select one to continue.' );
		?>
		<!--				</p>-->
		<!--			</div>-->
		<!--		</div>-->
		<form id="membership-old-selection-form" class="ur-membership-container layout-block column-<?php echo esc_attr( $column_number ); ?>"
				method="GET" data-layout="block">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$time = '';
				if ( 'paid' === $membership['type'] ) {
					$time = esc_html__( 'lifetime', 'user-registration' );
				}

				?>

				<div class="membership-block">
					<div class="membership-title">
						<span><?php echo esc_html( $membership['title'] ); ?></span>
					</div>
					<div class="membership-body">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
						<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
						<input type="hidden" name="urm_uuid" value="<?php echo $uuid; ?>">
						<input type="hidden" name="thank_you_page_id" value="<?php echo $thank_you_page_id; ?>">
						<?php if ( 'free' !== $membership['type'] ) { ?>
						<span
							class="membership-amount"><?php echo $symbol; ?>
							<?php
							echo esc_html( sprintf( '%.2f', $membership['amount'] ) ); if ( $time ) {
								echo ' / ' . esc_html( $time ); }
							?>
							</span>
							<?php } else { ?>
								<span
							class="membership-amount"><?php echo esc_html__( 'Free', 'user-registration' ); ?></span>
								<?php } ?>
						<button type="button"
								class="membership-signup-button" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?> ><?php echo $sign_up_text; ?>
						</button>
					</div>
					<?php if ( $show_description ) { ?>
					<div class="membership-footer">
						<span><?php echo esc_html__( 'Benefits', 'user-registration' ); ?></span>
						<div class="membership-description">
							<?php echo( $membership['description'] ); ?>
						</div>
					</div>
					<?php } ?>

				</div>
				<?php
			endforeach;
			?>
		</form>

	</div>
	<?php
elseif ( 'row' === $type ) :
	?>
	<div class="ur-membership-list-container">

		<!--		<div class="membership-list-notice-div">-->
		<!--			<div class="membership-title">-->
		<!--				-->
		<?php
		//echo esc_html__( 'Available Memberships', 'user-registration' );
		?>
		<!--			</div>-->
		<!--			<div class="subscription-message">-->
		<!--				<p>-->
		<!--					-->
		<?php
		//echo esc_html__( 'We have the following subscriptions available for our site. Please select one to continue.' );
		?>
		<!--				</p>-->
		<!--			</div>-->
		<!--		</div>-->
		<form id="membership-old-selection-form" class="ur-membership-container layout-row"
				method="GET" data-layout="row">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$time = '';

				if ( 'paid' === $membership['type'] ) {
					$time = esc_html__( 'lifetime', 'user-registration' );
				}

				?>
				<div class="membership-block">
					<div class="left-container">

					<div class="membership-title">
						<span><?php echo esc_html( $membership['title'] ); ?></span>
					</div>
					<div class="membership-body">
						<div class="membership-description">
							<?php echo( $membership['description'] ); ?>
						</div>
					</div>
					</div>
					<div class="membership-footer right-container">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
						<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
						<input type="hidden" name="urm_uuid" value="<?php echo $uuid; ?>">
						<input type="hidden" name="thank_you_page_id" value="<?php echo $thank_you_page_id; ?>">
							<?php if ( 'free' !== $membership['type'] ) { ?>
						<span
							class="membership-amount"><?php echo $symbol; ?>
								<?php
								echo esc_html( sprintf( '%.2f', $membership['amount'] ) ); if ( $time ) {
									echo ' / ' . esc_html( $time ); }
								?>
							</span>
							<?php } else { ?>
								<span
							class="membership-amount"><?php echo esc_html__( 'Free', 'user-registration' ); ?></span>
								<?php } ?>
						<button type="button"
								class="membership-signup-button" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?> ><?php echo $sign_up_text; ?></button>
					</div>
				</div>
				<?php
			endforeach;
			?>
		</form>

	</div>
	<?php
elseif ( 'list' === $type ) :
	?>
	<form id="membership-selection-form-<?php echo $uuid; ?>" class="ur-membership-container layout-list" method="GET"
			action="<?php echo $redirect_page_url; ?>">
		<input type="hidden" name="urm_uuid" value="<?php echo $uuid; ?>">
		<input type="hidden" name="thank_you" value="<?php echo $thank_you_page_id; ?>">
		<div class="ur_membership_frontend_input_container radio">
			<?php
			if ( ! empty( $memberships ) ) :
				foreach ( $memberships as $m => $membership ) :
					?>
					<div class="membership-block">
						<label class="ur_membership_input_label ur-label"
								for="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>">
							<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field"
									id="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>"
									type="radio"
									name="membership_id"
									value="<?php echo esc_attr( $membership['ID'] ); ?>"
							>
							<span
								class="ur-membership-duration"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>
							<span
								class="ur-membership-duration"> - <?php echo esc_html__( $membership['period'], 'user-registration' ); ?></span>
						</label>
					</div>

					<?php
				endforeach;
			endif;
			?>
			<div class="membership-footer">
				<button type="submit"
						class="membership-signup-button" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?>><?php echo $sign_up_text; ?></button>
			</div>
		</div>
	</form>
	<?php
endif;
?>
