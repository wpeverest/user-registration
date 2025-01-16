<div class="ur-membership-list-container">

	<div class="membership-list-notice-div">
		<div class="membership-title">
			<?php echo esc_html__( 'Available Memberships', 'user-registration' ); ?>
		</div>
		<div class="subscription-message">
			<p>
				<?php echo esc_html__( 'We have the following subscriptions available for our site. Please select one to continue.' ); ?>
			</p>
		</div>
	</div>
	<form id="membership-selection-form" class="ur-membership-container"
		  method="GET">
		<?php foreach ( $memberships as $k => $membership ) : ?>

			<div class="membership-block">
				<div class="membership-title">
					<span><?php echo esc_html( $membership['post_title'] ); ?></span>
				</div>
				<div class="membership-body">
					<div class="membership-description">
						<?php echo esc_html( $membership['post_content']['description'] ); ?>
					</div>
				</div>
				<div class="membership-footer">
					<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
					<span
						class="membership-amount"><?php echo $symbol ?> <?php echo esc_html( sprintf( '%.2f', $membership['meta_value']['amount'] ) ); ?></span>
					<button type="button"
							class="membership-signup-button" <?php echo (empty($registration_page_id) ? 'disabled' : '') ?>><?php echo esc_html__( 'Sign Up', 'user-registration' ); ?></button>
				</div>
			</div>
			<?php
		endforeach;
		?>
	</form>

</div>

