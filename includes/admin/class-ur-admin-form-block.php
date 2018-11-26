<?php

/**
 * Form Selector Gutenberg block with live preview.
 * @since      1.5.1
 */
class UR_Form_Block {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Enqueue Block Editor Assets.
	 * @return void.
	 */
	public function enqueue_block_editor_assets() {

		wp_register_script(
	        'user-registration-block-editor',
	        UR()->plugin_url() . '/assets/js/admin/form-block.build.js',
	        array( 'wp-blocks', 'wp-element' )
		);

		wp_enqueue_script( 'user-registration-block-editor' );
	}

	/**
	 * Register user registration Gutenberg block on the backend.
	 *
	 * @since 1.5.1
	 */
	public function register_block() {

		register_block_type( 'user-registration/form-selector', array(
			'attributes'      => array(
				'formId'       => array(
					'type' => 'string',
				),
			),
			'editor_script'   => 'user-registration-block-editor',
			'render_callback' => array( $this, 'render_callback' ),
		) );
	}

	/**
	 * Render Callback for the block. This is what is output
	 * in the preview within Gutenberg
	 *
	 * @param $block
	 */
	function render_callback( $block ) {

	}
}

new UR_Form_Block;
