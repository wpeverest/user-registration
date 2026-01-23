<?php
namespace WPEverest\URM\DiviBuilder;

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
			'background'   => false,
			'admin_label'  => false,
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
		return $this->_render_module_wrapper( static::render_module( $this->props ), $render_slug );
	}

	/**
	 * Enqueue Divi Builder JavaScript.
	 *
	 * @since xx.xx.xx
	 */
	public function load_scripts() {

		if ( wp_script_is( 'urm-divi-builder', 'enqueued' ) ) {
			return;
		}

		$enqueue_script = array( 'wp-element', 'react', 'react-dom', 'jquery' );

		wp_register_script(
			'urm-divi-builder',
			UR()->plugin_url() . '/chunks/divi-builder.js',
			$enqueue_script,
			UR()->version,
			true
		);

		if ( defined( 'UR_VERSION' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'user-registration-membership-frontend-script', UR()->plugin_url(). '/assets/js/modules/membership/frontend/user-registration-membership-frontend' . $suffix . '.js', array( 'jquery' ), UR_VERSION, true );

			wp_register_style( 'user-registration-membership-frontend-style', UR()->plugin_url(). '/assets/css/modules/membership/user-registration-membership-frontend.css', array(), UR_VERSION );

			wp_enqueue_script( 'user-registration-membership-frontend-script' );
			wp_enqueue_style( 'user-registration-membership-frontend-style' );
		}

		wp_register_style( 'urm-form-style', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR()->version );
		wp_localize_script(
			'urm-divi-builder',
			'_URM_DIVI_',
			array(
				'isPro' => defined( 'UR_PRO_ACTIVE' ),
			)
		);
		wp_enqueue_style( 'urm-form-style' );
		wp_enqueue_script( 'urm-divi-builder' );
	}
}
