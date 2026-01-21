<?php
/**
 * ADMIN setup
 *
 * @package ADMIN
 * @since  1.0.0
 */

namespace WPEverest\URM\ContentDrip;

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
			add_filter( 'user_registration_get_sections_membership', array( $this, 'add_section' ) );
			add_filter( 'user_registration_get_settings_membership', array( $this, 'add_settings' ) );
		}

		public function add_section( $section ) {

			$section['content-drip'] = __( 'Content Drip', 'user-registration' );

			return $section;
		}

		public function add_settings( $settings ) {
			global $current_section;
			if ( 'content-drip' === $current_section ) {
				$settings = $this->global_settings();
			}
			return $settings;
		}

		public function global_settings() {
			// Build sections array
			$sections = array();

			$default_message = Helper::global_default_message();

			$sections['user_registration_content_drip_settings'] = array(
				'title'    => __( 'Content Drip', 'user-registration' ),
				'type'     => 'card',
				'settings' => array(
					array(
						'title'                            => __( 'Global Drip Message', 'user-registration' ),
						'desc'                             => __( ' Default message for all restricted drip.', 'user-registration' ),
						'id'                               => 'user_registration_content_drip_global_message',
						'type'                             => 'tinymce',
						'default'                          => $default_message,
						'css'                              => '',
						'show-smart-tags-button'           => true,
						'show-ur-registration-form-button' => false,
						'show-reset-content-button'        => false,
						'desc_tip'                         => true,
					),
				),
			);

			return apply_filters(
				'user_registration_content_drip_settings',
				array(
					'title'    => __( 'Content Drip Settings', 'user-registration' ),
					'desc'     => '',
					'sections' => $sections,
				)
			);
		}
	}
endif;
