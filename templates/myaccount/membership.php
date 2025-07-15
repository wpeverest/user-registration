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
$is_upgraded     = ! empty( $_GET['is_upgraded'] ) ? absint( ur_string_to_bool( $_GET['is_upgraded'] ) ) : false;
$message         = ! empty( $_GET['message'] ) ? esc_html( $_GET['message'] ) : '';
$membership_info = ( isset( $_GET['info'] ) && ! empty( $_GET['info'] ) ) ? wp_kses_post_deep( $_GET['info'] ) : ( ! empty( $bank_data['bank_data'] ) ? wp_kses_post_deep( $bank_data['bank_data'] ) : '' );
$is_delayed      = ! empty( $delayed_until );

?>

<div class="user-registration-membership-content"
	 style="">
	<div class="membership-row">

		<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Membership Title', 'user-registration' ) . ':'; ?>
					</span>
		</div>
		<div class="membership-data">
				<span id="membership-title">
				<?php
				echo isset( $membership['post_title'] ) && ! empty( $membership['post_title'] ) ? esc_html( $membership['post_title'] ) : __( 'N/A', 'user-registration' ) ?>
			</span>

		</div>

	</div>
	<div class="membership-row">

		<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Membership Status', 'user-registration' ) . ':'; ?>
					</span>
		</div>
		<div class="membership-data">

			<?php
			$status = 'inactive';
			if ( isset( $membership['status'] ) ) {
				$status = ( '' != $membership['status'] ) ? $membership['status'] : $status;
				if ( 'inactive' !== $status && 'free' !== $membership['post_content']['type'] && 'paid' !== $membership['post_content']['type'] ) {
					$expiry_date = new DateTime( $membership['expiry_date'] );
					if ( date( 'Y-m-d' ) > $expiry_date->format( 'Y-m-d' ) ) {
						$status = 'expired';
					}
				}
			}
			?>
			<span id="ur-membership-status"
				  class="btn-<?php echo $status ?>"><?php echo esc_html__( ucfirst( $status ) ); ?></span>

		</div>
	</div>
	<?php
	if ( $membership['status'] === 'trial' ):
		?>
		<div class="membership-row">
			<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Trial Start Date', 'user-registration' ) . ':'; ?>
					</span>
			</div>
			<div class="membership-data">
				<span id="ur-membership-type">
				<?php
				echo ! empty( $membership['trial_start_date'] ) ? date( 'Y-m-d', strtotime( $membership['trial_start_date'] ) ) : __( 'N/A', 'user-registration' ) ?>
			</span>
			</div>
		</div>
		<div class="membership-row">
			<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Trial End Date', 'user-registration' ) . ':'; ?>
					</span>
			</div>
			<div class="membership-data">
				<span id="ur-membership-type">
				<?php
				echo ! empty( $membership['trial_end_date'] ) ? date( 'Y-m-d', strtotime( $membership['trial_end_date'] ) ) : __( 'N/A', 'user-registration' ) ?>
			</span>
			</div>
		</div>
	<?php
	else:
		?>
		<div class="membership-row">
			<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Start Date', 'user-registration' ) . ':'; ?>
					</span>
			</div>
			<div class="membership-data">
				<span id="ur-membership-type">
				<?php
				echo ! empty( $membership['start_date'] ) ? date( 'Y-m-d', strtotime( $membership['start_date'] ) ) : __( 'N/A', 'user-registration' ) ?>
			</span>
			</div>
		</div>
		<div class="membership-row">

			<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Renews On', 'user-registration' ) . ':'; ?>
					</span>
			</div>
			<div class="membership-data">
				<span id="ur-membership-type">
				<?php
				echo ! empty( $membership['next_billing_date'] ) && strtotime( $membership['next_billing_date'] ) > 0 ? date( 'Y-m-d', strtotime( $membership['next_billing_date'] ) ) : __( 'N/A', 'user-registration' ) ?>
			</span>
			</div>

		</div>

	<?php
	endif;
	?>

	<div class="membership-row">

		<div class="membership-label">
				<span style="font-weight: 500">
				<?php echo esc_html__( 'Membership Type', 'user-registration' ) . ':'; ?>
					</span>
		</div>
		<div class="membership-data">
				<span id="ur-membership-type">
				<?php
				echo isset( $membership['post_content'] ) && ! empty( $membership['post_content'] ) ? esc_html( ucfirst( wp_unslash( $membership['post_content']['type'] ) ) ) : __( 'N/A', 'user-registration' ) ?>
			</span>
		</div>

	</div>
	<div class="membership-row-btn-container">
		<div class="btn-div">
			<?php
			if ( !$is_upgrading ):
				?>

				<button type="button" class="membership-tab-btn change-membership-button"
						data-id="<?php echo ( isset( $membership['post_id'] ) && ! empty( $membership['post_id'] ) ) ? esc_attr( $membership['post_id'] ) : ''; ?>"
				>
					<?php echo __( "Change Plan", "user-registration" ); ?>
				</button>
			<?php
			endif;
			?>
			<?php
			if ( 'canceled' !== $membership['status'] ):
				?>
				<button type="button" class="membership-tab-btn cancel-membership-button"
						data-id="<?php echo ( isset( $membership['subscription_id'] ) && ! empty( $membership['subscription_id'] ) ) ? esc_attr( $membership['subscription_id'] ) : ''; ?>"
				>
					<?php echo __( "Cancel Membership", "user-registration" ); ?>
				</button>
			<?php
			endif;
			?>
		</div>
		<div id="membership-error-div" class="btn-success"
			 style="<?php echo $is_upgraded ? 'display:flex' : 'display:none' ?>">
				<span>
					<?php
					echo $message;
					?>
				</span>
			<span class="cancel-notice">
					x
				</span>
		</div>
		<?php
		if ( $is_upgrading ):
			if(!empty($bank_data['notice_1'])):
			?>
			<div id="bank-notice" class="btn-success">
				<span class="notice-1">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 18 22" fill="none">
						<g clip-path="url(#clip0_4801_13369)">
							<path d="M9 20.5C13.1421 20.5 16.5 17.1421 16.5 13C16.5 8.85786 13.1421 5.5 9 5.5C4.85786 5.5 1.5 8.85786 1.5 13C1.5 17.1421 4.85786 20.5 9 20.5Z" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							<path d="M9 13V16" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							<path d="M9 10H9.00875" stroke="#475BB2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						</g>
						<defs>
							<clipPath id="clip0_4801_13369">
								<rect width="18" height="18" fill="white" transform="translate(0 4)"></rect>
							</clipPath>
						</defs>
					</svg>
					<?php
					echo isset($bank_data['notice_1']) ? $bank_data['notice_1'] : '';
					?>
				</span>
				<span class="view-bank-data">
					<?php
					echo __( "Pay now", "user-registration" );
					?>
				</span>
			</div>
			<?php
			endif;
			?>
			<div class="upgrade-info urm-d-none">
				<?php
				echo $membership_info;
				?>
			</div>
		<?php
		endif;
		?>
	</div>
</div>
<div class="notice-container">
	<div class="notice_red">
		<span class="notice_message"></span>
		<span class="close_notice">&times;</span>
	</div>
</div>






