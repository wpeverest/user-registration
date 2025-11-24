<div class="wrap user-registration-content-restriction">
	<header class="user-registration-header ur-border-bottom ur-d-flex ur-align-items-center">
		<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( empty( $_SERVER['HTTP_REFERER'] ) ? '#' : $_SERVER['HTTP_REFERER'] ); ?>">
			<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
		</a>
		<div class="user-registration-editable-title">
			<input class="user-registration-editable-title__input urcr-content-access-rule-title-input" type="text" value="<?php isset( $_GET['name'] ) ? esc_html_e( $_GET['name'], 'user-registration' ) : esc_html_e( 'Untitled', 'user-registration' ); ?>" placeholder="<?php esc_html_e( ' Content Rule Title Here...', 'user-registration' ); ?>"/>
			<span class="user-registration-editable-title__icon dashicons dashicons-edit"></span>
		</div>
		<div class="user-registration-switch ur-ml-auto">
			<input id="urcr-enable-access-rule" type="checkbox" class="user-registration-switch__control hide-show-check enabled" checked="checked">
			<label class="urcr-enable-access-rule-label" for="urcr-enable-access-rule"><?php esc_html_e( 'Enabled', 'user-registration' ); ?></label>
		</div>
	</header>
	<form class="urcr-content-access-rule-creator-form" method="post">
		<!-- URCR content access rules creator tabs wrapper -->
		<nav class="nav-tab-wrapper urcr-nav-tab-wrapper">
			<a href="#" class="urcr-tab nav-tab nav-tab-active" data-tab-content-selector="#urcr-tab-content-conditions-and-logic"><?php esc_html_e( 'Conditions & Logics', 'user-registration' ); ?></a>
			<a href="#" class="urcr-tab nav-tab" data-tab-content-selector="#urcr-tab-content-target-contents"><?php esc_html_e( 'Target Contents', 'user-registration' ); ?></a>
			<a href="#" class="urcr-tab nav-tab" data-tab-content-selector="#urcr-action-tab-content"><?php esc_html_e( 'Action', 'user-registration' ); ?></a>
		</nav>
		<!-- URCR content access rules creator tab contents wrapper -->
		<div class="urcr-tab-contents-wrapper">
			<!-- URCR CARC tab content -->
			<div class="urcr-tab-content urcr-tab-content-active" id="urcr-tab-content-conditions-and-logic">
				<?php require __DIR__ . '/partials/conditions-and-logic-tab-content.php'; ?>
			</div>
			<!-- URCR Target Contents tab content -->
			<div class="urcr-tab-content" id="urcr-tab-content-target-contents" hidden>
				<?php require __DIR__ . '/partials/target-contents-tab-content.php'; ?>
			</div>
			<!-- URCR Action tab content -->
			<div class="urcr-tab-content" id="urcr-action-tab-content" hidden>
				<?php require __DIR__ . '/partials/action-tab-content.php'; ?>
			</div>
		</div>
		<p class="submit urcr-arc-buttons-container">
			<!-- URCR content access rules creator form's submit button -->
			<?php if ( ! isset( $GLOBALS['urcr_hide_save_button'] ) || ( isset( $GLOBALS['urcr_hide_save_button'] ) && true !== $GLOBALS['urcr_hide_save_button'] ) ) : ?>
				<button class="button button-primary urcr-save-rule" disabled='true'>
					<?php echo esc_html( apply_filters( 'urcr_create_rule_label', esc_html__( 'Create Rule', 'user-registration' ) ) ); ?>
				</button>
			<?php endif; ?>

			<!-- URCR content access rules creator form's submit draft button -->
			<?php if ( ! isset( $GLOBALS['urcr_hide_save_draft_button'] ) || ( isset( $GLOBALS['urcr_hide_save_draft_button'] ) && true !== $GLOBALS['urcr_hide_save_draft_button'] ) ) : ?>
				<button class="button button-secondary urcr-save-rule-as-draft" disabled='true'>
					<?php echo esc_html( apply_filters( 'urcr_save_rule_as_draft_label', esc_html__( 'Save as Draft', 'user-registration' ) ) ); ?>
				</button>
			<?php endif; ?>
		</p>
	</form>
</div>

