<?php
/**
 * Email wrapper template
 *
 * This template wraps email body content with basic styling.
 * The $body_content variable will be inserted where indicated.
 *
 * @var string $body_content Email body content to wrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if this is a preview and set width to 600px.
$is_preview  = isset( $_GET['ur_email_preview'] ) && 'email_template_option' === sanitize_text_field( wp_unslash( $_GET['ur_email_preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$email_width = $is_preview ? '600px' : '50%';
$max_width   = $is_preview ? '600px' : '600px'; // Max width for better readability on all devices.

// Responsive CSS styles for email template.
$responsive_styles = '<style type="text/css">
	/* Responsive Email Styles */
	@media only screen and (max-width: 600px) {
		.email-wrapper-outer {
			padding: 20px 0 !important;
		}
		.email-wrapper-inner {
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			border-radius: 0 !important;
		}
		.email-body {
			padding: 25px 15px !important;
		}
	}
	@media only screen and (max-width: 480px) {
		.email-wrapper-outer {
			padding: 10px 0 !important;
		}
		.email-body {
			padding: 20px 10px !important;
		}
	}
</style>';

// Check if body_content is already wrapped (contains email-wrapper-outer class).
$is_already_wrapped = false !== strpos( $body_content, 'email-wrapper-outer' );

// Build default header (can be filtered by pro version).
$default_header = $responsive_styles . '
<div class="email-wrapper-outer" style="font-family: Arial, sans-serif; padding: 100px 0; background-color: #ffffff;">
<div class="email-wrapper-inner" style="width: ' . esc_attr( $email_width ) . '; max-width: ' . esc_attr( $max_width ) . '; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
<div class="email-body" style="padding: 40px 30px; background-color: #ffffff;">';

// Apply header filter (pro version can override this to add header with logo/text).
$header = apply_filters( 'user_registration_email_template_header', $default_header );

// Build default footer (can be filtered by pro version).
$default_footer = '</div>
</div>
</div>';

// Apply footer filter (pro version can override this to add footer content).
$footer = apply_filters( 'user_registration_email_template_footer', $default_footer );

// Output the wrapped email content.
if ( $is_already_wrapped ) {
	// Content is already wrapped, just output it directly.
	echo $body_content;
} else {
	// Content not wrapped, use filtered header and footer.
	echo $header;
	echo $body_content;
	echo $footer;
}

