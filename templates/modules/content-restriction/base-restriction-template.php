<?php
defined( 'ABSPATH' ) || exit;

// Set default values if not provided
$message = isset( $message ) ? $message : '';
$login_url = isset( $login_url ) ? $login_url : '';
$signup_url = isset( $signup_url ) ? $signup_url : '';

// Get URLs if not provided
if ( empty( $login_url ) ) {
	$login_page_id = get_option( 'user_registration_login_page_id' );
	$login_url = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
}

if ( empty( $signup_url ) ) {
	$registration_page_id = get_option( 'user_registration_member_registration_page_id' );
	$signup_url = $registration_page_id ? get_permalink( $registration_page_id ) : ( isset( $login_page_id ) && $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );

	if ( ! $registration_page_id ) {
		$default_form_page_id = get_option( 'user_registration_default_form_page_id' );
		if ( $default_form_page_id ) {
			$signup_url = get_permalink( $default_form_page_id );
		}
	}
}

?>
<style>
	/* Hide page title for whole site restrictions */
	body.urcr-hide-page-title .wp-block-post-title,
	body.urcr-hide-page-title .entry-header,
	body.urcr-hide-page-title .page-header,
	body.urcr-hide-page-title .entry-title,
	body.urcr-hide-page-title .page-title,
	body.urcr-hide-page-title h1.entry-title,
	body.urcr-hide-page-title h1.page-title,
	body.urcr-hide-page-title .post-title,
	body.urcr-hide-page-title .single-post-title,
	body.urcr-hide-page-title .single-page-title,
	body.urcr-hide-page-title article header.entry-header,
	body.urcr-hide-page-title article .entry-title {
		display: none !important;
	}
	.urcr-access-card {
		background-color: #ffffff;
		border-radius: 8px;
		padding: 40px;
		max-width: 500px;
		width: 100%;
		box-shadow: 0 6px 26px 0 rgba(10, 10, 10, 0.08);
		margin: 20px auto;
	}
	.urcr-access-heading {
		font-size: 28px;
		font-weight: 700;
		color: #1a1a1a;
		margin: 0 0 16px 0;
		line-height: 1.2;
	}
	.urcr-access-description {
		font-size: 16px;
		color: #6B6B6B;
		margin: 0 0 40px 0;
		line-height: 1.5;
	}
	.urcr-access-description br {
		display: none;
	}
	.urcr-actions {
		display: block;
		text-align: center;
	}
	.urcr-actions br {
		display: none;
	}
	.urcr-actions a {
		text-decoration: none;
		margin: 0;
		display: block;
		text-align: left;
	}
	.urcr-actions .urcr-access-button {
		margin-bottom: 12px;
	}
	.urcr-access-button {
		color: #6366f1;
		font-size: 16px;
		font-weight: 500;
		text-decoration: none;
	}
	.urcr-access-button-primary {
		color: #475bb2;
		text-decoration: underline;
	}
	.urcr-access-button-primary:hover {
		color: #475BB2;
		text-decoration: underline;
	}
	.urcr-signup-link {
		max-width: 100%;
		padding: 14px 32px;
		border-radius: 5px;
		font-size: 16px;
		font-weight: 600;
		text-align: center;
		cursor: pointer;
		transition: all 0.2s ease;
		border: none;
		display: inline-block;
		background-color: #475BB2;
		color: #ffffff;
		text-decoration: none;
		margin-right: 20px;
	}
	.urcr-signup-link:hover {
		background-color: rgb(56.4578313253, 72.3614457831, 141.5421686747);
		color: #ffffff;
		text-decoration: none;
	}
	@media (max-width: 480px) {
		.urcr-access-card {
			padding: 32px 24px;
		}
		.urcr-access-heading {
			font-size: 24px;
		}
		.urcr-access-button {
			width: 100%;
		}
	}
</style>
<div class="urcr-access-card">
	<?php echo wp_kses_post( $message ); ?>
</div>
