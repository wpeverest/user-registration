<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/membership.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


?>

<div class="user-registration-membership-content"
	 style="">
	<div class="membership-row">
		<div class="membership-label">
			<span style="font-weight: 500">
			<?php echo esc_html__( 'Membership Title', 'user-registration' ) ?>
				</span>
		</div>
		<div class="membership-data">
			<span>
			<?php
			echo isset( $membership['post_title'] ) && ! empty( $membership['post_title'] ) ? esc_html( $membership['post_title'] ) : __('N/A', 'user-registration') ?>
		</span>
		</div>
	</div>
	<div class="membership-row">
		<div class="membership-label">
			<span style="font-weight: 500">
			<?php echo esc_html__( 'Membership Type', 'user-registration' ) ?>
				</span>
		</div>
		<div class="membership-data">
			<span id="ur-membership-type">
			<?php
			echo isset( $membership['post_content'] ) && ! empty( $membership['post_content'] ) ? esc_html( ucfirst( wp_unslash( $membership['post_content']['type'] ) ) ) : __('N/A', 'user-registration') ?>
		</span>
		</div>
	</div>
	<div class="membership-row">
		<div class="membership-label">
			<span style="font-weight: 500">
			<?php echo esc_html__( 'Membership Status', 'user-registration' ) ?>
				</span>
		</div>
		<div class="membership-data">

			<?php
			$status = 'inactive';
			if ( isset( $membership['status'] ) ) {
				$status = ( '' != $membership['status'] ) ? $membership['status'] : $status;
				if ( 'inactive' !== $status && 'free' !== $membership['post_content']['type'] && 'paid' !== $membership['post_content']['type']) {
					$expiry_date = new DateTime( $membership['expiry_date'] );
					if ( date( 'Y-m-d' ) > $expiry_date->format( 'Y-m-d' ) ) {
						$status = 'expired';
					}
				}
			}
			?>
			<span id="ur-membership-status" class="btn-<?php echo $status ?>"><?php echo esc_html__( ucfirst( $status ) ) ?></span>
		</div>
	</div>
	<?php
	if ( 'canceled' !== $membership['status'] ):
		?>
		<div class="membership-row-btn-container">
			<div class="btn-div">
				<button type="button" class="cancel-membership-button button"
						data-id="<?php echo ( isset( $membership['subscription_id'] ) && ! empty( $membership['subscription_id'] ) ) ? esc_attr( $membership['subscription_id'] ) : ''; ?>"
				>
					<span class="dashicons dashicons-dismiss"></span>
					<?php echo __( "Cancel Membership", "user-registration" ); ?>
				</button>
				<!-- <span class="ur-portal-tooltip tooltipstered"
					  data-tip="<?php echo esc_html__( 'Cancellation will be according to the current membership cancellation type.', 'user-registration' ) ?>"> -->
				</span>
			</div>
			<div id="membership-error-div" class="btn-error">
			</div>
		</div>
	<?php
	endif;
	?>
</div>
