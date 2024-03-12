<?php
/**
 * User registration blocks.
 *
 * @since 3.1.5
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * User registration blocks class.
 */
class UR_Blocks {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @since 3.1.5
	 */
	private function init_hooks() {
		add_filter( 'block_categories_all', array( $this, 'block_categories' ), PHP_INT_MAX, 2 );
		add_action( 'init', array( $this, 'register_block_types' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}
	/**
	 * Enqueue Block Editor Assets.
	 *
	 * @return void.
	 */
	public function enqueue_block_editor_assets() {
		global $pagenow;
		$enqueue_script = array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components', 'react', 'react-dom' );
		// error_log( print_r( $pagenow, true ) );
		wp_register_style(
			'user-registration-blocks-editor',
			UR()->plugin_url() . '/assets/css/user-registration.css',
			array( 'wp-edit-blocks' ),
			UR_VERSION
		);

		// if ( 'widgets.php' === $pagenow ) {
		// 	unset( $enqueue_script[ array_search( 'wp-editor', $enqueue_script ) ] );
		// }
		wp_register_script(
			'user-registration-blocks-editor',
			UR()->plugin_url() . '/chunks/blocks.js',
			$enqueue_script,
			UR_VERSION
		);

		// $form_block_data = array(
		// 'forms'    => ur_get_all_user_registration_form(),
		// 'logo_url' => UR()->plugin_url() . '/assets/images/logo.png',
		// 'i18n'     => array(
		// 'title'         => esc_html( 'User Registration' ),
		// 'description'   => esc_html__( 'Select &#38; display one of your form.', 'user-registration' ),
		// 'form_select'   => esc_html__( 'Select a Form', 'user-registration' ),
		// 'form_settings' => esc_html__( 'Form Settings', 'user-registration' ),
		// 'form_selected' => esc_html__( 'Form', 'user-registration' ),
		// ),
		// );

		// wp_localize_script( 'user-registration-block-editor', 'ur_form_block_data', $form_block_data );

		wp_enqueue_script( 'user-registration-blocks-editor' );
	}

	/**
	 * Add "User Registration" category to the blocks listing in post edit screen.
	 *
	 * @param array $block_categories All registered block categories.
	 * @return array
	 * @since 3.1.5
	 */
	public function block_categories( array $block_categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'user-registration',
					'title' => esc_html__( 'User Registration', 'user-registration' ),
				),
			),
			$block_categories
		);
	}
	/**
	 * Register block types.
	 *
	 * @return void
	 */
	public function register_block_types() {
		$block_types = $this->get_block_types();
		foreach ( $block_types as $block_type ) {
			new $block_type();
		}
	}

	/**
	 * Get block types.
	 *
	 * @return AbstractBlock[]
	 */
	private function get_block_types(): array {
		return apply_filters(
			'user_registration_block_types',
			array(
				UR_Block_Regstration_Form::class,
			)
		);
	}
}
return new UR_Blocks();
