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
		$enqueue_script = array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components', 'react', 'react-dom', 'tooltipster' );

		if ( 'widgets.php' === $pagenow ) {
			unset( $enqueue_script[ array_search( 'wp-editor', $enqueue_script ) ] );
		}

		wp_register_style(
			'user-registration-blocks-editor',
			UR()->plugin_url() . '/assets/css/user-registration.css',
			array( 'wp-edit-blocks' ),
			UR_VERSION
		);

		wp_register_script(
			'user-registration-blocks-editor',
			UR()->plugin_url() . '/chunks/blocks.js',
			$enqueue_script,
			UR_VERSION
		);
		wp_localize_script(
			'user-registration-blocks-editor',
			'_UR_BLOCKS_',
			array(
				'logoUrl'              => UR()->plugin_url() . '/assets/images/logo.png',
				'urRestApiNonce'       => wp_create_nonce( 'wp_rest' ),
				'restURL'              => rest_url(),
				'isPro'                => is_plugin_active( 'user-registration-pro/user-registration.php' ),
				'iscRestrictionActive' => ur_check_module_activation( 'content-restriction' ),
				'pages' 			   => array_map( function( $page ) { return [ 'label' => $page->post_title, 'value' => $page->ID ]; }, get_pages() ),
				'login_page_id'		   => get_option('user_registration_login_page_id')
			)
		);
		wp_register_script(
			'user-registration-shortcode-embed-form',
			UR()->plugin_url() . '/assets/js/admin/shortcode-form-embed.js',
			$enqueue_script,
			UR_VERSION
		);

		wp_enqueue_script( 'user-registration-blocks-editor' );
		if ( 'post.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['form'] ) && 'user_registration' === $_GET['form'] ) {
			wp_enqueue_script( 'user-registration-shortcode-embed-form' );
			wp_localize_script(
				'user-registration-shortcode-embed-form',
				'user_registration_blocks_editor_prams',
				array(
					'i18n_add_a_block'     => esc_html__( 'Add a block', 'user-registration' ),
					'i18n_add_a_block_tip' => sprintf( '%s %s', esc_html__( 'Click the plus button, search for User Registration, click the block to embed it. ', 'user-registration' ), '<a href="#" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn More', 'user-registration' ) . '</a>' ),
					'i18n_done_btn'        => esc_html__( 'Done', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * Add "User Registration" category to the blocks listing in post edit screen.
	 *
	 * @param array $block_categories All registered block categories.
	 * @return array
	 * @since 3.1.5
	 */
	public function block_categories( array $block_categories ) {
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
	private function get_block_types() {

		$ur_blocks_classes = array(
			UR_Block_Regstration_Form::class, //phpcs:ignore;
			UR_Block_Login_Form::class, //phpcs:ignore;
			UR_Block_Myaccount::class, //phpcs:ignore;
			UR_Block_Edit_Profile::class, //phpcs:ignore;
			UR_Block_Edit_Password::class, //phpcs:ignore;
			UR_Block_Login_Logout_Menu::class, //phpcs:ignore;
		);

		if ( ur_check_module_activation( 'content-restriction' ) ) {
			$ur_blocks_classes[] = UR_Block_Content_Restriction::class;
		}
		if ( ur_check_module_activation( 'membership' ) ) {
			$ur_blocks_classes[] = UR_Block_Membership_Listing::class;
			$ur_blocks_classes[] = 	UR_Block_Thank_You::class;
		}

		return apply_filters(
			'user_registration_block_types',
			$ur_blocks_classes
		);
	}
}
return new UR_Blocks();
