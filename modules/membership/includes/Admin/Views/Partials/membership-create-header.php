<?php
/**
 * Membership Create Page Header
 *
 * @var string $return_url Return URL
 * @var array  $membership_content Membership content data
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
					<button class="ur-page-title__wrapper--steps-btn ur-page-title__wrapper--steps-btn-active" data-step="0" id="ur-basic-tab">
						<div class="ur-page-title__wrapper--steps-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#e9e9e9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3.667c6.811 0 12.334 5.521 12.334 12.333 0 6.811-5.523 12.334-12.334 12.334S3.667 22.81 3.667 16C3.667 9.188 9.189 3.667 16 3.667"/><g clip-path="url(#a)"><path stroke="#222" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15.997 10.802a.65.65 0 0 1 .36.11l.097.08 4.554 4.553a.65.65 0 0 1 .08.817l-.08.098-4.554 4.553a.65.65 0 0 1-.816.08l-.098-.08-4.554-4.554a.65.65 0 0 1-.19-.457l.014-.125a.6.6 0 0 1 .096-.234l.08-.098 4.554-4.553a.65.65 0 0 1 .457-.19"/></g><defs><clipPath id="a"><path fill="#fff" d="M10 9.5h12v13H10z"/></clipPath></defs></svg>
							<span><?php esc_html_e('Basics','user-registration');?></span>
						</div>
					</button>
					<hr class="ur-page-title__wrapper--steps-separator" />
					<button class="ur-page-title__wrapper--steps-btn" data-step="1" id="ur-access-tab">
						<div class="ur-page-title__wrapper--steps-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#e9e9e9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 29.333c7.364 0 13.334-5.97 13.334-13.333S23.364 2.667 16 2.667 2.667 8.637 2.667 16 8.637 29.333 16 29.333"/><g stroke="#222" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.569" clip-path="url(#a)"><path d="M17.199 19h-5.4M20.199 13h-5.4M19.001 20.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6M13.001 14.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6"/></g><defs><clipPath id="a"><path fill="#fff" d="M10 10h12v12H10z"/></clipPath></defs></svg>
							<span><?php esc_html_e('Access','user-registration');?></span>
						</div>
					</button>
					<hr class="ur-page-title__wrapper--steps-separator" />
					<button class="ur-page-title__wrapper--steps-btn" data-step="2" id="ur-advanced-tab">
						<div class="ur-page-title__wrapper--steps-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#e9e9e9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 29.333c7.364 0 13.334-5.97 13.334-13.333S23.364 2.667 16 2.667 2.667 8.637 2.667 16 8.637 29.333 16 29.333"/><g stroke="#222" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.569" clip-path="url(#a)"><path d="M17.199 19h-5.4M20.199 13h-5.4M19.001 20.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6M13.001 14.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6"/></g><defs><clipPath id="a"><path fill="#fff" d="M10 10h12v12H10z"/></clipPath></defs></svg>
							<span><?php esc_html_e('Advanced','user-registration');?></span>
						</div>
					</button>
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
							<div class="ur-d-flex ur-align-items-center visible" style="gap: 5px">
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
											true,
											isset( $membership_content['status'] ) && 'true' === $membership_content['status'] ? ur_string_to_bool( $membership_content['status'] ) : true,
											true
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
								<?php esc_html_e( 'Publish', 'user-registration' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

