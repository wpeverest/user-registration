<?php
/**
 * User registration email preview template.
 *
 * @since 2.3.3.4
 *
 * @package User registration email preview template.
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
	<html <?php language_attributes(); ?> style="background-color: #E9EAEC;">
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>
				<?php get_bloginfo( 'name' ); ?>
			</title>
			<style>
				html,
				body {
					overflow: auto;
					-webkit-overflow-scrolling: auto;
					margin: 0;
					min-height: 100vh;
				}

				.ur-email-preview-container a {
					pointer-events: none;
					cursor: default;
				}
			</style>
			<style type="text/css">
			@media only screen and (max-width: 600px) {
				.ur-email-preview-container .email-wrapper-outer {
					padding: 20px 0 !important;
				}
				.ur-email-preview-container .email-wrapper-inner {
					width: 100% !important;
					max-width: 100% !important;
					margin: 0 !important;
					border-radius: 0 !important;
				}
				.ur-email-preview-container .email-header {
					padding: 20px 15px !important;
					border-radius: 0 !important;
				}
				.ur-email-preview-container .email-body {
					padding: 25px 15px !important;
				}
				.ur-email-preview-container .email-footer {
					padding: 20px 15px !important;
				}
				.ur-email-preview-container .email-logo img {
					max-width: 150px !important;
					max-height: 50px !important;
				}
				.ur-email-preview-container .email-header-text {
					font-size: 16px !important;
					margin-top: 10px !important;
				}
				.ur-email-preview-container .email-footer p {
					font-size: 12px !important;
				}
				.ur-email-preview-container .email-footer a {
					font-size: 13px !important;
				}
			}
			@media only screen and (max-width: 480px) {
				.ur-email-preview-container .email-wrapper-outer {
					padding: 10px 0 !important;
				}
				.ur-email-preview-container .email-header {
					padding: 15px 10px !important;
				}
				.ur-email-preview-container .email-body {
					padding: 20px 10px !important;
				}
				.ur-email-preview-container .email-footer {
					padding: 15px 10px !important;
				}
				.ur-email-preview-container .email-logo img {
					max-width: 120px !important;
					max-height: 40px !important;
				}
				.ur-email-preview-container .email-header-text {
					font-size: 14px !important;
				}
			}
			</style>
			<?php
			$style_content                = '';
			$email_content_without_styles = $email_content;

			if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $email_content, $style_matches ) ) {
				foreach ( $style_matches[0] as $style_tag ) {
					$style_content               .= $style_tag . "\n";
					$email_content_without_styles = str_replace( $style_tag, '', $email_content_without_styles );
				}
			}

			$processed_content = user_registration_process_email_content( $email_content_without_styles, $email_template );

			if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $processed_content, $processed_style_matches ) ) {
				foreach ( $processed_style_matches[0] as $style_tag ) {
					$style_content    .= $style_tag . "\n";
					$processed_content = str_replace( $style_tag, '', $processed_content );
				}
			}

			if ( ! empty( $style_content ) ) {
				echo $style_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</head>
		<body <?php body_class(); ?> >
			<div class="ur-email-preview-container">
			<?php
				echo $processed_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</div>
		</body>
	</html>
