<?php
/**
 *  Frontend.
 *
 * @class    Frontend
 * @package  Frontend
 * @category Frontend
 */

namespace WPEverest\URM\ContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	/**
	 * Constructor – initialize hooks.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register all frontend hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );
	}

	/**
	 * Enqueue styles for the course portal page.
	 *
	 * @return void
	 */
	public function load_scripts() {
		global $post;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'urm-masteriyo-frontend-style',
			URM_MASTERIYO_CSS_ASSETS_URL . '/urm-course-portal.css',
			array(),
			URM_CONTENT_DRIP_VERSION
		);

		wp_register_script(
			'urm-masteriyo-frontend-script',
			URM_MASTERIYO_JS_ASSETS_URL . '/frontend' . $suffix . '.js',
			array( 'jquery' ),
			URM_CONTENT_DRIP_VERSION,
			true
		);
	}
}
