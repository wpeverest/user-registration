<?php
/**
 * User Registration AJAX handler for frontend.
 *
 * @since 1.5.7
 *
 * @link https://www.coderrr.com/create-your-own-admin-ajax-php-type-handler/
 */

// Mimic the actual admin ajax.
define( 'DOING_AJAX', true );

if ( ! isset( $_POST['action'] ) ) {
	die( '-1' );
}

// Include wp-load.php.
// @TODO:: Needs better approah.
$root = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require_once $root . '/' . 'wp-load.php';

// Typical headers.
header( 'Content-Type: text/html' );
send_nosniff_header();

// Disable caching.
header( 'Cache-Control: no-cache' );
header( 'Pragma: no-cache' );

$action = esc_attr( trim( $_POST['action'] ) );

// A bit of security.
$allowed_actions = array(
	'user_form_submit',
);

if ( in_array( $action, $allowed_actions ) ) {
	if ( is_user_logged_in() ) {
		do_action( 'user_registration_ajax_' . $action );
	} else {
		do_action( 'user_registration_ajax_nopriv_' . $action );
	}
} else {
	die( '-1' );
}
