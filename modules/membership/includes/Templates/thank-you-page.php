<?php

$bank_data      = ( isset( $_GET['info'] ) && ! empty( $_GET['info'] ) ) ? wp_kses_post_deep( $_GET['info'] ) : '';
$transaction_id = ( isset( $_GET['transaction_id'] ) && ! empty( $_GET['transaction_id'] ) ) ? wp_kses_post( $_GET['transaction_id'] ) : '';
$username       = ( isset( $_GET['username'] ) && ! empty( $_GET['username'] ) ) ? wp_kses_post( $_GET['username'] ) : '';
?>
<!--order successful section-->
<div id="order-complete-section" class="thank-you-page-container">
	<div class="message-section">
		<p><?php echo __( 'Thank You.', 'user-registration' ); ?></p>
		<p><?php echo __( "Hello <b><i>$username</i></b>, Your registration was completed successfully.", 'user-registration' ); ?></p>
		<?php
		if ( isset( $_GET['payment_type'] ) && ! empty( $_GET['payment_type'] ) && 'paid' === $_GET['payment_type'] ) :
			?>
			<p class="thank-you-notice warning"><?php echo __( 'For paid memberships there might be a delay of few minutes for your subscription status to be updated by the payment gateways.', 'user-registration' ); ?></p>
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
	if ( ! empty( $transaction_id ) ) :
		?>
		<p><?php echo __( 'Please use this transaction/order id for support regarding payments if needed.', 'user-registration' ); ?></p>
		<p><?php echo __("Transaction ID : ", "user-registration") ?><b><i><?php echo $transaction_id; ?></i></b></p>
		<?php
	endif;
	?>
</div>
