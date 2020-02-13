<?php
/**
 * Plugin Updater
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistration/Admin
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_AddOn_Updater', false ) ) {
	include_once dirname( __FILE__ ) . '/admin/updater/class-ur-addon-updater.php';
}

if ( ! class_exists( 'UR_Plugin_Updates', false ) ) {
	include_once dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updates.php';
}

/**
 * UR_Plugin_Updater Class.
 */
class UR_Plugin_Updater extends UR_Plugin_Updates {

	/**
	 * Plugin File.
	 *
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	private $plugin_name = '';

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * Plugins data.
	 *
	 * @var array of strings
	 */
	private $plugin_data = array();

	/**
	 * Validation errors.
	 *
	 * @var array of strings
	 */
	private $errors = array();

	/**
	 * Plugin Api Key.
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_updates( UR_PLUGIN_FILE );
	}

	/**
	 * Init the updater.
	 */
	public function init_updates( $_plugin_file ) {
		$this->plugin_file = $_plugin_file;
		$this->plugin_slug = str_replace( '.php', '', basename( $this->plugin_file ) );
		$this->plugin_name = basename( dirname( $this->plugin_file ) ) . '/' . $this->plugin_slug . '.php';

		register_activation_hook( $this->plugin_name, array( $this, 'plugin_activation' ), 10 );
		register_deactivation_hook( $this->plugin_name, array( $this, 'plugin_deactivation' ), 10 );

		add_filter( 'block_local_requests', '__return_false' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Include required files.
		include_once dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updater-api.php';
	}

	/**
	 * Run on admin init.
	 */
	public function admin_init() {
		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );

		$this->api_key     = get_option( $this->plugin_slug . '_license_key' );
		$this->plugin_data = get_plugin_data( $this->plugin_file );

		// Check to make sure we've RP extensions and plugin update capability.
		$extensions = $this->get_plugins_with_header( self::VERSION_TESTED_HEADER );
		if ( ! empty( $extensions ) && current_user_can( 'update_plugins' ) ) {
			$this->plugin_requests();
			$this->plugin_license_view();
		}
	}

