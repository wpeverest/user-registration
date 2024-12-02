<?php
defined( 'ABSPATH' ) || exit;
get_header( '' );
$message = apply_filters( 'user_registration_process_smart_tags', $message );
if ( function_exists( 'apply_shortcodes' ) ) {
	echo apply_shortcodes( $message );
} else {
	echo do_shortcode( $message );
}

get_footer( '' );
