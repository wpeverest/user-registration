<?php
/**
 * UserRegistration UR_Form_Block
 *
 * AJAX Event Handler
 *
 * @class    UR_AJAX
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

/**
 * Form Selector Gutenberg block with live preview.
 *
 * @since      1.5.1
 */
class UR_Form_Block {

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Enqueue Block Editor Assets.
	 *
	 * @return void.
	 */
	public function enqueue_block_editor_assets() {
		global $pagenow;
		$enqueue_script = array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components' );

		wp_register_style(
			'user-registration-block-editor',
			UR()->plugin_url() . '/assets/css/user-registration.css',
			array( 'wp-edit-blocks' ),
			UR_VERSION
		);

		if ( 'widgets.php' === $pagenow ) {
			unset( $enqueue_script[ array_search( 'wp-editor', $enqueue_script ) ] );
		}
		wp_register_script(
			'user-registration-block-editor',
			UR()->plugin_url() . '/chunks/main.js',
			$enqueue_script,
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
			),
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

		register_block_type(
			'user-registration/form-selector',
			array(
				'attributes'      => array(
					'formId'      => array(
						'type' => 'string',
					),
					'formType'    => array(
						'type' => 'string',
					),
					'shortcode'   => array(
						'type' => 'string',
					),
					'redirectUrl' => array(
						'type' => 'string',
					),
					'logoutUrl'   => array(
						'type' => 'string',
					),
				),
				'editor_script'   => 'user-registration-block-editor',
				'editor_style'    => 'user-registration-block-editor',
				'render_callback' => array( $this, 'render_callback' ),
			)
		);
	}

	/**
	 * Render Callback for the block. This is what is output
	 * in the preview within Gutenberg
	 *
	 * @param array $attr Attributes.
	 */
	public function render_callback( $attr ) {

		$form_type = ! empty( $attr['formType'] ) ? _sanitize_text_fields( $attr['formType'] ) : 'registration_form';
		if ( 'registration_form' === $form_type ) {
			$form_id = ! empty( $attr['formId'] ) ? absint( $attr['formId'] ) : 0;

			if ( empty( $form_id ) ) {
				return '';
			}

			$is_gb_editor = defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context']; // phpcs:ignore WordPress.Security.NonceVerification

			if ( $is_gb_editor ) {
				add_filter(
					'user_registration_form_custom_class',
					function( $class ) {
						return $class . ' ur-gutenberg-editor';
					}
				);

				add_action(
					'user_registration_before_registration_form',
					function() {
						echo '<fieldset disabled>';
					}
				);

				add_action(
					'user_registration_form_registration',
					function() {
						echo '</fieldset>';
					}
				);
			}

			return UR_Shortcodes::form(
				array(
					'id' => $form_id,
				)
			);
		} elseif ( 'login_form' === $form_type ) {
			$shortcode = ! empty( $attr['shortcode'] ) ? _sanitize_text_fields( $attr['shortcode'] ) : '';

			if ( empty( $shortcode ) ) {
				return '';
			}
			$parameters = array();

			if ( ! empty( $attr['redirectUrl'] ) ) {
				$parameters['redirect_url'] = $attr['redirectUrl'];
			}

			if ( ! empty( $attr['logoutUrl'] ) ) {
				$parameters['logout_redirect'] = $attr['logoutUrl'];
			}

			if ( 'user_registration_login' === $shortcode ) {
				return UR_Shortcodes::login(
					$parameters
				);
			} else {
				return UR_Shortcodes::my_account(
					$parameters
				);
			}
		}
	}
}

new UR_Form_Block();
