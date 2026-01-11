<?php
/**
 * Membership Create Page Header
 *
 * @var string $return_url Return URL
 * @var array  $membership_content Membership content data
 * @var object $this Membership class instance
 * @var array  $membership_tabs Array of tab configurations
 */
?>
<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( $return_url ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items ur-page-title__wrapper--steps">
					<?php
					// Get tabs configuration

					$tab_count = count( $membership_tabs );

					foreach ( $membership_tabs as $index => $tab ) :
						$is_first     = ( $index === 0 );
						$is_last      = ( $index === $tab_count - 1 );
						$active_class = $is_first ? 'ur-page-title__wrapper--steps-btn-active' : '';
						?>
						<button class="ur-page-title__wrapper--steps-btn <?php echo esc_attr( $active_class ); ?>"
								data-step="<?php echo esc_attr( $tab['step'] ); ?>"
								id="<?php echo esc_attr( $tab['id'] ); ?>">
							<div class="ur-page-title__wrapper--steps-wrapper">
								<div class="urm-membership--stepper-icon">
								<?php echo $tab['icon_svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<span><?php echo esc_html( $tab['label'] ); ?></span>
							</div>
						</button>
						<?php if ( ! $is_last ) : ?>
							<hr class="ur-page-title__wrapper--steps-separator" />
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="ur-page-title__wrapper--right-menu">
				<div class="ur-page-title__wrapper--right-menu__item">
					<div class="ur-page-title__wrapper--actions">
						<div class="ur-page-title__wrapper--actions-status">
							<p>Status</p>
							<span class="separator">|</span>
							<div class="visible ur-d-flex ur-align-items-center" style="gap: 5px">
								<div class="ur-toggle-section">
									<span class="user-registration-toggle-form">
										<input
										data-key-name="Membership Status"
										id="ur-membership-status"
										class="ur-membership-change__status user-registration-switch__control hide-show-check enabled"
										type="checkbox"
										value="1"
										<?php
										checked(
											! isset( $membership_content['status'] ) || '1' == $membership_content['status']
										);
										?>
										>
										<span class="slider round"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="ur-page-title__wrapper--actions-publish">
							<button class="button-primary ur-membership-save-btn" type="submit">

								<?php ! empty( $membership_id ) ? esc_html_e( 'Save', 'user-registration' ) : esc_html_e( 'Publish', 'user-registration' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

