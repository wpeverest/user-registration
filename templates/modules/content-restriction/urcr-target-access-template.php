<?php
defined( 'ABSPATH' ) || exit;
get_header( '' );
global $post;
if ( ! is_object( $target_post ) ) {
	$target_post = $post;
}
urcr_advanced_access_actions( $target_post, (array) $actions[0] );
get_footer( '' );

