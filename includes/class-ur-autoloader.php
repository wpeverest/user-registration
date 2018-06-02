<?php
/**
 * UserRegistration Autoloader.
 *
 * @class    UR_Autoloader
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Autoloader Class
 */
class UR_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * Class Constructor Method.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class
	 *
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path
	 *
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );

			return true;
		}

		return false;
	}

	/**
	 * Auto-load UR classes on demand to reduce memory consumption.
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';

		if ( strpos( $class, 'ur_shortcode_' ) === 0 ) {
			$path = $this->include_path . 'shortcodes/';
		} elseif ( strpos( $class, 'ur_meta_box' ) === 0 ) {
			$path = $this->include_path . 'admin/meta-boxes/';
		} elseif ( strpos( $class, 'ur_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		} elseif ( strpos($class, 'ur_settings') === 0 ) {
			$path = $this->include_path . 'admin/settings/emails/';
		}
		elseif ( strpos( $class, 'ur_form' ) === 0 ) {
			$path = $this->include_path . 'form/';
		} elseif ( strpos( $class, 'ur_log_handler_' ) === 0 ) {
			$path = $this->include_path . 'log-handlers/';
		} elseif ( strpos( $class, 'ur_form_field_' ) === 0 ) {
			$path = $this->include_path . 'form/';
		}

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'ur_' ) === 0 ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new UR_Autoloader();
