<?php
namespace WPEverest\URMembership\DiviBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Builder Abstract class.
 *
 * @since xx.xx.xx
 */
class BuilderAbstract extends \ET_Builder_Module {

	/**
	 * Module slug.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $slug = null;

	/**
	 * Module title.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $title = null;

	/**
	 * Whether module support visual builder. e.g `on` or `off`.
	 *
	 * @since xx.xx.xx
	 * @var string
	 */
	public $vb_support = 'on';

	/**
	 * List of controls to allow module customization.
	 *
	 * @since 1.6.13
	 * @var array
	 */
	protected $setting_controls = array();

	/**
	 * Settings for module
	 *
	 * @return void
	 */
	public function settings_init() {
	}

	/**
	 * Render content.
	 *
	 * @param array $props The attributes values.
	 * @return void
	 */
	public function render_module( $props = array() ) {

		return '';
	}

	/**
	 * Divi builder init function.
	 *
	 * @since xx.xx.xx
	 */
	public function init() {

		$this->name = $this->title;

		$this->settings_init();

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
	}


	/**
	 * Advanced Fields Settings.
	 *
	 * @since xx.xx.xx
	 */
	public function get_advanced_fields_config() {
		return array(
			'link_options' => false,
			'text'         => false,
			'borders'      => false,
			'box_shadow'   => false,
			'button'       => false,
			'filters'      => false,
			'fonts'        => false,
		);
	}

	/**
	 * Render the module on frontend.
	 *
	 * @since xx.xx.xx
	 * @param array  $unprocessed_props Array of unprocessed Properties.
	 * @param string $content Contents being processed from the prop.
	 * @param string $render_slug The slug of rendering module for rendering output.
	 * @return string HTML content for rendering.
	 */
	public function render( $unprocessed_props, $content, $render_slug ) {
		return $this->_render_module_wrapper( $this->render_module( $this->props ), $render_slug );
	}

	/**
	 * Enqueue Divi Builder JavaScript.
	 *
	 * @since xx.xx.xx
	 */
	public function load_scripts() {
		if ( ! class_exists( 'UR' ) ) {
			return;
		}

		if ( wp_script_is( 'urm-divi-builder', 'enqueued' ) ) {
			return;
		}

		$enqueue_script = array( 'wp-element', 'react', 'react-dom', 'jquery' );

		wp_register_script(
			'urm-divi-builder',
			UR()->plugin_url() . '/chunks/divi-builder.min.js',
			$enqueue_script,
			UR()->version,
			true
		);

		wp_enqueue_script( 'urm-divi-builder' );
	}
}
