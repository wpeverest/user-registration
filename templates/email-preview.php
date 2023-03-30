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
	<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>
				<?php esc_html__( get_bloginfo( 'name' ) ); ?>
			</title>
			<?php wp_head(); ?>
			<style>
				html,
				body {
					overflow: hidden;
					-webkit-overflow-scrolling: auto;
					margin: 0;
				}
			</style>
		</head>
		<body <?php body_class(); ?> >
		 <?php
			$option_name = isset( $_GET['ur_email_preview'] ) ? sanitize_text_field( $_GET['ur_email_preview'] ) : '';
			$email_content = get_option( 'user_registration_' . $option_name );
			?>
			<div class="user-registration-email-preview">
				<?php echo sprintf( __( '%s', 'user-registration' ), $email_content ); ?>
			</div>

		</body>
		<?php wp_footer(); ?>
	</html>
