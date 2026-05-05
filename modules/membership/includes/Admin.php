<?php
/**
 * User_Registration_Membership setup
 *
 * @package User_Registration_Membership
 * @since  1.0.0
 */

namespace WPEverest\URMembership;

use WPEverest\URMembership\Admin\Database\Database;
use WPEverest\URMembership\Admin\Services\SubscriptionService;
use WPEverest\URMembership\Emails\EmailSettings;
use WPEverest\URMembership\Admin\Forms\FormFields;
use WPEverest\URMembership\Admin\Members\Members;
use WPEverest\URMembership\Admin\Membership\Membership;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Services\MembersService;
use WPEverest\URMembership\Admin\Services\PaymentGatewayLogging;
use WPEverest\URMembership\Admin\Services\PaymentGatewaysWebhookActions;
use WPEverest\URMembership\Admin\Services\PaymentService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;
use WPEverest\URMembership\Admin\Subscriptions\Subscriptions;
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
		const VERSION = UR_VERSION;

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
			// Actions and Filters.

			add_filter(
				'plugin_action_links_' . plugin_basename( UR_MEMBERSHIP_PLUGIN_FILE ),
				array(
					$this,
					'plugin_action_links',
				)
			);
			add_action( 'init', array( $this, 'includes' ) );
			add_action( 'init', array( $this, 'create_membership_post_type' ), 0 );
			add_action( 'init', array( $this, 'create_membership_groups_post_type' ), 0 );
			add_action( 'init', array( 'WPEverest\URMembership\ShortCodes', 'init' ) );
			add_action( 'init', array( $this, 'add_membership_options' ) );
			add_action( 'plugins_loaded', array( $this, 'include_membership_payment_files' ) );
			// add_filter( 'user_registration_get_settings_pages', array( $this, 'add_membership_settings_page' ), 10, 1 );

			add_filter(
				'user_registration_form_redirect_url',
				array(
					$this,
					'update_redirect_url_for_membership',
				),
				10,
				2
			);
			add_filter(
				'user_registration_success_params_before_send_json',
				array(
					$this,
					'update_success_params_for_membership',
				),
				10,
				4
			);
			add_filter(
				'user_registration_success_params_before_send_json',
				array(
					$this,
					'process_membership_after_registration',
				),
				20,
				4
			);

			register_deactivation_hook( UR_MEMBERSHIP_PLUGIN_FILE, array( $this, 'on_deactivation' ) );
			register_activation_hook( UR_MEMBERSHIP_PLUGIN_FILE, array( $this, 'on_activation' ) );
			add_filter(
				'user_registration_content_restriction_settings',
				array(
					$this,
					'add_memberships_in_urcr_settings',
				),
				10,
				1
			);
			add_action( 'admin_enqueue_scripts', array( $this, 'register_membership_admin_scripts' ) );
			add_action( 'plugins_loaded', array( __CLASS__, 'ur_membership_maybe_run_migrations' ), 20 );

			add_action( 'user_registration_single_user_details_content', array( $this, 'render_user_membership_details' ), 10, 2 );
		}

		public function register_membership_admin_scripts() {
			if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
				// Enqueue frontend scripts here.
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_register_script( 'user-registration-membership-frontend-script', UR()->plugin_url(). '/assets/js/modules/membership/frontend/user-registration-membership-frontend' . $suffix . '.js', array( 'jquery' ), UR_VERSION, true );
				wp_enqueue_script( 'user-registration-membership-frontend-script' );
				// Enqueue frontend styles here.
				wp_register_style( 'user-registration-membership-frontend-style', UR()->plugin_url(). '/assets/css/modules/membership/user-registration-membership-frontend.css', array(), UR_VERSION );
				wp_enqueue_style( 'user-registration-membership-frontend-style' );
			}
		}

		public function add_memberships_in_urcr_settings( $settings ) {
			$options             = get_active_membership_id_name();
			$additional_settings = array(
				array(
					'row_class' => 'urcr_content_restriction_allow_access_to_memberships',
					'title'     => __( 'Select Memberships', 'user-registration' ),
					'desc'      => __( 'The memberships selected here will have access to restricted content.', 'user-registration' ),
					'id'        => 'user_registration_content_restriction_allow_to_memberships',
					'type'      => 'multiselect',
					'class'     => 'ur-enhanced-select',
					'css'       => 'min-width: 350px; ' . ( '3' != get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) ? 'display:none;' : '',
					'desc_tip'  => true,
					'options'   => $options,
				),
			);
			$just_settings       = $settings['sections']['user_registration_content_restriction_settings']['settings'];

			array_splice( $just_settings, 2, 0, $additional_settings );

			$settings['sections']['user_registration_content_restriction_settings']['settings'] = $just_settings;

			return $settings;
		}

		public function update_success_params_for_membership( $success_params, $valid_form_data, $form_id, $user_id ) {
			$keyFound = false;

			foreach ( $valid_form_data as $key => $value ) {
				if ( 'membership' === $value->extra_params['field_key'] ) {
					$keyFound = true;
					break;
				}
			}

			if ( ! $keyFound ) {
				return $success_params;
			}
			$success_params['registration_type'] = 'membership';

			return $success_params;
		}

		public function process_membership_after_registration( $success_params, $valid_form_data, $form_id, $user_id ) {
		
			// Guard 1: module active
			if ( ! ur_check_module_activation( 'membership' ) ) {
				return $success_params;
			}
			// Guard 2: membership POST signals present
			if ( empty( $_POST['is_membership_active'] ) && empty( $_POST['membership_type'] ) ) {
				return $success_params;
			}
			// Guard 3: form has a membership field
			$has_membership_field = false;
			foreach ( $valid_form_data as $field_data ) {
				if ( isset( $field_data->extra_params['field_key'] ) && 'membership' === $field_data->extra_params['field_key'] ) {
					$has_membership_field = true;
					break;
				}
			}
			if ( ! $has_membership_field ) {
				return $success_params;
			}

			// Decode members_data (same filter as register_member())
			$data = apply_filters(
				'user_registration_membership_before_register_member',
				isset( $_POST['members_data'] ) ? (array) json_decode( wp_unslash( $_POST['members_data'] ), true ) : array()
			);
			if ( empty( $data ) || empty( $data['payment_method'] ) || empty( $data['membership'] ) ) {
				return $success_params;
			}

			// Inject user identity from the just-created user
			$member    = get_userdata( $user_id );
			$member_id = $user_id;
			if ( ! $member ) {
				return $success_params;
			}
			$data['username'] = $member->user_login;
			$data['email']    = $member->user_email;

			// Stripe validation (mirrors register_member() lines 151-169)
			if ( 'stripe' === $data['payment_method'] ) {
				if ( ! empty( $data['stripe_pm_error'] ) ) {
					wp_delete_user( absint( $member_id ) );
					wp_send_json_error( array( 'message' => sanitize_text_field( $data['stripe_pm_error'] ) ) );
				}
				if ( ! empty( $data['payment_method_id'] ) ) {
					$stripe_service = new StripeService();
					$mode_result    = $stripe_service->validate_card_mode( sanitize_text_field( $data['payment_method_id'] ) );
					if ( ! $mode_result['valid'] ) {
						wp_delete_user( absint( $member_id ) );
						wp_send_json_error( array( 'message' => $mode_result['message'] ) );
					}
				}
			}

			// Get membership type for logging
			$membership_repository = new MembershipRepository();
			$membership_data       = $membership_repository->get_single_membership_by_ID( $data['membership'] );
			$membership_meta       = json_decode( wp_unslash( $membership_data['meta_value'] ), true );
			$membership_type       = $membership_meta['type'] ?? 'unknown';
			$payment_gateway       = $data['payment_method'] ?? 'unknown';

			// PaymentGatewayLogging — session start + form submission
			if ( class_exists( 'WPEverest\URMembership\Admin\Services\PaymentGatewayLogging' ) ) {
				PaymentGatewayLogging::log_general(
					$payment_gateway,
					sprintf( ' [Member ID #%s] ========== ***NEW PAYMENT SESSION*** ==========', $member_id ) . "\n" . wp_json_encode(
						array(
							'timestamp'       => current_time( 'mysql' ),
							'membership_type' => $membership_type,
							'username'        => $member->user_login,
						),
						JSON_PRETTY_PRINT
					),
					'notice'
				);
				PaymentGatewayLogging::log_general(
					$payment_gateway,
					sprintf( ' [Member ID #%s] Membership registration form submitted.', $member_id ) . "\n" . wp_json_encode(
						array(
							'event_type'      => 'form_submission',
							'member_id'       => $member_id,
							'username'        => $member->user_login,
							'email'           => $member->user_email,
							'membership_id'   => $data['membership'] ?? 'N/A',
							'payment_method'  => $payment_gateway,
							'membership_type' => $membership_type,
						),
						JSON_PRETTY_PRINT
					),
					'info'
				);
			}

			// Create order + subscription
			$membership_service = new MembershipService();
			$response           = $membership_service->create_membership_order_and_subscription( $data );

			// PaymentGatewayLogging — order creation + free activation
			if ( $response['status'] && class_exists( 'WPEverest\URMembership\Admin\Services\PaymentGatewayLogging' ) ) {
				$initial_status = ( 'free' === $payment_gateway ) ? 'active' : 'pending';
				PaymentGatewayLogging::log_general(
					$payment_gateway,
					sprintf( ' [Member ID #%s] Order and subscription created - Status: %s', $member_id, $initial_status ) . "\n" . wp_json_encode(
						array(
							'event_type'      => 'status_change',
							'member_id'       => $member_id,
							'subscription_id' => $response['subscription_id'] ?? 'N/A',
							'transaction_id'  => $response['transaction_id'] ?? 'N/A',
							'status'          => $initial_status,
							'membership_id'   => $data['membership'] ?? 'N/A',
							'membership_type' => $membership_type,
						),
						JSON_PRETTY_PRINT
					),
					'info'
				);
				if ( 'free' === $payment_gateway ) {
					PaymentGatewayLogging::log_general(
						$payment_gateway,
						sprintf( ' [Member ID #%s] Subscription activated successfully.', $member_id ) . "\n" . wp_json_encode(
							array(
								'member_id'       => $member_id,
								'subscription_id' => $response['subscription_id'] ?? 'N/A',
								'status'          => 'active',
								'payment_method'  => $payment_gateway,
								'membership_type' => $membership_type,
								'auto_activated'  => true,
							),
							JSON_PRETTY_PRINT
						) . "\n  ",
						'info'
					);
				}
			}

			// Set data fields from response
			$transaction_id          = isset( $response['transaction_id'] ) ? $response['transaction_id'] : 0;
			$data['member_id']       = $member_id;
			$data['subscription_id'] = isset( $response['subscription_id'] ) ? $response['subscription_id'] : 0;
			if ( ur_check_module_activation( 'team' ) ) {
				$data['team_id'] = ! empty( $response['team_id'] ) ? $response['team_id'] : 0;
			}
			$data['email']    = $response['member_email'];
			$data['order_id'] = $response['order_id'];

			// Build payment gateway data
			// ur_authorize_net comes from $_POST directly (we are inside the first AJAX, not a second one)
			$pg_data = array();
			if ( 'free' !== $data['payment_method'] && $response['status'] ) {
				$payment_service  = new PaymentService( $data['payment_method'], $data['membership'], $data['email'] );
				$ur_authorize_net = array( 'ur_authorize_net' => ! empty( $_POST['ur_authorize_net'] ) ? (array) $_POST['ur_authorize_net'] : array() );
				$data             = array_merge( $data, $ur_authorize_net );
				$pg_data          = $payment_service->build_response( $data );
				if ( is_wp_error( $pg_data['payment_url'] ?? null ) ) {
					$message = isset( $response['message'] ) ? $response['message'] : esc_html__( 'Sorry! There was an unexpected error while registering the user.', 'user-registration' );
					wp_send_json_error( array( 'message' => $message ) );
				}
			}

			if ( $response['status'] ) {
				// Auto-login for free memberships
				if ( ! empty( $success_params['auto_login'] ) && 'free' === $data['payment_method'] ) {
					$members_service = new MembersService();
					$password        = isset( $data['password'] ) ? $data['password'] : '';
					$logged_in       = $members_service->login_member( $member_id, true, $password );
					if ( ! $logged_in ) {
						wp_send_json_error( array( 'message' => __( 'Invalid User', 'user-registration' ) ) );
					}
				}

				// Send emails
				$email_service = new EmailService();
				$email_service->send_email( $data, 'user_register_user' );
				$email_service->send_email( $data, 'user_register_admin' );

				// Build response_data (same filter as register_member())
				$response_data = apply_filters(
					'user_registration_membership_after_register_member',
					array(
						'member_id'      => absint( $member_id ),
						'transaction_id' => esc_html( $transaction_id ),
						'order_id'       => esc_html( $data['order_id'] ),
						'message'        => esc_html__( 'New member has been successfully created.', 'user-registration' ),
					)
				);
				if ( ur_check_module_activation( 'team' ) ) {
					$response_data['team_id'] = absint( $data['team_id'] );
				}
				if ( 'free' !== $data['payment_method'] ) {
					$response_data['pg_data'] = $pg_data;
				}
				return array_merge( $success_params, $response_data );

			} else {
				$message = isset( $response['message'] ) ? $response['message'] : esc_html__( 'Sorry! There was an unexpected error while registering the user.', 'user-registration' );
				wp_send_json_error( array( 'message' => $message ) );
			}
		}

		public function update_redirect_url_for_membership( $redirect_url, $form_id ) {
			$thank_you_page_id           = get_option( 'user_registration_thank_you_page_id' );
			$login_option                = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_login_options' );
			$redirect_after_registration = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_redirect_after_registration' );

			$form_data = ur_get_form_field_keys( $form_id );

			$keyFound = false;
			foreach ( $form_data as $value ) {
				if ( preg_match( '/^membership_field_.*/', $value ) ) {
					$keyFound = true;
					break;
				}
			}
			if ( ! $keyFound ) {
				return $redirect_url;
			}

			if ( in_array( $redirect_after_registration, array( 'external-url', 'internal-page', 'previous-page' ) ) ) {
				return $redirect_url;
			}
		}

		/**
		 * Includes.
		 */
		public function includes() {
			$this->ajax = new AJAX();
			if ( $this->is_admin() ) {
				$this->admin = new Membership();
				// $this->members = new Members();
			} else {
				// require file.
				$this->frontend = new Frontend();
			}
			new FormFields();
			new EmailSettings();
			new Crons();
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
				'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-membership' ) . '" title="' . esc_attr( __( 'View User Registration & Membership Settings', 'user-registration' ) ) . '">' . __( 'Settings', 'user-registration' ) . '</a>',
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
		 * Rgister Custom Post Type.
		 */
		public function create_membership_post_type() {
			$raw_referer = wp_parse_args( wp_parse_url( wp_get_raw_referer(), PHP_URL_QUERY ) );

			register_post_type(
				'ur_membership',
				apply_filters(
					'user_registration_membership_post_type',
					array(
						'labels'            => array(
							'name'                  => __( 'Memberships', 'user-registration' ),
							'singular_name'         => __( 'Membership', 'user-registration' ),
							'all_items'             => __( 'All Memberships', 'user-registration' ),
							'menu_name'             => _x( 'Memberships', 'Admin menu name', 'user-registration' ),
							'add_new'               => __( 'Add New', 'user-registration' ),
							'add_new_item'          => __( 'Add new', 'user-registration' ),
							'edit'                  => __( 'Edit', 'user-registration' ),
							'edit_item'             => __( 'Edit membership', 'user-registration' ),
							'new_item'              => __( 'New membership', 'user-registration' ),
							'view'                  => __( 'View membership', 'user-registration' ),
							'view_item'             => __( 'View memberships', 'user-registration' ),
							'search_items'          => __( 'Search memberships', 'user-registration' ),
							'not_found'             => __( 'No memberships found', 'user-registration' ),
							'not_found_in_trash'    => __( 'No memberships found in trash', 'user-registration' ),
							'parent'                => __( 'Parent membership', 'user-registration' ),
							'featured_image'        => __( 'Membership image', 'user-registration' ),
							'set_featured_image'    => __( 'Set membership image', 'user-registration' ),
							'remove_featured_image' => __( 'Remove membership image', 'user-registration' ),
							'use_featured_image'    => __( 'Use as membership image', 'user-registration' ),
							'insert_into_item'      => __( 'Insert into membership', 'user-registration' ),
							'uploaded_to_this_item' => __( 'Uploaded to this membership', 'user-registration' ),
							'filter_items_list'     => __( 'Filter membership', 'user-registration' ),
							'items_list_navigation' => __( 'Membership navigation', 'user-registration' ),
							'items_list'            => __( 'Membership list', 'user-registration' ),

						),
						'show_ui'           => true,
						'capability_type'   => 'post',
						'map_meta_cap'      => true,
						'show_in_menu'      => false,
						'hierarchical'      => false,
						'rewrite'           => false,
						'query_var'         => false,
						'show_in_nav_menus' => false,
						'show_in_admin_bar' => false,
						'supports'          => array( 'title' ),
					)
				)
			);
		}

		public function create_membership_groups_post_type() {
			$raw_referer = wp_parse_args( wp_parse_url( wp_get_raw_referer(), PHP_URL_QUERY ) );

			register_post_type(
				'ur_membership_groups',
				apply_filters(
					'user_registration_membership_groups_post_type',
					array(
						'labels'            => array(
							'name'                  => __( 'Membership Groups', 'user-registration' ),
							'singular_name'         => __( 'Membership Group', 'user-registration' ),
							'all_items'             => __( 'All Membership Groups', 'user-registration' ),
							'menu_name'             => _x( 'Membership Groups', 'Admin menu name', 'user-registration' ),
							'add_new'               => __( 'Add New', 'user-registration' ),
							'add_new_item'          => __( 'Add new', 'user-registration' ),
							'edit'                  => __( 'Edit', 'user-registration' ),
							'edit_item'             => __( 'Edit membership group', 'user-registration' ),
							'new_item'              => __( 'New membership group', 'user-registration' ),
							'view'                  => __( 'View membership group', 'user-registration' ),
							'view_item'             => __( 'View memberships group', 'user-registration' ),
							'search_items'          => __( 'Search membership groups', 'user-registration' ),
							'not_found'             => __( 'No membership groups found', 'user-registration' ),
							'not_found_in_trash'    => __( 'No membership groups found in trash', 'user-registration' ),
							'parent'                => __( 'Parent membership group', 'user-registration' ),
							'featured_image'        => __( 'Membership group image', 'user-registration' ),
							'set_featured_image'    => __( 'Set membership group image', 'user-registration' ),
							'remove_featured_image' => __( 'Remove membership group image', 'user-registration' ),
							'use_featured_image'    => __( 'Use as membership group image', 'user-registration' ),
							'insert_into_item'      => __( 'Insert into membership group', 'user-registration' ),
							'uploaded_to_this_item' => __( 'Uploaded to this membership group', 'user-registration' ),
							'filter_items_list'     => __( 'Filter membership group', 'user-registration' ),
							'items_list_navigation' => __( 'Membership group navigation', 'user-registration' ),
							'items_list'            => __( 'Membership group list', 'user-registration' ),

						),
						'show_ui'           => true,
						'capability_type'   => 'post',
						'map_meta_cap'      => true,
						'show_in_menu'      => false,
						'hierarchical'      => false,
						'rewrite'           => false,
						'query_var'         => false,
						'show_in_nav_menus' => false,
						'show_in_admin_bar' => false,
						'supports'          => array( 'title' ),
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
			/**
			 * Filters that holds the list of payment gateways to be stored in ur_membership_payment_gateways option.
			 */
			$membership_payment_gateways = apply_filters(
				'user_registration_membership_payment_gateways',
				array(
					'paypal' => __( 'PayPal', 'user-registration' ),
					'stripe' => __( 'Stripe', 'user-registration' ),
					'bank'   => __( 'Bank', 'user-registration' ),
				)
			);
			update_option(
				'ur_membership_payment_gateways',
				$membership_payment_gateways
			);
		}

		/**
		 * Includes the necessary payment files for the membership plugin if PayPal is activated.
		 *
		 * This function checks if PayPal is activated by calling the `ur_check_module_activation()` function.
		 * If PayPal is activated, it instantiates a new `PaypalActions` object.
		 *
		 * @return void
		 */
		public function include_membership_payment_files() {
			new PaymentGatewaysWebhookActions();
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

		/**
		 * Maybe run database migrations.
		 *
		 * @return void
		 */
		public static function ur_membership_maybe_run_migrations() {
			if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
				return;
			}

			$installed_version = get_option( 'ur_membership_db_version', '0.0.0' );

			if ( version_compare( $installed_version, '1.0.0', '<' ) ) {
				self::on_activation();
				update_option( 'ur_membership_db_version', '1.0.0' );
			}
		}

		public function render_user_membership_details( $user_id, $form_id ) {

			if ( ur_check_module_activation( 'membership' ) === false ) {
				return;
			}

			$members_repository = new MembersRepository();
			$memberships        = $members_repository->get_member_memberships_by_id( $user_id );

			if ( empty( $memberships ) ) {
				return;
			}

			ob_start();
			?>
			<div class="urm-admin-user-content-container">
				<div id="urm-admin-user-content-header" >
					<h3>
						<?php
						if ( count( $memberships ) > 1 ) {
							esc_html_e( 'Membership Details', 'user-registration' );
						} else {
							esc_html_e( 'Membership Detail', 'user-registration' );
						}
						?>
					</h3>
				</div>
				<div class="user-registration-user-form-details">
					<table class="wp-list-table widefat fixed striped users">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plan Type', 'user-registration' ); ?></th>
								<th><?php esc_html_e( 'Amount', 'user-registration' ); ?></th>
								<th><?php esc_html_e( 'Status', 'user-registration' ); ?></th>
								<th><?php esc_html_e( 'Starts On', 'user-registration' ); ?></th>
								<th><?php esc_html_e( 'Expires On', 'user-registration' ); ?></th>
								<th><?php esc_html_e( 'Action', 'user-registration' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $memberships as $membership ) {
								$plan_details = json_decode( $membership['post_content'], true );
								$amount       = $membership['billing_amount'];
								$currencies   = ur_payment_integration_get_currencies();
								$currency     = get_option( 'user_registration_payment_currency', 'USD' );

								$symbol = $currencies[ $currency ]['symbol'];
								$amount = ( ! empty( $currencies[ $currency ]['symbol_pos'] ) && 'left' === $currencies[ $currency ]['symbol_pos'] ) ? $symbol . number_format( $amount, 2 ) : number_format( $amount, 2 ) . $symbol;

								if ( isset( $plan_details['type'] ) && 'subscription' === $plan_details['type'] ) {
									$amount = $amount . ' / ' . $membership['billing_cycle'];
								}
								$expiry_date = 'subscription' === $plan_details['type'] && ! empty( $membership['expiry_date'] ) ? date_i18n( 'Y-m-d', strtotime( $membership['expiry_date'] ) ) : __( 'N/A', 'user-registration' );

								?>
								<tr>
									<td><?php echo esc_html( $membership['post_title'] ); ?></td>
									<td><?php echo esc_html( $amount ); ?></td>
									<td class="status-<?php echo esc_attr( $membership['status'] ); ?>"><?php echo esc_html( ucfirst( $membership['status'] ) ); ?></td>
									<td><?php echo ! empty( $membership['start_date'] ) ? esc_html( date_i18n( 'Y-m-d', strtotime( $membership['start_date'] ) ) ) : __( 'N/A', 'user-registration' ); ?></td>
									<td><?php echo esc_html( $expiry_date ); ?></td>
									<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-subscriptions&action=edit&id=' . ( $membership['subscription_id'] ?? 0 ) ) ); ?>"><?php esc_html_e( 'View', 'user-registration' ); ?></a></td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<?php

			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
endif;
