<?php
/**
 * URM_CONTENT_DRIP_INTEGRATION setup
 *
 * @package URM_CONTENT_DRIP_INTEGRATION
 * @since  1.0.0
 */

namespace WPEverest\URM\ContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Main' ) ) :

	/**
	 * Main content drip integration Class
	 *
	 */
	final class Main {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Admin class instance
		 *
		 * @var \Admin
		 * @since 1.0.0
		 */
		public $admin = null;

		/**
		 * Admin class instance
		 *
		 * @var use WPEverest\URMembership\Admin\Members\Members;
		 * @since 1.0.0
		 */
		public $members = null;

		/**
		 * Frontend class instance
		 *
		 * @var \Frontend
		 * @since 1.0.0
		 */
		public $frontend = null;

		/**
		 * Ajax.
		 *
		 * @since 1.0.0
		 *
		 * @var use WPEverest\URMembership\AJAX;
		 */
		public $ajax = null;

		/**
		 * Shortcodes.
		 *
		 * @since 1.0.0
		 *
		 * @var use WPEverest\URMembership\Admin\Shortcodes;
		 */
		public $shortcodes = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			if ( is_admin() ) {
				Admin::init();
			}

			new Frontend();
		}
	}
endif;
