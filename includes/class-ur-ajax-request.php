<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DOING_AJAX', true );

/**
 * UR_AJAX_Request Class
 */
class UR_AJAX_Request {

	public function __construct() {
		add_action( 'init', array( $this, 'ajax_request_handler' ) );
	}

	public function ajax_request_handler() {

		if ( ! isset( $_POST['action'] ) ) {
			die( '-1' );
		}

		// relative to where your plugin is located
		require_once '../../../../../wp-load.php';

		// Typical headers
		header( 'Content-Type: text/html' );
		send_nosniff_header();

		// Disable caching
		header( 'Cache-Control: no-cache' );
		header( 'Pragma: no-cache' );

		$action = esc_attr( $_POST['action'] );

		// A bit of security
		$allowed_actions = array(
			'action_1',
			'action_2',
			'action_3',
		);

		if ( in_array( $action, $allowed_actions ) ) {
			if ( is_user_logged_in() ) {
				do_action( 'plugin_name_ajax_' . $action );
			} else {
				do_action( 'plugin_name_ajax_nopriv_' . $action );
			}
		} else {
			die( '-1' );
		}
	}
}

new UR_AJAX_Request();
