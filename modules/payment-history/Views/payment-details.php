<?php
ob_start();
?>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Transaction ID', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php
		$transaction_id = esc_html__( 'N/A', 'user-registration' );
		if ( isset( $order_detail['order_id'] ) ) {
			$transaction_id = $order_detail['transaction_id'] ? esc_html( $order_detail['transaction_id'] ) : absint( $order_detail['order_id'] );
		} else {
			$transaction_id = $order_detail['transaction_id'] ? esc_html( $order_detail['transaction_id'] ) : $transaction_id;
		}
		echo $transaction_id;
		?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Full Name', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo esc_html( ucwords( str_replace( '-', ' ', $order_detail['user_nicename'] ) ) ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'User Name', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo esc_html( $order_detail['display_name'] ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Payer Email', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo esc_html( $order_detail['user_email'] ); ?>

	</div>
</div>
<?php
if ( isset( $order_detail['order_id'] ) ) :
	?>
	<div class="payment-detail-box">
		<div class="payment-detail-label">
			<?php echo esc_html__( 'Membership', 'user-registration' ); ?>
		</div>
		<div class="payment-detail-data">
			<?php echo esc_html( $order_detail['post_title'] ); ?>
		</div>
	</div>
	<div class="payment-detail-box">
		<div class="payment-detail-label">
			<?php echo esc_html__( 'Membership Type', 'user-registration' ); ?>
		</div>
		<div class="payment-detail-data">
			<?php
			$post_content = json_decode( wp_unslash( $order_detail['post_content'] ), true );
			echo esc_html( ucfirst( $post_content['type'] ) );
			?>
		</div>
	</div>
<?php
endif;
?>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Payment Gateway', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo esc_html( ucfirst( $order_detail['payment_method'] ) ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Payment Date', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order_detail['created_at'] ) ) ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Order Note', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<i><?php echo esc_html( ucfirst( $order_detail['notes'] ?? '' ) ); ?></i>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Transaction Status', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php
		$status = $order_detail['status'];
		?>
		<span
			class="payment-status-btn <?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span>
		<?php
		if ( 'pending' === $status && 'bank' === $order_detail['payment_method'] ) :
			?>
			<a href="javascript:void(0)" class="approve-payment"
			   data-order-id="<?php echo absint( $order_detail['order_id'] ); ?>"><?php echo __( 'Approve', 'user-registration' ); ?></a>
		<?php
		endif;
		?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Product Amount', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php
		$currency   = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies = ur_payment_integration_get_currencies();
		$symbol     = $currencies[ $currency ]['symbol'];
		$amount     = $order_detail['billing_amount'] ?? $order_detail['total_amount'];
		echo $symbol . absint( $amount )
		?>
	</div>
</div>


<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Trial Order', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<span
			class="payment-status-btn <?php echo isset( $order_detail['trial_status'] ) && 'on' === $order_detail['trial_status'] ? esc_attr( 'completed' ) : esc_attr( 'pending' ); ?>"><?php echo ( isset( $order_detail['trial_status'] ) && 'on' === $order_detail['trial_status'] ) ? esc_html( esc_html__( '✓', 'user-registration' ) ) : esc_html( esc_html__( 'x', 'user-registration' ) ); ?></span>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Trial Start Date', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo ( isset( $order_detail['trial_start_date'] ) && ! empty( $order_detail['trial_start_date'] ) ) ? date_i18n( get_option( 'date_format' ), strtotime( $order_detail['trial_start_date'] ) ) : esc_html__( 'N/A', 'user-registration' ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Trial End Date', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo ( isset( $order_detail['trial_end_date'] ) && ! empty( $order_detail['trial_end_date'] ) ) ? date_i18n( get_option( 'date_format' ), strtotime( $order_detail['trial_end_date'] ) ) : esc_html__( 'N/A', 'user-registration' ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Coupon', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php echo isset( $order_detail['coupon'] ) && $order_detail['coupon'] ? esc_html( $order_detail['coupon'] ) : esc_html__( 'N/A', 'user-registration' ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Coupon Discount', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php
		$currency             = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies           = ur_payment_integration_get_currencies();
		$symbol               = $currencies[ $currency ]['symbol'];
		$coupon_discount      = $order_detail['coupon_discount'] ?? '';
		$coupon_discount_type = $order_detail['coupon_discount_type'] ?? '';
		$discount             = ( isset( $order_detail['coupon_discount_type'] ) && $order_detail['coupon_discount_type'] == 'percent' ) ? $coupon_discount . '%' : $symbol . $coupon_discount;

		?>
		<?php echo $coupon_discount_type ? esc_html( $discount ) : esc_html__( 'N/A', 'user-registration' ); ?>
	</div>
</div>
<div class="payment-detail-box">
	<div class="payment-detail-label">
		<?php echo esc_html__( 'Total', 'user-registration' ); ?>
	</div>
	<div class="payment-detail-data">
		<?php
		$total  = $order_detail['total_amount'];
		$amount = ( $order_detail['product_amount'] ) ?? $order_detail['total_amount'];

		if ( 'bank' !== $order_detail['payment_method'] && isset( $post_content ) && ( 'paid' === $post_content['type'] || ( 'subscription' === $post_content['type'] && 'off' === $order_detail['trial_status'] ) ) ) {
			$discount_amount = ( isset( $order_detail['coupon_discount_type'] ) && $order_detail['coupon_discount_type'] === 'fixed' ) ? ( ! empty( $order_detail['coupon_discount'] ) ? $order_detail['coupon_discount'] : 0 ) : ( ! empty( $order_detail['coupon_discount'] ) ? ($amount * $order_detail['coupon_discount'])/100 : 0 );
			$total           = $amount - $discount_amount;
		}

		echo $symbol . number_format( $total, 2 )
		?>
	</div>
</div>
