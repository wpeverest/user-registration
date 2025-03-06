<?php
namespace WPEverest\URMembership\DiviBuilder;

use WPEverest\URMembership\DiviBuilder\Modules\RegistrationForm;
use WPEverest\URMembership\DiviBuilder\Modules\LoginForm;
use WPEverest\URMembership\DiviBuilder\Modules\MyAccount;

if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

/**
 * Builder.
 *
 * @since xx.xx.xx
 */
class Builder {

	/**
	 * Holds single instance of the class.
	 *
	 * @var null|static
	 */
	private static $instance = null;

	/**
	 * Get instance of the class.
	 *
	 * @return static
	 */
	final public static function init() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since xx.xx.xx
	 */
	public function __construct() {
		$this->setup();
	}
	/**
	 * Init.
	 *
	 * @since xx.xx.xx
	 */
	public function setup() {

		if ( ! function_exists( 'urm_is_divi_active' ) || ! urm_is_divi_active() ) {
			return;
		}

		add_action( 'et_builder_ready', array( $this, 'register_divi_builder' ) );
	}

	/**
	 * Function to check whether the divi module is loaded or not.
	 *
	 * @since xx.xx.xx
	 */
	public function register_divi_builder() {
		if ( ! class_exists( 'ET_Builder_Module' ) ) {
			return;
		}

		$modules = array( 'registration-form' => RegistrationForm::class, 'login-form'=> LoginForm::class, 'myaccount'=> MyAccount::class );

		foreach ( $modules as $module ) {
			new $module();
		}
	}
}