	/**
	 * Process plugin requests.
	 */
	private function plugin_requests() {
		if ( ! empty( $_POST[ $this->plugin_slug . '_license_key' ] ) ) {
			$this->activate_license_request();
		} elseif ( ! empty( $_GET[ $this->plugin_slug . '_deactivate_license' ] ) ) {
			$this->deactivate_license_request();
		} elseif ( ! empty( $_GET[ 'dismiss-' . sanitize_title( $this->plugin_slug ) ] ) ) {
			update_option( $this->plugin_slug . '_hide_key_notice', 1 );
		} elseif ( ! empty( $_GET['activated_license'] ) && $_GET['activated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'activated_key_notice' ) );
		} elseif ( ! empty( $_GET['deactivated_license'] ) && $_GET['deactivated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'deactivated_key_notice' ) );
		}
	}

	/**
	 * Activate a license request.
	 */
	private function activate_license_request() {
		$license_key = sanitize_text_field( $_POST[ $this->plugin_slug . '_license_key' ] );

		if ( $this->activate_license( $license_key ) ) {
			wp_redirect( remove_query_arg( array( 'deactivated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'activated_license', $this->plugin_slug ) ) );
			exit;
		} else {
			wp_redirect( remove_query_arg( array( 'activated_license', 'deactivated_license', $this->plugin_slug . '_deactivate_license' ) ) );
			exit;
		}
	}

	/**
	 * Deactivate a license request
	 */
	private function deactivate_license_request() {
		$this->deactivate_license();
		wp_redirect( remove_query_arg( array( 'activated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'deactivated_license', $this->plugin_slug ) ) );
		exit;
	}

	/**
	 * Display plugin license view.
	 */
	private function plugin_license_view() {
		if ( ! $this->api_key ) {
			add_action( 'after_plugin_row', array( $this, 'plugin_license_form' ) );
			$this->add_notice( array( $this, 'key_notice' ) );
		} else {
			add_filter( 'plugin_action_links_' . $this->plugin_name, array( $this, 'plugin_action_links' ) );
		}

		add_action( 'admin_notices', array( $this, 'error_notices' ) );
	}

	/**
	 * Add notices
	 */
	private function add_notice( $callback ) {
		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Add an error message
	 *
	 * @param string $message Your error message
	 * @param string $type    Type of error message
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Load errors from option
	 */
	public function load_errors() {
		$this->errors = get_option( $this->plugin_slug . '_errors', array() );
	}

	/**
	 * Store errors in option
	 */
	public function store_errors() {
		if ( sizeof( $this->errors ) > 0 ) {
			update_option( $this->plugin_slug . '_errors', $this->errors );
		} else {
			delete_option( $this->plugin_slug . '_errors' );
		}
	}

	/**
	 * Output errors
	 */
	public function error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				include dirname( __FILE__ ) . '/admin/views/html-notice-error.php';
				if ( $key !== 'invalid_key' && did_action( 'all_admin_notices' ) ) {
					unset( $this->errors[ $key ] );
				}
			}
		}
	}

	/**
	 * Ran on plugin-activation.
	 */
	public function plugin_activation() {
		delete_option( $this->plugin_slug . '_hide_key_notice' );
	}

	/**
	 * Ran on plugin-deactivation.
	 */
	public function plugin_deactivation() {
		delete_option( 'user_registration_activated' );
		$this->deactivate_license();
	}

	/**
	 * Show the input form for the license key.
	 */
	public function plugin_license_form( $plugin_file ) {
		if ( strtolower( basename( dirname( $plugin_file ) ) ) === strtolower( $this->plugin_slug ) ) {
			include_once dirname( __FILE__ ) . '/admin/views/html-license-form.php';
		}
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'deactivate_license' => '<a href="' . remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( $this->plugin_slug . '_deactivate_license', 1 ) ) . '" class="deactivate-license" style="color: #a00;" title="' . esc_attr( __( 'Deactivate License Key', 'user-registration' ) ) . '">' . __( 'Deactivate License', 'user-registration' ) . '</a>',
		);

		return array_merge( $actions, $new_actions );
	}

	/**
	 * Try to activate a license.
	 */
	public function activate_license( $license_key ) {
		try {

			if ( empty( $license_key ) ) {
				throw new Exception( 'Please enter your license key' );
			}

			$activate_results = json_decode(
				UR_Updater_Key_API::activate(
					array(
						'license' => $license_key,
					)
				)
			);

			// Update activate results.
			update_option( $this->plugin_slug . '_license_active', $activate_results );

			if ( ! empty( $activate_results ) && is_object( $activate_results ) ) {

				if ( isset( $activate_results->error_code ) ) {
					throw new Exception( $activate_results->error );

				} elseif ( false === $activate_results->success ) {
					switch ( $activate_results->error ) {
						case 'expired':
							$error_msg = sprintf( __( 'The provided license key expired on %1$s. Please <a href="%2$s" target="_blank">renew your license key</a>.', 'user-registration' ), date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ), 'https://wpeverest.com/checkout/?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired' );
							break;

						case 'revoked':
							$error_msg = sprintf( __( 'The provided license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'user-registration' ), 'https://wpeverest.com/contact?utm_campaign=admin&utm_source=licenses&utm_medium=revoked' );
							break;

						case 'missing':
							$error_msg = sprintf( __( 'The provided license is invalid. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'user-registration' ), 'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing' );
							break;

						case 'invalid':
						case 'site_inactive':
							$error_msg = sprintf( __( 'The provided license is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'user-registration' ), 'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing' );
							break;

						case 'invalid_item_id':
						case 'item_name_mismatch':
							$error_msg = sprintf( __( 'This appears to be an invalid license key for <strong>%1$s</strong>.', 'user-registration' ), $this->plugin_data['Name'] );
							break;

						case 'no_activations_left':
							$error_msg = sprintf( __( 'The provided license key has reached its activation limit. Please <a href="%1$s" target="_blank">View possible upgrades</a> now.', 'user-registration' ), 'https://wpeverest.com/my-account/' );
							break;

						case 'license_not_activable':
							$error_msg = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'user-registration' );
							break;

						default:
							$error_msg = sprintf( __( 'The provided license key could not be found. Please <a href="%s" target="_blank">contact support</a> for more information.', 'user-registration' ), 'https://wpeverest.com/contact/' );
							break;
					}

					throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $error_msg ) );

				} elseif ( 'valid' === $activate_results->license ) {
					$this->api_key = $license_key;
					$this->errors  = array();

					update_option( $this->plugin_slug . '_license_key', $this->api_key );
					delete_option( $this->plugin_slug . '_errors' );

					return true;
				}

				throw new Exception( 'License could not activate. Please contact support.' );
			} else {
				throw new Exception( 'Connection failed to the License Key API server - possible server issue.' );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Deactivate a license.
	 */
	public function deactivate_license() {
		$reset = UR_Updater_Key_API::deactivate(
			array(
				'license' => $this->api_key,
			)
		);

		delete_option( $this->plugin_slug . '_errors' );
		delete_option( $this->plugin_slug . '_license_key' );
		delete_option( $this->plugin_slug . '_license_active' );

		// Reset huh?
		$this->errors  = array();
		$this->api_key = '';
	}

	/**
	 * Show a notice prompting the user to update.
	 */
	public function key_notice() {
		if ( sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			include dirname( __FILE__ ) . '/admin/views/html-notice-key-unvalidated.php';
		}
	}

	/**
	 * Activation success notice.
	 */
	public function activated_key_notice() {
		include dirname( __FILE__ ) . '/admin/views/html-notice-key-activated.php';
	}

	/**
	 * Dectivation success notice.
	 */
	public function deactivated_key_notice() {
		include dirname( __FILE__ ) . '/admin/views/html-notice-key-deactivated.php';
	}
}

new UR_Plugin_Updater();
