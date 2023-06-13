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
			</style>
		</head>
		<body <?php body_class(); ?> >
			<?php
				echo user_registration_process_email_content( $email_content, $email_template ); // phpcs:ignore.
			?>
		</body>
	</html>
