<?php

/**
 * Form Selector Gutenberg block with live preview.
 * @since      1.5.2
 */
class UR_Form_Block {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register user registration Gutenberg block on the backend.
	 *
	 * @since 1.5.2
	 */
	public function register_block() {

	}
}

new UR_Form_Block;
