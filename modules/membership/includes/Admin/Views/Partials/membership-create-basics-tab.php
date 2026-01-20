<?php
/**
 * Membership Create - Basics Tab Content
 *
 * @var object $membership Membership post object
 * @var array $membership_content Membership content data
 * @var array $membership_details Membership details data
 */
?>
<div class="user-registration-card__body">
	<div id="ur-membership-main-fields">
		<!-- Membership Name -->
		<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
			<div class="ur-label" style="width: 30%">
				<label for="ur-input-type-membership-name">
					<?php esc_html_e( 'Name', 'user-registration' ); ?>
					<span style="color:red">*</span> :
				</label>
			</div>
			<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
				<div class="ur-field" data-field-key="membership_name">
					<input type="text" data-key-name="Membership Name"
						   id="ur-input-type-membership-name" name="ur_membership_name"
						   style="width: 100%"
						   autocomplete="off"
						   value="<?php echo isset( $membership->post_title ) && ! empty( $membership->post_title ) ? esc_html( $membership->post_title ) : ''; ?>"
						   required>
				</div>
			</div>
		</div>

		<!-- Membership Description -->
		<div class="ur-membership-input-container ur-input-type-textarea ur-d-flex ur-p-1 ur-mt-3" style="gap:20px;">
			<div class="ur-label" style="width: 30%">
				<label
					for="ur-input-type-membership-description"><?php esc_html_e( 'Description :', 'user-registration' ); ?></label>
			</div>
			<div class="ur-field" data-field-key="textarea" style="width: 100%">
				<?php
				wp_editor(
					! empty( $membership_content['description'] ) ? $membership_content['description'] : ( ! empty( $membership_details['description'] ) ? $membership_details['description'] : '' ),
					'ur-input-type-membership-description',
					array(
						'textarea_name'                    => 'Membership Description',
						'textarea_rows'                    => 10,
						'media_buttons'                    => false,
						'quicktags'                        => false,
						'teeny'                            => true,
						'show-ur-registration-form-button' => false,
						'show-smart-tags-button'           => true,
						'tinymce'                          => array(
							'theme'       => 'modern',
							'skin'        => 'lightgray',
							'toolbar1'    => 'undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
							'content_css' => 'default',
							'branding'    => false,
							'resize'      => true,
							'statusbar'   => false,
							'menubar'     => false,
							'menu'        => false,
							'elementpath' => true,
							'plugins'     => 'wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists',
						),
					)
				);
				?>
			</div>
		</div>

		<!-- Membership Type -->
		<div class="ur-membership-selection-container ur-d-flex ur-p-1" style="gap:20px;">
			<div class="ur-label" style="width: 30%">
				<label for="ur-membership-free-type"><?php esc_html_e( 'Type :', 'user-registration' ); ?></label>
			</div>
			<div class="ur-input-type-select ur-admin-template" style="width: 100%">
				<div class="ur-field ur-d-flex" data-field-key="radio">
					<!-- Free Type -->
					<label class="ur-membership-types" for="ur-membership-free-type">
						<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
							<input data-key-name="Type" id="ur-membership-free-type"
								   type="radio" value="free"
								   name="ur_membership_type"
								   style="margin: 0"
								   checked
								<?php echo isset( $membership_details['type'] ) && 'free' === $membership_details['type'] ? 'checked' : ''; ?>
								   required>
							<label class="ur-p-2" for="ur-membership-free-type">
								<b class="user-registration-image-label"><?php esc_html_e( 'Free', 'user-registration' ); ?></b>
							</label>
						</div>
					</label>
					<!-- Paid Type -->
					<label class="ur-membership-types" for="ur-membership-paid-type">
						<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
							<input data-key-name="Type" id="ur-membership-paid-type" type="radio" style="margin: 0"
								   value="paid" name="ur_membership_type" class="ur_membership_paid_type"
								<?php echo isset( $membership_details['type'] ) && 'paid' === $membership_details['type'] ? 'checked' : ''; ?>>
							<label class="ur-p-2" for="ur-membership-paid-type">
								<b class="user-registration-image-label"><?php esc_html_e( 'One-Time Payment', 'user-registration' ); ?></b>
							</label>
						</div>
					</label>
					<!-- Subscription Type -->
					<label class="ur-membership-types <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?>"
						   for="ur-membership-subscription-type">
						<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
							<input data-key-name="Type" id="ur-membership-subscription-type" style="margin: 0"
								   type="radio" value="subscription" name="ur_membership_type"
								   class="ur_membership_paid_type"
								<?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'subscription' ? 'checked' : ''; ?>
								<?php echo ! UR_PRO_ACTIVE ? 'disabled' : ''; ?>>
							<label class="ur-p-2" for="ur-membership-subscription-type">
								<b class="user-registration-image-label"><?php esc_html_e( 'Subscription Based', 'user-registration' ); ?></b>
							</label>
						</div>
					</label>
				</div>
			</div>
		</div>

		<!-- Paid Plan Fields -->
		<div id="paid-plan-container"
			 class="<?php echo isset( $membership_details['type'] ) && in_array( $membership_details['type'], array(
				 'paid',
				 'subscription'
			 ), true ) ? '' : 'ur-d-none'; ?>">
			<!-- Membership Amount and Duration -->
			<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label for="ur-membership-amount">
						<?php esc_html_e( 'Price', 'user-registration' ); ?>
						:
					</label>
				</div>
				<div class="ur-d-flex" style="gap:16px;width:100%;">
					<div class="ur-field field-amount" data-field-key="membership_amount">
						<?php
						$currency   = get_option( 'user_registration_payment_currency', 'USD' );
						$currencies = ur_payment_integration_get_currencies();
						?>
						<input data-key-name="Amount" type="number" id="ur-membership-amount"
							   value="<?php echo esc_attr( $membership_details['amount'] ?? "" ); ?>"
							   name="ur_membership_amount" style="width: 80%" min="0" required>
						<span class="ur-currency"><?php echo esc_html( $currency ); ?></span>
					</div>
				</div>
			</div>

			<!-- Membership Duration -->
			<div
				class="ur-membership-selection-container ur-p-1 ur-mt-3 ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? 'ur-d-flex' : 'ur-d-none'; ?>"
				id="ur-membership-duration-container" style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label for="ur-membership-duration">
						<?php esc_html_e( 'Billing Cycle', 'user-registration' ); ?>
						:
					</label>
				</div>
				<div class="ur-field ur-d-flex ur-align-items-center" data-field-key="membership_duration"
					 style="gap: 20px;">
					<input data-key-name="Duration Value"
						   value="<?php echo isset( $membership_details['subscription'] ) ? esc_attr( $membership_details['subscription']['value'] ) : ""; ?>"
						   class="" type="number" name="ur_membership[duration]_value"
						   autocomplete="off" id="ur-membership-duration-value" min="1">
				</div>
				<select id="ur-membership-duration" data-key-name="Duration"
						class="ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? '' : 'ur-d-none'; ?>"
						name="ur_membership[duration]_period" style="width: 15%">
					<option
						value="day" <?php echo isset( $membership_details['subscription'] ) && 'day' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>>
						Day(s)
					</option>
					<option
						value="week" <?php echo isset( $membership_details['subscription'] ) && 'week' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>>
						Week(s)
					</option>
					<option
						value="month" <?php echo isset( $membership_details['subscription'] ) && 'month' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>>
						Month(s)
					</option>
					<option
						value="year" <?php echo isset( $membership_details['subscription'] ) && 'year' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>>
						Year(s)
					</option>
				</select>
			</div>

			<!-- Payment Settings Notice -->
			<div id="ur-membership-payment-settings-notice"
				 class="<?php echo isset( $membership_details['type'] ) && in_array( $membership_details['type'], array(
					 'paid',
					 'subscription'
				 ), true ) && empty( urm_get_all_active_payment_gateways( $membership_details['type'] ) ) ? '' : 'ur-d-none'; ?>"
				 data-paid-configured="<?php echo empty( urm_get_all_active_payment_gateways( 'paid' ) ) ? '0' : '1'; ?>"
				 data-subscription-configured="<?php echo empty( urm_get_all_active_payment_gateways( 'subscription' ) ) ? '0' : '1'; ?>">
				<p>
					<?php esc_html_e( 'The payment setup is not configured yet. Please configure payment settings before proceeding.', 'user-registration' ) ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=payment' ) ) ?>"
					   target="_blank"><?php esc_html_e( 'Configure', 'user-registration' ); ?></a>
				</p>
			</div>
		</div>

		<?php
		$is_new_installation = ur_string_to_bool( get_option( 'urm_is_new_installation', '' ) );
		if ( ! $is_new_installation ):
			require __DIR__ . '/membership-admin-payments.php';
		endif;
		?>

		<?php
			do_action( 'ur_membership_team_membership', $membership, $membership_details );
		?>
	</div>
</div>

