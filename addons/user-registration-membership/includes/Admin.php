<?php
/**
 * User_Registration_Membership setup
 *
 * @package User_Registration_Membership
 * @since  1.0.0
 */

namespace WPEverest\URMembership;

use WPEverest\URMembership\Admin\Database\Database;
use WPEverest\URMembership\Admin\Members\Members;
use WPEverest\URMembership\Admin\Membership\Membership;
use WPEverest\URMembership\Admin\Services\PaymentGatewaysWebhookActions;
use WPEverest\URMembership\Frontend\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Admin' ) ) :

	/**
	 * Main Membership Class
	 *
	 * @class Membership
	 */
	final class Admin {


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Plugin Version
		 *
		 * @var string
		 */
		const VERSION = UR_MEMBERSHIP_VERSION;

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
			require 'Functions/CoreFunctions.php';

				// Actions and Filters.

				add_filter(
					'plugin_action_links_' . plugin_basename( UR_MEMBERSHIP_PLUGIN_FILE ),
					array(
						$this,
						'plugin_action_links',
					)
				);
				add_action( 'init', array( $this, 'includes' ) );
				add_action( 'init', array( $this, 'create_post_type' ), 0 );
				add_action( 'init', array( 'WPEverest\URMembership\ShortCodes', 'init' ) );
				add_action( 'init', array( $this, 'add_membership_options' ) );
				add_action( 'plugins_loaded', array( $this, 'include_membership_payment_files' ) );
				add_filter( 'user_registration_get_settings_pages', array( $this, 'add_membership_settings_page' ), 10, 1 );

				register_deactivation_hook( UR_PLUGIN_FILE, array( $this, 'on_deactivation' ) );

				register_activation_hook( UR_PLUGIN_FILE, array( $this, 'on_activation' ) );
				$this->load_plugin_textdomain();

		}

		/**
		 * Includes.
		 */
		public function includes() {
			$this->ajax = new AJAX();
			if ( $this->is_admin() ) {
				$this->admin   = new Membership();
				$this->members = new Members();
			} else {
				// require file.
				$this->frontend = new Frontend();
			}
		}

		/**
		 * Check if is admin or not and load the correct class
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function is_admin() {
			$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$check_context = isset( $_REQUEST['context'] ) && 'frontend' === $_REQUEST['context'];

			return is_admin() && ! ( $check_ajax && $check_context );
		}

		/**
		 * Display action links in the Plugins list table.
		 *
		 * @param array $actions Add plugin action link.
		 *
		 * @return array
		 */
		public function plugin_action_links( $actions ) {
			$new_actions = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-membership' ) . '" title="' . esc_attr( __( 'View User Registration Membership Settings', 'user-registration-membership' ) ) . '">' . __( 'Settings', 'user-registration-membership' ) . '</a>',
			);

			return array_merge( $new_actions, $actions );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Load Localization files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'user-registration-membership' );

			unload_textdomain( 'user-registration-membership' );
			load_textdomain( 'user-registration-membership', UR_MEMBERSHIP_DIR . 'languages/user-registration-membership-' . $locale . '.mo' );
			load_plugin_textdomain( 'user-registration-membership', false, UR_MEMBERSHIP_DIR . 'languages' );
		}

		/**
		 * Rgister Custom Post Type.
		 */
		public function create_post_type() {
			$raw_referer = wp_parse_args( wp_parse_url( wp_get_raw_referer(), PHP_URL_QUERY ) );

			register_post_type(
				'ur_membership',
				apply_filters(
					'user_registration_membership_post_type',
					array(
						'labels'              => array(
							'name'                  => __( 'Memberships', 'user-registration-membership' ),
							'singular_name'         => __( 'Membership', 'user-registration-membership' ),
							'all_items'             => __( 'All Memberships', 'user-registration-membership' ),
							'menu_name'             => _x( 'Memberships', 'Admin menu name', 'user-registration-membership' ),
							'add_new'               => __( 'Add New', 'user-registration-membership' ),
							'add_new_item'          => __( 'Add new', 'user-registration-membership' ),
							'edit'                  => __( 'Edit', 'user-registration-membership' ),
							'edit_item'             => __( 'Edit membership', 'user-registration-membership' ),
							'new_item'              => __( 'New membership', 'user-registration-membership' ),
							'view'                  => __( 'View membership', 'user-registration-membership' ),
							'view_item'             => __( 'View memberships', 'user-registration-membership' ),
							'search_items'          => __( 'Search memberships', 'user-registration-membership' ),
							'not_found'             => __( 'No memberships found', 'user-registration-membership' ),
							'not_found_in_trash'    => __( 'No memberships found in trash', 'user-registration-membership' ),
							'parent'                => __( 'Parent membership', 'user-registration-membership' ),
							'featured_image'        => __( 'Membership image', 'user-registration-membership' ),
							'set_featured_image'    => __( 'Set membership image', 'user-registration-membership' ),
							'remove_featured_image' => __( 'Remove membership image', 'user-registration-membership' ),
							'use_featured_image'    => __( 'Use as membership image', 'user-registration-membership' ),
							'insert_into_item'      => __( 'Insert into membership', 'user-registration-membership' ),
							'uploaded_to_this_item' => __( 'Uploaded to this membership', 'user-registration-membership' ),
							'filter_items_list'     => __( 'Filter membership', 'user-registration-membership' ),
							'items_list_navigation' => __( 'Membership navigation', 'user-registration-membership' ),
							'items_list'            => __( 'Membership list', 'user-registration-membership' ),

						),
						'show_ui'             => true,
						'capability_type'     => 'post',
						'map_meta_cap'        => true,
						'show_in_menu'        => false,
						'hierarchical'        => false,
						'rewrite'             => false,
						'query_var'           => false,
						'show_in_nav_menus'   => false,
						'show_in_admin_bar'   => false,
						'supports'            => array( 'title' ),
					)
				)
			);
		}

		/**
		 * Adds the membership options to the database.
		 *
		 * This function adds the payment gateways for the membership plugin to the
		 * WordPress options table. The payment gateways are stored in the 'ur_membership_payment_gateways'
		 * option and are an array containing the strings 'Paypal', 'Stripe', and 'Bank'.
		 *
		 * @return void
		 */
		public function add_membership_options() {
			add_option(
				'ur_membership_payment_gateways',
				array(
					'paypal'      => __( 'Paypal', 'user-registration-membership' ),
					'stripe'      => __( 'Stripe', 'user-registration-membership' ),
					'bank'        => __( 'Bank', 'user-registration-membership' ),
				)
			);
		}

		/**
		 * Includes the necessary payment files for the membership plugin if PayPal is activated.
		 *
		 * This function checks if PayPal is activated by calling the `ur_pro_is_paypal_activated()` function.
		 * If PayPal is activated, it instantiates a new `PaypalActions` object.
		 *
		 * @return void
		 */
		public function include_membership_payment_files() {
			if ( ur_membership_is_paypal_activated() ) {
				new PaymentGatewaysWebhookActions();
			}

		}

		/**
		 * Creates the necessary database tables for the plugin.
		 *
		 * This function calls the `create_tables` method of the `Database` class to create the necessary tables for the plugin.
		 *
		 * @return void
		 */
		public static function on_activation() {
			Database::create_tables();
		}

		/**
		 * Deactivates the plugin by dropping the database tables.
		 *
		 * @return void
		 */
		public static function on_deactivation() {
			if ( get_option( 'user_registration_general_setting_uninstall_option' ) ) {
				Database::drop_tables();
			}
		}

		/**
		 * add_membership_settings_page
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function add_membership_settings_page( $settings ) {
			if ( class_exists( 'UR_Settings_Page' ) ) {
				$settings[] = include 'Admin/Settings/class-ur-settings-membership.php';
			}
			return $settings;
		}
	}
endif;
