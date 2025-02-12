<?php
defined( 'ABSPATH' ) || exit;
$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

if ( $whole_site_access_restricted ) {
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
			<body>
				<?php
				$message = apply_filters( 'user_registration_process_smart_tags', $message );
				if ( function_exists( 'apply_shortcodes' ) ) {
					echo apply_shortcodes( $message );
				} else {
					echo do_shortcode( $message );
				}
				?>
			</body>
			<?php wp_footer(); ?>
		</html>
	<?php
} else {

	get_header( '' );
	$message = apply_filters( 'user_registration_process_smart_tags', $message );
	if ( function_exists( 'apply_shortcodes' ) ) {
		echo apply_shortcodes( $message );
	} else {
		echo do_shortcode( $message );
	}

	get_footer( '' );
}
