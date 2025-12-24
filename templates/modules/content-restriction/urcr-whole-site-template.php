<?php
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
		</head>
		<body class="user-registration-page">
			<?php
				global $post;
			if ( ! is_object( $target_post ) ) {
				$target_post = $post;
			}

				urcr_advanced_access_actions( $target_post, (array) $actions[0] );
			?>
		</body>
		<?php wp_footer(); ?>
	</html>

