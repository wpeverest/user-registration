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

		if( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	public function enqueue_block_assets() {
		wp_enqueue_style(
			'user-registration-block-editor-css',
			UR()->plugin_url() . '/assets/css/user-registration.css',
			array( 'wp-edit-blocks' ),
			UR_VERSION
		);
	}

	/**
	 * Enqueue Block Editor Assets.
	 * @return void.
	 */
	public function enqueue_block_editor_assets() {

		wp_register_script(
	        'user-registration-block-editor',
	        UR()->plugin_url() . '/assets/js/admin/form-block.build.js',
	        array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components' ),
	        UR_VERSION
		);

		$form_block_data = array(
			'forms'    => ur_get_all_user_registration_form(),
			'logo_url' => UR()->plugin_url() . '/assets/images/logo.png',
			'i18n'     => array(
				'title'         => esc_html__( 'User Registration', 'user-registration' ),
				'description'   => esc_html__( 'Select &#38; display one of your form.', 'user-registration' ),
				'form_select'   => esc_html__( 'Select a Form', 'user-registration' ),
				'form_settings' => esc_html__( 'Form Settings', 'user-registration' ),
				'form_selected' => esc_html__( 'Form', 'user-registration' ),
			)
		);

		wp_localize_script( 'user-registration-block-editor', 'ur_form_block_data', $form_block_data );

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
			'editor_style'    => 'user-registration-block-editor',
			'render_callback' => array( $this, 'render_callback' ),
		) );
	}

	/**
	 * Render Callback for the block. This is what is output
	 * in the preview within Gutenberg
	 *
	 * @param $attr
	 */
	function render_callback( $attr ) {

		$form_id = ! empty( $attr['formId'] ) ? absint( $attr['formId'] ) : 0;

 		if ( empty( $form_id ) ) {
			return '';
		}

		$is_gb_editor = defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'];

		if( $is_gb_editor ) {
			add_filter( 'user_registration_form_custom_class', function( $class ) {
				return $class .' ur-gutenberg-editor';
			});

			add_action( 'user_registration_before_registration_form', function() {
				echo '<fieldset disabled>';
			});

			add_action( 'user_registration_form_registration', function() {
				echo '</fieldset>';
			});
		}

		return UR_Shortcodes::form( array(
			'id' => $form_id,
		) );
	}
}

new UR_Form_Block;
