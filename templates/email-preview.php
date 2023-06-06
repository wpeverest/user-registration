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
				echo wp_kses_post( user_registration_process_email_content( $email_content, $email_subject ) );
			?>
		</body>
		<?php wp_footer(); ?>
	</html>
