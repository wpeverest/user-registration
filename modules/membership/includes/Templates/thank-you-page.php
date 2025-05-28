<?php

$bank_data        = ( isset( $_GET['info'] ) && ! empty( $_GET['info'] ) ) ? wp_kses_post_deep( $_GET['info'] ) : '';
$transaction_id   = ( isset( $_GET['transaction_id'] ) && ! empty( $_GET['transaction_id'] ) ) ? wp_kses_post( $_GET['transaction_id'] ) : '';
$username         = ( isset( $_GET['username'] ) && ! empty( $_GET['username'] ) ) ? wp_kses_post( $_GET['username'] ) : '';
$header            = ! empty( $attributes['header'] ) ? $attributes['header'] : sprintf(
	__( 'Hello <b><i>%s</i></b>, Your registration was completed successfully.', 'user-registration' ),
	esc_html( $username )
);
$footer           = ! empty( $attributes['footer'] ) ? $attributes['footer'] : "";
$notice_message   = ! empty( $attributes['notice_message'] ) ? $attributes['notice_message'] : "For paid memberships there might be a delay of few minutes for your subscription status to be updated by the payment gateways.";
$transaction_info = ! empty( $attributes['transaction_info'] ) ? $attributes['transaction_info'] : "Please use this transaction/order id for support regarding payments if needed.";
$is_preview       = ! empty( $attributes['is_preview'] ) ? $attributes['is_preview'] : false;
$show_notice_1    = isset( $attributes['show_notice_1'] ) ? $attributes['show_notice_1'] : true;
$show_notice_2    = isset( $attributes['show_notice_2'] ) ? $attributes['show_notice_2'] : true;

?>
<!--order successful section-->
<div id="order-complete-section" class="thank-you-page-container">
	<div class="message-section">
		<p><?php echo __( $header, 'user-registration' ); ?></p>
		<?php
		$is_payment_done = ( isset( $_GET['payment_type'] ) && ! empty( $_GET['payment_type'] ) && 'paid' === $_GET['payment_type'] );
		if ( $is_preview || $is_payment_done || $show_notice_1) :
			?>
			<?php
			if ( $show_notice_1 || $is_payment_done ):
				?>
				<p class="thank-you-notice warning"><?php echo __( $notice_message, 'user-registration' ); ?></p>
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
				<p>
					<?php echo __( $transaction_info, 'user-registration' ); ?>
				</p>

					<p>
						<?php echo __( "Transaction ID : ", "user-registration" ) ?>
						<b><i><?php echo $transaction_id; ?></i></b>
					</p>

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
