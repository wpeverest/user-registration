<?php
defined( 'ABSPATH' ) || exit;
global $post;
if ( ! is_object( $target_post ) ) {
	$target_post = $post;
}

$login_page_id = get_option( 'user_registration_login_page_id' );
$registration_page_id = get_option( 'user_registration_member_registration_page_id' );

$login_url = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
$signup_url = $registration_page_id ? get_permalink( $registration_page_id ) : ( $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );

if ( ! $registration_page_id ) {
	$default_form_page_id = get_option( 'user_registration_default_form_page_id' );
	if ( $default_form_page_id ) {
		$signup_url = get_permalink( $default_form_page_id );
	}
}
?>
<style>
	.urcr-access-modal {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 100vh;
		padding: 20px;
		background-color: #f5f5f5;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	}
	.urcr-access-card {
		background-color: #ffffff;
		border-radius: 12px;
		padding: 48px 40px;
		max-width: 500px;
		width: 100%;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
		color: #666666;
		margin: 0 0 32px 0;
		line-height: 1.5;
	}
	.urcr-access-buttons {
		display: flex;
		gap: 12px;
		flex-wrap: wrap;
	}
	.urcr-access-button {
		flex: 1;
		min-width: 140px;
		padding: 14px 24px;
		border-radius: 8px;
		font-size: 16px;
		font-weight: 600;
		text-decoration: none;
		text-align: center;
		cursor: pointer;
		transition: all 0.2s ease;
		border: none;
		display: inline-block;
	}
	.urcr-access-button-primary {
		background-color: #4f46e5;
		color: #ffffff;
	}
	.urcr-access-button-primary:hover {
		background-color: #4338ca;
		color: #ffffff;
	}
	.urcr-access-button-secondary {
		background-color: #f3f4f6;
		color: #4f46e5;
	}
	.urcr-access-button-secondary:hover {
		background-color: #e5e7eb;
		color: #4338ca;
	}
	@media (max-width: 480px) {
		.urcr-access-card {
			padding: 32px 24px;
		}
		.urcr-access-heading {
			font-size: 24px;
		}
		.urcr-access-buttons {
			flex-direction: column;
		}
		.urcr-access-button {
			width: 100%;
		}
	}
</style>
<div class="urcr-access-modal">
	<div class="urcr-access-card">
		<h1 class="urcr-access-heading"><?php esc_html_e( 'Full Access Required', 'user-registration' ); ?></h1>
		<p class="urcr-access-description"><?php  urcr_advanced_access_actions( $target_post, (array) $actions[0] ); ?></p>
		<div class="urcr-access-buttons">
			<a href="<?php echo esc_url( $signup_url ); ?>" class="urcr-access-button urcr-access-button-primary">
				<?php esc_html_e( 'Sign Up', 'user-registration' ); ?>
			</a>
			<a href="<?php echo esc_url( $login_url ); ?>" class="urcr-access-button urcr-access-button-secondary">
				<?php esc_html_e( 'Log In', 'user-registration' ); ?>
			</a>
		</div>
	</div>
</div>

