<?php
/**
 * URM_MASTERIYO_ADMIN setup
 *
 * @package URM_MASTERIYO_ADMIN
 * @since  1.0.0
 */

namespace WPEverest\URM\Masteriyo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Admin' ) ) :

	/**
	 * Admin masteriyo integration Clas s
	 *
	 */
	class Admin {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function init() {
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
			add_filter( 'urcr_content_type_options', array( $this, 'add_content_type_option' ) );
		}

		public function add_content_type_option( $options ) {

			$options[] = array(
				'value' => 'masteriyo_courses',
				'label' => __( 'Masteriyo Courses', 'user-registration' ),
			);

			return $options;
		}
	}
endif;
