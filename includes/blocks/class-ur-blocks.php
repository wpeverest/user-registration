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
