<?php

$bank_data        = ( isset( $_GET['info'] ) && ! empty( $_GET['info'] ) ) ? wp_kses_post_deep( $_GET['info'] ) : '';
$transaction_id   = ( isset( $_GET['transaction_id'] ) && ! empty( $_GET['transaction_id'] ) ) ? wp_kses_post( $_GET['transaction_id'] ) : '';
$username         = ( isset( $_GET['username'] ) && ! empty( $_GET['username'] ) ) ? wp_kses_post( $_GET['username'] ) : '';
$header            = ! empty( $attributes['header'] ) ? $attributes['header'] : sprintf(
	__( 'Thank You! Your registration was completed successfully.', 'user-registration' ),
	esc_html( $username )
);
$footer           = ! empty( $attributes['footer'] ) ? $attributes['footer'] : "";
$notice_message   = ! empty( $attributes['notice_message'] ) ? $attributes['notice_message'] : __("For paid memberships there might be a delay of few minutes for your subscription status to be updated by the payment gateways.", 'user-registration' );
$transaction_info = ! empty( $attributes['transaction_info'] ) ? $attributes['transaction_info'] : __("Please use this transaction/order id for support regarding payments if needed.", 'user-registration' );
$is_preview       = ! empty( $attributes['is_preview'] ) ? $attributes['is_preview'] : false;
$show_notice_1    = isset( $attributes['show_notice_1'] ) ? $attributes['show_notice_1'] : true;
$show_notice_2    = isset( $attributes['show_notice_2'] ) ? $attributes['show_notice_2'] : true;

?>
<!--order successful section-->
<div id="order-complete-section" class="thank-you-page-container">
	<div class="message-section">
		<div class="header-section">
			<p><?php echo __( $header, 'user-registration' ); ?></p>
		</div>
		<?php
		$is_payment_done = ( isset( $_GET['payment_type'] ) && ! empty( $_GET['payment_type'] ) && 'paid' === $_GET['payment_type'] );
		if ( $is_preview || $is_payment_done || $show_notice_1) :
			?>
			<?php
			if ( $show_notice_1 || $is_payment_done ):
				?>
				<p class="thank-you-notice info">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 18 22" fill="none">
						<g clip-path="url(#clip0_4801_13369)">
							<path d="M9 20.5C13.1421 20.5 16.5 17.1421 16.5 13C16.5 8.85786 13.1421 5.5 9 5.5C4.85786 5.5 1.5 8.85786 1.5 13C1.5 17.1421 4.85786 20.5 9 20.5Z" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M9 13V16" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M9 10H9.00875" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</g>
						<defs>
							<clipPath id="clip0_4801_13369">
								<rect width="18" height="18" fill="white" transform="translate(0 4)"/>
							</clipPath>
						</defs>
					</svg>
					<?php echo __( $notice_message, 'user-registration' ); ?>
				</p>
			<?php
			endif;
			?>
		<?php
		endif;
		?>
	</div>
	<div class="data-section <?php echo empty( $bank_data ) ? 'urm-d-none' : ''; ?>">
		<?php
		echo $bank_data;
		?>
	</div>
	<?php
	if ( ! empty( $transaction_id ) || $is_preview ) :
		?>
		<?php
		if ( $show_notice_2 ):
			?>
			<div class="thank-you-notice info">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 18 22" fill="none">
					<g clip-path="url(#clip0_4801_13369)">
						<path d="M9 20.5C13.1421 20.5 16.5 17.1421 16.5 13C16.5 8.85786 13.1421 5.5 9 5.5C4.85786 5.5 1.5 8.85786 1.5 13C1.5 17.1421 4.85786 20.5 9 20.5Z" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9 13V16" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9 10H9.00875" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</g>
					<defs>
						<clipPath id="clip0_4801_13369">
							<rect width="18" height="18" fill="white" transform="translate(0 4)"/>
						</clipPath>
					</defs>
				</svg>
				<div>
					<?php echo __( $transaction_info, 'user-registration' ); ?>
					<?php echo __( "Transaction ID : ", "user-registration" ) ?>
					<b><i><?php echo $transaction_id; ?></i></b>
				</div>
			</div>
		<?php
		endif;
		?>
	<?php
	endif;
	?>
	<div class="footer-section">
		<?php echo __( $footer, 'user-registration' ); ?>
	</div>
</div>
